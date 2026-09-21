#!/usr/bin/env python3
"""Deterministic installable ZIP builder for the BDC Knowledge Base plugin.

The source tree may keep disabled engineering evidence. The installable artifact
contains only:
- the plugin bootstrap;
- unconditional bootstrap dependencies;
- dependencies behind currently-true build flags;
- assets/css and assets/js files;
- explicit runtime resource files required by active capabilities.

No WordPress bootstrap, DB access, network access, or editorial write occurs.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import re
import zipfile
from pathlib import Path

PLUGIN_REL = Path("plugin/base-conhecimento-inteligencia-integrada")
BOOTSTRAP = "base-conhecimento-inteligencia-integrada.php"
ZIP_ROOT = "base-conhecimento-inteligencia-integrada"
FIXED_ZIP_TIME = (1980, 1, 1, 0, 0, 0)

GOLDEN_RUNTIME_RESOURCES = (
    "resources/search/golden-relevance-v1.0.0.json",
    "resources/search/technical-challenge-v1.0.0.json",
)

PUBLIC_PREVIEW_RUNTIME_FILES = (
    "templates/public-home-preview.php",
    "templates/public-article-preview.php",
)

BASE_RUNTIME_FILES = (
    "uninstall.php",
)

CONDITIONAL_RUNTIME_RESOURCES = {
    "BDC_KB_SPEC005_G550_GOLDEN_RUNNER_BUILD": GOLDEN_RUNTIME_RESOURCES,
    "BDC_KB_SPEC005_G585_ASI_INDEPENDENCE_BUILD": GOLDEN_RUNTIME_RESOURCES,
    "BDC_KB_SPEC005_G590_SECTION_BUILD": GOLDEN_RUNTIME_RESOURCES,
    "BDC_KB_UX004_H030_TECHNICAL_BUILD": GOLDEN_RUNTIME_RESOURCES,
    "BDC_KB_PUBLIC_EXPERIENCE_PREVIEW_BUILD": PUBLIC_PREVIEW_RUNTIME_FILES,
}


def sha256_bytes(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def flag_value(source: str, name: str):
    m = re.search(
        r"define\(\s*['\"]" + re.escape(name) + r"['\"]\s*,\s*(true|false)\s*\)",
        source,
        re.I,
    )
    return None if not m else m.group(1).lower() == "true"


def all_requires(source: str) -> list[str]:
    return re.findall(r"require_once BDC_KB_DIR \. '([^']+)'", source)


def unconditional_requires(source: str) -> list[str]:
    prefix = source.split("if ( defined( 'BDC_KB_SPEC004_PROFILE_BUILD' )", 1)[0]
    return all_requires(prefix)


def conditional_requires(source: str) -> dict[str, list[str]]:
    out: dict[str, list[str]] = {}
    pattern = re.compile(
        r"if\s*\(\s*defined\(\s*'([^']+)'\s*\)\s*&&\s*\1\s*\)\s*\{"
        r"(.*?)\}",
        re.S,
    )
    for match in pattern.finditer(source):
        paths = all_requires(match.group(2))
        if paths:
            out.setdefault(match.group(1), []).extend(paths)
    return out


def add_tree_files(plugin: Path, rel_dir: str, target: set[str]) -> None:
    base = plugin / rel_dir
    if not base.is_dir():
        return
    for path in base.rglob("*"):
        if path.is_file():
            target.add(str(path.relative_to(plugin)).replace("\\", "/"))


def write_deterministic_zip(plugin: Path, output: Path, files: list[str]) -> dict[str, str]:
    output.parent.mkdir(parents=True, exist_ok=True)
    if output.exists():
        output.unlink()

    hashes: dict[str, str] = {}
    with zipfile.ZipFile(output, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as zf:
        for rel in files:
            data = (plugin / rel).read_bytes()
            hashes[rel] = sha256_bytes(data)
            info = zipfile.ZipInfo(f"{ZIP_ROOT}/{rel}", FIXED_ZIP_TIME)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = (0o100644 & 0xFFFF) << 16
            info.create_system = 3
            zf.writestr(info, data, compress_type=zipfile.ZIP_DEFLATED, compresslevel=9)
    return hashes


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--root", default=".", help="repository root")
    ap.add_argument("--output", required=True, help="output ZIP")
    ap.add_argument("--manifest", help="sidecar manifest JSON")
    args = ap.parse_args()

    repo = Path(args.root).resolve()
    plugin = repo / PLUGIN_REL
    bootstrap = plugin / BOOTSTRAP
    output = Path(args.output).resolve()

    if not bootstrap.is_file():
        raise SystemExit("plugin bootstrap not found")

    source = bootstrap.read_text(encoding="utf-8")
    files: set[str] = {BOOTSTRAP}
    for rel in BASE_RUNTIME_FILES:
        if (plugin / rel).is_file():
            files.add(rel)

    uncond = unconditional_requires(source)
    cond = conditional_requires(source)
    active_flags: dict[str, bool | None] = {}

    for rel in uncond:
        files.add(rel)

    for flag, paths in cond.items():
        value = flag_value(source, flag)
        active_flags[flag] = value
        if value is True:
            files.update(paths)

    active_runtime_resources: dict[str, list[str]] = {}
    for flag, paths in CONDITIONAL_RUNTIME_RESOURCES.items():
        value = flag_value(source, flag)
        active_flags.setdefault(flag, value)
        if value is True:
            files.update(paths)
            active_runtime_resources[flag] = list(paths)

    add_tree_files(plugin, "assets/css", files)
    add_tree_files(plugin, "assets/js", files)

    missing = sorted(rel for rel in files if not (plugin / rel).is_file())
    if missing:
        raise SystemExit("missing active file(s): " + ", ".join(missing))

    files_sorted = sorted(files)
    file_hashes = write_deterministic_zip(plugin, output, files_sorted)
    zip_hash = sha256_bytes(output.read_bytes())

    version_match = re.search(
        r"define\(\s*'BDC_KB_VERSION'\s*,\s*'([^']+)'\s*\)", source
    )
    build_match = re.search(
        r"define\(\s*'BDC_KB_BUILD_ID'\s*,\s*'([^']+)'\s*\)", source
    )
    source_files = sorted(
        str(path.relative_to(plugin)).replace("\\", "/")
        for path in plugin.rglob("*")
        if path.is_file()
    )
    excluded = sorted(set(source_files) - set(files_sorted))

    manifest = {
        "schema_version": "1.0.0",
        "builder": "tools/t100e/build_release.py",
        "plugin_version": version_match.group(1) if version_match else "",
        "build_id": build_match.group(1) if build_match else "",
        "zip_root": ZIP_ROOT,
        "zip_sha256": zip_hash,
        "active_conditional_flags": active_flags,
        "unconditional_requires": uncond,
        "active_runtime_resources": active_runtime_resources,
        "included_file_count": len(files_sorted),
        "included_files": file_hashes,
        "excluded_source_file_count": len(excluded),
        "excluded_source_files": excluded,
    }

    if args.manifest:
        manifest_path = Path(args.manifest).resolve()
        manifest_path.parent.mkdir(parents=True, exist_ok=True)
        manifest_path.write_text(
            json.dumps(manifest, ensure_ascii=False, indent=2, sort_keys=True) + "\n",
            encoding="utf-8",
        )

    print(json.dumps(manifest, ensure_ascii=False, indent=2, sort_keys=True))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
