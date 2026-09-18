#!/usr/bin/env python3
"""T100E static regression runner.

No WordPress bootstrap, database, network or editorial write is performed.
Run from repository root:
    python tools/t100e/regression_runner.py
    python tools/t100e/regression_runner.py --json evidence.json
"""

from __future__ import annotations

import argparse
import hashlib
import json
import re
import shutil
import subprocess
import sys
from pathlib import Path

PLUGIN_REL = Path("plugin/base-conhecimento-inteligencia-integrada")
BOOTSTRAP = "base-conhecimento-inteligencia-integrada.php"

REQUIRED_FALSE_FLAGS = (
    "BDC_KB_SPEC004_G245_T099C_CANARY_BUILD",
    "BDC_KB_SPEC004_G245_T100A_BATCH_AUTHORIZATION_PACK_BUILD",
)

WARN_IF_TRUE_FLAGS = (
    "BDC_KB_SPEC004_G245_PREFLIGHT_BUILD",
    "BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD",
)

EXPECTED_TRUE_FLAGS = (
    "BDC_KB_SPEC004_G245_T100A_POST_WORKSPACE_BUILD",
    "BDC_KB_SPEC004_G245_T100C_CORE_BLOCKS_ACTIVITY_BUILD",
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

KNOWN_WRITE_SURFACES = {
    "class-summary-store.php",
    "class-classification-store.php",
    "class-review-store.php",
    "class-block-migration-journal-store.php",
    "class-block-migration-lock.php",
    "class-post-core-blocks-executor-t100d.php",
    "class-elementor-migration-journal-store.php",
    "class-elementor-migration-lock.php",
}

ENGINEERING_NAME_MARKERS = (
    "-smoke.php",
    "-diagnostic.php",
    "acceptance",
    "content-profile.php",
    "production-preflight.php",
)


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


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--root", default=".", help="repository root")
    ap.add_argument("--json", dest="json_path", help="write full JSON report")
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

    checks["counts"] = {
        "php": len(php_files),
        "css": len(css_files),
        "js": len(js_files),
        "includes": len(list(includes.glob("*.php"))),
    }

    # Unconditional require integrity.
    prefix = source.split("if ( defined( 'BDC_KB_SPEC004_PROFILE_BUILD' )", 1)[0]
    requires = re.findall(r"require_once BDC_KB_DIR \. '([^']+)'", prefix)
    missing = [rel for rel in requires if not (plugin / rel).is_file()]
    checks["unconditional_requires"] = {
        "count": len(requires),
        "missing": missing,
    }
    if missing:
        failures.append("missing unconditional require(s): " + ", ".join(missing))

    # Build flags.
    flags = {}
    for name in REQUIRED_FALSE_FLAGS + WARN_IF_TRUE_FLAGS + EXPECTED_TRUE_FLAGS:
        flags[name] = flag_value(source, name)
    flags["BDC_KB_ELEMENTOR_WRITER_ENABLED"] = flag_value(source, "BDC_KB_ELEMENTOR_WRITER_ENABLED")
    checks["flags"] = flags

    for name in REQUIRED_FALSE_FLAGS:
        if flags[name] is not False:
            failures.append(f"{name} must be false")
    for name in EXPECTED_TRUE_FLAGS:
        if flags[name] is not True:
            failures.append(f"{name} must be true")
    if flags["BDC_KB_ELEMENTOR_WRITER_ENABLED"] is not False:
        failures.append("BDC_KB_ELEMENTOR_WRITER_ENABLED must be false")
    for name in WARN_IF_TRUE_FLAGS:
        if flags[name] is True:
            warnings.append(f"{name} still enabled; candidate for T100E cleanup")

    # Read-only Core Blocks activity safety.
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

    # Engineering/test-only material still shipped.
    engineering_files = sorted(
        p.name for p in includes.glob("*.php") if classify_engineering(p.name)
    )
    checks["engineering_files_in_installable_tree"] = engineering_files
    if engineering_files:
        warnings.append(
            f"{len(engineering_files)} engineering/test files still ship in plugin tree"
        )

    # Writer inventory.
    writer_inventory = {}
    unknown_writer_files = []
    for path in includes.glob("*.php"):
        text = path.read_text(encoding="utf-8")
        hits = sorted({p for p in WRITE_PATTERNS if p in text})
        if hits:
            writer_inventory[path.name] = hits
            if path.name not in KNOWN_WRITE_SURFACES:
                unknown_writer_files.append(path.name)
    checks["writer_inventory"] = writer_inventory
    checks["unknown_writer_files"] = sorted(unknown_writer_files)
    if unknown_writer_files:
        warnings.append(
            "writer-like calls outside current allowlist: " + ", ".join(sorted(unknown_writer_files))
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

    # Duplicate defensive-family signal.
    paired = {}
    for suffix in (
        "migration-journal.php",
        "migration-journal-store.php",
        "stale-source-guard.php",
        "migration-dry-run.php",
        "migration-batch-plan.php",
        "migration-lock.php",
    ):
        block = includes / ("class-block-" + suffix)
        elem = includes / ("class-elementor-" + suffix)
        paired[suffix] = {"block": block.exists(), "elementor": elem.exists()}
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

    report = {
        "schema_version": "1.0.0",
        "gate": "T100E",
        "mode": "static_regression_runner",
        "plugin_version": re.search(
            r"define\(\s*'BDC_KB_VERSION'\s*,\s*'([^']+)'\s*\)", source
        ).group(1),
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
