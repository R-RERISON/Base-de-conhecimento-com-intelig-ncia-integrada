<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}

	final class WP_Error {
		public function __construct( private string $code, private mixed $data = null ) {}
		public function get_error_code(): string { return $this->code; }
		public function get_error_data(): mixed { return $this->data; }
	}
}

namespace BDC\KnowledgeBase {
	function is_wp_error( mixed $value ): bool { return $value instanceof \WP_Error; }
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-elementor-gateway.php';

	$result = Elementor_Gateway::save( 42, array( 'content' => array() ) );
	if ( ! is_wp_error( $result ) || 'bdc_kb_elementor_writer_blocked' !== $result->get_error_code() ) {
		throw new \RuntimeException( 'Writer Elementor deveria permanecer bloqueado.' );
	}

	echo "PASS elementor_gateway_writer_blocked\n";
	echo "RESULT passed=1 failed=0\n";
}