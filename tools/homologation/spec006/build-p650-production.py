#!/usr/bin/env python3
"""Builder local determinístico do pacote de produção P-650.

Não executa CI remoto. Não altera o source checkout.
Remove somente engenharia explicitamente declarada e gera bootstrap distribuível.
"""

from __future__ import annotations

import hashlib
import json
import pathlib
import re
import shutil
import subprocess
import sys
import tempfile
import zipfile

ROOT = pathlib.Path(__file__).resolve().parents[3]
SOURCE = ROOT / "plugin" / "base-conhecimento-inteligencia-integrada"
DIST = ROOT / "dist"
PACKAGE_ROOT = "base-conhecimento-inteligencia-integrada"
VERSION = "0.6.0-dev"
BUILD = "p650.3"
ZIP_PATH = DIST / f"base-conhecimento-inteligencia-integrada-{VERSION}-{BUILD}.zip"
MANIFEST_PATH = DIST / f"base-conhecimento-inteligencia-integrada-{VERSION}-{BUILD}.manifest.json"
VALIDATION_PATH = DIST / "p650-package-validation.json"
FIXED_TIME = (2026, 9, 29, 0, 0, 0)

BOOTSTRAP = "base-conhecimento-inteligencia-integrada.php"
ENGINEERING_LOADER = SOURCE / "includes" / "class-engineering-module-loader.php"
ENGINEERING_LOADER_REL = "includes/class-engineering-module-loader.php"
LEGACY_ENGINEERING_ORPHANS = {"includes/class-real-content-acceptance.php"}

REQUIRED_DIST_FILES = {
    "LICENSE",
    "CHANGELOG.md",
    "UPGRADE.md",
    "SECURITY.md",
    "CONTRIBUTING.md",
    "readme.txt",
    BOOTSTRAP,
}

ENGINEERING_FILE_PATTERN = re.compile(r"'(includes/[^']+\.php)'")
ENGINEERING_FLAG_PATTERN = re.compile(r"self::add\(\s*\$definitions,\s*'([^']+)'", re.MULTILINE)


def sha256_bytes(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def sha256_file(path: pathlib.Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def git_revision(spec: str) -> str | None:
    git = shutil.which("git")
    if not git or not (ROOT / ".git").exists():
        return None
    proc = subprocess.run(
        [git, "rev-parse", spec],
        cwd=ROOT,
        stdout=subprocess.PIPE,
        stderr=subprocess.DEVNULL,
        text=True,
        check=False,
    )
    return proc.stdout.strip() if proc.returncode == 0 else None


def engineering_contract() -> tuple[set[str], set[str]]:
    text = ENGINEERING_LOADER.read_text(encoding="utf-8")
    files = set(ENGINEERING_FILE_PATTERN.findall(text))
    flags = set(ENGINEERING_FLAG_PATTERN.findall(text))
    if not files or not flags:
        raise SystemExit("Não foi possível materializar o contrato explícito de engenharia.")
    return files, flags


def transform_bootstrap(data: bytes, engineering_flags: set[str]) -> bytes:
    text = data.decode("utf-8")
    lines = text.splitlines()

    kept: list[str] = []
    for line in lines:
        stripped = line.strip()
        if stripped == "require_once BDC_KB_DIR . 'includes/class-engineering-module-loader.php';":
            continue
        if "Engineering_Module_Loader::load_enabled();" in stripped:
            continue
        if "Engineering_Module_Loader::register_enabled();" in stripped:
            continue

        remove_flag = False
        for flag in engineering_flags:
            if stripped.startswith(f"define( '{flag}',"):
                remove_flag = True
                break
        if remove_flag:
            continue

        kept.append(line)

    transformed = "\n".join(kept) + "\n"

    forbidden = [
        "class-engineering-module-loader.php",
        "Engineering_Module_Loader::load_enabled()",
        "Engineering_Module_Loader::register_enabled()",
    ]
    forbidden.extend(sorted(engineering_flags))
    remaining = [token for token in forbidden if token in transformed]
    if remaining:
        raise SystemExit(
            json.dumps(
                {"bootstrap_forbidden_tokens_remaining": remaining},
                ensure_ascii=False,
                indent=2,
            )
        )

    required = [
        "class-core-runtime-loader.php",
        "class-runtime-module-registry.php",
        "class-plugin.php",
        "Runtime_Module_Registry::load( 'search' )",
        "Runtime_Module_Registry::load( 'public_experience_preview' )",
        "Runtime_Module_Registry::load( 'word_cloud' )",
        "Runtime_Module_Registry::register( 'search' )",
        "Runtime_Module_Registry::register( 'public_experience_preview' )",
        "Runtime_Module_Registry::register( 'word_cloud' )",
    ]
    missing = [token for token in required if token not in transformed]
    if missing:
        raise SystemExit(
            json.dumps(
                {"bootstrap_required_tokens_missing": missing},
                ensure_ascii=False,
                indent=2,
            )
        )

    return transformed.encode("utf-8")


def source_files(exclusions: set[str]) -> list[pathlib.Path]:
    files: list[pathlib.Path] = []
    for path in SOURCE.rglob("*"):
        if not path.is_file():
            continue
        rel = path.relative_to(SOURCE).as_posix()
        if rel in exclusions:
            continue
        if any(part in {".git", "__pycache__", "vendor", "tests", "tools", "evidence", "specs"} for part in path.relative_to(SOURCE).parts):
            continue
        if path.name in {".DS_Store", "Thumbs.db"}:
            continue
        files.append(path)
    return sorted(files, key=lambda p: p.relative_to(SOURCE).as_posix())


def build_zip(files: list[pathlib.Path], output: pathlib.Path, engineering_flags: set[str]) -> None:
    output.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(output, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for source in files:
            rel = source.relative_to(SOURCE).as_posix()
            data = source.read_bytes()
            if rel == BOOTSTRAP:
                data = transform_bootstrap(data, engineering_flags)

            info = zipfile.ZipInfo(f"{PACKAGE_ROOT}/{rel}", FIXED_TIME)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o100644 << 16
            archive.writestr(info, data)


def inspect_zip(zip_path: pathlib.Path, engineering_files: set[str], engineering_flags: set[str]) -> dict:
    with zipfile.ZipFile(zip_path, "r") as archive:
        names = sorted(archive.namelist())
        expected_prefix = PACKAGE_ROOT + "/"
        bad_roots = [name for name in names if not name.startswith(expected_prefix)]
        rel_names = [name[len(expected_prefix):] for name in names]

        forbidden_files = sorted(
            rel for rel in rel_names
            if rel == ENGINEERING_LOADER_REL or rel in engineering_files
        )

        missing_required = sorted(REQUIRED_DIST_FILES - set(rel_names))

        bootstrap = archive.read(expected_prefix + BOOTSTRAP).decode("utf-8")
        forbidden_tokens = [
            "Engineering_Module_Loader",
            ENGINEERING_LOADER_REL,
            *sorted(engineering_flags),
        ]
        bootstrap_forbidden = [token for token in forbidden_tokens if token in bootstrap]

        forbidden_paths = [
            rel for rel in rel_names
            if rel.startswith(("specs/", "evidence/", "tests/", "tools/", "vendor/"))
        ]

        return {
            "single_root": not bad_roots,
            "bad_roots": bad_roots,
            "missing_required_distribution_files": missing_required,
            "engineering_files_present": forbidden_files,
            "bootstrap_forbidden_tokens": bootstrap_forbidden,
            "forbidden_paths": forbidden_paths,
            "file_count": len(rel_names),
            "pass": not (
                bad_roots
                or missing_required
                or forbidden_files
                or bootstrap_forbidden
                or forbidden_paths
            ),
        }


def zip_manifest(zip_path: pathlib.Path) -> list[dict]:
    rows: list[dict] = []
    with zipfile.ZipFile(zip_path, "r") as archive:
        for name in sorted(archive.namelist()):
            data = archive.read(name)
            rows.append({
                "path": name,
                "bytes": len(data),
                "sha256": sha256_bytes(data),
            })
    return rows


def main() -> int:
    engineering_files, engineering_flags = engineering_contract()
    exclusions = set(engineering_files) | {ENGINEERING_LOADER_REL} | LEGACY_ENGINEERING_ORPHANS
    files = source_files(exclusions)

    with tempfile.TemporaryDirectory(prefix="bdc-p650-") as temp:
        first = pathlib.Path(temp) / "first.zip"
        second = pathlib.Path(temp) / "second.zip"
        build_zip(files, first, engineering_flags)
        build_zip(files, second, engineering_flags)

        deterministic = first.read_bytes() == second.read_bytes()
        if not deterministic:
            raise SystemExit("P-650 package build is not deterministic.")

        inspection = inspect_zip(first, engineering_files, engineering_flags)
        if not inspection["pass"]:
            raise SystemExit(json.dumps({"inspection": inspection}, ensure_ascii=False, indent=2))

        DIST.mkdir(parents=True, exist_ok=True)
        shutil.copy2(first, ZIP_PATH)

    source_commit = git_revision("HEAD")
    plugin_tree_sha = git_revision("HEAD:plugin/base-conhecimento-inteligencia-integrada")
    if not source_commit or not plugin_tree_sha:
        raise SystemExit("P-650 requires Git provenance in a local checkout.")

    manifest = {
        "schema_version": "1.0.0",
        "source_commit": source_commit,
        "plugin_tree_sha": plugin_tree_sha,
        "purpose": "SPEC-006 P-650 production package candidate",
        "production_package_candidate": True,
        "version": VERSION,
        "build": BUILD,
        "root": PACKAGE_ROOT,
        "zip_sha256": sha256_file(ZIP_PATH),
        "file_count": inspection["file_count"],
        "excluded_engineering_file_count": len(exclusions),
        "files": zip_manifest(ZIP_PATH),
    }
    MANIFEST_PATH.write_text(
        json.dumps(manifest, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )

    report = {
        "schema_version": "1.0.0",
        "gate": "P-650",
        "phase": "PACKAGE_BUILD",
        "status": "PASS",
        "execution_mode": "LOCAL_ONLY",
        "production_package_candidate": True,
        "version": VERSION,
        "build": BUILD,
        "zip": ZIP_PATH.name,
        "zip_sha256": manifest["zip_sha256"],
        "manifest": MANIFEST_PATH.name,
        "manifest_sha256": sha256_file(MANIFEST_PATH),
        "deterministic_equal": True,
        "inspection": inspection,
        "excluded_engineering_files": sorted(exclusions),
        "engineering_flags_removed": sorted(engineering_flags),
        "invariants": {
            "source_checkout_modified": False,
            "content_mutation": False,
            "data_migration": False,
            "cutover_authorized": False,
            "retirement_authorized": False,
        },
        "next_gate": "P650_LOCAL_PACKAGE_QUALITY",
    }
    VALIDATION_PATH.write_text(
        json.dumps(report, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    print(json.dumps(report, ensure_ascii=False, indent=2))
    return 0


if __name__ == "__main__":
    sys.exit(main())
