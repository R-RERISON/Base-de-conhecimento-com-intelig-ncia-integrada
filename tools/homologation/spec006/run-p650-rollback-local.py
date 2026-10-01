#!/usr/bin/env python3
"""Validador local de rollback P-650.

Executa somente quando invocado explicitamente em um WordPress de homologação.
Fluxo: candidato atual -> pacote anterior -> candidato atual.
Não altera conteúdo editorial diretamente e não usa GitHub Actions.
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

EXPECTED_CANDIDATE_SHA256 = "0e4860ed0be34033c63358c9f0798d7c9564420dd738fc66d587180fbf14cf77"
PLUGIN_SLUG = "base-conhecimento-inteligencia-integrada"
OUTPUT_NAME = "spec006-p650-rollback-current.json"


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
        return {"pass": False, "raw": result, "reason": "invalid_json_fingerprint"}
    return {"pass": True, "payload": payload, "raw": result}


def fingerprint(wp_cli: str, wp_path: pathlib.Path) -> dict:
    code = r'''<?php
global $wpdb;

$posts = $wpdb->get_results(
    "SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type = 'post' ORDER BY ID ASC",
    ARRAY_A
);
$post_count = count( $posts );
$post_hash_context = hash_init( 'sha256' );
foreach ( $posts as $row ) {
    hash_update(
        $post_hash_context,
        (string) $row['ID'] . "\0" . (string) $row['post_content'] . "\0"
    );
}
$post_content_sha256 = hash_final( $post_hash_context );

$meta_keys = array(
    '_bdc_es_objective',
    '_bdc_es_escalation',
    '_bdc_es_important',
    '_bdc_es_affected_service',
    '_bdc_es_systems_involved',
);

$meta_counts = array();
$meta_hash_context = hash_init( 'sha256' );
foreach ( $meta_keys as $meta_key ) {
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s ORDER BY post_id ASC, meta_id ASC",
            $meta_key
        ),
        ARRAY_A
    );
    $meta_counts[ $meta_key ] = count( $rows );
    foreach ( $rows as $row ) {
        hash_update(
            $meta_hash_context,
            (string) $row['post_id'] . "\0" .
            (string) $row['meta_key'] . "\0" .
            (string) $row['meta_value'] . "\0"
        );
    }
}
$bdc_meta_sha256 = hash_final( $meta_hash_context );

$elementor_rows = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT post_id, meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s ORDER BY post_id ASC, meta_id ASC",
        '_elementor_data'
    ),
    ARRAY_A
);
$elementor_hash_context = hash_init( 'sha256' );
foreach ( $elementor_rows as $row ) {
    hash_update(
        $elementor_hash_context,
        (string) $row['post_id'] . "\0" .
        (string) $row['meta_id'] . "\0" .
        (string) $row['meta_value'] . "\0"
    );
}
$elementor_data_sha256 = hash_final( $elementor_hash_context );

$search_table = $wpdb->prefix . 'bdc_kb_search_documents';
$search_table_exists = $search_table === $wpdb->get_var(
    $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $search_table ) )
);
$search_rows = array();
if ( $search_table_exists ) {
    $search_rows = $wpdb->get_results(
        "SELECT * FROM {$search_table} ORDER BY post_id ASC",
        ARRAY_A
    );
}
$search_hash_context = hash_init( 'sha256' );
foreach ( $search_rows as $row ) {
    hash_update( $search_hash_context, (string) wp_json_encode( $row ) . "\0" );
}
$search_hash_sha256 = hash_final( $search_hash_context );

$state_raw = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
        'bdc_kb_search_projection_state'
    )
);
$state_exists = null !== $state_raw;
$state_sha256 = $state_exists ? hash( 'sha256', (string) $state_raw ) : null;

echo wp_json_encode(
    array(
        'post_count' => $post_count,
        'post_content_sha256' => $post_content_sha256,
        'meta_counts' => $meta_counts,
        'bdc_meta_sha256' => $bdc_meta_sha256,
        'elementor_data_count' => count( $elementor_rows ),
        'elementor_data_sha256' => $elementor_data_sha256,
        'search_projection' => array(
            'table_exists' => $search_table_exists,
            'row_count' => count( $search_rows ),
            'hash_sha256' => $search_hash_sha256,
            'state_option_exists' => $state_exists,
            'state_option_sha256' => $state_sha256,
        ),
        'plugin_version' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : null,
    )
);
'''
    return wp_eval_file(wp_cli, wp_path, code)

def plugin_status(wp_cli: str, wp_path: pathlib.Path) -> dict:
    result = run([wp_cli, "plugin", "status", PLUGIN_SLUG, f"--path={wp_path}"])
    text = result["output"].lower()
    return {
        "pass": result["pass"] and "status: active" in text,
        "raw": result,
    }


def install_zip(wp_cli: str, wp_path: pathlib.Path, package: pathlib.Path) -> dict:
    return run([
        wp_cli,
        "plugin",
        "install",
        str(package),
        "--force",
        "--activate",
        f"--path={wp_path}",
    ])


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--wp-path", required=True)
    parser.add_argument("--previous-zip", required=True)
    parser.add_argument("--candidate-zip", required=True)
    parser.add_argument("--output", default=OUTPUT_NAME)
    args = parser.parse_args()

    wp_cli = shutil.which("wp")
    if not wp_cli:
        print(json.dumps({"status": "BLOCKED_LOCAL_TOOLING", "missing": ["wp"]}, indent=2))
        return 2

    wp_path = pathlib.Path(args.wp_path).resolve()
    previous = pathlib.Path(args.previous_zip).resolve()
    candidate = pathlib.Path(args.candidate_zip).resolve()
    output = pathlib.Path(args.output).resolve()

    missing = [str(path) for path in (previous, candidate) if not path.is_file()]
    if missing:
        print(json.dumps({"status": "FAIL", "missing_packages": missing}, indent=2))
        return 2
    if not (wp_path / "wp-config.php").is_file():
        print(json.dumps({"status": "FAIL", "reason": "invalid_wordpress_path"}, indent=2))
        return 2

    candidate_sha = sha256(candidate)
    if candidate_sha != EXPECTED_CANDIDATE_SHA256:
        print(json.dumps({
            "status": "FAIL_ARTIFACT_MISMATCH",
            "expected_candidate_sha256": EXPECTED_CANDIDATE_SHA256,
            "actual_candidate_sha256": candidate_sha,
        }, indent=2))
        return 3

    steps = []
    before = fingerprint(wp_cli, wp_path)
    steps.append({"name": "fingerprint_before", **before})
    if not before["pass"]:
        print(json.dumps({"status": "FAIL", "steps": steps}, ensure_ascii=False, indent=2))
        return 1

    rollback_install = install_zip(wp_cli, wp_path, previous)
    steps.append({"name": "install_previous", **rollback_install})
    rollback_status = plugin_status(wp_cli, wp_path)
    steps.append({"name": "previous_active", **rollback_status})
    rollback_fingerprint = fingerprint(wp_cli, wp_path)
    steps.append({"name": "fingerprint_previous", **rollback_fingerprint})

    restore_install = install_zip(wp_cli, wp_path, candidate)
    steps.append({"name": "restore_candidate", **restore_install})
    restore_status = plugin_status(wp_cli, wp_path)
    steps.append({"name": "candidate_active", **restore_status})
    after = fingerprint(wp_cli, wp_path)
    steps.append({"name": "fingerprint_after", **after})

    comparable = before["pass"] and rollback_fingerprint["pass"] and after["pass"]
    preserved = (
        comparable
        and before["payload"]["post_count"] == rollback_fingerprint["payload"]["post_count"] == after["payload"]["post_count"]
        and before["payload"]["post_content_sha256"] == rollback_fingerprint["payload"]["post_content_sha256"] == after["payload"]["post_content_sha256"]
        and before["payload"]["meta_counts"] == rollback_fingerprint["payload"]["meta_counts"] == after["payload"]["meta_counts"]
        and before["payload"]["bdc_meta_sha256"] == rollback_fingerprint["payload"]["bdc_meta_sha256"] == after["payload"]["bdc_meta_sha256"]
        and before["payload"]["elementor_data_count"] == rollback_fingerprint["payload"]["elementor_data_count"] == after["payload"]["elementor_data_count"]
        and before["payload"]["elementor_data_sha256"] == rollback_fingerprint["payload"]["elementor_data_sha256"] == after["payload"]["elementor_data_sha256"]
        and before["payload"]["search_projection"] == rollback_fingerprint["payload"]["search_projection"] == after["payload"]["search_projection"]
    )
    operational = all(step.get("pass", False) for step in steps if step["name"] not in {"fingerprint_before", "fingerprint_previous", "fingerprint_after"})
    passed = operational and preserved and after["pass"]

    report = {
        "schema_version": "1.0.0",
        "gate": "P-650",
        "phase": "ROLLBACK_ACCEPTANCE",
        "status": "PASS" if passed else "FAIL",
        "execution_mode": "LOCAL_ONLY",
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "candidate": {
            "name": candidate.name,
            "sha256": candidate_sha,
        },
        "previous": {
            "name": previous.name,
            "sha256": sha256(previous),
        },
        "steps": steps,
        "data_preserved": preserved,
        "invariants": {
            "content_mutation_requested_by_runner": False,
            "database_migration_requested_by_runner": False,
            "github_actions_used": False,
            "cutover_authorized": False,
            "retirement_authorized": False,
        },
        "next_gate": "P650_PLUGIN_CHECK_AND_CLOSEOUT" if passed else "P650_ROLLBACK_REMEDIATION",
    }

    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(report, ensure_ascii=False, indent=2))
    return 0 if passed else 1


if __name__ == "__main__":
    raise SystemExit(main())
