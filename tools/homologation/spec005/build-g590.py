#!/usr/bin/env python3
"""Deterministic homologation builder for SPEC-005 / G-590.

The source branch remains development-configured. This builder creates a temporary
staging copy, applies only homologation version/flags, runs local gates, then
produces the same deterministic installable artifact twice and requires identical
SHA-256.

No WordPress bootstrap, database access, network access or editorial write occurs.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import re
import shutil
import subprocess
import sys
import tempfile
import zipfile
from pathlib import Path

PLUGIN_REL = Path("plugin/base-conhecimento-inteligencia-integrada")
BOOTSTRAP = "base-conhecimento-inteligencia-integrada.php"
VERSION = "0.5.1-rc.5"
BUILD_LABEL = "g590.5"

ENGINEERING_FLAGS_FALSE = (
    "BDC_KB_SPEC004_PROFILE_BUILD",
    "BDC_KB_SPEC004_G220_SMOKE_BUILD",
    "BDC_KB_SPEC004_G230_SMOKE_BUILD",
    "BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD",
    "BDC_KB_SPEC004_KD_V2_SMOKE_BUILD",
    "BDC_KB_SPEC004_FINAL_DIAG_BUILD",
    "BDC_KB_SPEC004_PIPELINE_DIAG_BUILD",
    "BDC_KB_SPEC004_G245_PREFLIGHT_BUILD",
    "BDC_KB_SPEC004_G245_PROJECTION_SMOKE_BUILD",
    "BDC_KB_SPEC004_G245_JOURNAL_SMOKE_BUILD",
    "BDC_KB_SPEC004_G245_BLOCK_PROJECTION_SMOKE_BUILD",
    "BDC_KB_SPEC004_G245_EDITORIAL_FIDELITY_SMOKE_BUILD",
    "BDC_KB_SPEC004_G245_LOSSLESS_ROUNDTRIP_SMOKE_BUILD",
    "BDC_KB_SPEC004_G245_EDITORIAL_PARITY_SMOKE_BUILD",
    "BDC_KB_SPEC004_G245_BLOCK_MIGRATION_READINESS_SMOKE_BUILD",
    "BDC_KB_SPEC004_G245_STORAGE_LOCK_SMOKE_BUILD",
    "BDC_KB_SPEC004_G245_AUTHORIZATION_PACK_SMOKE_BUILD",
    "BDC_KB_SPEC004_G245_T099C_CANARY_BUILD",
    "BDC_KB_SPEC004_G245_T100A_BATCH_AUTHORIZATION_PACK_BUILD",
    "BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD",
    "BDC_KB_SPEC004_T100E_E6_REGRESSION_BUILD",
    "BDC_KB_SPEC004_G250_LIFECYCLE_BUILD",
    "BDC_KB_SPEC005_R500_DIAGNOSTIC_BUILD",
    "BDC_KB_SPEC005_R510_LEGACY_GOLDEN_BUILD",
    "BDC_KB_SPEC005_R510_GOLDEN_BASELINE_BUILD",
    "BDC_KB_SPEC005_R510_GOLDEN_AUTO_VALIDATOR_BUILD",
    "BDC_KB_SPEC005_G540_CORPUS_RUNNER_BUILD",
    "BDC_KB_SPEC005_G550_GOLDEN_RUNNER_BUILD",
    "BDC_KB_SPEC005_G560_SEARCH_UX_RUNNER_BUILD",
    "BDC_KB_SPEC005_G570_SECURITY_PERFORMANCE_BUILD",
    "BDC_KB_SPEC005_G580_LIFECYCLE_BUILD",
    "BDC_KB_SPEC005_G585_ASI_INDEPENDENCE_BUILD",
    "BDC_KB_P580_PUBLIC_INVENTORY_BUILD",
    "BDC_KB_UX004_H030_TECHNICAL_BUILD",
)

REQUIRED_TRUE_FLAGS = (
    "BDC_KB_SPEC004_G245_T100A_POST_WORKSPACE_BUILD",
    "BDC_KB_SPEC004_G245_T100C_CORE_BLOCKS_ACTIVITY_BUILD",
    "BDC_KB_SPEC005_G530_SEARCH_ENGINE_BUILD",
    "BDC_KB_SPEC005_G590_SECTION_BUILD",
    "BDC_KB_PUBLIC_EXPERIENCE_PREVIEW_BUILD",
    "BDC_KB_WORD_CLOUD_BUILD",
)

REQUIRED_ARTIFACT_FILES = (
    "includes/class-search-section-runner-g590.php",
    "assets/js/search-section-g590.js",
    "includes/class-golden-suite-loader.php",
    "includes/class-golden-gate-runner-g550.php",
    "resources/search/golden-relevance-v1.0.0.json",
    "resources/search/technical-challenge-v1.0.0.json",
    "templates/public-home-preview.php",
    "templates/public-article-preview.php",
    "uninstall.php",
)

FORBIDDEN_ARTIFACT_FILES = (
    "includes/class-search-security-performance-runner-g570.php",
    "includes/class-search-lifecycle-runner-g580.php",
    "includes/class-search-independence-runner-g585.php",
    "includes/class-public-home-technical-runner-h030.php",
    "includes/class-public-experience-inventory-runner-p580.php",
)

UNIT_TESTS = (
    "tests/unit/spec001-summary-store.php",
    "tests/unit/spec002-classification-contract.php",
    "tests/unit/spec003-review-store.php",
    "tests/unit/spec004-content-extractor.php",
    "tests/unit/spec004-knowledge-document.php",
    "tests/unit/spec004-hierarchy-v21.php",
    "tests/unit/spec005-search-engine.php",
    "tests/unit/spec005-golden-gate.php",
    "tests/unit/spec005-g590-resumable-runner.php",
    "tests/unit/spec005-g590-admin-assets.php",
    "tests/unit/spec005-g590-evidence-contract-v13.php",
    "tests/unit/spec005-runtime-resources.php",
    "tests/unit/spec005-section-retrieval-g590.php",
    "tests/unit/spec005-section-runner-g590.php",
)

def sha256(path: Path) -> str:
    return hashlib.sha256(path.read_bytes()).hexdigest()

def git_provenance(repo: Path) -> dict[str, object]:
    git = shutil.which("git")
    if not git:
        return {"available": False, "head": "", "dirty": None}

    head = run([git, "rev-parse", "HEAD"], repo)
    if head.returncode != 0:
        return {"available": False, "head": "", "dirty": None}

    status = run([git, "status", "--porcelain"], repo)
    if status.returncode != 0:
        raise RuntimeError("failed to inspect git status")

    branch = run([git, "rev-parse", "--abbrev-ref", "HEAD"], repo)
    if branch.returncode != 0:
        raise RuntimeError("failed to inspect git branch")

    return {
        "available": True,
        "head": head.stdout.strip(),
        "branch": branch.stdout.strip(),
        "dirty": bool(status.stdout.strip()),
    }

def run(cmd: list[str], cwd: Path) -> subprocess.CompletedProcess[str]:
    return subprocess.run(
        cmd,
        cwd=cwd,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        text=True,
        check=False,
    )

def require_success(label: str, result: subprocess.CompletedProcess[str]) -> str:
    if result.returncode != 0:
        raise RuntimeError(f"{label} failed\n{result.stdout}")
    return result.stdout

def set_flag(source: str, name: str, value: bool) -> str:
    target = "true" if value else "false"
    pattern = re.compile(
        r"(define\(\s*['\"]" + re.escape(name) + r"['\"]\s*,\s*)(true|false)(\s*\)\s*;)",
        re.I,
    )
    updated, count = pattern.subn(r"\1" + target + r"\3", source, count=1)
    if count != 1:
        raise RuntimeError(f"build flag not found exactly once: {name}")
    return updated

def patch_bootstrap(path: Path, build_id: str) -> dict[str, object]:
    source = path.read_text(encoding="utf-8")

    source, header_count = re.subn(
        r"(?m)^ \* Version:\s*.+$",
        f" * Version: {VERSION}",
        source,
        count=1,
    )
    if header_count != 1:
        raise RuntimeError("plugin header Version not found exactly once")

    source, constant_count = re.subn(
        r"define\(\s*'BDC_KB_VERSION'\s*,\s*'[^']+'\s*\);",
        f"define( 'BDC_KB_VERSION', '{VERSION}' );",
        source,
        count=1,
    )
    if constant_count != 1:
        raise RuntimeError("BDC_KB_VERSION not found exactly once")

    if "BDC_KB_BUILD_ID" in source:
        raise RuntimeError("source bootstrap must not carry a persistent engineering BUILD_ID")
    source = source.replace(
        f"define( 'BDC_KB_VERSION', '{VERSION}' );",
        f"define( 'BDC_KB_VERSION', '{VERSION}' );\ndefine( 'BDC_KB_BUILD_ID', '{build_id}' );",
        1,
    )

    for name in ENGINEERING_FLAGS_FALSE:
        source = set_flag(source, name, False)
    for name in REQUIRED_TRUE_FLAGS:
        source = set_flag(source, name, True)

    path.write_text(source, encoding="utf-8")

    return {
        "version": VERSION,
        "build_id": build_id,
        "engineering_flags_false": list(ENGINEERING_FLAGS_FALSE),
        "required_true_flags": list(REQUIRED_TRUE_FLAGS),
    }

def lint_tree(plugin: Path) -> dict[str, object]:
    php = shutil.which("php")
    if not php:
        raise RuntimeError("php CLI is required for G-590 homologation build")

    failures: list[dict[str, str]] = []
    checked = 0
    for path in sorted(plugin.rglob("*.php")):
        result = run([php, "-l", str(path)], plugin)
        checked += 1
        if result.returncode != 0:
            failures.append({
                "file": str(path.relative_to(plugin)).replace("\\", "/"),
                "output": result.stdout.strip(),
            })

    if failures:
        raise RuntimeError("PHP lint failure(s): " + json.dumps(failures, ensure_ascii=False))

    return {"checked": checked, "failures": failures}

def lint_zip(path: Path) -> dict[str, object]:
    php = shutil.which("php")
    if not php:
        raise RuntimeError("php CLI is required for G-590 homologation build")

    with tempfile.TemporaryDirectory(prefix="bdc-g590-unzip-") as tmp:
        base = Path(tmp)
        with zipfile.ZipFile(path) as zf:
            zf.extractall(base)

        failures: list[dict[str, str]] = []
        checked = 0
        for php_file in sorted(base.rglob("*.php")):
            result = run([php, "-l", str(php_file)], base)
            checked += 1
            if result.returncode != 0:
                failures.append({
                    "file": str(php_file.relative_to(base)).replace("\\", "/"),
                    "output": result.stdout.strip(),
                })

        if failures:
            raise RuntimeError("ZIP PHP lint failure(s): " + json.dumps(failures, ensure_ascii=False))

        return {"checked": checked, "failures": failures}

def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--root", default=".", help="repository root")
    ap.add_argument(
        "--output",
        default=f"dist/base-conhecimento-inteligencia-integrada-{VERSION}-{BUILD_LABEL}.zip",
    )
    ap.add_argument(
        "--manifest",
        default=f"dist/base-conhecimento-inteligencia-integrada-{VERSION}-{BUILD_LABEL}.manifest.json",
    )
    ap.add_argument(
        "--evidence",
        default=f"dist/g590-local-package-validation.json",
    )
    args = ap.parse_args()

    repo = Path(args.root).resolve()
    plugin = repo / PLUGIN_REL
    bootstrap = plugin / BOOTSTRAP
    build_tool = repo / "tools/t100e/build_release.py"
    regression_tool = repo / "tools/t100e/regression_runner.py"

    for required in (bootstrap, build_tool, regression_tool):
        if not required.is_file():
            raise SystemExit(f"required file missing: {required}")

    local_gates: dict[str, object] = {}
    provenance = git_provenance(repo)
    if not provenance.get("available"):
        raise SystemExit("G-590 homologation build requires git provenance")
    if provenance.get("dirty"):
        raise SystemExit("refusing G-590 build from dirty git working tree")
    head = str(provenance.get("head") or "")
    if not re.fullmatch(r"[a-f0-9]{40}", head):
        raise SystemExit("invalid git HEAD provenance")
    build_id = f"{BUILD_LABEL}-{head[:12]}"

    php = shutil.which("php")
    if not php:
        raise SystemExit("php CLI is required")

    for test_rel in UNIT_TESTS:
        test_path = repo / test_rel
        result = run([php, str(test_path)], repo)
        local_gates[test_rel] = {
            "returncode": result.returncode,
            "output": result.stdout,
        }
        require_success(test_rel, result)

    regression = run(
        [sys.executable, str(regression_tool), "--root", str(repo)],
        repo,
    )
    local_gates["t100e_regression"] = {
        "returncode": regression.returncode,
        "output": regression.stdout,
    }
    require_success("T100E regression", regression)

    with tempfile.TemporaryDirectory(prefix="bdc-g590-build-") as tmp:
        staging = Path(tmp) / "repo"
        staging_plugin = staging / PLUGIN_REL
        staging_tools = staging / "tools/t100e"
        staging_plugin.parent.mkdir(parents=True, exist_ok=True)
        staging_tools.mkdir(parents=True, exist_ok=True)

        shutil.copytree(plugin, staging_plugin)
        shutil.copy2(build_tool, staging_tools / "build_release.py")
        shutil.copy2(regression_tool, staging_tools / "regression_runner.py")

        build_profile = patch_bootstrap(staging_plugin / BOOTSTRAP, build_id)
        source_lint = lint_tree(staging_plugin)

        output = Path(args.output).resolve()
        manifest_path = Path(args.manifest).resolve()
        output.parent.mkdir(parents=True, exist_ok=True)
        manifest_path.parent.mkdir(parents=True, exist_ok=True)

        first = Path(tmp) / "first.zip"
        second = Path(tmp) / "second.zip"
        first_manifest = Path(tmp) / "first.json"
        second_manifest = Path(tmp) / "second.json"

        cmd_base = [sys.executable, str(staging_tools / "build_release.py"), "--root", str(staging)]
        require_success(
            "deterministic build pass 1",
            run(cmd_base + ["--output", str(first), "--manifest", str(first_manifest)], staging),
        )
        require_success(
            "deterministic build pass 2",
            run(cmd_base + ["--output", str(second), "--manifest", str(second_manifest)], staging),
        )

        sha1 = sha256(first)
        sha2 = sha256(second)
        if sha1 != sha2 or first.read_bytes() != second.read_bytes():
            raise RuntimeError(f"deterministic build mismatch: {sha1} != {sha2}")

        shutil.copy2(first, output)
        manifest_data = json.loads(first_manifest.read_text(encoding="utf-8"))
        if manifest_data.get("plugin_version") != VERSION:
            raise RuntimeError(
                f"manifest product version mismatch: {manifest_data.get('plugin_version')} != {VERSION}"
            )
        if manifest_data.get("build_id") != build_id:
            raise RuntimeError(
                f"manifest build id mismatch: {manifest_data.get('build_id')} != {build_id}"
            )

        included_files = set((manifest_data.get("included_files") or {}).keys())
        missing_required = sorted(set(REQUIRED_ARTIFACT_FILES) - included_files)
        forbidden_present = sorted(set(FORBIDDEN_ARTIFACT_FILES) & included_files)
        if missing_required:
            raise RuntimeError(
                "G-590 artifact missing required runtime file(s): " + ", ".join(missing_required)
            )
        if forbidden_present:
            raise RuntimeError(
                "G-590 artifact contains unrelated engineering runner(s): " + ", ".join(forbidden_present)
            )

        artifact_contract = {
            "required_files": list(REQUIRED_ARTIFACT_FILES),
            "missing_required": missing_required,
            "forbidden_files": list(FORBIDDEN_ARTIFACT_FILES),
            "forbidden_present": forbidden_present,
            "pass": True,
        }

        manifest_data["g590_build_profile"] = build_profile
        manifest_data["deterministic_second_sha256"] = sha2
        manifest_data["deterministic_equal"] = True
        manifest_path.write_text(
            json.dumps(manifest_data, ensure_ascii=False, indent=2, sort_keys=True) + "\n",
            encoding="utf-8",
        )

        zip_lint = lint_zip(output)

        artifact_regression_json = Path(tmp) / "artifact-regression.json"
        artifact_regression = run(
            [
                sys.executable,
                str(staging_tools / "regression_runner.py"),
                "--root",
                str(staging),
                "--artifact-zip",
                str(output),
                "--json",
                str(artifact_regression_json),
            ],
            staging,
        )
        require_success("T100E staged artifact regression", artifact_regression)
        staged_artifact_regression = json.loads(
            artifact_regression_json.read_text(encoding="utf-8")
        )

    evidence = {
        "schema_version": "1.0.0",
        "gate": "G-590-PACKAGE",
        "mode": "local_deterministic_homologation_build",
        "plugin_version": VERSION,
        "build_id": build_id,
        "source_provenance": provenance,
        "local_gates": local_gates,
        "source_lint": source_lint,
        "zip_lint": zip_lint,
        "staged_artifact_regression": staged_artifact_regression,
        "artifact_contract": artifact_contract,
        "artifact": {
            "path": str(output),
            "sha256": sha256(output),
            "manifest": str(manifest_path),
            "deterministic_equal": True,
        },
        "build_profile": build_profile,
        "pass": True,
    }

    evidence_path = Path(args.evidence).resolve()
    evidence_path.parent.mkdir(parents=True, exist_ok=True)
    evidence_path.write_text(
        json.dumps(evidence, ensure_ascii=False, indent=2, sort_keys=True) + "\n",
        encoding="utf-8",
    )

    print(json.dumps(evidence, ensure_ascii=False, indent=2, sort_keys=True))
    return 0

if __name__ == "__main__":
    raise SystemExit(main())
