<?php
/**
 * Controlled homologation smoke for durable journal storage.
 * SPEC-004 / G-245 / T083B.
 *
 * The smoke writes only private journal post metadata, verifies exact readback,
 * deletes the temporary event, and proves editorial fields are unchanged.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Elementor_Migration_Journal_Smoke {

	public const ACTION = 'bdc_kb_spec004_g245_journal_smoke';
	public const PAGE_SLUG = 'bdc-kb-spec004-g245-journal-smoke';

	private const NONCE_ACTION = 'bdc_kb_spec004_g245_journal_smoke_run';
	private const NONCE_FIELD = 'bdc_kb_spec004_g245_journal_smoke_nonce';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 35 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Journal Storage G-245',
			'Journal Storage G-245',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ) );
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'SPEC-004 — G-245 Journal Storage Smoke', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Smoke controlado.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'Cria um único evento temporário em postmeta privado, valida o round-trip e remove o evento. Não altera post_content nem _elementor_data.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		echo '<p><label for="bdc-kb-journal-smoke-post-id">' . esc_html__( 'Post ID de homologação:', 'bdc-knowledge-base' ) . '</label> ';
		echo '<input id="bdc-kb-journal-smoke-post-id" name="post_id" type="number" min="1" required></p>';
		submit_button( __( 'Executar smoke e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
		echo '</form></div>';
	}

	public static function handle_run(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( esc_html__( 'Método HTTP não permitido.', 'bdc-knowledge-base' ), '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] )
			? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] )
			: '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Nonce inválido ou expirado.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}
		$post_id = isset( $_POST['post_id'] ) && is_scalar( $_POST['post_id'] ) ? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
		if ( $post_id <= 0 ) {
			wp_die( esc_html__( 'Post ID inválido.', 'bdc-knowledge-base' ), '', array( 'response' => 400 ) );
		}

		$report = self::run( $post_id );
		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( esc_html__( 'Falha ao serializar relatório.', 'bdc-knowledge-base' ), '', array( 'response' => 500 ) );
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-g245-journal-smoke-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	private static function run( int $post_id ): array {
		$started = microtime( true );
		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) {
			return self::failure_report( 'INVALID_POST', $started );
		}

		$post_content_before = (string) ( $post->post_content ?? '' );
		$elementor_data_before = (string) get_post_meta( $post_id, '_elementor_data', true );
		$journal_count_before = self::journal_count( $post_id );
		$event_id = 0;
		$cleanup_ok = false;
		$roundtrip_ok = false;
		$record_integrity_ok = false;
		$error_code = null;

		try {
			$plan = Elementor_Projection_Plan::build( $post_id );
			if ( is_wp_error( $plan ) ) {
				return self::failure_report( 'PROJECTION_PLAN_ERROR', $started );
			}
			$source_hash = strtolower( trim( (string) ( $plan['source_hash_before'] ?? '' ) ) );
			$projection_hash = strtolower( trim( (string) ( $plan['projection_hash'] ?? '' ) ) );
			if ( 1 !== preg_match( '/^[a-f0-9]{64}$/', $source_hash ) || 1 !== preg_match( '/^[a-f0-9]{64}$/', $projection_hash ) ) {
				return self::failure_report( 'INVALID_PLAN_HASHES', $started );
			}

			$record = Elementor_Migration_Journal::prepare(
				array(
					'run_id' => 'journal-smoke-' . gmdate( 'YmdHis' ) . '-' . $post_id,
					'post_id' => $post_id,
					'source_hash_before' => $source_hash,
					'projection_hash' => $projection_hash,
					'recorded_at' => gmdate( 'c' ),
					'before' => array(
						'post_content' => $post_content_before,
						'elementor_data' => $elementor_data_before,
					),
				)
			);
			if ( is_wp_error( $record ) ) {
				return self::failure_report( 'JOURNAL_PREPARE_ERROR', $started );
			}

			$event = Elementor_Migration_Journal_Store::persist_prepared( $record );
			if ( is_wp_error( $event ) ) {
				$error_code = method_exists( $event, 'get_error_code' ) ? $event->get_error_code() : 'JOURNAL_PERSIST_ERROR';
			} else {
				$event_id = (int) ( $event['event_id'] ?? 0 );
				$stored = is_array( $event['record'] ?? null ) ? $event['record'] : array();
				$roundtrip_ok = (string) ( $stored['rollback_payload']['post_content'] ?? '' ) === $post_content_before
					&& (string) ( $stored['rollback_payload']['elementor_data'] ?? '' ) === $elementor_data_before;
				$record_integrity_ok = true === Elementor_Migration_Journal::validate_record( $stored, true );
			}
		} catch ( \Throwable $error ) {
			$error_code = 'THROWABLE:' . get_class( $error );
		} finally {
			if ( $event_id > 0 ) {
				$cleanup_ok = (bool) delete_metadata_by_mid( 'post', $event_id );
			} else {
				$cleanup_ok = true;
			}
		}

		$post_after = get_post( $post_id );
		$post_content_after = is_object( $post_after ) ? (string) ( $post_after->post_content ?? '' ) : '';
		$elementor_data_after = (string) get_post_meta( $post_id, '_elementor_data', true );
		$journal_count_after = self::journal_count( $post_id );
		$editorial_unchanged = hash_equals( hash( 'sha256', $post_content_before ), hash( 'sha256', $post_content_after ) )
			&& hash_equals( hash( 'sha256', $elementor_data_before ), hash( 'sha256', $elementor_data_after ) );
		$count_restored = $journal_count_before === $journal_count_after;
		$gate = $event_id > 0 && $roundtrip_ok && $record_integrity_ok && $cleanup_ok && $editorial_unchanged && $count_restored && null === $error_code;

		return array(
			'schema_version' => '1.0.0',
			'mode' => 'spec004_g245_journal_storage_smoke',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
			),
			'target' => array(
				'post_id_sha256' => hash( 'sha256', (string) $post_id ),
			),
			'journal' => array(
				'storage' => 'private_postmeta_append_only',
				'meta_key' => Elementor_Migration_Journal_Store::META_KEY,
				'event_created' => $event_id > 0,
				'roundtrip_exact' => $roundtrip_ok,
				'record_integrity_ok' => $record_integrity_ok,
				'cleanup_ok' => $cleanup_ok,
				'count_before' => $journal_count_before,
				'count_after' => $journal_count_after,
				'count_restored' => $count_restored,
			),
			'editorial' => array(
				'post_content_unchanged' => hash_equals( hash( 'sha256', $post_content_before ), hash( 'sha256', $post_content_after ) ),
				'elementor_data_unchanged' => hash_equals( hash( 'sha256', $elementor_data_before ), hash( 'sha256', $elementor_data_after ) ),
				'editorial_unchanged' => $editorial_unchanged,
			),
			'safety' => array(
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'calls_external_network' => false,
				'executes_shortcodes' => false,
				'writer_allowed' => false,
				'migration_execution_allowed' => false,
			),
			'error_code' => $error_code,
			'gate' => array(
				't083b_storage_pass' => $gate,
			),
			'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
		);
	}

	private static function journal_count( int $post_id ): int {
		$values = get_post_meta( $post_id, Elementor_Migration_Journal_Store::META_KEY, false );
		return is_array( $values ) ? count( $values ) : 0;
	}

	/** @return array<string,mixed> */
	private static function failure_report( string $code, float $started ): array {
		return array(
			'schema_version' => '1.0.0',
			'mode' => 'spec004_g245_journal_storage_smoke',
			'generated_at' => gmdate( 'c' ),
			'error_code' => $code,
			'gate' => array( 't083b_storage_pass' => false ),
			'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
		);
	}
}
