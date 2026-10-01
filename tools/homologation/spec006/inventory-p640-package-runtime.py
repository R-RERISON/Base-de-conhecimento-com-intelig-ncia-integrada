#!/usr/bin/env python3
"""Inventário package/runtime do SPEC-006 / P-640.

Prepara o handoff para P-650 sem remover arquivos.
Não altera runtime, package ou dados.
"""

from __future__ import annotations

import json
import pathlib
import re
import sys
from datetime import datetime, timezone

ROOT = pathlib.Path(__file__).resolve().parents[3]
PLUGIN = ROOT / "plugin" / "base-conhecimento-inteligencia-integrada"
INCLUDES = PLUGIN / "includes"
DIST = ROOT / "dist"
OUTPUT = DIST / "p640-package-runtime-inventory.json"

CORE_LOADER = INCLUDES / "class-core-runtime-loader.php"
PRODUCT_REGISTRY = INCLUDES / "class-runtime-module-registry.php"
ENGINEERING_LOADER = INCLUDES / "class-engineering-module-loader.php"

FILE_PATTERN = re.compile(r"'(includes/[^']+\.php)'")


def declared_files(path: pathlib.Path) -> set[str]:
    text = path.read_text(encoding="utf-8")
    return set(FILE_PATTERN.findall(text))


def all_plugin_files() -> set[str]:
    result: set[str] = set()
    for path in PLUGIN.rglob("*"):
        if path.is_file():
            result.add(path.relative_to(PLUGIN).as_posix())
    return result


def main() -> int:
    required = (CORE_LOADER, PRODUCT_REGISTRY, ENGINEERING_LOADER)
    missing = [str(path.relative_to(ROOT)) for path in required if not path.is_file()]
    if missing:
        print(json.dumps({"status": "FAIL", "missing": missing}, indent=2))
        return 1

    core = declared_files(CORE_LOADER)
    product = declared_files(PRODUCT_REGISTRY)
    engineering = declared_files(ENGINEERING_LOADER)
    plugin_files = all_plugin_files()

    overlaps = {
        "core_product": sorted(core & product),
        "core_engineering": sorted(core & engineering),
        "product_engineering": sorted(product & engineering),
    }

    engineering_existing = sorted(path for path in engineering if path in plugin_files)
    engineering_missing = sorted(path for path in engineering if path not in plugin_files)

    php_files = sorted(path for path in plugin_files if path.endswith(".php"))
    owned_php = core | product | engineering | {
        "base-conhecimento-inteligencia-integrada.php",
        "includes/class-plugin.php",
        "uninstall.php",
    }
    unclassified_php = sorted(set(php_files) - owned_php)
    infrastructure_php = sorted(
        path for path in unclassified_php
        if path in {
            "includes/class-core-runtime-loader.php",
            "includes/class-runtime-module-registry.php",
            "includes/class-engineering-module-loader.php",
        }
    )
    template_php = sorted(path for path in unclassified_php if path.startswith("templates/"))
    legacy_elementor_candidates = sorted(
        path for path in unclassified_php
        if path.startswith("includes/class-elementor-")
    )
    historical_acceptance_candidates = sorted(
        path for path in unclassified_php
        if path in {"includes/class-real-content-acceptance.php"}
    )
    categorized = (
        set(infrastructure_php)
        | set(template_php)
        | set(legacy_elementor_candidates)
        | set(historical_acceptance_candidates)
    )
    other_unclassified_php = sorted(set(unclassified_php) - categorized)

    report = {
        "schema_version": "1.0.0",
        "gate": "P-640",
        "phase": "PACKAGE_RUNTIME_INVENTORY",
        "status": "PASS" if not engineering_missing else "FAIL",
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "counts": {
            "plugin_files": len(plugin_files),
            "plugin_php_files": len(php_files),
            "core_declared_php": len(core),
            "product_declared_php": len(product),
            "engineering_declared_php": len(engineering),
            "engineering_existing_php": len(engineering_existing),
            "engineering_missing_php": len(engineering_missing),
            "unclassified_php": len(unclassified_php),
            "legacy_elementor_candidates": len(legacy_elementor_candidates),
            "other_unclassified_php": len(other_unclassified_php),
        },
        "ownership": {
            "core": sorted(core),
            "product_modules": sorted(product),
            "engineering_homologation": engineering_existing,
            "infrastructure": infrastructure_php,
            "templates": template_php,
            "legacy_elementor_candidates": legacy_elementor_candidates,
            "historical_acceptance_candidates": historical_acceptance_candidates,
            "other_unclassified_php": other_unclassified_php,
            "unclassified_php": unclassified_php,
        },
        "overlaps": overlaps,
        "p650_handoff": {
            "production_exclusion_candidates": engineering_existing,
            "legacy_disposition_candidates": legacy_elementor_candidates + historical_acceptance_candidates,
            "remove_now": False,
            "reason": "P-640 apenas inventaria. Exclusão física ou retirement pertence ao P-650/P-014 conforme contrato.",
        },
        "invariants": {
            "files_deleted": False,
            "runtime_changed": False,
            "cutover_authorized": False,
            "retirement_authorized": False,
        },
        "next_gate": "P-650_PACKAGING_INSTALL_UPGRADE",
    }

    DIST.mkdir(parents=True, exist_ok=True)
    OUTPUT.write_text(
        json.dumps(report, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    print(json.dumps(report, ensure_ascii=False, indent=2))
    return 0 if report["status"] == "PASS" else 1


if __name__ == "__main__":
    sys.exit(main())
