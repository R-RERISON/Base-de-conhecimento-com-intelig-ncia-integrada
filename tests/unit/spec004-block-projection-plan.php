<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}

	if ( ! class_exists( 'WP_Error' ) ) {
		class WP_Error {
			public function __construct(
				private string $code = '',
				private string $message = '',
				public mixed $data = null
			) {}

			public function get_error_code(): string {
				return $this->code;
			}
		}
	}
}

namespace BDC\KnowledgeBase {
	if ( ! class_exists( Canonical_JSON::class ) ) {
		final class Canonical_JSON {
			public static function hash( array $value ): string {
				self::sort_recursive( $value );
				return hash( 'sha256', json_encode( $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR ) );
			}

			private static function sort_recursive( array &$value ): void {
				foreach ( $value as &$item ) {
					if ( is_array( $item ) ) {
						self::sort_recursive( $item );
					}
				}
				unset( $item );
				if ( ! array_is_list( $value ) ) {
					ksort( $value );
				}
			}
		}
	}

	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-block-projection-plan.php';

	$assertions = 0;
	function assert_block_projection( bool $condition, string $message ): void {
		global $assertions;
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
		++$assertions;
		echo "PASS {$message}\n";
	}

	$source_hash = str_repeat( 'a', 64 );
	$document = array(
		'post_id' => 10,
		'source_hash' => $source_hash,
		'source_kind' => 'legacy_html',
		'ai_readiness' => array( 'status' => 'candidate_ready' ),
		'blocks' => array(
			array( 'kind' => 'heading', 'text' => 'Título', 'meta' => array( 'level' => 2 ) ),
			array( 'kind' => 'paragraph', 'text' => 'Texto' ),
			array(
				'kind' => 'list',
				'list_type' => 'ul',
				'items' => array(
					array( 'text' => 'A', 'children' => array() ),
					array( 'text' => 'B', 'children' => array() ),
				),
			),
			array(
				'kind' => 'table',
				'caption' => 'Cap',
				'rows' => array(
					array( 'cells' => array( array( 'text' => 'X', 'tag' => 'td' ), array( 'text' => 'Y', 'tag' => 'td' ) ) ),
				),
			),
			array( 'kind' => 'code', 'text' => 'echo 1;' ),
			array( 'kind' => 'quote', 'text' => 'Citação' ),
		),
	);

	$plan = Block_Projection_Plan::from_document( $document );
	assert_block_projection( is_array( $plan ), 'plan created' );
	assert_block_projection( 'projectable' === $plan['plan_status'], 'legacy ready is projectable' );
	assert_block_projection( 'wordpress_core_blocks' === $plan['target'], 'target core blocks' );
	assert_block_projection( '1.1.0' === $plan['schema_version'], 'projection schema v1.1.0' );
	assert_block_projection( 6 === count( $plan['blocks'] ), 'six blocks mapped' );
	assert_block_projection( 'core/heading' === $plan['blocks'][0]['block_name'], 'heading mapped' );
	assert_block_projection( 2 === $plan['blocks'][0]['attrs']['level'], 'heading level kept' );
	assert_block_projection( 'core/paragraph' === $plan['blocks'][1]['block_name'], 'paragraph mapped' );
	assert_block_projection( 'core/list' === $plan['blocks'][2]['block_name'], 'list mapped' );
	assert_block_projection( 'core/list-item' === $plan['blocks'][2]['inner_blocks'][0]['block_name'], 'list item mapped' );
	assert_block_projection( 'core/table' === $plan['blocks'][3]['block_name'], 'table mapped' );
	assert_block_projection( 'core/code' === $plan['blocks'][4]['block_name'], 'code mapped' );
	assert_block_projection( 'core/quote' === $plan['blocks'][5]['block_name'], 'quote mapped' );
	assert_block_projection( false === $plan['writer_allowed'] && false === $plan['migration_execution_allowed'], 'writer disabled' );
	assert_block_projection( null === $plan['serialized_post_content'], 'no serialization in T092' );
	assert_block_projection( false === $plan['safety']['depends_on_gutenberg_plugin'], 'no Gutenberg plugin dependency' );
	assert_block_projection( 64 === strlen( $plan['block_projection_hash'] ), 'projection hash present' );

	$repeat = Block_Projection_Plan::from_document( $document );
	assert_block_projection( is_array( $repeat ) && $repeat['block_projection_hash'] === $plan['block_projection_hash'], 'deterministic hash' );

	$native = $document;
	$native['source_kind'] = 'gutenberg';
	$native_plan = Block_Projection_Plan::from_document( $native );
	assert_block_projection( is_array( $native_plan ) && 'native_noop' === $native_plan['plan_status'], 'native blocks noop' );

	$unsupported = $document;
	$unsupported['blocks'][] = array( 'kind' => 'image', 'text' => 'Imagem sem referência canônica de mídia' );
	$unsupported_plan = Block_Projection_Plan::from_document( $unsupported );
	assert_block_projection( is_array( $unsupported_plan ) && 'review_required' === $unsupported_plan['plan_status'], 'image without media provenance requires review' );
	assert_block_projection( in_array( 'BLOCK_PROJECTION_UNSUPPORTED_KIND:image', $unsupported_plan['warnings'], true ), 'image review reason emitted' );

	$table_span = $document;
	$table_span['blocks'] = array(
		array(
			'kind' => 'table',
			'rows' => array( array( 'cells' => array( array( 'text' => 'x', 'rowspan' => 2 ) ) ) ),
		),
	);
	$table_span_plan = Block_Projection_Plan::from_document( $table_span );
	assert_block_projection( is_array( $table_span_plan ) && 'review_required' === $table_span_plan['plan_status'], 'table spans require review' );

	$blocked = $document;
	$blocked['ai_readiness'] = array( 'status' => 'not_ready' );
	$blocked_plan = Block_Projection_Plan::from_document( $blocked );
	assert_block_projection( is_array( $blocked_plan ) && 'blocked' === $blocked_plan['plan_status'], 'not ready blocks' );

	$empty = array(
		'post_id' => 10,
		'source_hash' => $source_hash,
		'source_kind' => 'empty',
		'ai_readiness' => array( 'status' => 'not_applicable' ),
		'blocks' => array(),
	);
	$empty_plan = Block_Projection_Plan::from_document( $empty );
	assert_block_projection( is_array( $empty_plan ) && 'not_applicable' === $empty_plan['plan_status'], 'empty not applicable' );

	$invalid = Block_Projection_Plan::from_document( array( 'post_id' => 0 ) );
	assert_block_projection( $invalid instanceof \WP_Error, 'invalid doc fails closed' );

	echo "ALL PASS {$assertions}\n";
}
