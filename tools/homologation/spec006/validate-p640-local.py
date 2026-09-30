#!/usr/bin/env python3
"""Validador local canônico do SPEC-006 / P-640.

Este programa é executado na workstation/local checkout.
Não usa GitHub Actions, APIs remotas ou CI como executor de gate.
"""

from __future__ import annotations

import hashlib
import json
import os
import pathlib
import shutil
import subprocess
import sys
from datetime import datetime, timezone

ROOT = pathlib.Path(__file__).resolve().parents[3]
PLUGIN = ROOT / "plugin" / "base-conhecimento-inteligencia-integrada"
DIST = ROOT / "dist"
EVIDENCE = ROOT / "evidence" / "spec006-p640-local-validation-current.json"

STATIC_CONTRACTS = (
    ROOT / "tests" / "unit" / "spec006-p640-runtime-module-registry.php",
    ROOT / "tests" / "unit" / "spec006-p640-modular-runtime.php",
)

WPCS_FILES = (
    PLUGIN / "base-conhecimento-inteligencia-integrada.php",
    PLUGIN / "includes" / "class-core-runtime-loader.php",
    PLUGIN / "includes" / "class-runtime-module-registry.php",
    PLUGIN / "includes" / "class-engineering-module-loader.php",
    PLUGIN / "includes" / "class-modular-runtime-runner-p640.php",
)

BUILD_SCRIPT = ROOT / "tools" / "homologation" / "spec006" / "build-p640.py"
PACKAGE = DIST / "base-conhecimento-inteligencia-integrada-0.6.0-dev-p640.2.zip"
MANIFEST = DIST / "base-conhecimento-inteligencia-integrada-0.6.0-dev-p640.2.manifest.json"
PACKAGE_VALIDATION = DIST / "p640-local-package-validation.json"


def sha256(path: pathlib.Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def run(name: str, command: list[str], cwd: pathlib.Path = ROOT) -> dict:
    proc = subprocess.run(
        command,
        cwd=cwd,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        text=True,
        check=False,
    )
    return {
        "name": name,
        "command": command,
        "exit_code": proc.returncode,
        "pass": proc.returncode == 0,
        "output": proc.stdout.strip(),
    }


def resolve_vendor_binary(name: str) -> pathlib.Path | None:
    candidates = [
        ROOT / "vendor" / "bin" / name,
        ROOT / "vendor" / "bin" / f"{name}.bat",
    ]
    for candidate in candidates:
        if candidate.is_file():
            return candidate
    return None


def require_local_tools() -> tuple[str, pathlib.Path, pathlib.Path]:
    php = shutil.which("php")
    phpcs = resolve_vendor_binary("phpcs")
    phpunit = resolve_vendor_binary("phpunit")

    missing = []
    if not php:
        missing.append("php")
    if phpcs is None:
        missing.append("vendor/bin/phpcs")
    if phpunit is None:
        missing.append("vendor/bin/phpunit")

    if missing:
        print(
            json.dumps(
                {
                    "gate": "P-640",
                    "status": "BLOCKED_LOCAL_TOOLING",
                    "missing": missing,
                    "instruction": "Execute composer install localmente e rode novamente este validador.",
                },
                ensure_ascii=False,
                indent=2,
            )
        )
        raise SystemExit(2)

    return php, phpcs, phpunit


def git_revision(spec: str) -> str | None:
    git = shutil.which("git")
    if not git or not (ROOT / ".git").exists():
        return None
    result = subprocess.run(
        [git, "rev-parse", spec],
        cwd=ROOT,
        stdout=subprocess.PIPE,
        stderr=subprocess.DEVNULL,
        text=True,
        check=False,
    )
    return result.stdout.strip() if result.returncode == 0 else None


def source_commit() -> str | None:
    return git_revision("HEAD")


def plugin_tree_sha() -> str | None:
    return git_revision("HEAD:plugin/base-conhecimento-inteligencia-integrada")


def main() -> int:
    php, phpcs, phpunit = require_local_tools()
    steps: list[dict] = []

    for contract in STATIC_CONTRACTS:
        steps.append(run(f"static:{contract.name}", [php, str(contract)]))

    php_files = sorted(PLUGIN.rglob("*.php"))
    lint_failures = []
    for php_file in php_files:
        result = run(
            f"lint:{php_file.relative_to(ROOT).as_posix()}",
            [php, "-l", str(php_file)],
        )
        if not result["pass"]:
            lint_failures.append(result)

    steps.append(
        {
            "name": "php_lint_full_plugin",
            "pass": not lint_failures,
            "checked": len(php_files),
            "failed": len(lint_failures),
            "failures": lint_failures,
        }
    )

    steps.append(
        run(
            "wpcs_affected_runtime",
            [str(phpcs), *[str(path) for path in WPCS_FILES]],
        )
    )

    steps.append(
        run(
            "phpunit_foundation",
            [str(phpunit), "-c", str(ROOT / "phpunit.xml.dist")],
        )
    )

    steps.append(run("deterministic_build", [sys.executable, str(BUILD_SCRIPT)]))

    passed = all(step.get("pass", False) for step in steps)

    artifact = None
    if passed and PACKAGE.is_file() and MANIFEST.is_file() and PACKAGE_VALIDATION.is_file():
        artifact = {
            "package": PACKAGE.name,
            "package_sha256": sha256(PACKAGE),
            "manifest": MANIFEST.name,
            "manifest_sha256": sha256(MANIFEST),
            "package_validation": PACKAGE_VALIDATION.name,
            "package_validation_sha256": sha256(PACKAGE_VALIDATION),
        }
    elif passed:
        passed = False
        steps.append(
            {
                "name": "package_outputs_present",
                "pass": False,
                "missing": [
                    str(path.relative_to(ROOT))
                    for path in (PACKAGE, MANIFEST, PACKAGE_VALIDATION)
                    if not path.is_file()
                ],
            }
        )

    if passed and not plugin_tree_sha():
        passed = False
        steps.append(
            {
                "name": "plugin_tree_provenance",
                "pass": False,
                "reason": "Unable to resolve Git plugin subtree SHA.",
            }
        )

    report = {
        "schema_version": "1.0.0",
        "gate": "P-640",
        "phase": "LOCAL_VALIDATION_AND_PACKAGE",
        "status": "PASS" if passed else "FAIL",
        "execution_mode": "LOCAL_ONLY",
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source_commit": source_commit(),
        "plugin_tree_sha": plugin_tree_sha(),
        "environment": {
            "python": sys.version.split()[0],
            "platform": sys.platform,
            "cwd": str(ROOT),
        },
        "steps": steps,
        "artifact": artifact,
        "invariants": {
            "remote_ci_used_as_gate_executor": False,
            "content_mutation": False,
            "data_migration": False,
            "cutover_authorized": False,
            "retirement_authorized": False,
        },
        "next_gate": "P640_ENVIRONMENTAL_ACCEPTANCE" if passed else "P640_LOCAL_REMEDIATION",
    }

    EVIDENCE.parent.mkdir(parents=True, exist_ok=True)
    EVIDENCE.write_text(
        json.dumps(report, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    print(json.dumps(report, ensure_ascii=False, indent=2))
    return 0 if passed else 1


if __name__ == "__main__":
    sys.exit(main())
