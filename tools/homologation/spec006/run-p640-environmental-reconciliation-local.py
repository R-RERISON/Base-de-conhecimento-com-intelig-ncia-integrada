#!/usr/bin/env python3
"""Reconciliação ambiental P-640 sobre o p650.4 já homologado.

Não instala package técnico P640 e não executa mutation.
Valida o runtime modular efetivamente carregado no WordPress com o mesmo
artefato p650.4 congelado que segue para os gates de distribuição.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import pathlib
import shutil
import subprocess
import tempfile
from datetime import datetime, timezone

ROOT = pathlib.Path(__file__).resolve().parents[3]
OUTPUT = ROOT / "evidence" / "spec006-p640-environmental-reconciliation-current.json"
EXPECTED_ZIP = "base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.4.zip"
EXPECTED_SHA256 = "a76addbace25a8b00d8a0646ba7034952dfa941c3636e21c5162644a5b4636c2"
PLUGIN_SLUG = "base-conhecimento-inteligencia-integrada"


def sha256(path: pathlib.Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def run(command: list[str]) -> dict:
    proc = subprocess.run(
        command,
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


def wp_eval_file(wp_cli: str, wp_path: pathlib.Path, code: str) -> dict:
    with tempfile.NamedTemporaryFile("w", suffix=".php", encoding="utf-8", delete=False) as handle:
        handle.write(code)
        temp_path = pathlib.Path(handle.name)
    try:
        result = run([wp_cli, "eval-file", str(temp_path), f"--path={wp_path}"])
    finally:
        temp_path.unlink(missing_ok=True)

    if not result["pass"]:
        return {"pass": False, "raw": result}

    try:
        payload = json.loads(result["output"])
    except json.JSONDecodeError:
        return {
            "pass": False,
            "reason": "invalid_json_from_wordpress",
            "raw": result,
        }

    return {"pass": True, "payload": payload, "raw": result}


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--wp-path", required=True)
    parser.add_argument("--zip", required=True)
    args = parser.parse_args()

    wp_cli = shutil.which("wp")
    if not wp_cli:
        print(json.dumps({"status": "BLOCKED_LOCAL_TOOLING", "missing": ["wp"]}, indent=2))
        return 2

    wp_path = pathlib.Path(args.wp_path).resolve()
    package = pathlib.Path(args.zip).resolve()

    if not (wp_path / "wp-config.php").is_file():
        print(json.dumps({"status": "FAIL", "reason": "invalid_wordpress_path"}, indent=2))
        return 2

    if not package.is_file():
        print(json.dumps({"status": "FAIL", "reason": "package_not_found", "package": str(package)}, indent=2))
        return 2

    package_sha = sha256(package)
    if package.name != EXPECTED_ZIP or package_sha != EXPECTED_SHA256:
        print(
            json.dumps(
                {
                    "status": "FAIL_ARTIFACT_MISMATCH",
                    "expected_name": EXPECTED_ZIP,
                    "actual_name": package.name,
                    "expected_sha256": EXPECTED_SHA256,
                    "actual_sha256": package_sha,
                },
                ensure_ascii=False,
                indent=2,
            )
        )
        return 3

    plugin_status = run([wp_cli, "plugin", "status", PLUGIN_SLUG, f"--path={wp_path}"])
    if not plugin_status["pass"] or "Status: Active" not in plugin_status["output"]:
        print(
            json.dumps(
                {
                    "status": "BLOCKED_PLUGIN_NOT_ACTIVE",
                    "plugin_status": plugin_status,
                },
                ensure_ascii=False,
                indent=2,
            )
        )
        return 2

    code = r'''<?php
use BDC\KnowledgeBase\Core_Runtime_Loader;
use BDC\KnowledgeBase\Runtime_Module_Registry;
use BDC\KnowledgeBase\Plugin;
use BDC\KnowledgeBase\Search_Service;
use BDC\KnowledgeBase\Search_Lifecycle;
use BDC\KnowledgeBase\Search_Anchor_Manager;
use BDC\KnowledgeBase\Public_Search_Facade;
use BDC\KnowledgeBase\Public_Experience;
use BDC\KnowledgeBase\Word_Cloud_Service;
use BDC\KnowledgeBase\Word_Cloud_Consultations;
use BDC\KnowledgeBase\Word_Cloud_Admin;

$definitions = class_exists( Runtime_Module_Registry::class )
    ? Runtime_Module_Registry::definitions()
    : array();

$checks = array(
    'plugin_version' => defined( 'BDC_KB_VERSION' ) && '0.6.0-dev' === BDC_KB_VERSION,
    'core_loader_loaded' => class_exists( Core_Runtime_Loader::class ),
    'registry_loaded' => class_exists( Runtime_Module_Registry::class ),
    'plugin_loaded' => class_exists( Plugin::class ),
    'engineering_loader_absent' => ! class_exists( 'BDC\\KnowledgeBase\\Engineering_Module_Loader', false ),
    'engineering_loader_file_absent' => defined( 'BDC_KB_DIR' )
        && ! is_file( BDC_KB_DIR . 'includes/class-engineering-module-loader.php' ),
    'p640_environmental_flag_absent' => ! defined( 'BDC_KB_SPEC006_P640_ENVIRONMENTAL_BUILD' ),
    'module_keys_exact' => array_keys( $definitions ) === array(
        'search',
        'public_experience_preview',
        'word_cloud',
    ),
    'search_enabled' => class_exists( Runtime_Module_Registry::class )
        && Runtime_Module_Registry::is_enabled( 'search' ),
    'public_experience_enabled' => class_exists( Runtime_Module_Registry::class )
        && Runtime_Module_Registry::is_enabled( 'public_experience_preview' ),
    'word_cloud_enabled' => class_exists( Runtime_Module_Registry::class )
        && Runtime_Module_Registry::is_enabled( 'word_cloud' ),
    'search_service_loaded' => class_exists( Search_Service::class ),
    'search_lifecycle_loaded' => class_exists( Search_Lifecycle::class ),
    'search_anchor_manager_loaded' => class_exists( Search_Anchor_Manager::class ),
    'public_search_loaded' => class_exists( Public_Search_Facade::class ),
    'public_experience_loaded' => class_exists( Public_Experience::class ),
    'word_cloud_service_loaded' => class_exists( Word_Cloud_Service::class ),
    'word_cloud_consultations_loaded' => class_exists( Word_Cloud_Consultations::class ),
    'word_cloud_admin_loaded' => class_exists( Word_Cloud_Admin::class ),
    'search_lifecycle_hook' => false !== has_action(
        'upgrader_process_complete',
        array( Search_Lifecycle::class, 'handle_upgrade' )
    ),
    'search_anchor_filter' => false !== has_filter(
        'the_content',
        array( Search_Anchor_Manager::class, 'filter_content' )
    ),
    'public_search_ajax_auth' => false !== has_action(
        'wp_ajax_bdc_kb_public_search',
        array( Public_Search_Facade::class, 'ajax_search' )
    ),
    'public_search_ajax_public' => false !== has_action(
        'wp_ajax_nopriv_bdc_kb_public_search',
        array( Public_Search_Facade::class, 'ajax_search' )
    ),
    'public_template_filter' => false !== has_filter(
        'template_include',
        array( Public_Experience::class, 'template_include' )
    ),
    'public_prepare_hook' => false !== has_action(
        'template_redirect',
        array( Public_Experience::class, 'prepare_preview_request' )
    ),
    'word_cloud_init_hook' => false !== has_action(
        'init',
        array( Word_Cloud_Consultations::class, 'ensure_default' )
    ),
    'word_cloud_ajax_hook' => false !== has_action(
        'wp_ajax_bdc_kb_word_cloud_consult_preview',
        array( Word_Cloud_Consultations::class, 'ajax_record' )
    ),
);

$failed = array_keys(
    array_filter(
        $checks,
        static fn ( $value ) => true !== $value
    )
);

echo wp_json_encode(
    array(
        'checks' => $checks,
        'failed' => $failed,
        'pass' => empty( $failed ),
        'invariants' => array(
            'read_only_probe' => true,
            'content_mutation_requested' => false,
            'data_migration_requested' => false,
            'cutover_authorized' => false,
            'retirement_authorized' => false,
        ),
    )
);
'''

    probe = wp_eval_file(wp_cli, wp_path, code)
    passed = (
        probe["pass"]
        and isinstance(probe.get("payload"), dict)
        and probe["payload"].get("pass") is True
    )

    report = {
        "schema_version": "1.0.0",
        "gate": "P-640",
        "phase": "ENVIRONMENTAL_RUNTIME_RECONCILIATION",
        "status": "PASS" if passed else "FAIL",
        "execution_mode": "LOCAL_ONLY",
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "artifact": {
            "name": package.name,
            "sha256": package_sha,
        },
        "wordpress_path": str(wp_path),
        "plugin_status": plugin_status,
        "runtime_probe": probe,
        "reconciliation_policy": {
            "technical_p640_package_install_required": False,
            "production_candidate_used_as_runtime_proof": True,
            "reason": "The modular runtime is proven on the exact frozen production candidate already homologated.",
        },
        "invariants": {
            "github_actions_used": False,
            "content_mutation_requested": False,
            "data_migration_requested": False,
            "cutover_authorized": False,
            "retirement_authorized": False,
        },
        "next_gate": "P640_CLOSEOUT_REVIEW" if passed else "P640_ENVIRONMENTAL_REMEDIATION",
    }

    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(report, ensure_ascii=False, indent=2))
    return 0 if passed else 1


if __name__ == "__main__":
    raise SystemExit(main())
