<?php
/**
 * Lifecycle seguro da Search Projection.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_Lifecycle {

	public static function register(): void {
		if ( defined( 'BDC_KB_FILE' ) ) {
			register_activation_hook( BDC_KB_FILE, array( self::class, 'activate' ) );
			register_deactivation_hook( BDC_KB_FILE, array( self::class, 'deactivate' ) );
		}

		add_action( 'upgrader_process_complete', array( self::class, 'handle_upgrade' ), 10, 2 );
	}

	/**
	 * Activation leve: prepara somente o schema derivado.
	 */
	public static function activate(): void {
		self::prepare_schema();
	}

	/**
	 * Deactivation v1 é deliberadamente não destrutiva.
	 */
	public static function deactivate(): void {
		// Retenção por default: não remove tabela, Option, fixtures ou conteúdo editorial.
	}

	/**
	 * @param object              $upgrader   Instância do upgrader WordPress.
	 * @param array<string,mixed> $hook_extra Contexto do upgrade.
	 */
	public static function handle_upgrade( object $upgrader, array $hook_extra ): void {
		unset( $upgrader );

		if ( 'plugin' !== (string) ( $hook_extra['type'] ?? '' ) || 'update' !== (string) ( $hook_extra['action'] ?? '' ) ) {
			return;
		}

		$plugins = isset( $hook_extra['plugins'] ) && is_array( $hook_extra['plugins'] )
			? array_map( 'strval', $hook_extra['plugins'] )
			: array();

		$self = defined( 'BDC_KB_FILE' ) ? plugin_basename( BDC_KB_FILE ) : '';
		if ( '' === $self || ! in_array( $self, $plugins, true ) ) {
			return;
		}

		self::prepare_schema();
	}

	/**
	 * Prepara schema sem processar o corpus.
	 *
	 * @return array<string,mixed>
	 */
	public static function prepare_schema(): array {
		$state_before = Search_Projection_Repository::state();
		$schema_before = Search_Projection_Repository::schema_exists();
		$rows_before = $schema_before ? Search_Projection_Repository::count_rows() : null;
		$errors = array();
		$throwables = array();

		try {
			Search_Projection_Repository::ensure_schema();
		} catch ( \Throwable $error ) {
			$throwables[] = array(
				'class' => get_class( $error ),
				'code' => (string) $error->getCode(),
				'message' => $error->getMessage(),
			);
		}

		$schema_after = Search_Projection_Repository::schema_exists();
		$schema_contract = $schema_after ? Search_Projection_Repository::schema_contract() : null;

		if ( $schema_contract instanceof \WP_Error ) {
			$errors[] = array(
				'code' => $schema_contract->get_error_code(),
				'message' => $schema_contract->get_error_message(),
			);
		} elseif ( is_array( $schema_contract ) && empty( $schema_contract['pass'] ) ) {
			$errors[] = array(
				'code' => 'search_projection_schema_incomplete',
				'missing_columns' => (array) ( $schema_contract['missing_columns'] ?? array() ),
				'missing_indexes' => (array) ( $schema_contract['missing_indexes'] ?? array() ),
			);
		}

		if ( $schema_after && empty( $throwables ) && empty( $errors ) ) {
			if ( empty( $state_before ) ) {
				Search_Projection_Repository::write_state(
					array(
						'status' => 'not_built',
						'corpus_count' => 0,
						'source_fingerprint' => '',
						'last_success_at_gmt' => '',
						'last_error_code' => '',
					)
				);
			} elseif ( ! $schema_before ) {
				self::write_degraded_state( $state_before, 'search_projection_schema_recreated' );
			} elseif ( ! self::state_versions_compatible( $state_before ) ) {
				self::write_degraded_state( $state_before, 'search_projection_version_mismatch' );
			}
		}

		if ( ! $schema_after ) {
			$errors[] = array( 'code' => 'search_projection_schema_unavailable' );
		}

		if ( ( ! empty( $errors ) || ! empty( $throwables ) ) && ! empty( $state_before ) ) {
			self::write_degraded_state( $state_before, 'search_projection_schema_incomplete' );
		}

		$rows_after = $schema_after ? Search_Projection_Repository::count_rows() : null;

		return array(
			'schema_before' => $schema_before,
			'schema_after' => $schema_after,
			'rows_before' => $rows_before instanceof \WP_Error ? null : $rows_before,
			'rows_after' => $rows_after instanceof \WP_Error ? null : $rows_after,
			'state_before' => $state_before,
			'schema_contract' => $schema_contract instanceof \WP_Error ? array(
				'pass' => false,
				'error_code' => $schema_contract->get_error_code(),
			) : $schema_contract,
			'state_after' => Search_Projection_Repository::state(),
			'implicit_rebuild' => false,
			'errors' => $errors,
			'throwables' => $throwables,
		);
	}

	/**
	 * @param array<string,mixed> $state
	 */
	private static function state_versions_compatible( array $state ): bool {
		return Search_Projection_Repository::SCHEMA_VERSION === (string) ( $state['schema_version'] ?? '' )
			&& Search_Document_Builder::VERSION === (string) ( $state['document_version'] ?? '' )
			&& Search_Query_Normalizer::VERSION === (string) ( $state['normalizer_version'] ?? '' )
			&& Search_Section_Projector::VERSION === (string) ( $state['section_projection_version'] ?? '' );
	}

	/**
	 * @param array<string,mixed> $state
	 */
	private static function write_degraded_state( array $state, string $error_code ): void {
		Search_Projection_Repository::write_state(
			array(
				'status' => 'degraded',
				'corpus_count' => (int) ( $state['corpus_count'] ?? 0 ),
				'source_fingerprint' => (string) ( $state['source_fingerprint'] ?? '' ),
				'last_success_at_gmt' => (string) ( $state['last_success_at_gmt'] ?? '' ),
				'last_error_code' => $error_code,
			)
		);
	}
}
