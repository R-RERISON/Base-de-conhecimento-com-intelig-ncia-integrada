#!/usr/bin/env python3
"""Executor local canônico do Plugin Check para SPEC-006 / P-650/P-660.

Requer WP-CLI funcional em um WordPress local/homologação.
Não usa GitHub Actions.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import pathlib
import shutil
import subprocess
import sys
from datetime import datetime, timezone

ROOT = pathlib.Path(__file__).resolve().parents[3]
DEFAULT_ZIP = ROOT / "dist" / "base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.4.zip"
EXPECTED_SHA256 = "0e4860ed0be34033c63358c9f0798d7c9564420dd738fc66d587180fbf14cf77"
OUTPUT = ROOT / "evidence" / "spec006-p650-p660-plugin-check-current.json"


def sha256(path: pathlib.Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def run(command: list[str], cwd: pathlib.Path | None = None) -> dict:
    proc = subprocess.run(
        command,
        cwd=cwd,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        text=True,
        check=False,
    )
    return {
        "command": command,
        "exit_code": proc.returncode,
        "pass": proc.returncode == 0,
        "output": proc.stdout.strip(),
    }


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--wp-path", required=True, help="Path do WordPress local/homologação.")
    parser.add_argument("--zip", default=str(DEFAULT_ZIP), help="ZIP exato a validar.")
    args = parser.parse_args()

    wp = pathlib.Path(args.wp_path).resolve()
    package = pathlib.Path(args.zip).resolve()
    wp_cli = shutil.which("wp")

    if not wp_cli:
        print(json.dumps({"status": "BLOCKED_LOCAL_TOOLING", "missing": ["wp"]}, indent=2))
        return 2
    if not package.is_file():
        print(json.dumps({"status": "FAIL", "reason": "package_not_found", "package": str(package)}, indent=2))
        return 2
    if not (wp / "wp-config.php").is_file():
        print(json.dumps({"status": "FAIL", "reason": "wordpress_path_invalid", "wp_path": str(wp)}, indent=2))
        return 2

    checks: list[dict] = []
    checks.append(run([wp_cli, "plugin", "is-installed", "plugin-check", f"--path={wp}"]))
    if not checks[-1]["pass"]:
        print(json.dumps({
            "status": "BLOCKED_PLUGIN_CHECK_NOT_INSTALLED",
            "instruction": "Instale o plugin oficial Plugin Check neste WordPress local e execute novamente.",
            "preflight": checks,
        }, ensure_ascii=False, indent=2))
        return 2

    checks.append(run([wp_cli, "plugin", "activate", "plugin-check", f"--path={wp}"]))
    if not checks[-1]["pass"] and "already active" not in checks[-1]["output"].lower():
        print(json.dumps({"status": "FAIL", "preflight": checks}, ensure_ascii=False, indent=2))
        return 1

    package_sha = sha256(package)
    if package_sha != EXPECTED_SHA256:
        print(json.dumps({
            "status": "FAIL_ARTIFACT_MISMATCH",
            "package": package.name,
            "expected_sha256": EXPECTED_SHA256,
            "actual_sha256": package_sha,
            "instruction": "Use exatamente o artefato p650.4 candidato; não reconstrua o ZIP.",
        }, ensure_ascii=False, indent=2))
        return 3

    # O Plugin Check oficial opera sobre plugin instalado. A instalação deste ZIP deve
    # ocorrer no mesmo WordPress antes desta execução para preservar o artefato testado.
    plugin_check_cli = wp / "wp-content" / "plugins" / "plugin-check" / "cli.php"
    if not plugin_check_cli.is_file():
        print(json.dumps({
            "status": "BLOCKED_PLUGIN_CHECK_CLI_MISSING",
            "required": str(plugin_check_cli),
            "instruction": "Plugin Check oficial deve estar instalado no WordPress informado.",
        }, ensure_ascii=False, indent=2))
        return 2

    checks.append(run([
        wp_cli,
        "plugin",
        "check",
        str(package),
        "--format=strict-json",
        "--mode=update",
        f"--require={plugin_check_cli}",
        f"--path={wp}",
    ]))

    result = checks[-1]
    report = {
        "schema_version": "1.0.0",
        "gate": "P-650/P-660",
        "phase": "OFFICIAL_PLUGIN_CHECK",
        "status": "PASS" if result["pass"] else "FAIL",
        "execution_mode": "LOCAL_ONLY",
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "package": package.name,
        "package_sha256": package_sha,
        "expected_sha256": EXPECTED_SHA256,
        "artifact_identity_match": True,
        "plugin_check_target": "ZIP_PATH_DIRECT",
        "runtime_checks_enabled": True,
        "format": "strict-json",
        "mode": "update",
        "wp_path": str(wp),
        "checks": checks,
        "invariants": {
            "github_actions_used": False,
            "package_rebuilt": False,
            "cutover_authorized": False,
            "retirement_authorized": False,
        },
        "next_gate": "P660_PLUGIN_CHECK_DISPOSITION" if not result["pass"] else "P650_P660_CLOSEOUT_REVIEW",
    }

    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(report, ensure_ascii=False, indent=2))
    return 0 if result["pass"] else 1


if __name__ == "__main__":
    sys.exit(main())
