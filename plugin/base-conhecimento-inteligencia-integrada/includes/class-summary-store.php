<?php
/**
 * Persistência consistente do Summary narrativo.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lê e atualiza as três metas da SPEC-001.
 */
final class Summary_Store {

	public const STATUS_SUCCESS                  = 'SUCCESS';
	public const STATUS_FAIL_SAFE                = 'FAIL_SAFE';
	public const STATUS_PARTIAL_FAILURE_CRITICAL = 'PARTIAL_FAILURE_CRITICAL';

	/**
	 * Leitura side-effect free do snapshot canônico.
	 *
	 * @param mixed $post_id ID candidato.
	 * @return array<string,int|string>|\WP_Error
	 */
	public static function read( mixed $post_id ): array|\WP_Error {
		$post = self::validate_post( $post_id );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		return self::compose_snapshot( $post, self::read_field_values( (int) $post->ID ) );
	}

	/**
	 * Atualiza parcialmente o Summary usando a semântica B-006.
	 *
	 * @param mixed               $post_id ID candidato.
	 * @param array<string,mixed> $changes Mudanças lógicas já desslashadas.
	 * @return array{status:string,state:array<string,int|string>,changed_fields:array<int,string>}|\WP_Error
	 */
	public static function update( mixed $post_id, array $changes ): array|\WP_Error {
		$post = self::validate_post( $post_id );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$id = (int) $post->ID;
		if ( ! current_user_can( 'edit_post', $id ) ) {
			return self::error( 'bdc_kb_forbidden', 'Você não tem permissão para editar este artigo.' );
		}

		$prepared = self::prepare_changes( $changes );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$snapshot = self::read_field_values( $id );
		$expected = $snapshot;
		$diff     = array();

		foreach ( $prepared as $field => $value ) {
			if ( $snapshot[ $field ] === $value ) {
				continue;
			}

			$expected[ $field ] = $value;
			$diff[ $field ]     = $value;
		}

		if ( array() === $diff ) {
			return array(
				'status'         => self::STATUS_SUCCESS,
				'state'          => self::compose_snapshot( $post, $snapshot ),
				'changed_fields' => array(),
			);
		}

		foreach ( $diff as $field => $value ) {
			self::write_field( $id, $field, $value );
		}

		$actual = self::read_field_values( $id );
		if ( $actual === $expected ) {
			return array(
				'status'         => self::STATUS_SUCCESS,
				'state'          => self::compose_snapshot( $post, $actual ),
				'changed_fields' => array_keys( $diff ),
			);
		}

		self::restore_snapshot( $id, $snapshot, array_keys( $diff ) );
		$restored = self::read_field_values( $id );

		if ( $restored === $snapshot ) {
			return self::error(
				'bdc_kb_persistence_failed',
				'Não foi possível salvar o Summary. O estado anterior foi restaurado.',
				array(
					'status'         => self::STATUS_FAIL_SAFE,
					'final_state'    => $restored,
					'expected_state' => $expected,
					'changed_fields' => array_keys( $diff ),
				)
			);
		}

		error_log(
			sprintf(
				'[BDC-KB][PARTIAL_FAILURE_CRITICAL] post_id=%d fields=%s',
				$id,
				implode( ',', array_keys( $diff ) )
			)
		);

		return self::error(
			'bdc_kb_partial_failure_critical',
			'Falha crítica de persistência. O estado anterior não pôde ser restaurado integralmente.',
			array(
				'status'         => self::STATUS_PARTIAL_FAILURE_CRITICAL,
				'final_state'    => $restored,
				'expected_state' => $expected,
				'original_state' => $snapshot,
				'changed_fields' => array_keys( $diff ),
			)
		);
	}

	/**
	 * Valida todo o payload antes do primeiro write.
	 *
	 * @param array<string,mixed> $changes Mudanças desslashadas.
	 * @return array<string,string>|\WP_Error
	 */
	private static function prepare_changes( array $changes ): array|\WP_Error {
		$fields   = Meta_Contract::fields();
		$prepared = array();

		foreach ( $changes as $field => $value ) {
			if ( ! is_string( $field ) || ! array_key_exists( $field, $fields ) ) {
				return self::error( 'bdc_kb_unknown_field', 'O payload contém um campo não autorizado.' );
			}

			if ( ! is_string( $value ) ) {
				return self::error( 'bdc_kb_invalid_value', 'Os campos do Summary devem ser strings.' );
			}

			if ( strlen( $value ) > Meta_Contract::MAX_BYTES ) {
				return self::error( 'bdc_kb_value_too_large', 'Um campo do Summary excede o limite permitido.' );
			}

			$prepared[ $field ] = Meta_Contract::sanitize_text( $value );
		}

		return $prepared;
	}

	/**
	 * @return array<string,string>
	 */
	private static function read_field_values( int $post_id ): array {
		$values = array();

		foreach ( Meta_Contract::fields() as $field => $definition ) {
			$value = get_post_meta( $post_id, $definition['key'], true );
			$values[ $field ] = is_string( $value ) ? $value : '';
		}

		return $values;
	}

	/**
	 * @param array<string,string> $values Valores do domínio.
	 * @return array<string,int|string>
	 */
	private static function compose_snapshot( object $post, array $values ): array {
		return array_merge(
			array(
				'post_id' => (int) $post->ID,
				'title'   => (string) $post->post_title,
			),
			$values
		);
	}

	private static function write_field( int $post_id, string $field, string $value ): void {
		$key = Meta_Contract::fields()[ $field ]['key'];

		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
			return;
		}

		update_post_meta( $post_id, $key, wp_slash( $value ) );
	}

	/**
	 * Compensação best-effort restrita aos campos efetivamente alterados.
	 *
	 * @param array<string,string> $snapshot Estado anterior.
	 * @param array<int,string>    $changed_fields Campos alterados.
	 */
	private static function restore_snapshot( int $post_id, array $snapshot, array $changed_fields ): void {
		foreach ( $changed_fields as $field ) {
			self::write_field( $post_id, $field, $snapshot[ $field ] );
		}
	}

	/**
	 * @param mixed $post_id ID candidato.
	 * @return object Objeto WP_Post ou WP_Error; diferenciar com is_wp_error().
	 */
	private static function validate_post( mixed $post_id ): object {
		$is_integer_id = is_int( $post_id );
		$is_digit_id   = is_string( $post_id ) && '' !== $post_id && ctype_digit( $post_id );

		if ( ! $is_integer_id && ! $is_digit_id ) {
			return self::error( 'bdc_kb_invalid_post', 'O ID do artigo é inválido.' );
		}

		$id = (int) $post_id;
		if ( $id <= 0 ) {
			return self::error( 'bdc_kb_invalid_post', 'O ID do artigo é inválido.' );
		}

		$post = get_post( $id );
		if ( ! is_object( $post ) || ! isset( $post->ID, $post->post_type, $post->post_title ) ) {
			return self::error( 'bdc_kb_invalid_post', 'O artigo informado não existe.' );
		}

		if ( Meta_Contract::POST_TYPE !== $post->post_type ) {
			return self::error( 'bdc_kb_unsupported_post_type', 'A SPEC-001 suporta somente artigos do tipo post.' );
		}

		return $post;
	}

	/**
	 * @param array<string,mixed> $data Contexto técnico seguro.
	 */
	private static function error( string $code, string $message, array $data = array() ): \WP_Error {
		return new \WP_Error( $code, $message, $data );
	}
}
