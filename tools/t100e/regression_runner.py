#!/usr/bin/env python3
"""T100E static regression runner v1.1.

No WordPress bootstrap, database, network, or editorial write is performed.

Examples:
    python tools/t100e/regression_runner.py
    python tools/t100e/regression_runner.py --json evidence.json
    python tools/t100e/regression_runner.py --artifact-zip /path/plugin.zip
"""

from __future__ import annotations

import argparse
import hashlib
import json
import re
import shutil
import subprocess
import sys
import zipfile
from pathlib import Path

PLUGIN_REL = Path("plugin/base-conhecimento-inteligencia-integrada")
BOOTSTRAP = "base-conhecimento-inteligencia-integrada.php"

REQUIRED_FALSE_FLAGS = (
    "BDC_KB_SPEC004_G245_T099C_CANARY_BUILD",
    "BDC_KB_SPEC004_G245_T100A_BATCH_AUTHORIZATION_PACK_BUILD",
    "BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD",
    "BDC_KB_ELEMENTOR_WRITER_ENABLED",
)

EXPECTED_TRUE_FLAGS = (
    "BDC_KB_SPEC004_G245_T100A_POST_WORKSPACE_BUILD",
    "BDC_KB_SPEC004_G245_T100C_CORE_BLOCKS_ACTIVITY_BUILD",
)

WARN_IF_TRUE_FLAGS = (
    "BDC_KB_SPEC004_G245_PREFLIGHT_BUILD",
)

FORBIDDEN_IN_READ_ONLY_CORE_ACTIVITY = (
    "wp_update_post(",
    "update_post_meta(",
    "add_post_meta(",
    "wp_remote_",
    "do_shortcode(",
    "render_block(",
    "do_blocks(",
)

WRITE_PATTERNS = (
    "wp_update_post(",
    "update_post_meta(",
    "add_post_meta(",
    "delete_post_meta(",
    "set_object_terms(",
    "wp_set_object_terms(",
)

KNOWN_PRODUCT_WRITE_SURFACES = {
    "class-summary-store.php",
    "class-classification-store.php",
    "class-review-store.php",
    "class-block-migration-journal-store.php",
    "class-block-migration-lock.php",
}

KNOWN_ENGINEERING_WRITE_SURFACES = {
    "class-post-core-blocks-executor-t100d.php",
    "class-block-migration-canary-t099c.php",
    "class-elementor-migration-journal-store.php",
    "class-elementor-migration-lock.php",
}

ENGINEERING_NAME_MARKERS = (
    "-smoke.php",
    "-diagnostic.php",
    "acceptance",
    "content-profile.php",
    "production-preflight.php",
    "executor-t100d.php",
    "canary-t099c.php",
)

LEGACY_ELEMENTOR_MIGRATION_FILES = (
    "includes/class-elementor-projection-plan.php",
    "includes/class-elementor-gateway.php",
    "includes/class-elementor-migration-journal.php",
    "includes/class-elementor-migration-journal-store.php",
    "includes/class-elementor-stale-source-guard.php",
    "includes/class-elementor-migration-dry-run.php",
    "includes/class-elementor-migration-batch-plan.php",
    "includes/class-elementor-canary-readiness.php",
    "includes/class-elementor-migration-lock.php",
)

GOLDEN_RUNTIME_RESOURCES = (
    "resources/search/golden-relevance-v1.0.0.json",
    "resources/search/technical-challenge-v1.0.0.json",
)

CONDITIONAL_RUNTIME_RESOURCES = {
    "BDC_KB_SPEC005_G550_GOLDEN_RUNNER_BUILD": GOLDEN_RUNTIME_RESOURCES,
    "BDC_KB_SPEC005_G585_ASI_INDEPENDENCE_BUILD": GOLDEN_RUNTIME_RESOURCES,
    "BDC_KB_SPEC005_G590_SECTION_BUILD": GOLDEN_RUNTIME_RESOURCES,
    "BDC_KB_UX004_H030_TECHNICAL_BUILD": GOLDEN_RUNTIME_RESOURCES,
}

DEFENSIVE_PAIRS = {
    "journal": (
        "class-block-migration-journal.php",
        "class-elementor-migration-journal.php",
    ),
    "journal_store": (
        "class-block-migration-journal-store.php",
        "class-elementor-migration-journal-store.php",
    ),
    "stale_guard": (
        "class-block-migration-stale-source-guard.php",
        "class-elementor-stale-source-guard.php",
    ),
    "dry_run": (
        "class-block-migration-dry-run.php",
        "class-elementor-migration-dry-run.php",
    ),
    "batch_plan": (
        "class-block-migration-batch-plan.php",
        "class-elementor-migration-batch-plan.php",
    ),
    "lock": (
        "class-block-migration-lock.php",
        "class-elementor-migration-lock.php",
    ),
}


def sha256(path: Path) -> str:
    return hashlib.sha256(path.read_bytes()).hexdigest()


def flag_value(source: str, name: str):
    m = re.search(
        r"define\(\s*['\"]" + re.escape(name) + r"['\"]\s*,\s*(true|false)\s*\)",
        source,
        re.I,
    )
    return None if not m else m.group(1).lower() == "true"


def classify_engineering(filename: str) -> bool:
    return any(marker in filename for marker in ENGINEERING_NAME_MARKERS)


def all_requires(source: str) -> list[str]:
    return re.findall(r"require_once BDC_KB_DIR \. '([^']+)'", source)


def unconditional_requires(source: str) -> list[str]:
    prefix = source.split("if ( defined( 'BDC_KB_SPEC004_PROFILE_BUILD' )", 1)[0]
    return all_requires(prefix)


def conditional_requires(source: str) -> dict[str, list[str]]:
    out: dict[str, list[str]] = {}
    pattern = re.compile(
        r"if\s*\(\s*defined\(\s*'([^']+)'\s*\)\s*&&\s*\1\s*\)\s*\{"
        r"(.*?)\}",
        re.S,
    )
    for match in pattern.finditer(source):
        flag = match.group(1)
        paths = all_requires(match.group(2))
        if paths:
            out.setdefault(flag, []).extend(paths)
    return out


def inspect_artifact(path: Path, source_files: set[str], active_required: set[str]) -> dict[str, object]:
    result: dict[str, object] = {
        "path": str(path),
        "exists": path.is_file(),
        "sha256": "",
        "plugin_roots": [],
        "source_only": [],
        "artifact_only": [],
        "active_required_missing": [],
    }
    if not path.is_file():
        return result

    result["sha256"] = sha256(path)
    with zipfile.ZipFile(path) as zf:
        names = [n for n in zf.namelist() if n and not n.endswith("/")]
    roots = sorted({n.split("/", 1)[0] for n in names})
    result["plugin_roots"] = roots
    if len(roots) != 1:
        return result

    root = roots[0]
    artifact_rel = {
        n[len(root) + 1 :]
        for n in names
        if n.startswith(root + "/")
    }
    result["source_only"] = sorted(source_files - artifact_rel)
    result["artifact_only"] = sorted(artifact_rel - source_files)
    result["active_required_missing"] = sorted(active_required - artifact_rel)
    return result


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--root", default=".", help="repository root")
    ap.add_argument("--json", dest="json_path", help="write full JSON report")
    ap.add_argument("--artifact-zip", help="optional installable ZIP to compare")
    args = ap.parse_args()

    repo = Path(args.root).resolve()
    plugin = repo / PLUGIN_REL
    includes = plugin / "includes"
    bootstrap = plugin / BOOTSTRAP

    failures: list[str] = []
    warnings: list[str] = []
    checks: dict[str, object] = {}

    if not bootstrap.is_file() or not includes.is_dir():
        print("T100E FAIL: plugin path not found", file=sys.stderr)
        return 2

    source = bootstrap.read_text(encoding="utf-8")
    php_files = sorted(plugin.rglob("*.php"))
    css_files = sorted((plugin / "assets" / "css").glob("*.css"))
    js_files = sorted((plugin / "assets" / "js").glob("*.js"))
    source_rel_files = {
        str(path.relative_to(plugin)).replace("\\", "/")
        for path in plugin.rglob("*")
        if path.is_file()
    }

    checks["counts"] = {
        "php": len(php_files),
        "css": len(css_files),
        "js": len(js_files),
        "includes": len(list(includes.glob("*.php"))),
        "all_files": len(source_rel_files),
    }

    # Require integrity.
    uncond = unconditional_requires(source)
    cond = conditional_requires(source)
    all_req = sorted(set(all_requires(source)))
    missing_source = [rel for rel in all_req if not (plugin / rel).is_file()]
    checks["requires"] = {
        "unconditional_count": len(uncond),
        "conditional_flags": cond,
        "all_required_count": len(all_req),
        "missing_source": missing_source,
    }
    if missing_source:
        failures.append("missing source require(s): " + ", ".join(missing_source))

    # Build flags.
    all_flag_names = set(REQUIRED_FALSE_FLAGS + EXPECTED_TRUE_FLAGS + WARN_IF_TRUE_FLAGS + tuple(cond))
    flags = {name: flag_value(source, name) for name in sorted(all_flag_names)}
    checks["flags"] = flags

    for name in REQUIRED_FALSE_FLAGS:
        if flags.get(name) is not False:
            failures.append(f"{name} must be false")
    for name in EXPECTED_TRUE_FLAGS:
        if flags.get(name) is not True:
            failures.append(f"{name} must be true")
    for name in WARN_IF_TRUE_FLAGS:
        if flags.get(name) is True:
            warnings.append(f"{name} still enabled; not yet replaced by consolidated tooling")

    active_required = set(uncond)
    for flag, paths in cond.items():
        if flags.get(flag) is True:
            active_required.update(paths)

    active_runtime_resources = {}
    for flag, paths in CONDITIONAL_RUNTIME_RESOURCES.items():
        value = flag_value(source, flag)
        flags.setdefault(flag, value)
        if value is True:
            active_required.update(paths)
            active_runtime_resources[flag] = list(paths)
    checks["active_runtime_resources"] = active_runtime_resources

    missing_runtime_resources = sorted(
        rel for rel in active_required
        if rel.startswith("resources/") and not (plugin / rel).is_file()
    )
    checks["missing_runtime_resources"] = missing_runtime_resources
    if missing_runtime_resources:
        failures.append(
            "missing active runtime resource(s): " + ", ".join(missing_runtime_resources)
        )

    legacy_active = sorted(set(LEGACY_ELEMENTOR_MIGRATION_FILES) & active_required)
    checks["legacy_elementor_migration_active"] = legacy_active
    if legacy_active:
        failures.append(
            "historical Elementor migration contract(s) returned to active runtime: "
            + ", ".join(legacy_active)
        )

    # Core Blocks read-only preparation contract.
    core_activity = includes / "class-post-core-blocks-activity.php"
    core_text = core_activity.read_text(encoding="utf-8")
    forbidden_found = [p for p in FORBIDDEN_IN_READ_ONLY_CORE_ACTIVITY if p in core_text]
    checks["core_blocks_activity_read_only"] = {
        "forbidden_found": forbidden_found,
        "sha256": sha256(core_activity),
    }
    if forbidden_found:
        failures.append("read-only Core Blocks activity contains forbidden operation(s)")

    # Post-centric Workspace invariants.
    admin = (includes / "class-admin-page.php").read_text(encoding="utf-8")
    registry = (includes / "class-post-activity-registry.php").read_text(encoding="utf-8")
    workspace_invariants = {
        "manage_action": "Gerenciar" in admin,
        "post_context": "Post_Management_Context::build" in admin,
        "activity_registry": "Post_Activity_Registry::definitions" in admin,
        "content_activity": "'content'" in registry,
        "intelligence_activity": "'intelligence'" in registry,
        "core_blocks_activity": "'core_blocks'" in registry,
        "history_activity": "'history'" in registry,
    }
    checks["workspace_invariants"] = workspace_invariants
    if not all(workspace_invariants.values()):
        failures.append("Post Management Workspace invariant failed")

    # Engineering material inventory.
    engineering_files = sorted(
        p.name for p in includes.glob("*.php") if classify_engineering(p.name)
    )
    checks["engineering_files_in_source_tree"] = engineering_files
    checks["legacy_elementor_migration_files_in_source_tree"] = [
        rel for rel in LEGACY_ELEMENTOR_MIGRATION_FILES if (plugin / rel).is_file()
    ]
    if engineering_files:
        warnings.append(f"{len(engineering_files)} engineering/test files remain in source plugin tree")

    # Writer inventory.
    writer_inventory: dict[str, list[str]] = {}
    unknown_writer_files: list[str] = []
    engineering_writer_files: list[str] = []
    for path in includes.glob("*.php"):
        text = path.read_text(encoding="utf-8")
        hits = sorted({p for p in WRITE_PATTERNS if p in text})
        if not hits:
            continue
        writer_inventory[path.name] = hits
        if path.name in KNOWN_ENGINEERING_WRITE_SURFACES:
            engineering_writer_files.append(path.name)
        elif path.name not in KNOWN_PRODUCT_WRITE_SURFACES:
            unknown_writer_files.append(path.name)

    checks["writer_inventory"] = writer_inventory
    checks["engineering_writer_files"] = sorted(engineering_writer_files)
    checks["unknown_writer_files"] = sorted(unknown_writer_files)
    if unknown_writer_files:
        failures.append(
            "writer-like calls outside allowlist: " + ", ".join(sorted(unknown_writer_files))
        )
    if engineering_writer_files:
        warnings.append(
            "engineering writer source remains in tree: " + ", ".join(sorted(engineering_writer_files))
        )

    # Network / shortcode / dynamic rendering inventory.
    inventory_patterns = {
        "network": ("wp_remote_",),
        "shortcode_execution": ("do_shortcode(",),
        "dynamic_block_rendering": ("render_block(", "do_blocks("),
    }
    inventories = {}
    for label, patterns in inventory_patterns.items():
        found = {}
        for path in includes.glob("*.php"):
            text = path.read_text(encoding="utf-8")
            hits = [p for p in patterns if p in text]
            if hits:
                found[path.name] = hits
        inventories[label] = found
    checks["capability_inventory"] = inventories

    # Parallel defensive family inventory.
    paired = {}
    for label, (block_name, elementor_name) in DEFENSIVE_PAIRS.items():
        paired[label] = {
            "block": (includes / block_name).exists(),
            "elementor": (includes / elementor_name).exists(),
            "block_file": block_name,
            "elementor_file": elementor_name,
        }
    checks["parallel_defensive_families"] = paired
    if any(v["block"] and v["elementor"] for v in paired.values()):
        warnings.append("parallel Block/Elementor defensive families remain")

    # PHP lint.
    php = shutil.which("php")
    lint = {"available": bool(php), "checked": 0, "failures": []}
    if php:
        for path in php_files:
            result = subprocess.run(
                [php, "-l", str(path)],
                stdout=subprocess.PIPE,
                stderr=subprocess.STDOUT,
                text=True,
            )
            lint["checked"] += 1
            if result.returncode != 0:
                lint["failures"].append(
                    {"file": str(path.relative_to(repo)), "output": result.stdout.strip()}
                )
        if lint["failures"]:
            failures.append("PHP lint failure(s)")
    else:
        warnings.append("php executable unavailable; lint skipped")
    checks["php_lint"] = lint

    # Optional source/artifact comparison.
    if args.artifact_zip:
        artifact = inspect_artifact(Path(args.artifact_zip).resolve(), source_rel_files, active_required)
        checks["artifact"] = artifact
        if not artifact["exists"]:
            failures.append("artifact ZIP not found")
        elif len(artifact["plugin_roots"]) != 1:
            failures.append("artifact must contain exactly one plugin root")
        elif artifact["active_required_missing"]:
            failures.append(
                "artifact missing active runtime file(s): "
                + ", ".join(artifact["active_required_missing"])
            )
        if artifact["source_only"] or artifact["artifact_only"]:
            warnings.append("source tree and artifact file manifests differ")

    version_match = re.search(
        r"define\(\s*'BDC_KB_VERSION'\s*,\s*'([^']+)'\s*\)", source
    )
    build_match = re.search(
        r"define\(\s*'BDC_KB_BUILD_ID'\s*,\s*'([^']+)'\s*\)", source
    )
    report = {
        "schema_version": "1.1.0",
        "gate": "T100E",
        "mode": "static_regression_runner",
        "plugin_version": version_match.group(1) if version_match else "",
        "build_id": build_match.group(1) if build_match else "",
        "checks": checks,
        "warnings": warnings,
        "failures": failures,
        "pass": not failures,
    }

    encoded = json.dumps(report, ensure_ascii=False, indent=2, sort_keys=True)
    if args.json_path:
        Path(args.json_path).write_text(encoded + "\n", encoding="utf-8")
    print(encoded)
    return 0 if not failures else 1


if __name__ == "__main__":
    raise SystemExit(main())
