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
			public function get_error_code(): string { return $this->code; }
			public function get_error_data(): mixed { return $this->data; }
		}
	}
}

namespace BDC\KnowledgeBase {
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-elementor-gateway.php';

	function assert_gateway( bool $condition, string $message ): void {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
		echo "PASS {$message}\n";
	}

	$compatible = Elementor_Gateway::assess( '4.1.0', false, false );
	assert_gateway( '1.0.0' === $compatible['contract_version'], 'contract version explicit' );
	assert_gateway( 'g245-compatibility-matrix-v1' === $compatible['matrix_version'], 'matrix version explicit' );
	assert_gateway( 'compatible_read_only' === $compatible['compatibility_status'], '4.1.0 compatible read-only' );
	assert_gateway( false === $compatible['writer_allowed'], 'writer disabled on homologated version' );
	assert_gateway( false === $compatible['migration_execution_allowed'], 'migration disabled on homologated version' );
	assert_gateway( in_array( 'FEATURE_FLAG_DISABLED', $compatible['writer_denials'], true ), 'feature flag denial explicit' );
	assert_gateway( in_array( 'CAPABILITY_REQUIRED:manage_options', $compatible['writer_denials'], true ), 'capability denial explicit' );
	assert_gateway( in_array( 'PHASE_T082_READ_ONLY', $compatible['writer_denials'], true ), 'phase denial explicit' );
	assert_gateway( 'bdc_kb_elementor_write' === $compatible['writer_action'], 'future writer action explicit' );
	assert_gateway( '_bdc_kb_elementor_nonce' === $compatible['nonce_field'], 'future nonce field explicit' );
	assert_gateway( 'bdc_kb_elementor_write_' === $compatible['nonce_action_prefix'], 'future nonce action prefix explicit' );
	assert_gateway( true === $compatible['nonce_validation_required'], 'future mutation requires nonce validation' );
	assert_gateway( in_array( 'source_hash_before', $compatible['required_projection_fields'], true ), 'source hash contract explicit for T084' );

	$all_external_gates = Elementor_Gateway::assess( '4.1.0', true, true );
	assert_gateway( false === $all_external_gates['writer_allowed'], 'feature+capability still cannot bypass T082 phase gate' );
	assert_gateway( array( 'PHASE_T082_READ_ONLY' ) === $all_external_gates['writer_denials'], 'only phase gate remains when external gates pass' );

	$unknown = Elementor_Gateway::assess( '4.2.0', true, true );
	assert_gateway( 'review_required' === $unknown['compatibility_status'], 'unknown Elementor version requires review' );
	assert_gateway( in_array( 'ELEMENTOR_VERSION_NOT_HOMOLOGATED:4.2.0', $unknown['compatibility_reasons'], true ), 'unknown version reason explicit' );
	assert_gateway( in_array( 'VERSION_GATE_NOT_COMPATIBLE', $unknown['writer_denials'], true ), 'unknown version blocks writer' );
	assert_gateway( false === $unknown['writer_allowed'], 'unknown version fail-closed' );

	$missing = Elementor_Gateway::assess( '', true, true );
	assert_gateway( 'blocking' === $missing['compatibility_status'], 'missing Elementor blocks gateway' );
	assert_gateway( in_array( 'ELEMENTOR_MISSING', $missing['compatibility_reasons'], true ), 'missing Elementor reason explicit' );
	assert_gateway( false === $missing['writer_allowed'], 'missing Elementor fail-closed' );

	foreach ( array( $compatible, $all_external_gates, $unknown, $missing ) as $state ) {
		foreach ( array( 'persists_state', 'writes_post_content', 'writes_elementor_data', 'calls_external_network', 'executes_shortcodes' ) as $key ) {
			assert_gateway( false === $state['safety'][ $key ], "safety {$key} remains false" );
		}
	}

	$guard = Elementor_Gateway::assert_writer_allowed( $all_external_gates );
	assert_gateway( $guard instanceof \WP_Error, 'writer guard returns WP_Error during T082' );
	assert_gateway( 'bdc_kb_elementor_writer_disabled' === $guard->get_error_code(), 'writer guard error code stable' );
	$guard_data = $guard->get_error_data();
	assert_gateway( is_array( $guard_data ) && 409 === $guard_data['status'], 'writer guard exposes fail-closed status' );

	echo "ALL PASS\n";
}
