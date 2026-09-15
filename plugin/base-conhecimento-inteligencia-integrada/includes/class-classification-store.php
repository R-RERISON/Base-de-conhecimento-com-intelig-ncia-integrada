<?php
/**
 * Persistência consistente da Classificação de Conhecimento.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lê e atualiza relações taxonômicas canônicas da SPEC-002.
 */
final class Classification_Store {

	public const STATUS_SUCCESS                  = 'SUCCESS';
	public const STATUS_FAIL_SAFE                = 'FAIL_SAFE';
	public const STATUS_PARTIAL_FAILURE_CRITICAL = 'PARTIAL_FAILURE_CRITICAL';

	/**
	 * @param mixed $post_id ID candidato.
	 * @return array<string,mixed>|\WP_Error
	 */
	public static function read( mixed $post_id ): array|\WP_Error {
		$post = self::validate_post( $post_id );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$values = self::read_field_values( (int) $post->ID );
		if ( is_wp_error( $values ) ) {
			return $values;
		}

		return self::compose_snapshot( $post, $values );
	}

	/**
	 * @param mixed                                $post_id ID candidato.
	 * @param array<string,array<int,int|string>> $changes Mudanças por conceito.
	 * @return array<string,mixed>|\WP_Error
	 */
	public static function update( mixed $post_id, array $changes ): array|\WP_Error {
		$post = self::validate_post( $post_id );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$id = (int) $post->ID;
		if ( ! current_user_can( 'edit_post', $id ) ) {
			return self::error( 'bdc_kb_classification_forbidden', 'Você não tem permissão para classificar este artigo.' );
		}

		$prepared = self::prepare_changes( $changes );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$snapshot = self::read_field_values( $id );
		if ( is_wp_error( $snapshot ) ) {
			return $snapshot;
		}

		$expected = $snapshot;
		$diff     = array();

		foreach ( $prepared as $field => $term_ids ) {
			if ( $snapshot[ $field ] === $term_ids ) {
				continue;
			}

			$expected[ $field ] = $term_ids;
			$diff[ $field ]     = $term_ids;
		}

		if ( array() === $diff ) {
			return array(
				'status'         => self::STATUS_SUCCESS,
				'state'          => self::compose_snapshot( $post, $snapshot ),
				'changed_fields' => array(),
			);
		}

		$write_failed = false;
		foreach ( $diff as $field => $term_ids ) {
			$result = self::write_field( $id, $field, $term_ids );
			if ( is_wp_error( $result ) ) {
				$write_failed = true;
				break;
			}
		}

		$actual = self::read_field_values( $id );
		if ( ! $write_failed && ! is_wp_error( $actual ) && $actual === $expected ) {
			return array(
				'status'         => self::STATUS_SUCCESS,
				'state'          => self::compose_snapshot( $post, $actual ),
				'changed_fields' => array_keys( $diff ),
			);
		}

		self::restore_snapshot( $id, $snapshot, array_keys( $diff ) );
		$restored = self::read_field_values( $id );

		if ( ! is_wp_error( $restored ) && $restored === $snapshot ) {
			return self::error(
				'bdc_kb_classification_persistence_failed',
				'Não foi possível salvar a classificação. O estado anterior foi restaurado.',
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
				'[BDC-KB][CLASSIFICATION_PARTIAL_FAILURE_CRITICAL] post_id=%d fields=%s',
				$id,
				implode( ',', array_keys( $diff ) )
			)
		);

		return self::error(
			'bdc_kb_classification_partial_failure_critical',
			'Falha crítica de classificação. O estado anterior não pôde ser restaurado integralmente.',
			array(
				'status'         => self::STATUS_PARTIAL_FAILURE_CRITICAL,
				'final_state'    => is_wp_error( $restored ) ? array() : $restored,
				'expected_state' => $expected,
				'original_state' => $snapshot,
				'changed_fields' => array_keys( $diff ),
			)
		);
	}

	/**
	 * @param array<string,array<int,int|string>> $changes Mudanças.
	 * @return array<string,array<int,int>>|\WP_Error
	 */
	private static function prepare_changes( array $changes ): array|\WP_Error {
		$fields   = Classification_Contract::fields();
		$prepared = array();

		foreach ( $changes as $field => $raw_ids ) {
			if ( ! is_string( $field ) || ! array_key_exists( $field, $fields ) ) {
				return self::error( 'bdc_kb_classification_unknown_field', 'O payload contém um conceito não autorizado.' );
			}

			if ( ! is_array( $raw_ids ) ) {
				return self::error( 'bdc_kb_classification_invalid_value', 'Cada classificação deve ser enviada como uma lista de IDs.' );
			}

			if ( count( $raw_ids ) > Classification_Contract::MAX_TERMS_PER_FIELD ) {
				return self::error( 'bdc_kb_classification_too_many_terms', 'A classificação excede o limite de termos permitido.' );
			}

			$term_ids = array();
			foreach ( $raw_ids as $raw_id ) {
				$is_integer_id = is_int( $raw_id );
				$is_digit_id   = is_string( $raw_id ) && '' !== $raw_id && ctype_digit( $raw_id );
				if ( ! $is_integer_id && ! $is_digit_id ) {
					return self::error( 'bdc_kb_classification_invalid_term', 'Um termo informado possui ID inválido.' );
				}

				$term_id = (int) $raw_id;
				if ( $term_id <= 0 ) {
					return self::error( 'bdc_kb_classification_invalid_term', 'Um termo informado possui ID inválido.' );
				}

				$term_ids[] = $term_id;
			}

			$term_ids = array_values( array_unique( $term_ids ) );
			sort( $term_ids, SORT_NUMERIC );

			if ( ! $fields[ $field ]['multiple'] && count( $term_ids ) > 1 ) {
				return self::error( 'bdc_kb_classification_single_only', 'Este conceito aceita apenas um termo.' );
			}

			$taxonomy = $fields[ $field ]['taxonomy'];
			if ( ! taxonomy_exists( $taxonomy ) ) {
				return self::error( 'bdc_kb_classification_taxonomy_unavailable', 'A taxonomia canônica não está disponível.' );
			}

			foreach ( $term_ids as $term_id ) {
				$exists = term_exists( $term_id, $taxonomy );
				if ( null === $exists || 0 === $exists ) {
					return self::error( 'bdc_kb_classification_term_not_found', 'Um termo não existe na taxonomia esperada.' );
				}
			}

			$prepared[ $field ] = $term_ids;
		}

		return $prepared;
	}

	/**
	 * @return array<string,array<int,int>>|\WP_Error
	 */
	private static function read_field_values( int $post_id ): array|\WP_Error {
		$values = array();
		foreach ( Classification_Contract::fields() as $field => $definition ) {
			$term_ids = wp_get_object_terms(
				$post_id,
				$definition['taxonomy'],
				array( 'fields' => 'ids' )
			);

			if ( is_wp_error( $term_ids ) ) {
				return self::error( 'bdc_kb_classification_read_failed', 'Não foi possível reler a classificação canônica.' );
			}

			$ids = array_map( 'intval', $term_ids );
			$ids = array_values( array_unique( $ids ) );
			sort( $ids, SORT_NUMERIC );
			$values[ $field ] = $ids;
		}

		return $values;
	}

	/**
	 * @param array<string,array<int,int>> $values Valores canônicos.
	 * @return array<string,mixed>
	 */
	private static function compose_snapshot( object $post, array $values ): array {
		return array(
			'post_id' => (int) $post->ID,
			'title'   => (string) $post->post_title,
			'terms'   => $values,
		);
	}

	/**
	 * @param array<int,int> $term_ids IDs.
	 * @return array<int,int>|\WP_Error
	 */
	private static function write_field( int $post_id, string $field, array $term_ids ): array|\WP_Error {
		$taxonomy = Classification_Contract::fields()[ $field ]['taxonomy'];
		$result   = wp_set_object_terms( $post_id, $term_ids, $taxonomy, false );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return array_map( 'intval', $result );
	}

	/**
	 * @param array<string,array<int,int>> $snapshot Estado anterior.
	 * @param array<int,string>            $changed_fields Campos que podem ter sido alterados.
	 */
	private static function restore_snapshot( int $post_id, array $snapshot, array $changed_fields ): void {
		foreach ( $changed_fields as $field ) {
			self::write_field( $post_id, $field, $snapshot[ $field ] );
		}
	}

	/**
	 * @param mixed $post_id ID candidato.
	 * @return object WP_Post ou WP_Error; diferenciar com is_wp_error().
	 */
	private static function validate_post( mixed $post_id ): object {
		$is_integer_id = is_int( $post_id );
		$is_digit_id   = is_string( $post_id ) && '' !== $post_id && ctype_digit( $post_id );

		if ( ! $is_integer_id && ! $is_digit_id ) {
			return self::error( 'bdc_kb_classification_invalid_post', 'O ID do artigo é inválido.' );
		}

		$id = (int) $post_id;
		if ( $id <= 0 ) {
			return self::error( 'bdc_kb_classification_invalid_post', 'O ID do artigo é inválido.' );
		}

		$post = get_post( $id );
		if ( ! is_object( $post ) || ! isset( $post->ID, $post->post_type, $post->post_title ) ) {
			return self::error( 'bdc_kb_classification_invalid_post', 'O artigo informado não existe.' );
		}

		if ( Classification_Contract::POST_TYPE !== $post->post_type ) {
			return self::error( 'bdc_kb_classification_unsupported_post_type', 'A classificação suporta somente artigos do tipo post.' );
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
