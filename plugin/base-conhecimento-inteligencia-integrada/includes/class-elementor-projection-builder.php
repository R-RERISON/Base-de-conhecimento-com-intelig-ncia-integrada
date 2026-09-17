<?php
/**
 * Builder determinístico de projeção Elementor da SPEC-004.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Converte fragments semânticos controlados em uma árvore Elementor mínima.
 */
final class Elementor_Projection_Builder {

	public const SCHEMA_VERSION = '1.0.0';

	/** @return array<string,mixed>|\WP_Error */
	public static function build( int $post_id ): array|\WP_Error {
		$document = Knowledge_Document::build( $post_id );
		if ( is_wp_error( $document ) ) {
			return $document;
		}

		$readiness = (string) ( $document['extraction']['elementor_compatibility']['status'] ?? 'blocked' );
		if ( 'native' === $readiness ) {
			return new \WP_Error( 'bdc_kb_projection_native_noop', 'Post Elementor nativo não precisa de projeção.' );
		}
		if ( 'projectable' !== $readiness ) {
			return new \WP_Error( 'bdc_kb_projection_review_required', 'A fonte exige revisão antes da projeção Elementor.' );
		}

		$elements = array();
		$ordinal = 0;
		foreach ( (array) ( $document['sections'] ?? array() ) as $section ) {
			$widget = self::widget_for_section( $section, $ordinal );
			if ( null !== $widget ) {
				$elements[] = $widget;
			}
		}

		if ( empty( $elements ) ) {
			return new \WP_Error( 'bdc_kb_projection_empty', 'A fonte não produziu elementos Elementor projetáveis.' );
		}

		$projection = array(
			'schema_version' => self::SCHEMA_VERSION,
			'post_id' => (int) $document['post_id'],
			'source_hash' => (string) $document['source_hash'],
			'elements' => array(
				array(
					'id' => self::element_id( 'container', 0 ),
					'elType' => 'container',
					'settings' => array(),
					'elements' => $elements,
				),
			),
		);

		try {
			$projection['projection_hash'] = Canonical_JSON::hash( $projection );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_projection_hash_failed', 'Falha ao calcular o hash da projeção.', array( 'exception' => get_class( $error ) ) );
		}

		return $projection;
	}

	/** @param array<string,mixed> $section @return array<string,mixed>|null */
	private static function widget_for_section( array $section, int &$ordinal ): ?array {
		$text = (string) ( $section['text'] ?? '' );
		$kind = (string) ( $section['kind'] ?? 'paragraph' );
		if ( '' === $text ) {
			return null;
		}

		if ( 'heading' === $kind ) {
			$level = max( 1, min( 6, (int) ( $section['meta']['level'] ?? 2 ) ) );
			return self::widget( 'heading', array( 'title' => self::escape( $text ), 'header_size' => 'h' . $level ), $ordinal++ );
		}
		if ( 'code' === $kind ) {
			return self::widget( 'text-editor', array( 'editor' => '<pre>' . self::escape( $text ) . '</pre>' ), $ordinal++ );
		}
		if ( 'quote' === $kind ) {
			return self::widget( 'text-editor', array( 'editor' => '<blockquote>' . self::escape( $text ) . '</blockquote>' ), $ordinal++ );
		}
		if ( 'list_item' === $kind ) {
			return self::widget( 'text-editor', array( 'editor' => '<ul><li>' . self::escape( $text ) . '</li></ul>' ), $ordinal++ );
		}
		if ( 'table_row' === $kind ) {
			$cells = array_map( 'trim', explode( '|', $text ) );
			return self::widget( 'text-editor', array( 'editor' => '<table><tbody><tr>' . implode( '', array_map( static fn ( string $cell ): string => '<td>' . self::escape( $cell ) . '</td>', $cells ) ) . '</tr></tbody></table>' ), $ordinal++ );
		}
		return self::widget( 'text-editor', array( 'editor' => '<p>' . self::escape( $text ) . '</p>' ), $ordinal++ );
	}

	private static function escape( string $value ): string {
		return function_exists( 'esc_html' ) ? (string) \esc_html( $value ) : htmlspecialchars( $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
	}

	/** @param array<string,mixed> $settings @return array<string,mixed> */
	private static function widget( string $widget_type, array $settings, int $ordinal ): array {
		return array(
			'id' => self::element_id( $widget_type, $ordinal ),
			'elType' => 'widget',
			'widgetType' => $widget_type,
			'settings' => $settings,
			'elements' => array(),
		);
	}

	private static function element_id( string $type, int $ordinal ): string {
		return substr( hash( 'sha256', self::SCHEMA_VERSION . '|' . $type . '|' . $ordinal ), 0, 8 );
	}
}