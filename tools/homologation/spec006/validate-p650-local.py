#!/usr/bin/env python3
"""Validação local canônica do package P-650.

Pré-condição: P640 local PASS. Este programa não usa CI remoto.
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
from datetime import datetime, timezone

ROOT = pathlib.Path(__file__).resolve().parents[3]
DIST = ROOT / "dist"
P640_EVIDENCE = ROOT / "evidence" / "spec006-p640-local-validation-current.json"
P650_EVIDENCE = ROOT / "evidence" / "spec006-p650-local-package-validation-current.json"
P650_BUILDER = ROOT / "tools" / "homologation" / "spec006" / "build-p650-production.py"
P650_STATIC = ROOT / "tests" / "unit" / "spec006-p650-package-contract.php"
PACKAGE = DIST / "base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.3.zip"
PACKAGE_REPORT = DIST / "p650-package-validation.json"
EXPECTED_PACKAGE_SHA256 = "985091a289f11c0ae449e6f93e2f4090ddd3790762df42cff4a8a97fc775c231"


def sha256(path: pathlib.Path) -> str:
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


def require_p640_pass() -> dict:
    if not P640_EVIDENCE.is_file():
        raise SystemExit(
            json.dumps(
                {
                    "gate": "P-650",
                    "status": "BLOCKED_P640",
                    "reason": "P640 local PASS evidence not found.",
                    "required": str(P640_EVIDENCE.relative_to(ROOT)),
                },
                ensure_ascii=False,
                indent=2,
            )
        )
    data = json.loads(P640_EVIDENCE.read_text(encoding="utf-8"))
    if data.get("status") != "PASS" or data.get("execution_mode") != "LOCAL_ONLY":
        raise SystemExit(
            json.dumps(
                {
                    "gate": "P-650",
                    "status": "BLOCKED_P640",
                    "reason": "P640 evidence exists but is not LOCAL_ONLY PASS.",
                },
                ensure_ascii=False,
                indent=2,
            )
        )

    current_plugin_tree = git_revision("HEAD:plugin/base-conhecimento-inteligencia-integrada")
    evidence_plugin_tree = data.get("plugin_tree_sha")
    if not current_plugin_tree or not evidence_plugin_tree or current_plugin_tree != evidence_plugin_tree:
        raise SystemExit(
            json.dumps(
                {
                    "gate": "P-650",
                    "status": "BLOCKED_STALE_P640_EVIDENCE",
                    "reason": "P640 PASS does not match the current plugin source tree.",
                    "p640_plugin_tree_sha": evidence_plugin_tree,
                    "current_plugin_tree_sha": current_plugin_tree,
                },
                ensure_ascii=False,
                indent=2,
            )
        )
    return data


def lint_package(php: str) -> dict:
    failures: list[dict] = []
    checked = 0
    with tempfile.TemporaryDirectory(prefix="bdc-p650-lint-") as temp:
        target = pathlib.Path(temp)
        with zipfile.ZipFile(PACKAGE, "r") as archive:
            archive.extractall(target)

        for path in sorted(target.rglob("*.php")):
            checked += 1
            result = run(
                f"lint:{path.name}",
                [php, "-l", str(path)],
                cwd=target,
            )
            if not result["pass"]:
                failures.append(result)

    return {
        "name": "package_php_lint",
        "pass": not failures,
        "checked": checked,
        "failed": len(failures),
        "failures": failures,
    }


def main() -> int:
    p640 = require_p640_pass()
    php = shutil.which("php")
    if not php:
        print(
            json.dumps(
                {"gate": "P-650", "status": "BLOCKED_LOCAL_TOOLING", "missing": ["php"]},
                ensure_ascii=False,
                indent=2,
            )
        )
        return 2

    steps = [
        run("p650_static_contract", [php, str(P650_STATIC)]),
        run("p650_deterministic_builder", [sys.executable, str(P650_BUILDER)]),
    ]

    if all(step["pass"] for step in steps) and PACKAGE.is_file():
        steps.append(lint_package(php))
        actual_sha = sha256(PACKAGE)
        steps.append(
            {
                "name": "frozen_package_sha256",
                "pass": actual_sha == EXPECTED_PACKAGE_SHA256,
                "expected_sha256": EXPECTED_PACKAGE_SHA256,
                "actual_sha256": actual_sha,
            }
        )
    else:
        steps.append(
            {
                "name": "package_php_lint",
                "pass": False,
                "checked": 0,
                "failed": 0,
                "reason": "package not available after builder",
            }
        )

    package_report = None
    if PACKAGE_REPORT.is_file():
        package_report = json.loads(PACKAGE_REPORT.read_text(encoding="utf-8"))

    passed = all(step.get("pass", False) for step in steps)
    passed = passed and isinstance(package_report, dict) and package_report.get("status") == "PASS"

    report = {
        "schema_version": "1.0.0",
        "gate": "P-650",
        "phase": "LOCAL_PACKAGE_QUALITY",
        "status": "PASS" if passed else "FAIL",
        "execution_mode": "LOCAL_ONLY",
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "p640_precondition": {
            "status": p640.get("status"),
            "execution_mode": p640.get("execution_mode"),
            "source_commit": p640.get("source_commit"),
            "plugin_tree_sha": p640.get("plugin_tree_sha"),
        },
        "current_source": {
            "source_commit": git_revision("HEAD"),
            "plugin_tree_sha": git_revision("HEAD:plugin/base-conhecimento-inteligencia-integrada"),
        },
        "steps": steps,
        "package_report": package_report,
        "invariants": {
            "remote_ci_used_as_gate_executor": False,
            "source_checkout_modified": False,
            "content_mutation": False,
            "data_migration": False,
            "cutover_authorized": False,
            "retirement_authorized": False,
        },
        "next_gate": "P650_INSTALL_UPGRADE_ROLLBACK_ENVIRONMENTAL" if passed else "P650_LOCAL_REMEDIATION",
    }

    P650_EVIDENCE.parent.mkdir(parents=True, exist_ok=True)
    P650_EVIDENCE.write_text(
        json.dumps(report, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    print(json.dumps(report, ensure_ascii=False, indent=2))
    return 0 if passed else 1


if __name__ == "__main__":
    sys.exit(main())
