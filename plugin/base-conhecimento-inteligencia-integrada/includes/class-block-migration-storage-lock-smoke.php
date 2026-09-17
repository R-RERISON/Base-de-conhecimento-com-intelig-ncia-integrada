<?php
/**
 * T099A — durable Block Migration journal + lock environmental smoke.
 *
 * Mutates ONLY private postmeta temporarily, then performs cleanup.
 * Never writes post_content or _elementor_data.
 */
namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Block_Migration_Storage_Lock_Smoke {
	public const ACTION = 'bdc_kb_spec004_g245_t099a_storage_lock';
	public const PAGE_SLUG = 'bdc-kb-spec004-g245-t099a-storage-lock';
	private const NONCE_ACTION = 'bdc_kb_spec004_g245_t099a_storage_lock_run';
	private const NONCE_FIELD = 'bdc_kb_spec004_g245_t099a_storage_lock_nonce';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 40 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'T099A Journal + Lock G-245',
			'T099A Journal + Lock G-245',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ) );
		}
		echo '<div class="wrap"><h1>' . esc_html__( 'SPEC-004 — T099A Journal + Lock', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>Smoke mutável somente em postmeta privado.</strong> O teste seleciona automaticamente um artigo elegível, persiste um journal temporário, adquire/libera lock, remove o journal e confirma que conteúdo editorial não mudou.</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar T099A e baixar JSON', 'bdc-knowledge-base' ) );
		echo '</form></div>';
	}

	public static function handle_run(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( 'Método não permitido.', '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Permissão insuficiente.', '', array( 'response' => 403 ) );
		}
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] ) ? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die( 'Nonce inválido.', '', array( 'response' => 403 ) );
		}

		$report = self::run();
		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( 'Falha JSON.', '', array( 'response' => 500 ) );
		}
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-t099a-storage-lock-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json;
		exit;
	}

	private static function run(): array {
		$started = microtime( true );
		$target_id = self::select_candidate();
		$errors = array();
		$journal_event_id = 0;
		$lock_token = '';
		$journal_cleanup = false;
		$lock_cleanup = false;
		$journal_persisted = false;
		$journal_readback = false;
		$lock_acquired = false;
		$lock_readback = false;
		$dry_status = null;
		$source_kind = null;
		$before = array();
		$after = array();
		$journal_count_before = 0;
		$journal_count_after = 0;
		$lock_present_before = false;
		$lock_present_after = false;

		if ( $target_id <= 0 ) {
			$errors[] = 'NO_SAFE_CANDIDATE_FOUND';
			return self::result( 0, $started, $errors, array() );
		}

		$before = self::editorial_snapshot( $target_id );
		$journal_count_before = count( (array) get_post_meta( $target_id, Block_Migration_Journal_Store::META_KEY, false ) );
		$lock_present_before = '' !== (string) get_post_meta( $target_id, Block_Migration_Lock::META_KEY, true );

		try {
			if ( 0 !== $journal_count_before ) {
				throw new \RuntimeException( 'PREEXISTING_BLOCK_JOURNAL' );
			}
			if ( $lock_present_before ) {
				throw new \RuntimeException( 'PREEXISTING_BLOCK_LOCK' );
			}

			$dry = Block_Migration_Dry_Run::build( $target_id );
			if ( $dry instanceof \WP_Error ) {
				throw new \RuntimeException( 'DRY_RUN_ERROR:' . $dry->get_error_code() );
			}
			$dry_status = (string) ( $dry['dry_run_status'] ?? '' );
			$source_kind = (string) ( $dry['source_kind'] ?? '' );
			if ( 'ready' !== $dry_status ) {
				throw new \RuntimeException( 'DRY_RUN_NOT_READY:' . $dry_status );
			}

			$source = Content_Source::inspect( $target_id );
			if ( $source instanceof \WP_Error ) {
				throw new \RuntimeException( 'SOURCE_INSPECT_ERROR:' . $source->get_error_code() );
			}
			$elementor_raw = $source['elementor_raw'] ?? '';
			if ( ! is_string( $elementor_raw ) ) {
				$tmp = wp_json_encode( $elementor_raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
				$elementor_raw = is_string( $tmp ) ? $tmp : '';
			}

			$journal = Block_Migration_Journal::prepare(
				array(
					'run_id' => 't099a-' . gmdate( 'YmdHis' ) . '-' . substr( wp_generate_uuid4(), 0, 8 ),
					'post_id' => $target_id,
					'fidelity_hash_before' => (string) ( $dry['fidelity_hash_before'] ?? '' ),
					'serialization_hash' => (string) ( $dry['serialization_hash'] ?? '' ),
					'recorded_at' => gmdate( 'c' ),
					'source_kind' => $source_kind,
					'before' => array(
						'post_content' => (string) ( $source['post_content'] ?? '' ),
						'elementor_data' => $elementor_raw,
					),
				)
			);
			if ( $journal instanceof \WP_Error ) {
				throw new \RuntimeException( 'JOURNAL_PREPARE_ERROR:' . $journal->get_error_code() );
			}

			$event = Block_Migration_Journal_Store::persist_prepared( $journal );
			if ( $event instanceof \WP_Error ) {
				throw new \RuntimeException( 'JOURNAL_PERSIST_ERROR:' . $event->get_error_code() );
			}
			$journal_event_id = (int) ( $event['event_id'] ?? 0 );
			$journal_persisted = $journal_event_id > 0;
			if ( ! $journal_persisted ) {
				throw new \RuntimeException( 'JOURNAL_EVENT_ID_MISSING' );
			}

			$readback = Block_Migration_Journal_Store::read_event( $journal_event_id );
			$journal_readback = ! ( $readback instanceof \WP_Error )
				&& (string) ( $readback['record']['journal_hash'] ?? '' ) === (string) ( $event['record']['journal_hash'] ?? '' );
			if ( ! $journal_readback ) {
				throw new \RuntimeException( 'JOURNAL_READBACK_MISMATCH' );
			}

			$lock = Block_Migration_Lock::acquire( $target_id, 't099a-lock-' . gmdate( 'YmdHis' ), 60 );
			if ( $lock instanceof \WP_Error ) {
				throw new \RuntimeException( 'LOCK_ACQUIRE_ERROR:' . $lock->get_error_code() );
			}
			$lock_token = (string) ( $lock['token'] ?? '' );
			$lock_acquired = 'held' === (string) ( $lock['status'] ?? '' ) && '' !== $lock_token;
			if ( ! $lock_acquired ) {
				throw new \RuntimeException( 'LOCK_NOT_HELD' );
			}

			$lock_state = Block_Migration_Lock::inspect( $target_id );
			$lock_readback = ! ( $lock_state instanceof \WP_Error )
				&& 'held' === (string) ( $lock_state['status'] ?? '' )
				&& hash_equals( $lock_token, (string) ( $lock_state['token'] ?? '' ) );
			if ( ! $lock_readback ) {
				$errors[] = 'LOCK_READBACK_MISMATCH';
			}
		} catch ( \RuntimeException $e ) {
			$errors[] = $e->getMessage();
		} catch ( \Throwable $e ) {
			$errors[] = 'THROWABLE:' . get_class( $e ) . ':' . substr( hash( 'sha256', $e->getMessage() ), 0, 16 );
		} finally {
			if ( '' !== $lock_token ) {
				$released = Block_Migration_Lock::release( $target_id, $lock_token );
				$lock_cleanup = ! ( $released instanceof \WP_Error )
					&& in_array( (string) ( $released['status'] ?? '' ), array( 'released', 'already_free' ), true );
			} else {
				$lock_cleanup = '' === (string) get_post_meta( $target_id, Block_Migration_Lock::META_KEY, true );
			}

			if ( $journal_event_id > 0 ) {
				$journal_cleanup = (bool) delete_metadata_by_mid( 'post', $journal_event_id );
			} else {
				$journal_cleanup = 0 === count( (array) get_post_meta( $target_id, Block_Migration_Journal_Store::META_KEY, false ) );
			}

			$after = self::editorial_snapshot( $target_id );
			$journal_count_after = count( (array) get_post_meta( $target_id, Block_Migration_Journal_Store::META_KEY, false ) );
			$lock_present_after = '' !== (string) get_post_meta( $target_id, Block_Migration_Lock::META_KEY, true );
		}

		$details = array(
			'dry_run_status' => $dry_status,
			'source_kind' => $source_kind,
			'journal_count_before' => $journal_count_before,
			'journal_count_after' => $journal_count_after,
			'journal_persisted' => $journal_persisted,
			'journal_readback' => $journal_readback,
			'journal_cleanup' => $journal_cleanup,
			'lock_present_before' => $lock_present_before,
			'lock_present_after' => $lock_present_after,
			'lock_acquired' => $lock_acquired,
			'lock_readback' => $lock_readback,
			'lock_cleanup' => $lock_cleanup,
			'editorial_before' => $before,
			'editorial_after' => $after,
		);

		return self::result( $target_id, $started, $errors, $details );
	}

	private static function result( int $target_id, float $started, array $errors, array $details ): array {
		$before = is_array( $details['editorial_before'] ?? null ) ? $details['editorial_before'] : array();
		$after = is_array( $details['editorial_after'] ?? null ) ? $details['editorial_after'] : array();
		$editorial_equal = ! empty( $before ) && ! empty( $after )
			&& hash_equals( (string) ( $before['post_content_sha256'] ?? '' ), (string) ( $after['post_content_sha256'] ?? '' ) )
			&& hash_equals( (string) ( $before['elementor_data_sha256'] ?? '' ), (string) ( $after['elementor_data_sha256'] ?? '' ) );

		$gate = empty( $errors )
			&& true === ( $details['journal_persisted'] ?? false )
			&& true === ( $details['journal_readback'] ?? false )
			&& true === ( $details['journal_cleanup'] ?? false )
			&& true === ( $details['lock_acquired'] ?? false )
			&& true === ( $details['lock_readback'] ?? false )
			&& true === ( $details['lock_cleanup'] ?? false )
			&& 0 === (int) ( $details['journal_count_before'] ?? -1 )
			&& 0 === (int) ( $details['journal_count_after'] ?? -1 )
			&& false === ( $details['lock_present_before'] ?? true )
			&& false === ( $details['lock_present_after'] ?? true )
			&& $editorial_equal;

		return array(
			'schema_version' => '1.0.0',
			'gate' => 'T099A',
			'mode' => 'durable_journal_and_lock_environmental_smoke',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'journal_meta_key' => Block_Migration_Journal_Store::META_KEY,
				'lock_meta_key' => Block_Migration_Lock::META_KEY,
				'gutenberg_plugin_dependency' => false,
			),
			'target' => array(
				'post_id' => $target_id,
				'source_kind' => $details['source_kind'] ?? null,
				'dry_run_status' => $details['dry_run_status'] ?? null,
			),
			'journal' => array(
				'before_count' => $details['journal_count_before'] ?? null,
				'persisted' => $details['journal_persisted'] ?? false,
				'readback_pass' => $details['journal_readback'] ?? false,
				'cleanup_pass' => $details['journal_cleanup'] ?? false,
				'after_count' => $details['journal_count_after'] ?? null,
			),
			'lock' => array(
				'present_before' => $details['lock_present_before'] ?? null,
				'acquired' => $details['lock_acquired'] ?? false,
				'readback_pass' => $details['lock_readback'] ?? false,
				'cleanup_pass' => $details['lock_cleanup'] ?? false,
				'present_after' => $details['lock_present_after'] ?? null,
			),
			'editorial_integrity' => array(
				'post_content_before_sha256' => $before['post_content_sha256'] ?? null,
				'post_content_after_sha256' => $after['post_content_sha256'] ?? null,
				'elementor_data_before_sha256' => $before['elementor_data_sha256'] ?? null,
				'elementor_data_after_sha256' => $after['elementor_data_sha256'] ?? null,
				'equal' => $editorial_equal,
			),
			'errors' => array_values( $errors ),
			'safety' => array(
				'temporary_private_postmeta_only' => true,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'writer_allowed' => false,
				'migration_execution_allowed' => false,
				'executes_shortcodes' => false,
				'renders_blocks' => false,
				'calls_external_network' => false,
			),
			'gate_result' => array(
				't099a_storage_lock_pass' => $gate,
			),
			'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
		);
	}

	private static function select_candidate(): int {
		$ids = get_posts(
			array(
				'post_type' => 'post',
				'post_status' => 'any',
				'numberposts' => -1,
				'fields' => 'ids',
				'orderby' => 'ID',
				'order' => 'ASC',
				'suppress_filters' => false,
			)
		);
		foreach ( array_map( 'intval', is_array( $ids ) ? $ids : array() ) as $id ) {
			if ( count( (array) get_post_meta( $id, Block_Migration_Journal_Store::META_KEY, false ) ) > 0 ) {
				continue;
			}
			if ( '' !== (string) get_post_meta( $id, Block_Migration_Lock::META_KEY, true ) ) {
				continue;
			}
			$dry = Block_Migration_Dry_Run::build( $id );
			if ( $dry instanceof \WP_Error || 'ready' !== (string) ( $dry['dry_run_status'] ?? '' ) ) {
				continue;
			}
			$kind = (string) ( $dry['source_kind'] ?? '' );
			if ( in_array( $kind, array( 'legacy_html', 'plain_text' ), true ) ) {
				return $id;
			}
		}
		return 0;
	}

	private static function editorial_snapshot( int $post_id ): array {
		$post = get_post( $post_id );
		$content = is_object( $post ) ? (string) ( $post->post_content ?? '' ) : '';
		$raw = get_post_meta( $post_id, '_elementor_data', true );
		if ( ! is_string( $raw ) ) {
			$tmp = wp_json_encode( $raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			$raw = is_string( $tmp ) ? $tmp : '';
		}
		return array(
			'post_content_sha256' => hash( 'sha256', $content ),
			'elementor_data_sha256' => hash( 'sha256', $raw ),
		);
	}
}
