<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
	if ( ! class_exists( 'WP_Error' ) ) {
		class WP_Error {
			public function __construct(
				public string $code = '',
				public string $message = '',
				public mixed $data = null
			) {}
		}
	}
}

namespace BDC\KnowledgeBase {
	function canary_sort_recursive( array &$array ): void {
		foreach ( $array as &$value ) {
			if ( is_array( $value ) ) {
				canary_sort_recursive( $value );
			}
		}
		ksort( $array );
	}

	final class Canonical_JSON {
		public static function hash( array $value ): string {
			canary_sort_recursive( $value );
			return hash( 'sha256', json_encode( $value, JSON_THROW_ON_ERROR ) );
		}
	}

	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-elementor-canary-readiness.php';

	$assertions = 0;
	function assert_canary( bool $condition, string $message ): void {
		global $assertions;
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
		++$assertions;
		echo "PASS {$message}\n";
	}

	$source_hash = hash( 'sha256', 'source' );
	$projection_hash = hash( 'sha256', 'projection' );
	$dry_run_hash = hash( 'sha256', 'dry-run' );
	$candidate = array(
		'post_id' => 10,
		'source_hash_before' => $source_hash,
		'projection_hash' => $projection_hash,
		'dry_run_hash' => $dry_run_hash,
	);
	$dry_run = $candidate + array(
		'dry_run_status' => 'ready',
		'execution_allowed' => false,
		'writer_allowed' => false,
		'migration_execution_allowed' => false,
	);
	$base = array(
		'candidate' => $candidate,
		'dry_run' => $dry_run,
		'gateway' => array( 'compatibility_status' => 'compatible_read_only' ),
		'stale' => array( 'status' => 'fresh', 'is_fresh' => true ),
		'canary_scope_size' => 1,
		'journal_storage_durable' => true,
		'rollback_capsule_integrity' => true,
		'explicit_human_authorization' => false,
	);

	$readiness = Elementor_Canary_Readiness::assess( $base );
	assert_canary( is_array( $readiness ), 'readiness built' );
	assert_canary( 'awaiting_explicit_authorization' === $readiness['status'], 'technical readiness waits explicit authorization' );
	assert_canary( true === $readiness['technical_preconditions_satisfied'], 'technical preconditions pass' );
	assert_canary( false === $readiness['explicit_human_authorization'], 'authorization false by default' );
	assert_canary( false === $readiness['execution_allowed'], 'readiness never executes' );
	assert_canary( false === $readiness['writer_allowed'], 'readiness writer stays false' );
	assert_canary( true === $readiness['requires_separate_mutable_executor'], 'separate mutable executor required' );
	assert_canary( 64 === strlen( (string) $readiness['readiness_hash'] ), 'readiness hash present' );

	$authorized = $base;
	$authorized['explicit_human_authorization'] = true;
	$authorized_result = Elementor_Canary_Readiness::assess( $authorized );
	assert_canary( 'ready_for_controlled_canary' === $authorized_result['status'], 'authorization can satisfy readiness only' );
	assert_canary( false === $authorized_result['execution_allowed'], 'authorized readiness still does not execute' );

	foreach ( array( 'journal_storage_durable', 'rollback_capsule_integrity' ) as $key ) {
		$blocked = $base;
		$blocked[ $key ] = false;
		assert_canary( 'blocked' === Elementor_Canary_Readiness::assess( $blocked )['status'], "{$key} blocks" );
	}

	$blocked = $base;
	$blocked['canary_scope_size'] = 2;
	assert_canary( 'blocked' === Elementor_Canary_Readiness::assess( $blocked )['status'], 'scope greater than one blocks' );

	$blocked = $base;
	$blocked['stale'] = array( 'status' => 'stale', 'is_fresh' => false );
	assert_canary( 'blocked' === Elementor_Canary_Readiness::assess( $blocked )['status'], 'stale source blocks' );

	$blocked = $base;
	$blocked['gateway'] = array( 'compatibility_status' => 'review_required' );
	assert_canary( 'blocked' === Elementor_Canary_Readiness::assess( $blocked )['status'], 'unhomologated gateway blocks canary' );

	$blocked = $base;
	$blocked['dry_run']['dry_run_status'] = 'review_required';
	assert_canary( 'blocked' === Elementor_Canary_Readiness::assess( $blocked )['status'], 'non-ready dry-run blocks' );

	$blocked = $base;
	$blocked['dry_run']['source_hash_before'] = hash( 'sha256', 'other-source' );
	assert_canary( 'blocked' === Elementor_Canary_Readiness::assess( $blocked )['status'], 'candidate identity mismatch blocks' );

	$blocked = $base;
	$blocked['dry_run']['writer_allowed'] = true;
	assert_canary( 'blocked' === Elementor_Canary_Readiness::assess( $blocked )['status'], 'unsafe dry-run blocks' );

	$repeat = Elementor_Canary_Readiness::assess( $base );
	assert_canary( $readiness['readiness_hash'] === $repeat['readiness_hash'], 'readiness deterministic' );
	foreach ( array( 'persists_state', 'writes_post_content', 'writes_elementor_data', 'calls_external_network', 'executes_shortcodes' ) as $key ) {
		assert_canary( false === $readiness['safety'][ $key ], "safety {$key} false" );
	}

	assert_canary( Elementor_Canary_Readiness::assess( array( 'candidate' => array() ) ) instanceof \WP_Error, 'invalid candidate fails closed' );

	echo "ALL PASS {$assertions}\n";
}
