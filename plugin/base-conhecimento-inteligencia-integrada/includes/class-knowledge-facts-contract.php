<?php
/**
 * Canonical contract for inherited knowledge facts.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines canonical ownership without requiring immediate physical migration.
 */
final class Knowledge_Facts_Contract {

	public const POST_TYPE = 'post';
	public const MAX_BYTES = 8192;

	/**
	 * @return array<string,array{label:string,key:string,fallback_keys:array<int,string>}>
	 */
	public static function fields(): array {
		return array(
			'affected_service' => array(
				'label'         => 'Serviço Afetado',
				'key'           => '_bdc_es_affected_service',
				'fallback_keys' => array( '_kb2ops_service' ),
			),
			'systems_involved' => array(
				'label'         => 'Sistemas envolvidos',
				'key'           => '_bdc_es_systems_involved',
				'fallback_keys' => array(),
			),
			'technologies' => array(
				'label'         => 'Tecnologias',
				'key'           => '_kb2ops_technologies',
				'fallback_keys' => array(),
			),
			'keywords' => array(
				'label'         => 'Palavras-chave',
				'key'           => '_kb2ops_keywords',
				'fallback_keys' => array(),
			),
			'versions' => array(
				'label'         => 'Versões',
				'key'           => '_kb2ops_versions',
				'fallback_keys' => array(),
			),
		);
	}

	/**
	 * Register canonical physical keys under the BDC runtime.
	 */
	public static function register(): void {
		foreach ( self::fields() as $definition ) {
			register_post_meta(
				self::POST_TYPE,
				$definition['key'],
				array(
					'type'              => 'string',
					'single'            => true,
					'default'           => '',
					'sanitize_callback' => array( self::class, 'sanitize_registered_value' ),
					'auth_callback'     => array( self::class, 'authorize_meta' ),
					'show_in_rest'      => false,
					'revisions_enabled' => false,
				)
			);
		}
	}

	/**
	 * Sanitize a canonical fact.
	 */
	public static function sanitize_text( string $value ): string {
		return trim( sanitize_textarea_field( $value ) );
	}

	/**
	 * Metadata API sanitizer.
	 *
	 * @param mixed $value Value supplied by WordPress.
	 */
	public static function sanitize_registered_value( mixed $value ): string {
		if ( ! is_string( $value ) ) {
			return '';
		}

		return self::sanitize_text( $value );
	}

	/**
	 * Authorize metadata by target object.
	 *
	 * @param bool   $allowed   Previous result.
	 * @param string $meta_key  Meta key.
	 * @param int    $object_id Post ID.
	 * @param int    $user_id   User ID.
	 * @param string $cap       Capability.
	 * @param array  $caps      Primitive capabilities.
	 */
	public static function authorize_meta(
		bool $allowed,
		string $meta_key,
		int $object_id,
		int $user_id,
		string $cap = '',
		array $caps = array()
	): bool {
		unset( $allowed, $meta_key, $cap, $caps );

		return user_can( $user_id, 'edit_post', $object_id );
	}
}
