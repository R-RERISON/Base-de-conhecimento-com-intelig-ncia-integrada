#!/usr/bin/env python3
"""Gera candidato de closeout P-670 após PASS_PRECONDITIONS.

Não altera o Master Ledger e não fecha a SPEC automaticamente.
"""

from __future__ import annotations

import json
import pathlib
import shutil
import subprocess
from datetime import datetime, timezone
from typing import Any

ROOT = pathlib.Path(__file__).resolve().parents[3]
EVIDENCE = ROOT / "evidence"
SPEC = ROOT / "specs" / "006-premium-product-foundation"
PREFLIGHT = EVIDENCE / "spec006-p670-preflight-current.json"
OUTPUT_JSON = EVIDENCE / "spec006-p670-closeout-candidate-current.json"
OUTPUT_MD = SPEC / "p670-closeout-candidate-current.md"
EXPECTED_PACKAGE = "base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.4.zip"
EXPECTED_SHA = "a76addbace25a8b00d8a0646ba7034952dfa941c3636e21c5162644a5b4636c2"


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


def load(path: pathlib.Path) -> dict[str, Any]:
    if not path.is_file():
        raise SystemExit(json.dumps({"status": "BLOCKED_P670_PREFLIGHT_MISSING", "required": str(path.relative_to(ROOT))}, indent=2))
    data = json.loads(path.read_text(encoding="utf-8-sig"))
    if not isinstance(data, dict):
        raise SystemExit("Invalid P670 preflight evidence.")
    return data


def main() -> int:
    preflight = load(PREFLIGHT)
    current_tree = git_revision("HEAD:plugin/base-conhecimento-inteligencia-integrada")
    current_commit = git_revision("HEAD")

    if preflight.get("status") != "PASS_PRECONDITIONS":
        print(json.dumps({"status": "BLOCKED_P670_PREFLIGHT", "preflight_status": preflight.get("status")}, ensure_ascii=False, indent=2))
        return 2

    if preflight.get("plugin_tree_sha") != current_tree:
        print(json.dumps({"status": "BLOCKED_STALE_P670_PREFLIGHT", "preflight_plugin_tree_sha": preflight.get("plugin_tree_sha"), "current_plugin_tree_sha": current_tree}, ensure_ascii=False, indent=2))
        return 2

    artifact = preflight.get("artifact", {})
    if not isinstance(artifact, dict) or artifact.get("name") != EXPECTED_PACKAGE or artifact.get("sha256") != EXPECTED_SHA:
        print(json.dumps({"status": "BLOCKED_ARTIFACT_MISMATCH", "artifact": artifact}, ensure_ascii=False, indent=2))
        return 2

    promotions = {
        "PROD-003": {"from": "PARTIAL", "candidate": "IMPROVED_VERIFIED", "basis": "P640/P660 local WPCS, PHPUnit/static contracts and local quality evidence.", "limitation": "Does not claim the entire historical plugin is WPCS-clean."},
        "PROD-004": {"from": "PARTIAL", "candidate": "IMPROVED_VERIFIED", "basis": "Official local Plugin Check on the exact frozen p650.4 artifact.", "limitation": "Any residual warning requires explicit disposition before application."},
        "PROD-005": {"from": "PARTIAL", "candidate": "IMPROVED_VERIFIED", "basis": "P640 modular runtime + same-tree evidence + environmental runtime smoke.", "limitation": "No cutover or legacy retirement implied."},
        "PROD-006": {"from": "PARTIAL", "candidate": "IMPROVED_VERIFIED", "basis": "Deterministic p650.4 ZIP, lab pruning, checksum, environmental smoke and rollback.", "limitation": "Production release remains a later product/release decision."},
    }

    report = {
        "schema_version": "1.0.0",
        "gate": "P-670",
        "phase": "CLOSEOUT_CANDIDATE",
        "status": "READY_FOR_HUMAN_LEDGER_REVIEW",
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source_commit": current_commit,
        "plugin_tree_sha": current_tree,
        "artifact": {"name": EXPECTED_PACKAGE, "sha256": EXPECTED_SHA},
        "preflight": {"status": preflight.get("status"), "path": str(PREFLIGHT.relative_to(ROOT))},
        "ledger_promotions": promotions,
        "ledger_updated": False,
        "spec_closed": False,
        "known_non_blocking_foundation_note": {"composer_lock_versioned": False, "meaning": "Dev dependency resolution is not bit-for-bit pinned by the repository; do not claim that property."},
        "boundaries": {"version_1_0_authorized": False, "cutover_authorized": False, "retirement_authorized": False, "bulk_migration_authorized": False},
        "next_gate": "P670_HUMAN_LEDGER_REVIEW_AND_CLOSEOUT",
    }

    OUTPUT_JSON.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_JSON.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

    lines = [
        "# P-670 — Closeout Candidate",
        "",
        "**Status:** READY_FOR_HUMAN_LEDGER_REVIEW",
        f"**Artifact:** {EXPECTED_PACKAGE}",
        f"**SHA-256:** {EXPECTED_SHA}",
        "",
        "## Foundation evidence",
        "",
        "- P670 preflight: PASS_PRECONDITIONS;",
        "- P640 local quality: required PASS;",
        "- P650 local package quality: required PASS;",
        "- P660 local security/privacy: required PASS;",
        "- official Plugin Check on frozen p650.4: required PASS;",
        "- rollback with data fingerprints: required PASS;",
        "- environmental smoke p650.4: PASS.",
        "",
        "## Proposed Master Ledger disposition",
        "",
        "| ID | Current | Candidate | Basis |",
        "|---|---|---|---|",
    ]
    for ledger_id, row in promotions.items():
        lines.append(f"| {ledger_id} | {row['from']} | {row['candidate']} | {row['basis']} |")

    lines.extend([
        "",
        "## Explicit limitations",
        "",
        "- This file does not edit the Master Ledger.",
        "- It does not close SPEC-006 automatically.",
        "- It does not authorize 1.0.0.",
        "- It does not authorize public cutover.",
        "- It does not authorize ASI/GRE/KB2Ops retirement.",
        "- It does not authorize bulk migration or legacy storage deletion.",
        "- composer.lock is not currently versioned; do not claim bit-for-bit reproducibility of dev dependency resolution.",
        "",
        "## Required human closeout",
        "",
        "1. review the exact final evidence JSONs;",
        "2. confirm Plugin Check findings/disposition;",
        "3. confirm rollback preservation;",
        "4. apply only supported PROD-003..006 promotions;",
        "5. write final P670 evidence/closeout;",
        "6. update CONTINUIDADE.md;",
        "7. keep cutover/retirement for SPEC-014.",
        "",
    ])
    OUTPUT_MD.write_text("\n".join(lines), encoding="utf-8")
    print(json.dumps(report, ensure_ascii=False, indent=2))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
