#!/usr/bin/env python3
"""Validação local canônica do P-660 Security/Privacy.

Executa somente sobre o source atual e o ZIP p650.4 congelado.
Não substitui o Plugin Check oficial; prepara sua entrada com WPCS e contratos locais.
"""

from __future__ import annotations

import hashlib
import json
import pathlib
import shutil
import subprocess
import sys
from datetime import datetime, timezone

ROOT = pathlib.Path(__file__).resolve().parents[3]
PLUGIN = ROOT / "plugin" / "base-conhecimento-inteligencia-integrada"
DIST = ROOT / "dist"
EVIDENCE = ROOT / "evidence" / "spec006-p660-local-validation-current.json"
P640_EVIDENCE = ROOT / "evidence" / "spec006-p640-local-validation-current.json"
STATIC_CONTRACT = ROOT / "tests" / "unit" / "spec006-p660-security-baseline.php"
INVENTORY = ROOT / "tools" / "homologation" / "spec006" / "inventory-p660-security-privacy.py"
INVENTORY_OUTPUT = DIST / "p660-security-privacy-inventory.json"
PACKAGE = DIST / "base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.4.zip"
EXPECTED_PACKAGE_SHA256 = "a76addbace25a8b00d8a0646ba7034952dfa941c3636e21c5162644a5b4636c2"

AFFECTED_FILES = (
    PLUGIN / "base-conhecimento-inteligencia-integrada.php",
    PLUGIN / "includes" / "class-admin-page.php",
    PLUGIN / "includes" / "class-block-migration-journal-store.php",
    PLUGIN / "includes" / "class-classification-admin.php",
    PLUGIN / "includes" / "class-content-source.php",
    PLUGIN / "includes" / "class-elementor-migration-journal-store.php",
    PLUGIN / "includes" / "class-knowledge-details-admin.php",
    PLUGIN / "includes" / "class-legacy-html-adapter.php",
    PLUGIN / "includes" / "class-post-management-activities.php",
    PLUGIN / "includes" / "class-post-core-blocks-activity.php",
    PLUGIN / "includes" / "class-public-auth-bridge.php",
    PLUGIN / "includes" / "class-public-experience.php",
    PLUGIN / "includes" / "class-public-search-facade.php",
    PLUGIN / "includes" / "class-review-admin.php",
    PLUGIN / "includes" / "class-search-projection-repository.php",
    PLUGIN / "includes" / "class-search-rebuild-service.php",
    PLUGIN / "includes" / "class-visual-foundation.php",
    PLUGIN / "includes" / "class-word-cloud-admin.php",
)


def sha256(path: pathlib.Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def resolve_vendor_binary(name: str) -> pathlib.Path | None:
    for candidate in (
        ROOT / "vendor" / "bin" / name,
        ROOT / "vendor" / "bin" / f"{name}.bat",
    ):
        if candidate.is_file():
            return candidate
    return None


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


def run(name: str, command: list[str]) -> dict:
    proc = subprocess.run(
        command,
        cwd=ROOT,
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


def require_p640_same_source() -> dict:
    if not P640_EVIDENCE.is_file():
        raise SystemExit(
            json.dumps(
                {
                    "gate": "P-660",
                    "status": "BLOCKED_P640",
                    "reason": "P640 local evidence is missing.",
                },
                ensure_ascii=False,
                indent=2,
            )
        )
    data = json.loads(P640_EVIDENCE.read_text(encoding="utf-8"))
    current_tree = git_revision("HEAD:plugin/base-conhecimento-inteligencia-integrada")
    if (
        data.get("status") != "PASS"
        or data.get("execution_mode") != "LOCAL_ONLY"
        or not current_tree
        or data.get("plugin_tree_sha") != current_tree
    ):
        raise SystemExit(
            json.dumps(
                {
                    "gate": "P-660",
                    "status": "BLOCKED_STALE_P640_EVIDENCE",
                    "p640_status": data.get("status"),
                    "p640_plugin_tree_sha": data.get("plugin_tree_sha"),
                    "current_plugin_tree_sha": current_tree,
                },
                ensure_ascii=False,
                indent=2,
            )
        )
    return data


def main() -> int:
    p640 = require_p640_same_source()
    php = shutil.which("php")
    phpcs = resolve_vendor_binary("phpcs")
    missing = []
    if not php:
        missing.append("php")
    if phpcs is None:
        missing.append("vendor/bin/phpcs")
    if missing:
        print(json.dumps({"gate": "P-660", "status": "BLOCKED_LOCAL_TOOLING", "missing": missing}, indent=2))
        return 2
    if not PACKAGE.is_file():
        print(json.dumps({"gate": "P-660", "status": "BLOCKED_PACKAGE_MISSING", "package": str(PACKAGE)}, indent=2))
        return 2

    package_sha = sha256(PACKAGE)
    steps: list[dict] = [
        {
            "name": "frozen_package_sha256",
            "pass": package_sha == EXPECTED_PACKAGE_SHA256,
            "expected_sha256": EXPECTED_PACKAGE_SHA256,
            "actual_sha256": package_sha,
        },
        run("p660_static_contract", [php, str(STATIC_CONTRACT)]),
    ]

    lint_failures = []
    for path in AFFECTED_FILES:
        result = run(f"lint:{path.name}", [php, "-l", str(path)])
        if not result["pass"]:
            lint_failures.append(result)
    steps.append(
        {
            "name": "php_lint_affected_security_runtime",
            "pass": not lint_failures,
            "checked": len(AFFECTED_FILES),
            "failed": len(lint_failures),
            "failures": lint_failures,
        }
    )

    steps.append(
        run(
            "wpcs_affected_security_runtime",
            [str(phpcs), *[str(path) for path in AFFECTED_FILES]],
        )
    )
    steps.append(run("p660_security_inventory", [sys.executable, str(INVENTORY), str(PACKAGE)]))

    inventory = None
    if INVENTORY_OUTPUT.is_file():
        inventory = json.loads(INVENTORY_OUTPUT.read_text(encoding="utf-8"))

    inventory_pass = (
        isinstance(inventory, dict)
        and inventory.get("status") == "PASS"
        and not inventory.get("critical_secret_files")
        and int(inventory.get("risk_counts", {}).get("SECRET_CRITICAL", 0)) == 0
        and int(inventory.get("risk_counts", {}).get("NETWORK_HIGH", 0)) == 0
        and int(inventory.get("risk_counts", {}).get("MUTATION_HIGH", 0)) == 0
        and int(inventory.get("risk_counts", {}).get("DB_HIGH", 0)) == 0
    )
    steps.append(
        {
            "name": "inventory_high_risk_contract",
            "pass": inventory_pass,
            "risk_counts": inventory.get("risk_counts") if isinstance(inventory, dict) else None,
        }
    )

    passed = all(step.get("pass", False) for step in steps)
    report = {
        "schema_version": "1.0.0",
        "gate": "P-660",
        "phase": "LOCAL_SECURITY_PRIVACY_QUALITY",
        "status": "PASS" if passed else "FAIL",
        "execution_mode": "LOCAL_ONLY",
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source_commit": git_revision("HEAD"),
        "plugin_tree_sha": git_revision("HEAD:plugin/base-conhecimento-inteligencia-integrada"),
        "p640_precondition": {
            "status": p640.get("status"),
            "plugin_tree_sha": p640.get("plugin_tree_sha"),
        },
        "artifact": {
            "name": PACKAGE.name,
            "sha256": package_sha,
        },
        "steps": steps,
        "inventory": inventory,
        "invariants": {
            "github_actions_used": False,
            "content_mutation": False,
            "data_migration": False,
            "cutover_authorized": False,
            "retirement_authorized": False,
        },
        "next_gate": "P660_OFFICIAL_PLUGIN_CHECK" if passed else "P660_LOCAL_REMEDIATION",
    }

    EVIDENCE.parent.mkdir(parents=True, exist_ok=True)
    EVIDENCE.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(report, ensure_ascii=False, indent=2))
    return 0 if passed else 1


if __name__ == "__main__":
    raise SystemExit(main())
