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
	function dry_ksort_recursive( array &$array ): void {
		foreach ( $array as &$value ) {
			if ( is_array( $value ) ) {
				dry_ksort_recursive( $value );
			}
		}
		ksort( $array );
	}

	if ( ! class_exists( Canonical_JSON::class ) ) {
		final class Canonical_JSON {
			public static function hash( array $value ): string {
				dry_ksort_recursive( $value );
				return hash( 'sha256', json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR ) );
			}
			public static function encode( array $value ): string {
				dry_ksort_recursive( $value );
				return json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR );
			}
		}
	}

	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-elementor-migration-dry-run.php';

	$assertions = 0;
	function assert_dry( bool $condition, string $message ): void {
		global $assertions;
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
		++$assertions;
		echo "PASS {$message}\n";
	}

	function dry_plan( string $status = 'projectable', bool $review = false, bool $unsafe = false ): array {
		return array(
			'post_id' => 123,
			'source_hash_before' => str_repeat( 'a', 64 ),
			'projection_hash' => str_repeat( 'b', 64 ),
			'plan_status' => $status,
			'requires_review' => $review,
			'writer_allowed' => false,
			'safety' => array(
				'persists_plan' => false,
				'writes_post_content' => $unsafe,
				'writes_elementor_data' => false,
			),
		);
	}

	function dry_gateway( string $status = 'compatible_read_only' ): array {
		return array(
			'compatibility_status' => $status,
			'writer_allowed' => false,
			'writer_denials' => array( 'FEATURE_FLAG_DISABLED', 'PHASE_T082_READ_ONLY' ),
		);
	}

	function dry_stale( string $status = 'fresh' ): array {
		return array(
			'status' => $status,
			'is_fresh' => 'fresh' === $status,
			'is_stale' => 'stale' === $status,
		);
	}

	$ready = Elementor_Migration_Dry_Run::simulate( dry_plan(), dry_gateway(), dry_stale() );
	assert_dry( is_array( $ready ), 'ready dry-run built' );
	assert_dry( '1.0.0' === $ready['schema_version'], 'dry-run schema explicit' );
	assert_dry( 'ready' === $ready['dry_run_status'], 'projectable fresh compatible plan is ready' );
	assert_dry( true === $ready['simulation']['would_prepare_journal'], 'ready simulation requires journal' );
	assert_dry( true === $ready['simulation']['would_recheck_stale_source'], 'ready simulation rechecks stale source' );
	assert_dry( true === $ready['simulation']['would_apply_projection'], 'ready simulation would apply projection only hypothetically' );
	assert_dry( false === $ready['execution_allowed'], 'dry-run never authorizes execution' );
	assert_dry( false === $ready['writer_allowed'], 'dry-run writer always false' );
	assert_dry( false === $ready['migration_execution_allowed'], 'dry-run migration execution always false' );
	assert_dry( 1 === preg_match( '/^[a-f0-9]{64}$/', (string) $ready['dry_run_hash'] ), 'dry-run hash is sha256' );

	$repeat = Elementor_Migration_Dry_Run::simulate( dry_plan(), dry_gateway(), dry_stale() );
	assert_dry( $ready['dry_run_hash'] === $repeat['dry_run_hash'], 'dry-run hash deterministic' );
	assert_dry( Elementor_Migration_Dry_Run::canonical_json( $ready ) === Elementor_Migration_Dry_Run::canonical_json( $repeat ), 'dry-run canonical JSON deterministic' );

	$review = Elementor_Migration_Dry_Run::simulate( dry_plan( 'review_required', true ), dry_gateway(), dry_stale() );
	assert_dry( 'review_required' === $review['dry_run_status'], 'review-required plan remains review-required' );
	assert_dry( false === $review['simulation']['would_apply_projection'], 'review-required dry-run never simulates apply' );
	assert_dry( false === $review['simulation']['would_prepare_journal'], 'review-required dry-run waits for approval before journal' );

	$noop = Elementor_Migration_Dry_Run::simulate( dry_plan( 'native_noop' ), dry_gateway(), dry_stale() );
	assert_dry( 'noop' === $noop['dry_run_status'], 'native plan becomes noop' );
	assert_dry( 'no_migration_needed' === $noop['simulated_action'], 'noop has no migration action' );
	assert_dry( false === $noop['simulation']['would_prepare_journal'], 'noop does not need journal' );
	assert_dry( false === $noop['simulation']['would_apply_projection'], 'noop does not simulate apply' );

	$stale = Elementor_Migration_Dry_Run::simulate( dry_plan(), dry_gateway(), dry_stale( 'stale' ) );
	assert_dry( 'blocked' === $stale['dry_run_status'], 'stale source blocks dry-run readiness' );
	assert_dry( in_array( 'STALE_SOURCE_GUARD_NOT_FRESH:stale', $stale['reasons'], true ), 'stale block reason explicit' );
	assert_dry( false === $stale['simulation']['would_apply_projection'], 'stale source never simulates apply' );

	$missing_elementor = Elementor_Migration_Dry_Run::simulate( dry_plan(), dry_gateway( 'blocking' ), dry_stale() );
	assert_dry( 'blocked' === $missing_elementor['dry_run_status'], 'blocking gateway blocks dry-run readiness' );

	$unknown_version = Elementor_Migration_Dry_Run::simulate( dry_plan(), dry_gateway( 'review_required' ), dry_stale() );
	assert_dry( 'review_required' === $unknown_version['dry_run_status'], 'unhomologated Elementor requires review' );
	assert_dry( false === $unknown_version['simulation']['would_apply_projection'], 'unhomologated version never simulates apply' );

	$unsafe = Elementor_Migration_Dry_Run::simulate( dry_plan( 'projectable', false, true ), dry_gateway(), dry_stale() );
	assert_dry( 'blocked' === $unsafe['dry_run_status'], 'unsafe Projection Plan is blocked' );
	assert_dry( in_array( 'PROJECTION_PLAN_SAFETY_VIOLATION', $unsafe['reasons'], true ), 'unsafe projection reason explicit' );

	$unknown = Elementor_Migration_Dry_Run::simulate( dry_plan( 'unexpected_status' ), dry_gateway(), dry_stale() );
	assert_dry( 'blocked' === $unknown['dry_run_status'], 'unknown plan status fails closed' );

	$invalid = dry_plan();
	$invalid['projection_hash'] = 'bad';
	assert_dry( Elementor_Migration_Dry_Run::simulate( $invalid, dry_gateway(), dry_stale() ) instanceof \WP_Error, 'invalid projection hash fails closed' );

	foreach ( array( 'persists_state', 'persists_journal', 'writes_post_content', 'writes_elementor_data', 'calls_external_network', 'executes_shortcodes' ) as $key ) {
		assert_dry( false === $ready['safety'][ $key ], "dry-run safety {$key} remains false" );
	}

	assert_dry( true === $ready['preconditions']['journal_must_be_persisted_before_future_write'], 'write-ahead journal is mandatory precondition' );
	assert_dry( true === $ready['preconditions']['stale_source_must_be_rechecked_immediately_before_future_write'], 'last-moment stale recheck is mandatory' );
	assert_dry( true === $ready['preconditions']['gateway_writer_authorization_required'], 'gateway authorization remains mandatory' );

	echo "ALL PASS {$assertions}\n";
}
