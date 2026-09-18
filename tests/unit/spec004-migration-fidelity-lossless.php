<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
	if ( ! class_exists( 'WP_Error' ) ) {
		class WP_Error {
			public function __construct( private string $code = '', private string $message = '', public mixed $data = null ) {}
			public function get_error_code(): string { return $this->code; }
		}
	}
	if ( ! function_exists( 'serialize_blocks' ) ) {
		function serialize_blocks( array $blocks ): string {
			$out = '';
			foreach ( $blocks as $block ) {
				$name = (string) ( $block['blockName'] ?? '' );
				$slug = str_starts_with( $name, 'core/' ) ? substr( $name, 5 ) : $name;
				$inner = (string) ( $block['innerHTML'] ?? '' );
				$out .= '<!-- wp:' . $slug . " -->\n" . $inner . "\n<!-- /wp:" . $slug . ' -->';
			}
			return $out;
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
					if ( is_array( $item ) ) { self::sort_recursive( $item ); }
				}
				unset( $item );
				if ( ! array_is_list( $value ) ) { ksort( $value ); }
			}
		}
	}

	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-migration-fidelity-source.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-core-block-lossless-serializer.php';

	$assertions = 0;
	function assert_lossless( bool $condition, string $message ): void {
		global $assertions;
		if ( ! $condition ) { throw new \RuntimeException( $message ); }
		++$assertions;
		echo "PASS {$message}\n";
	}

	$base_source = array(
		'post_content' => '<p><strong>Olá</strong> <a href="/x">link</a><img src="/a.jpg" alt="A"></p>',
		'elementor_raw' => '',
		'elementor_data' => array(),
		'flags' => array( 'has_blocks' => false, 'has_elementor_meta' => false ),
		'sizes' => array( 'post_content' => 76, 'elementor_data' => 0 ),
	);

	$legacy = Migration_Fidelity_Source::from_sources( 10, $base_source, array( 'source_kind' => 'legacy_html' ) );
	assert_lossless( is_array( $legacy ), 'legacy source created' );
	assert_lossless( 'ready' === $legacy['status'], 'legacy ready' );
	assert_lossless( 'post_content_rich_html' === $legacy['units'][0]['kind'], 'legacy raw html unit' );
	assert_lossless( $base_source['post_content'] === $legacy['units'][0]['raw'], 'legacy raw byte preserved' );
	assert_lossless( hash( 'sha256', $base_source['post_content'] ) === $legacy['units'][0]['raw_sha256'], 'legacy raw hash exact' );
	assert_lossless( false === $legacy['safety']['writes_post_content'], 'source no writer' );

	$serialized = Core_Block_Lossless_Serializer::serialize_source( $legacy );
	assert_lossless( is_array( $serialized ), 'legacy serialized' );
	assert_lossless( 'serialized_in_memory' === $serialized['status'], 'legacy serialized status' );
	assert_lossless( 'core/freeform' === $serialized['blocks'][0]['blockName'], 'legacy to core/freeform' );
	assert_lossless( $base_source['post_content'] === $serialized['blocks'][0]['innerHTML'], 'freeform preserves raw HTML' );
	assert_lossless( false === $serialized['writer_allowed'] && false === $serialized['migration_execution_allowed'], 'serializer writer disabled' );

	$plain_source = $base_source;
	$plain_source['post_content'] = "Linha 1\nLinha 2";
	$plain = Migration_Fidelity_Source::from_sources( 11, $plain_source, array( 'source_kind' => 'plain_text' ) );
	assert_lossless( 'post_content_plain_text' === $plain['units'][0]['kind'], 'plain unit' );
	$plain_serialized = Core_Block_Lossless_Serializer::serialize_source( $plain );
	assert_lossless( 'core/freeform' === $plain_serialized['blocks'][0]['blockName'], 'plain lossless freeform' );
	assert_lossless( "Linha 1\nLinha 2" === $plain_serialized['blocks'][0]['innerHTML'], 'plain bytes preserved' );

	$gutenberg_source = $base_source;
	$gutenberg_source['post_content'] = '<!-- wp:paragraph --><p>A</p><!-- /wp:paragraph -->';
	$gutenberg_source['flags']['has_blocks'] = true;
	$gutenberg = Migration_Fidelity_Source::from_sources( 12, $gutenberg_source, array( 'source_kind' => 'gutenberg' ) );
	$gutenberg_serialized = Core_Block_Lossless_Serializer::serialize_source( $gutenberg );
	assert_lossless( 'native_core_blocks' === $gutenberg['strategy'], 'gutenberg native strategy' );
	assert_lossless( 'native_noop' === $gutenberg_serialized['status'], 'gutenberg no-op' );
	assert_lossless( $gutenberg_source['post_content'] === $gutenberg_serialized['serialized_post_content'], 'gutenberg exact content preserved' );

	$elementor_data = array(
		array( 'elType' => 'widget', 'widgetType' => 'text-editor', 'settings' => array( 'editor' => '<p><em>E</em></p>' ) ),
		array( 'elType' => 'widget', 'widgetType' => 'shortcode', 'settings' => array( 'shortcode' => '[table id=1 /]' ) ),
	);
	$elementor_source = $base_source;
	$elementor_source['post_content'] = '';
	$elementor_source['elementor_raw'] = json_encode( $elementor_data );
	$elementor_source['elementor_data'] = $elementor_data;
	$elementor_source['flags']['has_elementor_meta'] = true;
	$elementor = Migration_Fidelity_Source::from_sources( 13, $elementor_source, array( 'source_kind' => 'elementor' ) );
	assert_lossless( 'ready' === $elementor['status'], 'elementor supported ready' );
	assert_lossless( 2 === count( $elementor['units'] ), 'elementor two units' );
	assert_lossless( 'elementor_text_editor_html' === $elementor['units'][0]['kind'], 'elementor text unit' );
	assert_lossless( 'elementor_shortcode' === $elementor['units'][1]['kind'], 'elementor shortcode unit' );
	$elementor_serialized = Core_Block_Lossless_Serializer::serialize_source( $elementor );
	assert_lossless( 'core/freeform' === $elementor_serialized['blocks'][0]['blockName'], 'elementor editor to freeform' );
	assert_lossless( 'core/shortcode' === $elementor_serialized['blocks'][1]['blockName'], 'elementor shortcode to core shortcode' );
	assert_lossless( '<p><em>E</em></p>' === $elementor_serialized['blocks'][0]['innerHTML'], 'elementor editor raw preserved' );
	assert_lossless( '[table id=1 /]' === $elementor_serialized['blocks'][1]['innerHTML'], 'elementor shortcode raw preserved' );

	$unsupported_data = array( array( 'elType' => 'widget', 'widgetType' => 'image', 'settings' => array( 'image' => array( 'url' => '/x.jpg' ) ) ) );
	$unsupported_source = $elementor_source;
	$unsupported_source['elementor_data'] = $unsupported_data;
	$unsupported_source['elementor_raw'] = json_encode( $unsupported_data );
	$unsupported = Migration_Fidelity_Source::from_sources( 14, $unsupported_source, array( 'source_kind' => 'elementor' ) );
	assert_lossless( 'review_required' === $unsupported['status'], 'unsupported elementor review' );
	assert_lossless( in_array( 'MIGRATION_SOURCE_ELEMENTOR_UNSUPPORTED_WIDGET:image', $unsupported['warnings'], true ), 'unsupported reason' );

	$mixed_source = $elementor_source;
	$mixed_source['post_content'] = '<!-- wp:paragraph --><p>X</p><!-- /wp:paragraph -->';
	$mixed = Migration_Fidelity_Source::from_sources( 15, $mixed_source, array( 'source_kind' => 'mixed' ) );
	assert_lossless( 'review_required' === $mixed['status'], 'mixed review required' );
	assert_lossless( in_array( 'MIGRATION_SOURCE_MIXED_REQUIRES_HUMAN_SELECTION', $mixed['warnings'], true ), 'mixed reason' );
	$mixed_serialized = Core_Block_Lossless_Serializer::serialize_source( $mixed );
	assert_lossless( 'review_required' === $mixed_serialized['status'], 'mixed serializer fail-closed' );

	$empty = Migration_Fidelity_Source::from_sources( 16, array( 'post_content' => '', 'elementor_raw' => '', 'elementor_data' => array(), 'flags' => array(), 'sizes' => array() ), array( 'source_kind' => 'empty' ) );
	assert_lossless( 'not_applicable' === $empty['status'], 'empty not applicable' );
	$empty_serialized = Core_Block_Lossless_Serializer::serialize_source( $empty );
	assert_lossless( 'not_applicable' === $empty_serialized['status'], 'empty serializer not applicable' );

	$repeat = Migration_Fidelity_Source::from_sources( 10, $base_source, array( 'source_kind' => 'legacy_html' ) );
	assert_lossless( $legacy['fidelity_hash'] === $repeat['fidelity_hash'], 'source deterministic hash' );
	$repeat_serialized = Core_Block_Lossless_Serializer::serialize_source( $repeat );
	assert_lossless( $serialized['serialization_hash'] === $repeat_serialized['serialization_hash'], 'serialization deterministic hash' );

	echo "ALL PASS {$assertions}\n";
}
