#!/usr/bin/env python3
"""Inventário local de segurança/privacidade do ZIP P-660.

Foco em classificação e priorização. Não substitui WPCS/Plugin Check.
"""

from __future__ import annotations

import json
import pathlib
import re
import sys
import tempfile
import zipfile
from datetime import datetime, timezone

ROOT = pathlib.Path(__file__).resolve().parents[3]
DIST = ROOT / "dist"
DEFAULT_PACKAGE = DIST / "base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.4.zip"
OUTPUT = DIST / "p660-security-privacy-inventory.json"

SUPERGLOBAL_RE = re.compile(r"\$_(POST|GET|REQUEST|SERVER|FILES|COOKIE)")
NONCE_RE = re.compile(r"wp_verify_nonce|check_admin_referer|check_ajax_referer|wp_nonce_field")
CAPABILITY_RE = re.compile(r"current_user_can|user_can")
WPDB_RE = re.compile(r"\$wpdb")
PREPARE_RE = re.compile(r"->prepare\s*\(")
ECHO_RE = re.compile(r"\becho\b|\bprint\b|printf\s*\(")
ESCAPE_RE = re.compile(r"esc_(html|attr|url|js|textarea)|wp_kses|wp_json_encode")
HTTP_RE = re.compile(r"wp_remote_(get|post|request)|curl_|file_get_contents\s*\(\s*['\"]https?://")
SECRET_RE = re.compile(
    r"(sk-[A-Za-z0-9_-]{20,}|AIza[0-9A-Za-z_-]{20,}|"
    r"-----BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY-----|"
    r"(?i)(api[_-]?key|client[_-]?secret|password)\s*[:=]\s*['\"][^'\"]{8,})"
)


def read_package(path: pathlib.Path) -> dict[str, str]:
    out: dict[str, str] = {}
    with zipfile.ZipFile(path, "r") as archive:
        for name in archive.namelist():
            if not name.endswith(".php"):
                continue
            out[name] = archive.read(name).decode("utf-8", errors="replace")
    return out


def line_hits(text: str, pattern: re.Pattern[str]) -> list[int]:
    lines: list[int] = []
    for idx, line in enumerate(text.splitlines(), 1):
        if pattern.search(line):
            lines.append(idx)
    return lines


def classify(name: str, text: str) -> dict:
    superglobals = line_hits(text, SUPERGLOBAL_RE)
    nonce = line_hits(text, NONCE_RE)
    capability = line_hits(text, CAPABILITY_RE)
    wpdb = line_hits(text, WPDB_RE)
    prepare = line_hits(text, PREPARE_RE)
    output = line_hits(text, ECHO_RE)
    escape = line_hits(text, ESCAPE_RE)
    http = line_hits(text, HTTP_RE)
    secrets = line_hits(text, SECRET_RE)

    risks: list[str] = []
    if secrets:
        risks.append("SECRET_CRITICAL")
    if superglobals and ("admin" in name.lower() or "handle_" in text or "ACTION" in text):
        if not nonce or not capability:
            risks.append("MUTATION_HIGH")
        else:
            risks.append("MUTATION_REVIEW")
    elif superglobals:
        risks.append("READ_MEDIUM")
    if wpdb:
        if not prepare:
            risks.append("DB_HIGH")
        else:
            risks.append("DB_REVIEW")
    if output:
        risks.append("OUTPUT_MEDIUM")
    if http:
        risks.append("NETWORK_HIGH")

    return {
        "file": name,
        "risks": sorted(set(risks)),
        "signals": {
            "superglobal_lines": superglobals,
            "nonce_lines": nonce,
            "capability_lines": capability,
            "wpdb_lines": wpdb,
            "prepare_lines": prepare,
            "output_lines": output,
            "escape_lines": escape,
            "http_lines": http,
            "secret_lines": secrets,
        },
    }


def main() -> int:
    package = pathlib.Path(sys.argv[1]) if len(sys.argv) > 1 else DEFAULT_PACKAGE
    if not package.is_file():
        print(json.dumps({"status": "FAIL", "reason": "package_not_found", "path": str(package)}, indent=2))
        return 2

    files = read_package(package)
    rows = [classify(name, text) for name, text in sorted(files.items())]
    flagged = [row for row in rows if row["risks"]]

    by_risk: dict[str, int] = {}
    for row in flagged:
        for risk in row["risks"]:
            by_risk[risk] = by_risk.get(risk, 0) + 1

    secret_files = [row["file"] for row in flagged if "SECRET_CRITICAL" in row["risks"]]
    report = {
        "schema_version": "1.0.0",
        "gate": "P-660",
        "phase": "SECURITY_PRIVACY_INVENTORY",
        "status": "PASS" if not secret_files else "FAIL_CRITICAL",
        "execution_mode": "LOCAL_ONLY",
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "package": package.name,
        "php_files_scanned": len(files),
        "flagged_files": len(flagged),
        "risk_counts": dict(sorted(by_risk.items())),
        "critical_secret_files": secret_files,
        "files": flagged,
        "notes": [
            "This is a prioritization inventory, not a replacement for WPCS or official Plugin Check.",
            "Signal presence does not by itself prove a vulnerability.",
            "Mutation and DB findings require source review before remediation."
        ],
        "next_gate": "P660_SOURCE_REVIEW_AND_REMEDIATION"
    }

    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(report, ensure_ascii=False, indent=2))
    return 0 if report["status"] == "PASS" else 1


if __name__ == "__main__":
    sys.exit(main())
