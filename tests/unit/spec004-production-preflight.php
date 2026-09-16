<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
}

namespace BDC\KnowledgeBase {
	$root = __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/';
	require_once $root . 'class-production-preflight.php';

	function assert_preflight( bool $condition, string $message ): void {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
		echo "PASS {$message}\n";
	}

	$base = array(
		'target_environment' => 'homologation',
		'backup_confirmed' => false,
		'wordpress' => '6.9.4',
		'php' => '8.5.10',
		'domdocument' => true,
		'elementor_loaded' => true,
		'elementor_version' => '4.1.0',
		'wp_cron_disabled' => false,
		'shortcode_dependencies' => array(
			'unregistered_used_tags' => array(),
			'provider_unresolved_tags' => array(),
		),
	);

	$result = Production_Preflight::assess( $base );
	assert_preflight( 'review_required' === $result['status'], 'baseline remains review_required because loopback is intentionally not tested' );
	assert_preflight( 0 === $result['blocking_count'], 'homologated baseline has no blocking check' );

	$unknown = $base;
	$unknown['elementor_version'] = '4.2.0';
	$result = Production_Preflight::assess( $unknown );
	assert_preflight( 'review_required' === $result['status'], 'unknown Elementor version requires review' );

	$production = $base;
	$production['target_environment'] = 'production';
	$production['backup_confirmed'] = false;
	$result = Production_Preflight::assess( $production );
	assert_preflight( 'blocking' === $result['status'], 'production without confirmed backup is blocking' );

	$missing_elementor = $base;
	$missing_elementor['elementor_loaded'] = false;
	$missing_elementor['elementor_version'] = '';
	$result = Production_Preflight::assess( $missing_elementor );
	assert_preflight( 'blocking' === $result['status'], 'missing Elementor blocks editorial migration' );

	$shortcode_gap = $base;
	$shortcode_gap['shortcode_dependencies']['unregistered_used_tags'] = array( 'dbc_table' );
	$result = Production_Preflight::assess( $shortcode_gap );
	assert_preflight( 'review_required' === $result['status'], 'unregistered used shortcode requires review' );

	$old_php = $base;
	$old_php['php'] = '8.0.30';
	$result = Production_Preflight::assess( $old_php );
	assert_preflight( 'blocking' === $result['status'], 'PHP below minimum is blocking' );

	echo "ALL PASS\n";
}
