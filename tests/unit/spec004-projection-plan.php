<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}

	final class WP_Error {
		public function __construct( private string $code ) {}
		public function get_error_code(): string { return $this->code; }
	}

	$GLOBALS['bdc_plan_post'] = (object) array(
		'ID' => 42,
		'post_type' => 'post',
		'post_title' => 'Artigo',
		'post_modified_gmt' => '2026-09-16 20:00:00',
	);
	$GLOBALS['bdc_plan_extraction'] = array(
		'source_kind' => 'legacy_html',
		'source_hash' => str_repeat( 'a', 64 ),
		'extraction' => array(
			'elementor_compatibility' => array( 'status' => 'projectable', 'reasons' => array() ),
			'warnings' => array( 'SOURCE_EMPTY' ),
		),
	);

	function get_post( int $post_id ): ?object { return 42 === $post_id ? $GLOBALS['bdc_plan_post'] : null; }
	function get_permalink( int $post_id ): string { unset( $post_id ); return 'https://example.test/artigo'; }
}

namespace BDC\KnowledgeBase {
	function is_wp_error( mixed $value ): bool { return $value instanceof \WP_Error; }

	final class Knowledge_Document {
		public static function build( int $post_id ): array|\WP_Error {
			if ( 42 !== $post_id ) { return new \WP_Error( 'invalid' ); }
			return array(
				'post_id' => 42,
				'source_kind' => 'legacy_html',
				'source_hash' => str_repeat( 'a', 64 ),
				'extraction' => $GLOBALS['bdc_plan_extraction']['extraction'],
			);
		}
	}

	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-canonical-json.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-projection-plan.php';

	function assert_same( mixed $expected, mixed $actual, string $message ): void {
		if ( $expected !== $actual ) { throw new \RuntimeException( $message ); }
	}

	$first = Projection_Plan::build( 42 );
	$second = Projection_Plan::build( 42 );
	assert_same( $first, $second, 'Projection Plan deve ser determinístico.' );
	assert_same( 'controlled_html_projection', $first['projection_strategy'], 'Estratégia legacy incorreta.' );
	assert_same( 'projectable', $first['elementor_compatibility']['status'], 'Readiness incorreto.' );
	assert_same( false, $first['requires_review'], 'Caso projectable sem reasons não exige review.' );
	assert_same( 64, strlen( $first['projection_hash'] ), 'projection_hash inválido.' );
	assert_same( array( 'projection_schema_version', 'post_id', 'source_kind', 'source_hash_before', 'post_modified_gmt_before', 'elementor_compatibility', 'projection_strategy', 'projection_hash', 'warnings', 'requires_review' ), array_keys( $first ), 'Shape do plano divergente.' );

	echo "PASS projection_plan_deterministic\n";
	echo "RESULT passed=1 failed=0\n";
}