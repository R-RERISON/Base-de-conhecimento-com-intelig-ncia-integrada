#!/usr/bin/env python3
"""Deterministic local builder for SPEC-007 PX-730 homologation package.

Reuses the accepted P-650 production pruning contract, then adds PX-730
static regression and PHP lint checks against the distributed ZIP.
It does not mutate the source checkout or authorize public cutover.
"""

from __future__ import annotations

import importlib.util
import json
import pathlib
import shutil
import subprocess
import sys
import tempfile
import zipfile

ROOT = pathlib.Path(__file__).resolve().parents[3]
P650_PATH = ROOT / "tools" / "homologation" / "spec006" / "build-p650-production.py"
DIST = ROOT / "dist"
PACKAGE_ROOT = "base-conhecimento-inteligencia-integrada"
VERSION = "0.6.0-dev"
BUILD = "px730.1"
SHELL_VERSION = "public-shell-v1.0.0"
LIVE_SEARCH_CONTRACT = "px730-live-search-v1.0.0"
ZIP_PATH = DIST / f"base-conhecimento-inteligencia-integrada-{VERSION}-{BUILD}.zip"
MANIFEST_PATH = DIST / f"base-conhecimento-inteligencia-integrada-{VERSION}-{BUILD}.manifest.json"
VALIDATION_PATH = DIST / "px730-package-validation.json"
STATIC_TEST = ROOT / "tests" / "unit" / "spec007-px730-live-search-contract.php"

spec = importlib.util.spec_from_file_location("bdc_p650_builder", P650_PATH)
if spec is None or spec.loader is None:
    raise SystemExit("Unable to load accepted P-650 builder.")
p650 = importlib.util.module_from_spec(spec)
spec.loader.exec_module(p650)


def run_static_contract() -> dict:
    php = shutil.which("php")
    if not php:
        raise SystemExit("PHP executable is required for PX-730 static contract.")
    proc = subprocess.run(
        [php, str(STATIC_TEST)],
        cwd=ROOT,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        text=True,
        check=False,
    )
    return {
        "command": [php, str(STATIC_TEST)],
        "exit_code": proc.returncode,
        "output": proc.stdout,
        "pass": proc.returncode == 0,
    }


def build_zip_stored(files, output: pathlib.Path, engineering_flags: set[str]) -> None:
    output.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(output, "w", compression=zipfile.ZIP_STORED) as archive:
        for source in files:
            rel = source.relative_to(p650.SOURCE).as_posix()
            data = source.read_bytes()
            if rel == p650.BOOTSTRAP:
                data = p650.transform_bootstrap(data, engineering_flags)
            info = zipfile.ZipInfo(f"{PACKAGE_ROOT}/{rel}", p650.FIXED_TIME)
            info.compress_type = zipfile.ZIP_STORED
            info.external_attr = 0o100644 << 16
            archive.writestr(info, data)


def lint_distribution(zip_path: pathlib.Path) -> dict:
    php = shutil.which("php")
    if not php:
        raise SystemExit("PHP executable is required for PX-730 package lint.")
    failures = []
    checked = 0
    with tempfile.TemporaryDirectory(prefix="bdc-px730-lint-") as temp:
        temp_path = pathlib.Path(temp)
        with zipfile.ZipFile(zip_path, "r") as archive:
            archive.extractall(temp_path)
        for path in sorted((temp_path / PACKAGE_ROOT).rglob("*.php")):
            checked += 1
            proc = subprocess.run(
                [php, "-l", str(path)],
                stdout=subprocess.PIPE,
                stderr=subprocess.STDOUT,
                text=True,
                check=False,
            )
            if proc.returncode != 0:
                failures.append({"path": path.relative_to(temp_path).as_posix(), "output": proc.stdout})
    return {"checked": checked, "failures": failures, "pass": not failures}


def main() -> int:
    static = run_static_contract()
    if not static["pass"]:
        raise SystemExit(json.dumps({"static_contract": static}, ensure_ascii=False, indent=2))

    engineering_files, engineering_flags = p650.engineering_contract()
    exclusions = set(engineering_files) | {p650.ENGINEERING_LOADER_REL} | p650.LEGACY_ENGINEERING_ORPHANS
    files = p650.source_files(exclusions)

    with tempfile.TemporaryDirectory(prefix="bdc-px730-") as temp:
        first = pathlib.Path(temp) / "first.zip"
        second = pathlib.Path(temp) / "second.zip"
        build_zip_stored(files, first, engineering_flags)
        build_zip_stored(files, second, engineering_flags)

        deterministic = first.read_bytes() == second.read_bytes()
        if not deterministic:
            raise SystemExit("PX-730 package build is not deterministic.")

        inspection = p650.inspect_zip(first, engineering_files, engineering_flags)
        if not inspection["pass"]:
            raise SystemExit(json.dumps({"inspection": inspection}, ensure_ascii=False, indent=2))

        lint = lint_distribution(first)
        if not lint["pass"]:
            raise SystemExit(json.dumps({"php_lint": lint}, ensure_ascii=False, indent=2))

        DIST.mkdir(parents=True, exist_ok=True)
        shutil.copy2(first, ZIP_PATH)

    source_commit = p650.git_revision("HEAD")
    plugin_tree_sha = p650.git_revision("HEAD:plugin/base-conhecimento-inteligencia-integrada")
    if not source_commit or not plugin_tree_sha:
        raise SystemExit("PX-730 requires Git provenance in a local checkout.")

    manifest = {
        "schema_version": "1.0.0",
        "gate": "PX-730",
        "source_commit": source_commit,
        "plugin_tree_sha": plugin_tree_sha,
        "purpose": "SPEC-007 PX-730 Live Search homologation package",
        "homologation_candidate": True,
        "version": VERSION,
        "build": BUILD,
        "shell_version": SHELL_VERSION,
        "live_search_contract": LIVE_SEARCH_CONTRACT,
        "root": PACKAGE_ROOT,
        "zip_sha256": p650.sha256_file(ZIP_PATH),
        "file_count": inspection["file_count"],
        "files": p650.zip_manifest(ZIP_PATH),
    }
    MANIFEST_PATH.write_text(json.dumps(manifest, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

    report = {
        "schema_version": "1.0.0",
        "gate": "PX-730",
        "phase": "PACKAGE_BUILD",
        "status": "PASS",
        "execution_mode": "LOCAL_ONLY",
        "homologation_candidate": True,
        "version": VERSION,
        "build": BUILD,
        "shell_version": SHELL_VERSION,
        "live_search_contract": LIVE_SEARCH_CONTRACT,
        "zip": ZIP_PATH.name,
        "zip_sha256": manifest["zip_sha256"],
        "manifest": MANIFEST_PATH.name,
        "manifest_sha256": p650.sha256_file(MANIFEST_PATH),
        "deterministic_equal": True,
        "static_contract": static,
        "php_lint": lint,
        "inspection": inspection,
        "excluded_engineering_files": sorted(exclusions),
        "engineering_flags_removed": sorted(engineering_flags),
        "invariants": {
            "source_checkout_modified": False,
            "content_mutation": False,
            "theme_mutation": False,
            "page_on_front_mutation": False,
            "ranker_schema_mutation": False,
            "cutover_authorized": False,
            "retirement_authorized": False,
        },
        "next_gate": "PX730_WORDPRESS_CANDIDATE_SMOKE",
    }
    VALIDATION_PATH.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(report, ensure_ascii=False, indent=2))
    return 0


if __name__ == "__main__":
    sys.exit(main())
