#!/usr/bin/env python3
"""Deterministic homologation builder for SPEC-006 / P-630.

This is intentionally a homologation package, not the P-650 production package.
"""

from __future__ import annotations

import hashlib
import json
import pathlib
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
BUILD = "p630.1"
ZIP_PATH = DIST / f"base-conhecimento-inteligencia-integrada-{VERSION}-{BUILD}.zip"
MANIFEST_PATH = DIST / f"base-conhecimento-inteligencia-integrada-{VERSION}-{BUILD}.manifest.json"
VALIDATION_PATH = DIST / "p630-local-package-validation.json"
FIXED_TIME = (2026, 9, 29, 0, 0, 0)

EXCLUDE_NAMES = {".DS_Store", "Thumbs.db"}
EXCLUDE_PARTS = {".git", "__pycache__"}


def sha256_bytes(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def sha256_file(path: pathlib.Path) -> str:
    h = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            h.update(chunk)
    return h.hexdigest()


def source_files() -> list[pathlib.Path]:
    files: list[pathlib.Path] = []
    for path in SOURCE.rglob("*"):
        if not path.is_file():
            continue
        rel = path.relative_to(SOURCE)
        if path.name in EXCLUDE_NAMES or any(part in EXCLUDE_PARTS for part in rel.parts):
            continue
        files.append(path)
    return sorted(files, key=lambda p: p.relative_to(SOURCE).as_posix())


def validate_source(files: list[pathlib.Path]) -> dict:
    bootstrap = SOURCE / "base-conhecimento-inteligencia-integrada.php"
    text = bootstrap.read_text(encoding="utf-8")
    required = [
        "Version: 0.6.0-dev",
        "class-knowledge-facts-contract.php",
        "class-knowledge-facts-store.php",
        "class-helpful-tips-store.php",
        "class-coverage-read-model.php",
        "class-knowledge-details-admin.php",
    ]
    missing = [token for token in required if token not in text]

    php_files = [p for p in files if p.suffix.lower() == ".php"]
    lint_failed: list[str] = []
    for php in php_files:
        result = subprocess.run(
            ["php", "-l", str(php)],
            cwd=ROOT,
            stdout=subprocess.PIPE,
            stderr=subprocess.STDOUT,
            text=True,
            check=False,
        )
        if result.returncode != 0:
            lint_failed.append(php.relative_to(ROOT).as_posix())

    if missing or lint_failed:
        raise SystemExit(
            json.dumps(
                {
                    "missing_contract_tokens": missing,
                    "php_lint_failed": lint_failed,
                },
                ensure_ascii=False,
                indent=2,
            )
        )

    return {
        "source_file_count": len(files),
        "php_lint_checked": len(php_files),
        "php_lint_failed": 0,
        "required_contract_tokens": len(required),
    }


def build_zip(files: list[pathlib.Path], output: pathlib.Path) -> None:
    output.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(output, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for source in files:
            rel = source.relative_to(SOURCE).as_posix()
            info = zipfile.ZipInfo(f"{PACKAGE_ROOT}/{rel}", FIXED_TIME)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o100644 << 16
            archive.writestr(info, source.read_bytes())


def zip_file_manifest(zip_path: pathlib.Path) -> list[dict]:
    rows = []
    with zipfile.ZipFile(zip_path, "r") as archive:
        for name in sorted(archive.namelist()):
            data = archive.read(name)
            rows.append({"path": name, "bytes": len(data), "sha256": sha256_bytes(data)})
    return rows


def main() -> int:
    files = source_files()
    validation = validate_source(files)

    with tempfile.TemporaryDirectory(prefix="bdc-p630-") as temp:
        first = pathlib.Path(temp) / "first.zip"
        second = pathlib.Path(temp) / "second.zip"
        build_zip(files, first)
        build_zip(files, second)
        deterministic = first.read_bytes() == second.read_bytes()
        if not deterministic:
            raise SystemExit("P-630 homologation package is not deterministic.")
        shutil.copy2(first, ZIP_PATH)

    manifest = {
        "schema_version": "1.0.0",
        "purpose": "SPEC-006 P-630 environmental homologation",
        "production_package": False,
        "version": VERSION,
        "build": BUILD,
        "root": PACKAGE_ROOT,
        "zip_sha256": sha256_file(ZIP_PATH),
        "file_count": len(files),
        "files": zip_file_manifest(ZIP_PATH),
    }
    MANIFEST_PATH.write_text(json.dumps(manifest, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

    report = {
        "schema_version": "1.0.0",
        "gate": "P-630-PACKAGE",
        "status": "PASS",
        "production_package": False,
        "version": VERSION,
        "build": BUILD,
        "zip": ZIP_PATH.name,
        "zip_sha256": manifest["zip_sha256"],
        "manifest": MANIFEST_PATH.name,
        "manifest_sha256": sha256_file(MANIFEST_PATH),
        "deterministic_equal": True,
        "validation": validation,
        "next_gate": "P630_ENVIRONMENTAL_ACCEPTANCE",
    }
    VALIDATION_PATH.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

    print(json.dumps(report, ensure_ascii=False, indent=2))
    return 0


if __name__ == "__main__":
    sys.exit(main())
