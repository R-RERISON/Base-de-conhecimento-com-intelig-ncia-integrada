#!/usr/bin/env python3
"""P-670 Premium Foundation Acceptance preflight.

Consolida evidências finais da SPEC-006 sem promover Ledger ou autorizar cutover.
Fail-closed para evidência ausente, stale, artefato divergente ou disposition
incompleta. O Plugin Check runtime oficial pode permanecer como limitação
ambiental somente quando a execução estática oficial e o runtime nativo do BDC
estiverem comprovados no mesmo artefato e houver disposition explícita.
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

EXPECTED_PACKAGE_NAME = "base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.4.zip"
EXPECTED_PACKAGE_SHA256 = "0e4860ed0be34033c63358c9f0798d7c9564420dd738fc66d587180fbf14cf77"
PACKAGE = DIST / EXPECTED_PACKAGE_NAME
OUTPUT = EVIDENCE / "spec006-p670-preflight-current.json"

PATHS = {
    "p600": EVIDENCE / "spec006-p600-metadata-license-pass-20260929.json",
    "p610": EVIDENCE / "spec006-p610-tooling-pass-20260929.json",
    "p620": EVIDENCE / "spec006-p620-plugin-check-disposition-20260929.json",
    "p630": EVIDENCE / "spec006-p630-domain-closure-pass-20260929.json",
    "p640": EVIDENCE / "spec006-p640-local-validation-current.json",
    "p640_env": EVIDENCE / "spec006-p640-environmental-reconciliation-current.json",
    "p650": EVIDENCE / "spec006-p650-local-package-validation-current.json",
    "p660": EVIDENCE / "spec006-p660-local-validation-current.json",
    "wordpress_final": EVIDENCE / "spec006-p6504-wordpress-final-gates-current.json",
    "plugin_disposition": EVIDENCE / "spec006-p6504-plugin-check-disposition-current.json",
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


def nested(data: dict[str, Any] | None, *keys: str) -> Any:
    current: Any = data
    for key in keys:
        if not isinstance(current, dict):
            return None
        current = current.get(key)
    return current


def status_is(data: dict[str, Any] | None, *allowed: str) -> bool:
    return isinstance(data, dict) and str(data.get("status", "")) in allowed


def step(name: str, passed: bool, **details: Any) -> dict[str, Any]:
    return {"name": name, "pass": bool(passed), **details}


def artifact_sha(data: dict[str, Any] | None) -> Any:
    return nested(data, "artifact", "sha256")


def main() -> int:
    current_commit = git_revision("HEAD")
    current_plugin_tree = git_revision("HEAD:plugin/base-conhecimento-inteligencia-integrada")
    loaded = {name: load_json(path) for name, path in PATHS.items()}
    checks: list[dict[str, Any]] = []

    missing = [name for name, data in loaded.items() if data is None]
    checks.append(step("all_required_evidence_present", not missing, missing=missing))

    missing_governance = [
        str(path.relative_to(ROOT)) for path in GOVERNANCE_PATHS if not path.is_file()
    ]
    checks.append(
        step("governance_documents_present", not missing_governance, missing=missing_governance)
    )

    checks.extend(
        [
            step("p600_historical_status", status_is(loaded["p600"], "PASS")),
            step("p610_historical_status", status_is(loaded["p610"], "PASS")),
            step(
                "p620_historical_disposition",
                status_is(loaded["p620"], "PASS_INVENTORY_AND_DISPOSITION"),
            ),
            step("p630_environmental_domain_closure", status_is(loaded["p630"], "PASS")),
        ]
    )

    p640 = loaded["p640"]
    checks.append(
        step(
            "p640_local_pass_current_tree",
            status_is(p640, "PASS")
            and nested(p640, "execution_mode") == "LOCAL_ONLY"
            and bool(current_plugin_tree)
            and nested(p640, "plugin_tree_sha") == current_plugin_tree,
            evidence_plugin_tree_sha=nested(p640, "plugin_tree_sha"),
            current_plugin_tree_sha=current_plugin_tree,
        )
    )
    checks.append(
        step(
            "p640_build_is_p640_2",
            nested(p640, "artifact", "package")
            == "base-conhecimento-inteligencia-integrada-0.6.0-dev-p640.2.zip",
        )
    )

    p640_env = loaded["p640_env"]
    checks.append(
        step(
            "p640_environmental_runtime_reconciled",
            status_is(p640_env, "PASS")
            and nested(p640_env, "execution_mode") in {"LOCAL_ONLY", "WORDPRESS_CLICK_RUNNER"}
            and artifact_sha(p640_env) == EXPECTED_PACKAGE_SHA256
            and (
                nested(p640_env, "runtime_probe", "payload", "pass") is True
                or nested(p640_env, "runtime_probe", "pass") is True
            ),
            status=nested(p640_env, "status"),
            package_sha256=artifact_sha(p640_env),
        )
    )

    p650 = loaded["p650"]
    p650_sha_step = next(
        (
            row
            for row in (p650 or {}).get("steps", [])
            if isinstance(row, dict) and row.get("name") == "frozen_package_sha256"
        ),
        None,
    )
    checks.append(
        step(
            "p650_local_pass_current_tree",
            status_is(p650, "PASS")
            and nested(p650, "execution_mode") == "LOCAL_ONLY"
            and nested(p650, "current_source", "plugin_tree_sha") == current_plugin_tree
            and nested(p650, "p640_precondition", "plugin_tree_sha") == current_plugin_tree,
        )
    )
    checks.append(
        step(
            "p650_frozen_sha_pass",
            isinstance(p650_sha_step, dict)
            and p650_sha_step.get("pass") is True
            and p650_sha_step.get("actual_sha256") == EXPECTED_PACKAGE_SHA256,
            actual_sha256=(
                p650_sha_step.get("actual_sha256") if isinstance(p650_sha_step, dict) else None
            ),
        )
    )

    p660 = loaded["p660"]
    checks.append(
        step(
            "p660_local_pass_same_source_and_package",
            status_is(p660, "PASS")
            and nested(p660, "execution_mode") == "LOCAL_ONLY"
            and nested(p660, "plugin_tree_sha") == current_plugin_tree
            and artifact_sha(p660) == EXPECTED_PACKAGE_SHA256,
            status=nested(p660, "status"),
            package_sha256=artifact_sha(p660),
        )
    )

    wp_final = loaded["wordpress_final"]
    checks.append(
        step(
            "wordpress_final_same_artifact",
            artifact_sha(wp_final) == EXPECTED_PACKAGE_SHA256
            and nested(wp_final, "native_preflight", "identity", "pass") is True
            and nested(wp_final, "native_preflight", "runtime", "pass") is True
            and nested(wp_final, "rollback", "pass") is True
            and nested(wp_final, "rollback", "data_preserved") is True
            and nested(wp_final, "final_identity", "pass") is True,
            status=nested(wp_final, "status"),
            package_sha256=artifact_sha(wp_final),
        )
    )
    checks.append(
        step(
            "wordpress_final_scope",
            nested(wp_final, "gate_scope", "wordpress_environmental") is True
            and nested(wp_final, "gate_scope", "official_plugin_check_static") is True
            and nested(wp_final, "gate_scope", "native_runtime_validation") is True
            and nested(wp_final, "gate_scope", "rollback") is True,
            official_runtime=nested(wp_final, "gate_scope", "official_plugin_check_runtime"),
        )
    )

    disposition = loaded["plugin_disposition"]
    official_runtime = nested(wp_final, "gate_scope", "official_plugin_check_runtime")
    checks.append(
        step(
            "plugin_check_explicit_disposition",
            status_is(disposition, "PASS_WITH_EXPLICIT_DISPOSITION")
            and artifact_sha(disposition) == EXPECTED_PACKAGE_SHA256
            and nested(disposition, "official_static_completed") is True
            and nested(disposition, "unresolved_blocking_errors") == 0
            and nested(disposition, "unresolved_high_or_critical") == 0
            and nested(disposition, "global_ignore_used") is False
            and (
                official_runtime is True
                or (
                    official_runtime is False
                    and nested(disposition, "runtime_environment_limitation_accepted") is True
                    and nested(disposition, "native_runtime_validation") == "PASS"
                )
            ),
            status=nested(disposition, "status"),
            artifact_sha256=artifact_sha(disposition),
        )
    )

    package_present = PACKAGE.is_file()
    package_sha = sha256(PACKAGE) if package_present else None
    checks.append(
        step(
            "frozen_package_present_and_exact",
            package_present and package_sha == EXPECTED_PACKAGE_SHA256,
            actual_sha256=package_sha,
            expected_sha256=EXPECTED_PACKAGE_SHA256,
        )
    )

    boundary_sources = [p640, p640_env, p650, p660, wp_final, disposition]
    forbidden_true: list[str] = []
    for idx, data in enumerate(boundary_sources):
        if not isinstance(data, dict):
            continue
        for key in ("cutover_authorized", "retirement_authorized", "version_1_0_authorized"):
            if data.get(key) is True:
                forbidden_true.append(f"source[{idx}].{key}")
        invariants = data.get("invariants")
        if isinstance(invariants, dict):
            for key in ("cutover_authorized", "retirement_authorized", "version_1_0_authorized"):
                if invariants.get(key) is True:
                    forbidden_true.append(f"source[{idx}].invariants.{key}")
    checks.append(step("no_cutover_retirement_or_1_0_authorized", not forbidden_true, violations=forbidden_true))

    passed = all(row["pass"] for row in checks)
    report = {
        "schema_version": "1.1.0",
        "gate": "P-670",
        "phase": "PREMIUM_FOUNDATION_PREFLIGHT",
        "status": "PASS_PRECONDITIONS" if passed else "BLOCKED",
        "execution_mode": "LOCAL_ONLY",
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source_commit": current_commit,
        "plugin_tree_sha": current_plugin_tree,
        "artifact": {"name": EXPECTED_PACKAGE_NAME, "sha256": EXPECTED_PACKAGE_SHA256},
        "checks": checks,
        "plugin_check_policy": {
            "official_static_required": True,
            "official_runtime_preferred": True,
            "runtime_environment_limitation_allowed_only_with_explicit_disposition": True,
            "native_runtime_required_when_official_runtime_unavailable": True,
            "global_ignore_allowed": False,
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
        "next_gate": (
            "P670_LEDGER_AND_CLOSEOUT_REVIEW"
            if passed
            else "P670_EVIDENCE_REMEDIATION"
        ),
    }
    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(report, ensure_ascii=False, indent=2))
    return 0 if passed else 1


if __name__ == "__main__":
    raise SystemExit(main())
