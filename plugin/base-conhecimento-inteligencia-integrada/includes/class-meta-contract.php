<?php
/**
 * Contrato de metadata do Summary narrativo.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define as três metas autorizadas pela SPEC-001.
 */
final class Meta_Contract {

	public const POST_TYPE = 'post';
	public const MAX_BYTES = 32768;

	/**
	 * @return array<string,array{label:string,key:string}>
	 */
	public static function fields(): array {
		return array(
			'objective'  => array(
				'label' => 'Objetivo',
				'key'   => '_bdc_es_objective',
			),
			'escalation' => array(
				'label' => 'Escalonamento',
				'key'   => '_bdc_es_escalation',
			),
			'important'  => array(
				'label' => 'Importante',
				'key'   => '_bdc_es_important',
			),
		);
	}

	/**
	 * Registra somente as metas autorizadas para posts.
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
	 * Sanitização canônica após validação de tipo e tamanho.
	 */
	public static function sanitize_text( string $value ): string {
		return trim( sanitize_textarea_field( $value ) );
	}

	/**
	 * Fronteira adicional para caminhos nativos de metadata.
	 *
	 * @param mixed $value Valor recebido pelo WordPress.
	 */
	public static function sanitize_registered_value( mixed $value ): string {
		if ( ! is_string( $value ) ) {
			return '';
		}

		return self::sanitize_text( $value );
	}

	/**
	 * Autoriza metadata pelo objeto alvo.
	 *
	 * @param bool   $allowed   Resultado anterior.
	 * @param string $meta_key  Meta key.
	 * @param int    $object_id Post ID.
	 * @param int    $user_id   User ID.
	 * @param string $cap       Capability solicitada.
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
