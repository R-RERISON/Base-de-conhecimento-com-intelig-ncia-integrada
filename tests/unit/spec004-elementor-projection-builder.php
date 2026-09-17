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
}

namespace BDC\KnowledgeBase {
	function is_wp_error( mixed $value ): bool { return $value instanceof \WP_Error; }

	final class Knowledge_Document {
		public static function build( int $post_id ): array|\WP_Error {
			if ( 1102 === $post_id ) {
				return array( 'post_id' => $post_id, 'source_hash' => str_repeat( 'a', 64 ), 'source_kind' => 'legacy_html', 'extraction' => array( 'elementor_compatibility' => array( 'status' => 'review_required', 'reasons' => array( 'SHORTCODE_NOT_EXPANDED:table' ) ), 'warnings' => array() ), 'sections' => array( array( 'kind' => 'paragraph', 'text' => 'blocked', 'source' => 'post_content', 'ordinal' => 0 ) ) );
			}
			return array( 'post_id' => $post_id, 'source_hash' => str_repeat( 'b', 64 ), 'source_kind' => 'plain_text', 'extraction' => array( 'elementor_compatibility' => array( 'status' => 'projectable', 'reasons' => array() ), 'warnings' => array() ), 'sections' => array( array( 'kind' => 'paragraph', 'text' => 'Texto', 'source' => 'post_content', 'ordinal' => 0 ) ) );
		}
	}

	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-canonical-json.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-elementor-projection-builder.php';

	$first = Elementor_Projection_Builder::build( 1 );
	$second = Elementor_Projection_Builder::build( 1 );
	$blocked = Elementor_Projection_Builder::build( 1102 );
	if ( ! is_array( $first ) || ! is_array( $second ) || serialize( $first ) !== serialize( $second ) ) { throw new \RuntimeException( 'Builder não determinístico.' ); }
	if ( empty( $first['elements'][0]['elements'][0]['widgetType'] ) || 'text-editor' !== $first['elements'][0]['elements'][0]['widgetType'] ) { throw new \RuntimeException( 'Widget de texto ausente.' ); }
	if ( ! is_wp_error( $blocked ) || 'bdc_kb_projection_review_required' !== $blocked->get_error_code() ) { throw new \RuntimeException( 'Review required deveria bloquear builder.' ); }

	echo "PASS elementor_projection_builder\n";
	echo "RESULT passed=1 failed=0\n";
}