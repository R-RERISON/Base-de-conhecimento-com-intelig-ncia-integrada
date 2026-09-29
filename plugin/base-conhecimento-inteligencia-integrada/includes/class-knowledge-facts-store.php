<?php
/**
 * Canonical persistence for inherited knowledge facts.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads and updates facts without permanent dual-write.
 */
final class Knowledge_Facts_Store {

	public const STATUS_SUCCESS   = 'SUCCESS';
	public const STATUS_FAIL_SAFE = 'FAIL_SAFE';

	/**
	 * Read the canonical fact snapshot.
	 *
	 * @param mixed $post_id Post ID candidate.
	 * @return array{post_id:int,values:array<string,string>,sources:array<string,string>}|\WP_Error
	 */
	public static function read( mixed $post_id ): array|\WP_Error {
		$post = self::validate_post( $post_id );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$values  = array();
		$sources = array();

		foreach ( Knowledge_Facts_Contract::fields() as $field => $definition ) {
			$value  = self::read_key( (int) $post->ID, $definition['key'] );
			$source = $definition['key'];

			if ( '' === $value ) {
				foreach ( $definition['fallback_keys'] as $fallback_key ) {
					$fallback = self::read_key( (int) $post->ID, $fallback_key );
					if ( '' !== $fallback ) {
						$value  = $fallback;
						$source = $fallback_key;
						break;
					}
				}
			}

			$values[ $field ]  = $value;
			$sources[ $field ] = $source;
		}

		return array(
			'post_id' => (int) $post->ID,
			'values'  => $values,
			'sources' => $sources,
		);
	}

	/**
	 * Update canonical fact values and confirm the final state.
	 *
	 * @param mixed               $post_id Post ID candidate.
	 * @param array<string,mixed> $changes Canonical changes.
	 * @return array{status:string,state:array<string,mixed>,changed_fields:array<int,string>}|\WP_Error
	 */
	public static function update( mixed $post_id, array $changes ): array|\WP_Error {
		$post = self::validate_post( $post_id );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$id = (int) $post->ID;
		if ( ! current_user_can( 'edit_post', $id ) ) {
			return self::error( 'bdc_kb_facts_forbidden', 'Você não tem permissão para editar estes dados.' );
		}

		$prepared = self::prepare_changes( $changes );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$definitions = Knowledge_Facts_Contract::fields();
		$snapshot    = array();
		foreach ( $prepared as $field => $value ) {
			$key                = $definitions[ $field ]['key'];
			$snapshot[ $field ] = self::read_key( $id, $key );
		}

		$changed_fields = array();
		foreach ( $prepared as $field => $value ) {
			if ( $snapshot[ $field ] === $value ) {
				continue;
			}

			update_post_meta( $id, $definitions[ $field ]['key'], $value );
			$changed_fields[] = $field;
		}

		$confirmed = true;
		foreach ( $prepared as $field => $value ) {
			if ( self::read_key( $id, $definitions[ $field ]['key'] ) !== $value ) {
				$confirmed = false;
				break;
			}
		}

		if ( $confirmed ) {
			$state = self::read( $id );
			if ( is_wp_error( $state ) ) {
				return $state;
			}

			return array(
				'status'         => self::STATUS_SUCCESS,
				'state'          => $state,
				'changed_fields' => $changed_fields,
			);
		}

		foreach ( $snapshot as $field => $value ) {
			update_post_meta( $id, $definitions[ $field ]['key'], $value );
		}

		return self::error(
			'bdc_kb_facts_persistence_failed',
			'Não foi possível confirmar os dados. O estado anterior foi restaurado.',
			array( 'status' => self::STATUS_FAIL_SAFE )
		);
	}

	/**
	 * Validate and sanitize all requested changes before the first write.
	 *
	 * @param array<string,mixed> $changes Raw changes.
	 * @return array<string,string>|\WP_Error
	 */
	private static function prepare_changes( array $changes ): array|\WP_Error {
		$definitions = Knowledge_Facts_Contract::fields();
		$prepared    = array();

		foreach ( $changes as $field => $value ) {
			if ( ! is_string( $field ) || ! isset( $definitions[ $field ] ) ) {
				return self::error( 'bdc_kb_facts_unknown_field', 'O payload contém um campo não autorizado.' );
			}
			if ( ! is_string( $value ) ) {
				return self::error( 'bdc_kb_facts_invalid_value', 'Os campos devem ser strings.' );
			}
			if ( strlen( $value ) > Knowledge_Facts_Contract::MAX_BYTES ) {
				return self::error( 'bdc_kb_facts_value_too_large', 'Um campo excede o limite permitido.' );
			}
			$prepared[ $field ] = Knowledge_Facts_Contract::sanitize_text( $value );
		}

		return $prepared;
	}

	/**
	 * Read and normalize one physical metadata key.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Metadata key.
	 * @return string Normalized value.
	 */
	private static function read_key( int $post_id, string $key ): string {
		$value = get_post_meta( $post_id, $key, true );
		return is_scalar( $value ) ? trim( sanitize_textarea_field( (string) $value ) ) : '';
	}

	/**
	 * Validate the target article.
	 *
	 * @param mixed $post_id Post ID candidate.
	 * @return object WP_Post-like object or WP_Error; inspect with is_wp_error().
	 */
	private static function validate_post( mixed $post_id ): object {
		$id = is_int( $post_id ) || ( is_string( $post_id ) && ctype_digit( $post_id ) )
			? (int) $post_id
			: 0;
		if ( $id <= 0 ) {
			return self::error( 'bdc_kb_facts_invalid_post', 'O artigo informado é inválido.' );
		}

		$post = get_post( $id );
		if ( ! is_object( $post ) || 'post' !== (string) ( $post->post_type ?? '' ) ) {
			return self::error( 'bdc_kb_facts_invalid_post', 'O artigo informado não existe ou está fora do escopo.' );
		}

		return $post;
	}

	/**
	 * Build a domain error.
	 *
	 * @param string              $code    Error code.
	 * @param string              $message Human-readable message.
	 * @param array<string,mixed> $data    Safe technical context.
	 * @return \WP_Error Error instance.
	 */
	private static function error( string $code, string $message, array $data = array() ): \WP_Error {
		return new \WP_Error( $code, $message, $data );
	}
}
