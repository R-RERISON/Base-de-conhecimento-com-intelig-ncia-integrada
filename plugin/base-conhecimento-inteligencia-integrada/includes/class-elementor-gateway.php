<?php
/**
 * Gateway Elementor version-gated da SPEC-004/G-245.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Expõe inspeção segura e um canário explícito, limitado a um post.
 */
final class Elementor_Gateway {

	public const CONTRACT_VERSION = '1.0.0';
	public const HOMOLOGATED_VERSION = '4.1.0';

	/** @return array<string,mixed> */
	public static function status(): array {
		$version = defined( 'ELEMENTOR_VERSION' ) ? (string) ELEMENTOR_VERSION : '';
		$loaded  = class_exists( '\Elementor\Plugin' ) && class_exists( '\Elementor\Core\Base\Document' );
		$canary  = 'true' === strtolower( (string) getenv( 'BDC_KB_ELEMENTOR_CANARY_WRITE' ) );
		return array(
			'contract_version' => self::CONTRACT_VERSION,
			'elementor_loaded' => $loaded,
			'elementor_version' => '' === $version ? null : $version,
			'version_supported' => $loaded && self::HOMOLOGATED_VERSION === $version,
			'writer_enabled' => $canary,
			'canary_only' => true,
		);
	}

	/** @return array<string,mixed>|\WP_Error */
	public static function inspect( int $post_id ): array|\WP_Error {
		$gate = self::assert_read_compatibility();
		if ( is_wp_error( $gate ) ) {
			return $gate;
		}

		$post = get_post( $post_id );
		if ( ! is_object( $post ) || 'post' !== (string) $post->post_type ) {
			return new \WP_Error( 'bdc_kb_elementor_unsupported_post', 'O post não é suportado pelo gateway Elementor.' );
		}

		$document = \Elementor\Plugin::$instance->documents->get_doc_for_frontend( $post_id );
		if ( ! is_object( $document ) || ! is_a( $document, '\Elementor\Core\Base\Document' ) ) {
			return new \WP_Error( 'bdc_kb_elementor_document_unavailable', 'O Document Elementor não está disponível.' );
		}

		return array(
			'post_id' => $post_id,
			'document_class' => get_class( $document ),
			'elementor_version' => self::HOMOLOGATED_VERSION,
			'writer_enabled' => false,
			'can_write' => false,
		);
	}

	/** @return array<string,mixed>|\WP_Error */
	public static function snapshot( int $post_id ): array|\WP_Error {
		$document = self::document( $post_id );
		if ( is_wp_error( $document ) ) {
			return $document;
		}
		$post = get_post( $post_id );
		$meta = array();
		foreach ( array( '_elementor_data', '_elementor_edit_mode', '_elementor_version', '_elementor_template_type' ) as $key ) {
			$values = get_post_meta( $post_id, $key, false );
			$meta[ $key ] = array(
				'present' => ! empty( $values ),
				'values' => $values,
			);
		}

		return array(
			'elements' => $document->get_elements_data(),
			'meta' => $meta,
			'post' => array(
				'post_content' => is_object( $post ) ? (string) $post->post_content : '',
				'post_status' => is_object( $post ) ? (string) $post->post_status : '',
				'post_modified' => is_object( $post ) ? (string) $post->post_modified : '',
				'post_modified_gmt' => is_object( $post ) ? (string) $post->post_modified_gmt : '',
			),
		);
	}

	/** @return array<string,mixed>|\WP_Error */
	public static function save( int $post_id, array $data ): array|\WP_Error {
		if ( ! self::canary_write_enabled() ) {
			return self::blocked_error();
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'bdc_kb_elementor_canary_forbidden', 'Canário Elementor exige manage_options.', array( 'status' => 'BLOCKED' ) );
		}
		if ( ! is_array( $data ) || empty( $data ) ) {
			return new \WP_Error( 'bdc_kb_elementor_canary_invalid_data', 'Canário Elementor exige dados explícitos.', array( 'status' => 'BLOCKED' ) );
		}
		$document = self::document( $post_id );
		if ( is_wp_error( $document ) ) {
			return $document;
		}
		$document->save( $data );
		return array( 'status' => 'APPLIED_CANARY', 'post_id' => $post_id, 'writer_enabled' => true );
	}

	/** @return array<string,mixed>|\WP_Error */
	public static function restore( int $post_id, array $snapshot ): array|\WP_Error {
		if ( ! self::canary_write_enabled() ) {
			return self::blocked_error();
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'bdc_kb_elementor_canary_forbidden', 'Rollback do canário exige manage_options.', array( 'status' => 'BLOCKED' ) );
		}
		$document = self::document( $post_id );
		if ( is_wp_error( $document ) ) {
			return $document;
		}
		$elements = isset( $snapshot['elements'] ) && is_array( $snapshot['elements'] ) ? $snapshot['elements'] : $snapshot;
		$document->save( $elements );
		if ( isset( $snapshot['meta'] ) && is_array( $snapshot['meta'] ) ) {
			foreach ( $snapshot['meta'] as $key => $definition ) {
				if ( ! is_array( $definition ) || ! isset( $definition['present'], $definition['values'] ) ) {
					continue;
				}
				if ( ! $definition['present'] ) {
					$document->delete_main_meta( (string) $key );
					continue;
				}
				$document->delete_main_meta( (string) $key );
				foreach ( (array) $definition['values'] as $value ) {
					$document->update_main_meta( (string) $key, $value );
				}
			}
		}
		if ( isset( $snapshot['post'] ) && is_array( $snapshot['post'] ) ) {
			$post_data = array( 'ID' => $post_id );
			foreach ( array( 'post_content', 'post_status', 'post_modified', 'post_modified_gmt' ) as $key ) {
				if ( array_key_exists( $key, $snapshot['post'] ) ) {
					$post_data[ $key ] = $snapshot['post'][ $key ];
				}
			}
			if ( count( $post_data ) > 1 ) {
				wp_update_post( $post_data );
			}
			if ( isset( $snapshot['post']['post_modified'], $snapshot['post']['post_modified_gmt'] ) ) {
				global $wpdb;
				$wpdb->update(
					$wpdb->posts,
					array(
						'post_modified' => (string) $snapshot['post']['post_modified'],
						'post_modified_gmt' => (string) $snapshot['post']['post_modified_gmt'],
					),
					array( 'ID' => $post_id ),
					array( '%s', '%s' ),
					array( '%d' )
				);
				clean_post_cache( $post_id );
			}
		}
		return array( 'status' => 'ROLLED_BACK_CANARY', 'post_id' => $post_id, 'writer_enabled' => true );
	}

	private static function canary_write_enabled(): bool {
		return 'true' === strtolower( (string) getenv( 'BDC_KB_ELEMENTOR_CANARY_WRITE' ) );
	}

	/** @return object|\WP_Error */
	private static function document( int $post_id ): mixed {
		$gate = self::assert_read_compatibility();
		if ( is_wp_error( $gate ) ) {
			return $gate;
		}
		$post = get_post( $post_id );
		if ( ! is_object( $post ) || 'post' !== (string) $post->post_type ) {
			return new \WP_Error( 'bdc_kb_elementor_unsupported_post', 'O post não é suportado pelo gateway Elementor.' );
		}
		$document = \Elementor\Plugin::$instance->documents->get_doc_for_frontend( $post_id );
		if ( ! is_object( $document ) || ! is_a( $document, '\Elementor\Core\Base\Document' ) ) {
			return new \WP_Error( 'bdc_kb_elementor_document_unavailable', 'O Document Elementor não está disponível.' );
		}
		return $document;
	}

	private static function blocked_error(): \WP_Error {
		return new \WP_Error( 'bdc_kb_elementor_writer_blocked', 'Writer Elementor bloqueado; canário exige BDC_KB_ELEMENTOR_CANARY_WRITE=true.', array( 'status' => 'BLOCKED', 'editorial_write' => false ) );
	}

	private static function assert_read_compatibility(): true|\WP_Error {
		$status = self::status();
		if ( ! $status['elementor_loaded'] ) {
			return new \WP_Error( 'bdc_kb_elementor_not_loaded', 'Elementor não está carregado.' );
		}
		if ( ! $status['version_supported'] ) {
			return new \WP_Error( 'bdc_kb_elementor_version_unsupported', 'A versão Elementor não está na matriz homologada.' );
		}
		return true;
	}
}