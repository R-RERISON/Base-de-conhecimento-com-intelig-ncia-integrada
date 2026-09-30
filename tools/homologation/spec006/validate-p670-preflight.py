#!/usr/bin/env python3
"""P-670 Premium Foundation Acceptance preflight.

Consolida evidências da SPEC-006 sem promover Ledger ou autorizar cutover.
Fail-closed para evidência ausente, stale, remota onde o gate final exige local,
ou artefato diferente do p650.3 homologado.
"""

from __future__ import annotations

import hashlib
import json
import pathlib
import shutil
import subprocess
from datetime import datetime, timezone
from typing import Any

ROOT = pathlib.Path(__file__).resolve().parents[3]
EVIDENCE = ROOT / "evidence"
DIST = ROOT / "dist"

EXPECTED_PACKAGE_NAME = "base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.3.zip"
EXPECTED_PACKAGE_SHA256 = "985091a289f11c0ae449e6f93e2f4090ddd3790762df42cff4a8a97fc775c231"
PACKAGE = DIST / EXPECTED_PACKAGE_NAME
OUTPUT = EVIDENCE / "spec006-p670-preflight-current.json"

PATHS = {
    "p600": EVIDENCE / "spec006-p600-metadata-license-pass-20260929.json",
    "p610": EVIDENCE / "spec006-p610-tooling-pass-20260929.json",
    "p620": EVIDENCE / "spec006-p620-plugin-check-disposition-20260929.json",
    "p630": EVIDENCE / "spec006-p630-domain-closure-pass-20260929.json",
    "p640": EVIDENCE / "spec006-p640-local-validation-current.json",
    "p650": EVIDENCE / "spec006-p650-local-package-validation-current.json",
    "p650_env": EVIDENCE / "spec006-p6503-environmental-smoke-pass-20260930.json",
    "p660": EVIDENCE / "spec006-p660-local-validation-current.json",
    "plugin_check": EVIDENCE / "spec006-p650-p660-plugin-check-current.json",
    "rollback": EVIDENCE / "spec006-p650-rollback-current.json",
    "final_summary": EVIDENCE / "spec006-final-local-gates-summary-current.json",
}

GOVERNANCE_PATHS = (
    ROOT / "docs" / "PREMIUM-PLUGIN-PRODUCT-STANDARD.md",
    ROOT / "docs" / "DEFINITION-OF-DONE.md",
    ROOT / "specs" / "MASTER-FUNCTIONAL-PARITY-LEDGER.md",
    ROOT / "specs" / "006-premium-product-foundation" / "CONTINUIDADE.md",
    ROOT / "specs" / "006-premium-product-foundation" / "p670-premium-foundation-acceptance-contract-v1.md",
)


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


def load_json(path: pathlib.Path) -> dict[str, Any] | None:
    if not path.is_file():
        return None
    try:
        data = json.loads(path.read_text(encoding="utf-8-sig"))
    except (json.JSONDecodeError, UnicodeDecodeError):
        return None
    return data if isinstance(data, dict) else None


def step(name: str, passed: bool, **details: Any) -> dict[str, Any]:
    return {"name": name, "pass": bool(passed), **details}


def status_is(data: dict[str, Any] | None, *allowed: str) -> bool:
    return isinstance(data, dict) and str(data.get("status", "")) in allowed


def nested(data: dict[str, Any] | None, *keys: str) -> Any:
    current: Any = data
    for key in keys:
        if not isinstance(current, dict):
            return None
        current = current.get(key)
    return current


def main() -> int:
    current_commit = git_revision("HEAD")
    current_plugin_tree = git_revision("HEAD:plugin/base-conhecimento-inteligencia-integrada")

    loaded = {name: load_json(path) for name, path in PATHS.items()}
    checks: list[dict[str, Any]] = []

    missing_evidence = [name for name, data in loaded.items() if data is None]
    checks.append(
        step(
            "all_required_evidence_present",
            not missing_evidence,
            missing=missing_evidence,
        )
    )

    missing_governance = [str(path.relative_to(ROOT)) for path in GOVERNANCE_PATHS if not path.is_file()]
    checks.append(
        step(
            "governance_documents_present",
            not missing_governance,
            missing=missing_governance,
        )
    )

    # Historical gates remain provenance history. Their material claims are
    # superseded/re-proven by the final local P640/P650/P660/Plugin Check gates.
    checks.extend(
        [
            step("p600_historical_status", status_is(loaded["p600"], "PASS"), status=nested(loaded["p600"], "status")),
            step("p610_historical_status", status_is(loaded["p610"], "PASS"), status=nested(loaded["p610"], "status")),
            step(
                "p620_historical_disposition",
                status_is(loaded["p620"], "PASS_INVENTORY_AND_DISPOSITION"),
                status=nested(loaded["p620"], "status"),
            ),
            step("p630_environmental_domain_closure", status_is(loaded["p630"], "PASS"), status=nested(loaded["p630"], "status")),
        ]
    )

    p640 = loaded["p640"]
    checks.append(
        step(
            "p640_local_pass",
            status_is(p640, "PASS") and nested(p640, "execution_mode") == "LOCAL_ONLY",
            status=nested(p640, "status"),
            execution_mode=nested(p640, "execution_mode"),
        )
    )
    checks.append(
        step(
            "p640_current_plugin_tree",
            bool(current_plugin_tree)
            and nested(p640, "plugin_tree_sha") == current_plugin_tree,
            evidence_plugin_tree_sha=nested(p640, "plugin_tree_sha"),
            current_plugin_tree_sha=current_plugin_tree,
        )
    )
    checks.append(
        step(
            "p640_build_is_p640_2",
            nested(p640, "artifact", "package") == "base-conhecimento-inteligencia-integrada-0.6.0-dev-p640.2.zip",
            package=nested(p640, "artifact", "package"),
        )
    )

    p650 = loaded["p650"]
    checks.append(
        step(
            "p650_local_pass",
            status_is(p650, "PASS") and nested(p650, "execution_mode") == "LOCAL_ONLY",
            status=nested(p650, "status"),
            execution_mode=nested(p650, "execution_mode"),
        )
    )
    checks.append(
        step(
            "p650_current_plugin_tree",
            bool(current_plugin_tree)
            and nested(p650, "current_source", "plugin_tree_sha") == current_plugin_tree
            and nested(p650, "p640_precondition", "plugin_tree_sha") == current_plugin_tree,
            p650_plugin_tree_sha=nested(p650, "current_source", "plugin_tree_sha"),
            p640_precondition_plugin_tree_sha=nested(p650, "p640_precondition", "plugin_tree_sha"),
            current_plugin_tree_sha=current_plugin_tree,
        )
    )
    p650_sha_step = None
    for row in (p650 or {}).get("steps", []):
        if isinstance(row, dict) and row.get("name") == "frozen_package_sha256":
            p650_sha_step = row
            break
    checks.append(
        step(
            "p650_frozen_sha_pass",
            isinstance(p650_sha_step, dict)
            and p650_sha_step.get("pass") is True
            and p650_sha_step.get("actual_sha256") == EXPECTED_PACKAGE_SHA256,
            actual_sha256=p650_sha_step.get("actual_sha256") if isinstance(p650_sha_step, dict) else None,
        )
    )

    p660 = loaded["p660"]
    checks.append(
        step(
            "p660_local_pass",
            status_is(p660, "PASS") and nested(p660, "execution_mode") == "LOCAL_ONLY",
            status=nested(p660, "status"),
            execution_mode=nested(p660, "execution_mode"),
        )
    )
    checks.append(
        step(
            "p660_same_source_and_package",
            nested(p660, "plugin_tree_sha") == current_plugin_tree
            and nested(p660, "artifact", "sha256") == EXPECTED_PACKAGE_SHA256,
            plugin_tree_sha=nested(p660, "plugin_tree_sha"),
            package_sha256=nested(p660, "artifact", "sha256"),
        )
    )

    env = loaded["p650_env"]
    checks.append(
        step(
            "p6503_environmental_smoke",
            status_is(env, "PASS")
            and nested(env, "artifact", "sha256") == EXPECTED_PACKAGE_SHA256
            and nested(env, "validated_scope", "runtime_smoke") is True
            and nested(env, "validated_scope", "search") is True
            and nested(env, "validated_scope", "public_experience") is True
            and nested(env, "validated_scope", "word_cloud") is True,
            status=nested(env, "status"),
            package_sha256=nested(env, "artifact", "sha256"),
        )
    )

    plugin_check = loaded["plugin_check"]
    checks.append(
        step(
            "official_plugin_check_local_pass",
            status_is(plugin_check, "PASS")
            and nested(plugin_check, "execution_mode") == "LOCAL_ONLY"
            and nested(plugin_check, "package_sha256") == EXPECTED_PACKAGE_SHA256
            and nested(plugin_check, "artifact_identity_match") is True
            and nested(plugin_check, "runtime_checks_enabled") is True
            and nested(plugin_check, "invariants", "github_actions_used") is False,
            status=nested(plugin_check, "status"),
            package_sha256=nested(plugin_check, "package_sha256"),
        )
    )

    rollback = loaded["rollback"]
    checks.append(
        step(
            "rollback_pass_and_data_preserved",
            status_is(rollback, "PASS")
            and nested(rollback, "execution_mode") == "LOCAL_ONLY"
            and nested(rollback, "candidate", "sha256") == EXPECTED_PACKAGE_SHA256
            and nested(rollback, "data_preserved") is True
            and nested(rollback, "invariants", "github_actions_used") is False,
            status=nested(rollback, "status"),
            candidate_sha256=nested(rollback, "candidate", "sha256"),
            data_preserved=nested(rollback, "data_preserved"),
        )
    )

    summary = loaded["final_summary"]
    checks.append(
        step(
            "final_local_summary_same_source_and_package",
            isinstance(summary, dict)
            and nested(summary, "execution_mode") == "LOCAL_ONLY"
            and nested(summary, "plugin_tree_sha") == current_plugin_tree
            and nested(summary, "candidate", "sha256") == EXPECTED_PACKAGE_SHA256
            and nested(summary, "github_actions_used") is False,
            plugin_tree_sha=nested(summary, "plugin_tree_sha"),
            candidate_sha256=nested(summary, "candidate", "sha256"),
        )
    )

    package_present = PACKAGE.is_file()
    package_sha = sha256(PACKAGE) if package_present else None
    checks.append(
        step(
            "frozen_package_present_and_exact",
            package_present and package_sha == EXPECTED_PACKAGE_SHA256,
            package=str(PACKAGE.relative_to(ROOT)),
            actual_sha256=package_sha,
            expected_sha256=EXPECTED_PACKAGE_SHA256,
        )
    )

    # Explicitly preserve product-governance boundaries.
    boundary_sources = [p640, p650, p660, env, plugin_check, rollback, summary]
    forbidden_true: list[str] = []
    for idx, data in enumerate(boundary_sources):
        if not isinstance(data, dict):
            continue
        for key in ("cutover_authorized", "retirement_authorized"):
            if data.get(key) is True:
                forbidden_true.append(f"source[{idx}].{key}")
        invariants = data.get("invariants")
        if isinstance(invariants, dict):
            for key in ("cutover_authorized", "retirement_authorized"):
                if invariants.get(key) is True:
                    forbidden_true.append(f"source[{idx}].invariants.{key}")
    checks.append(
        step(
            "no_cutover_or_retirement_authorized",
            not forbidden_true,
            violations=forbidden_true,
        )
    )

    passed = all(row["pass"] for row in checks)

    report = {
        "schema_version": "1.0.0",
        "gate": "P-670",
        "phase": "PREMIUM_FOUNDATION_PREFLIGHT",
        "status": "PASS_PRECONDITIONS" if passed else "BLOCKED",
        "execution_mode": "LOCAL_ONLY",
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source_commit": current_commit,
        "plugin_tree_sha": current_plugin_tree,
        "artifact": {
            "name": EXPECTED_PACKAGE_NAME,
            "sha256": EXPECTED_PACKAGE_SHA256,
        },
        "checks": checks,
        "historical_gate_policy": {
            "p600_p610_p620_are_historical": True,
            "material_claims_reproved_locally_by": [
                "P640 local WPCS/PHPUnit/build",
                "P650 local deterministic package and checksum",
                "P660 local security WPCS/inventory",
                "Official Plugin Check on frozen p650.3",
            ],
            "remote_ci_not_used_to_close_current_gates": True,
        },
        "ledger": {
            "updated_by_validator": False,
            "candidate_ids": ["PROD-003", "PROD-004", "PROD-005", "PROD-006"],
            "human_evidence_review_required": True,
        },
        "invariants": {
            "github_actions_used_as_gate_executor": False,
            "version_1_0_authorized": False,
            "cutover_authorized": False,
            "retirement_authorized": False,
            "bulk_migration_authorized": False,
        },
        "next_gate": "P670_LEDGER_AND_CLOSEOUT_REVIEW" if passed else "P670_EVIDENCE_REMEDIATION",
    }

    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(report, ensure_ascii=False, indent=2))
    return 0 if passed else 1


if __name__ == "__main__":
    raise SystemExit(main())
