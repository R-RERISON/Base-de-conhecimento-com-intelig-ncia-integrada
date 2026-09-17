<?php
/**
 * T099C — one authorized Core Blocks canary with immediate rollback.
 *
 * Hard scoped to one post + one authorization_id.
 * Uses WordPress Core wp_update_post(), durable journal, exclusive lock,
 * stale-source recheck, post-write verification and immediate rollback.
 */
namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Block_Migration_Canary_T099C {
	public const ACTION = 'bdc_kb_spec004_g245_t099c_canary';
	public const PAGE_SLUG = 'bdc-kb-spec004-g245-t099c-canary';
	public const SCHEMA_VERSION = '1.0.0';
	public const AUTHORIZED_POST_ID = 358;
	public const AUTHORIZATION_ID = '1557c1ee50e1a7a46df7d7952032cb1dd0cb374c7222de8656bb9c055f561bc9';

	private const NONCE_ACTION = 'bdc_kb_spec004_g245_t099c_canary_run';
	private const NONCE_FIELD = 'bdc_kb_spec004_g245_t099c_canary_nonce';
	private const AUTH_FIELD = 'bdc_kb_spec004_g245_t099c_authorization_id';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 41 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'T099C Canary G-245',
			'T099C Canary G-245',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ) );
		}
		$post = get_post( self::AUTHORIZED_POST_ID );
		$title = is_object( $post ) ? (string) ( $post->post_title ?? '' ) : '';
		echo '<div class="wrap"><h1>' . esc_html__( 'SPEC-004 — T099C Canary', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-error inline"><p><strong>WRITE CONTROLADO AUTORIZADO.</strong> Este gate altera temporariamente apenas o post_content do post 358, verifica a serialização Core Blocks e executa rollback imediato para o snapshot original.</p></div>';
		echo '<table class="widefat striped" style="max-width:1100px"><tbody>';
		echo '<tr><th>Post</th><td>' . esc_html( (string) self::AUTHORIZED_POST_ID . ' — ' . $title ) . '</td></tr>';
		echo '<tr><th>authorization_id</th><td><code>' . esc_html( self::AUTHORIZATION_ID ) . '</code></td></tr>';
		echo '<tr><th>Destino temporário</th><td><code>WP_Post.post_content → core/freeform → rollback imediato</code></td></tr>';
		echo '</tbody></table>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:16px">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		echo '<input type="hidden" name="' . esc_attr( self::AUTH_FIELD ) . '" value="' . esc_attr( self::AUTHORIZATION_ID ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar T099C: apply + verify + rollback imediato', 'bdc-knowledge-base' ), 'primary' );
		echo '</form></div>';
	}

	public static function handle_run(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( 'Método não permitido.', '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Permissão insuficiente.', '', array( 'response' => 403 ) );
		}
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] )
			? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die( 'Nonce inválido.', '', array( 'response' => 403 ) );
		}
		$authorization = isset( $_POST[ self::AUTH_FIELD ] ) && is_scalar( $_POST[ self::AUTH_FIELD ] )
			? strtolower( trim( wp_unslash( (string) $_POST[ self::AUTH_FIELD ] ) ) ) : '';
		if ( ! hash_equals( self::AUTHORIZATION_ID, $authorization ) ) {
			wp_die( 'Authorization ID divergente.', '', array( 'response' => 409 ) );
		}

		$report = self::run();
		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( 'Falha JSON.', '', array( 'response' => 500 ) );
		}
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-t099c-canary-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json;
		exit;
	}

	private static function run(): array {
		$started = microtime( true );
		$post_id = self::AUTHORIZED_POST_ID;
		$run_id = 't099c-' . gmdate( 'YmdHis' ) . '-' . substr( self::AUTHORIZATION_ID, 0, 12 );

		$errors = array();
		$lock_token = '';
		$lock_acquired = false;
		$write_attempted = false;
		$apply_verified = false;
		$rollback_attempted = false;
		$rollback_verified = false;
		$prepared_event_id = 0;
		$applied_event_id = 0;
		$rolled_back_event_id = 0;
		$prepared_record = null;
		$applied_record = null;
		$before = array();
		$after_apply = array();
		$after_rollback = array();
		$serialized_content = '';
		$identity = array();

		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) { return self::failure( 'TARGET_NOT_FOUND', $started ); }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return self::failure( 'EDIT_POST_CAPABILITY_REQUIRED', $started ); }
		if ( ! current_user_can( 'unfiltered_html' ) ) { return self::failure( 'UNFILTERED_HTML_CAPABILITY_REQUIRED', $started ); }

		$preexisting_journal_count = count( get_post_meta( $post_id, Block_Migration_Journal_Store::META_KEY, false ) );
		$initial_lock = Block_Migration_Lock::inspect( $post_id );
		if ( $initial_lock instanceof \WP_Error || 'free' !== (string) ( $initial_lock['status'] ?? '' ) || 0 !== $preexisting_journal_count ) {
			return self::failure( 'PREEXISTING_BLOCK_MIGRATION_STATE', $started );
		}

		$dry = Block_Migration_Dry_Run::build( $post_id );
		if ( $dry instanceof \WP_Error || 'ready' !== (string) ( $dry['dry_run_status'] ?? '' ) || 'legacy_html' !== (string) ( $dry['source_kind'] ?? '' ) ) {
			return self::failure( 'DRY_RUN_NOT_READY', $started );
		}
		$auth = self::authorization_identity( $dry );
		if ( $auth instanceof \WP_Error || ! hash_equals( self::AUTHORIZATION_ID, (string) $auth['authorization_id'] ) ) {
			return self::failure( 'AUTHORIZATION_ID_STALE', $started, is_array( $auth ) ? array( 'computed_authorization_id' => (string) $auth['authorization_id'] ) : array() );
		}
		$identity = $auth;

		$planned_source = Migration_Fidelity_Source::build( $post_id );
		if ( $planned_source instanceof \WP_Error ) { return self::failure( 'SOURCE_BUILD_FAILED', $started ); }
		$serialization = Core_Block_Lossless_Serializer::serialize_source( $planned_source );
		if ( $serialization instanceof \WP_Error || 'serialized_in_memory' !== (string) ( $serialization['status'] ?? '' ) ) {
			return self::failure( 'SERIALIZATION_NOT_READY', $started );
		}
		$serialized_content = (string) ( $serialization['serialized_post_content'] ?? '' );
		if ( ! hash_equals( (string) ( $dry['serialized_post_content_sha256'] ?? '' ), hash( 'sha256', $serialized_content ) ) ) {
			return self::failure( 'SERIALIZED_HASH_DIVERGED', $started );
		}

		$before = self::snapshot( $post_id );
		if ( $before instanceof \WP_Error ) { return self::failure( 'SNAPSHOT_FAILED', $started ); }

		$lock = Block_Migration_Lock::acquire( $post_id, $run_id, 300 );
		if ( $lock instanceof \WP_Error ) { return self::failure( 'LOCK_ACQUIRE_FAILED', $started, array( 'wp_error_code' => $lock->get_error_code() ) ); }
		$lock_token = (string) ( $lock['token'] ?? '' );
		$lock_acquired = true;

		try {
			$current_dry = Block_Migration_Dry_Run::build( $post_id );
			if ( $current_dry instanceof \WP_Error || 'ready' !== (string) ( $current_dry['dry_run_status'] ?? '' ) ) { throw new \RuntimeException( 'DRY_RUN_RECHECK_FAILED' ); }
			$current_auth = self::authorization_identity( $current_dry );
			if ( $current_auth instanceof \WP_Error || ! hash_equals( self::AUTHORIZATION_ID, (string) $current_auth['authorization_id'] ) ) { throw new \RuntimeException( 'AUTHORIZATION_RECHECK_STALE' ); }

			$current_source = Migration_Fidelity_Source::build( $post_id );
			if ( $current_source instanceof \WP_Error ) { throw new \RuntimeException( 'SOURCE_RECHECK_FAILED' ); }
			$stale = Block_Migration_Stale_Source_Guard::assess( $planned_source, $current_source );
			if ( 'fresh' !== (string) ( $stale['status'] ?? '' ) ) { throw new \RuntimeException( 'STALE_SOURCE_RECHECK_FAILED' ); }

			$journal = Block_Migration_Journal::prepare( array(
				'run_id' => $run_id,
				'post_id' => $post_id,
				'fidelity_hash_before' => (string) ( $current_dry['fidelity_hash_before'] ?? '' ),
				'serialization_hash' => (string) ( $current_dry['serialization_hash'] ?? '' ),
				'recorded_at' => gmdate( 'c' ),
				'source_kind' => (string) ( $current_dry['source_kind'] ?? '' ),
				'before' => array( 'post_content' => (string) $before['post_content'], 'elementor_data' => (string) $before['elementor_data'] ),
			) );
			if ( $journal instanceof \WP_Error ) { throw new \RuntimeException( 'JOURNAL_PREPARE_FAILED:' . $journal->get_error_code() ); }
			$persisted = Block_Migration_Journal_Store::persist_prepared( $journal );
			if ( $persisted instanceof \WP_Error ) { throw new \RuntimeException( 'JOURNAL_PERSIST_FAILED:' . $persisted->get_error_code() ); }
			$prepared_event_id = (int) ( $persisted['event_id'] ?? 0 );
			$prepared_record = is_array( $persisted['record'] ?? null ) ? $persisted['record'] : null;
			if ( $prepared_event_id <= 0 || ! is_array( $prepared_record ) ) { throw new \RuntimeException( 'JOURNAL_PERSIST_READBACK_INVALID' ); }

			$final_source = Migration_Fidelity_Source::build( $post_id );
			if ( $final_source instanceof \WP_Error ) { throw new \RuntimeException( 'FINAL_SOURCE_RECHECK_FAILED' ); }
			$final_fresh = Block_Migration_Stale_Source_Guard::assess( $planned_source, $final_source );
			if ( 'fresh' !== (string) ( $final_fresh['status'] ?? '' ) ) { throw new \RuntimeException( 'FINAL_STALE_SOURCE_BLOCK' ); }
			$final_dry = Block_Migration_Dry_Run::build( $post_id );
			$final_auth = $final_dry instanceof \WP_Error ? $final_dry : self::authorization_identity( $final_dry );
			if ( $final_auth instanceof \WP_Error || ! hash_equals( self::AUTHORIZATION_ID, (string) $final_auth['authorization_id'] ) ) { throw new \RuntimeException( 'FINAL_AUTHORIZATION_ID_STALE' ); }

			$write_attempted = true;
			$update = wp_update_post( wp_slash( array( 'ID' => $post_id, 'post_content' => $serialized_content ) ), true );
			if ( $update instanceof \WP_Error || (int) $update !== $post_id ) { throw new \RuntimeException( 'WP_UPDATE_POST_APPLY_FAILED' ); }

			$after_apply = self::snapshot( $post_id );
			if ( $after_apply instanceof \WP_Error ) { throw new \RuntimeException( 'APPLY_SNAPSHOT_FAILED' ); }
			$apply_content_hash = hash( 'sha256', (string) $after_apply['post_content'] );
			$apply_elementor_hash = hash( 'sha256', (string) $after_apply['elementor_data'] );
			$expected_serialized_hash = hash( 'sha256', $serialized_content );
			$expected_elementor_hash = hash( 'sha256', (string) $before['elementor_data'] );
			$parsed = function_exists( 'parse_blocks' ) ? parse_blocks( (string) $after_apply['post_content'] ) : array();
			$parsed_name = ''; $parsed_inner_hash = '';
			if ( is_array( $parsed ) && 1 === count( $parsed ) && is_array( $parsed[0] ?? null ) ) {
				$parsed_name = (string) ( $parsed[0]['blockName'] ?? '' );
				$parsed_inner = isset( $parsed[0]['innerHTML'] ) && is_string( $parsed[0]['innerHTML'] ) ? $parsed[0]['innerHTML'] : '';
				$parsed_inner_hash = hash( 'sha256', $parsed_inner );
			}
			$apply_verified = hash_equals( $expected_serialized_hash, $apply_content_hash )
				&& hash_equals( $expected_elementor_hash, $apply_elementor_hash )
				&& 'core/freeform' === $parsed_name
				&& hash_equals( hash( 'sha256', (string) $before['post_content'] ), $parsed_inner_hash );

			$applied_record = $apply_verified
				? Block_Migration_Journal::mark_applied( $prepared_record, $apply_content_hash, $apply_elementor_hash )
				: Block_Migration_Journal::mark_partial_failure( $prepared_record, $apply_content_hash, $apply_elementor_hash );
			if ( $applied_record instanceof \WP_Error ) { throw new \RuntimeException( 'JOURNAL_APPLY_TRANSITION_FAILED:' . $applied_record->get_error_code() ); }
			$apply_event = Block_Migration_Journal_Store::persist_transition( $applied_record, $prepared_event_id );
			if ( $apply_event instanceof \WP_Error ) { throw new \RuntimeException( 'JOURNAL_APPLY_PERSIST_FAILED:' . $apply_event->get_error_code() ); }
			$applied_event_id = (int) ( $apply_event['event_id'] ?? 0 );

			$rollback_decision = Block_Migration_Journal::rollback_decision( $applied_record, $apply_content_hash, $apply_elementor_hash );
			if ( true !== ( $rollback_decision['allowed'] ?? false ) ) { throw new \RuntimeException( 'ROLLBACK_DECISION_BLOCKED:' . (string) ( $rollback_decision['status'] ?? 'unknown' ) ); }

			$rollback_attempted = true;
			$rollback = wp_update_post( wp_slash( array( 'ID' => $post_id, 'post_content' => (string) $before['post_content'] ) ), true );
			if ( $rollback instanceof \WP_Error || (int) $rollback !== $post_id ) { throw new \RuntimeException( 'WP_UPDATE_POST_ROLLBACK_FAILED' ); }
			$after_rollback = self::snapshot( $post_id );
			if ( $after_rollback instanceof \WP_Error ) { throw new \RuntimeException( 'ROLLBACK_SNAPSHOT_FAILED' ); }
			$rollback_content_hash = hash( 'sha256', (string) $after_rollback['post_content'] );
			$rollback_elementor_hash = hash( 'sha256', (string) $after_rollback['elementor_data'] );
			$rollback_verified = hash_equals( hash( 'sha256', (string) $before['post_content'] ), $rollback_content_hash )
				&& hash_equals( hash( 'sha256', (string) $before['elementor_data'] ), $rollback_elementor_hash );
			if ( ! $rollback_verified ) { throw new \RuntimeException( 'ROLLBACK_HASH_MISMATCH' ); }

			$rolled_record = Block_Migration_Journal::mark_rolled_back( $applied_record, $rollback_content_hash, $rollback_elementor_hash );
			if ( $rolled_record instanceof \WP_Error ) { throw new \RuntimeException( 'JOURNAL_ROLLBACK_TRANSITION_FAILED:' . $rolled_record->get_error_code() ); }
			$rolled_event = Block_Migration_Journal_Store::persist_transition( $rolled_record, $applied_event_id );
			if ( $rolled_event instanceof \WP_Error ) { throw new \RuntimeException( 'JOURNAL_ROLLBACK_PERSIST_FAILED:' . $rolled_event->get_error_code() ); }
			$rolled_back_event_id = (int) ( $rolled_event['event_id'] ?? 0 );
		} catch ( \Throwable $error ) {
			$errors[] = array( 'class' => get_class( $error ), 'code' => substr( hash( 'sha256', $error->getMessage() ), 0, 16 ), 'stage' => self::safe_stage( $error->getMessage() ) );
			if ( $write_attempted && ! $rollback_verified && is_array( $before ) && isset( $before['post_content'] ) ) {
				$rollback_attempted = true;
				$emergency = wp_update_post( wp_slash( array( 'ID' => $post_id, 'post_content' => (string) $before['post_content'] ) ), true );
				if ( ! ( $emergency instanceof \WP_Error ) && (int) $emergency === $post_id ) {
					$emergency_snapshot = self::snapshot( $post_id );
					if ( is_array( $emergency_snapshot ) ) {
						$rollback_verified = hash_equals( hash( 'sha256', (string) $before['post_content'] ), hash( 'sha256', (string) $emergency_snapshot['post_content'] ) )
							&& hash_equals( hash( 'sha256', (string) $before['elementor_data'] ), hash( 'sha256', (string) $emergency_snapshot['elementor_data'] ) );
						$after_rollback = $emergency_snapshot;
					}
				}
			}
		} finally {
			if ( $lock_acquired && '' !== $lock_token ) {
				$release = Block_Migration_Lock::release( $post_id, $lock_token );
				if ( $release instanceof \WP_Error ) {
					$errors[] = array( 'class' => 'WP_Error', 'code' => substr( hash( 'sha256', $release->get_error_code() ), 0, 16 ), 'stage' => 'LOCK_RELEASE_FAILED' );
				}
			}
		}

		$final = self::snapshot( $post_id );
		$final_lock = Block_Migration_Lock::inspect( $post_id );
		$latest_journal = Block_Migration_Journal_Store::latest_for_post( $post_id );
		$final_content_equal = is_array( $final ) && is_array( $before ) && hash_equals( hash( 'sha256', (string) $before['post_content'] ), hash( 'sha256', (string) $final['post_content'] ) );
		$final_elementor_equal = is_array( $final ) && is_array( $before ) && hash_equals( hash( 'sha256', (string) $before['elementor_data'] ), hash( 'sha256', (string) $final['elementor_data'] ) );
		$lock_free = ! ( $final_lock instanceof \WP_Error ) && 'free' === (string) ( $final_lock['status'] ?? '' );
		$latest_is_rolled_back = ! ( $latest_journal instanceof \WP_Error ) && Block_Migration_Journal::STATE_ROLLED_BACK === (string) ( $latest_journal['record']['state'] ?? '' );
		$journal_event_count = count( get_post_meta( $post_id, Block_Migration_Journal_Store::META_KEY, false ) );
		$pass = empty( $errors ) && $write_attempted && $apply_verified && $rollback_attempted && $rollback_verified
			&& $final_content_equal && $final_elementor_equal && $lock_free && $latest_is_rolled_back
			&& $prepared_event_id > 0 && $applied_event_id > 0 && $rolled_back_event_id > 0 && $journal_event_count >= 3;

		return array(
			'schema_version' => self::SCHEMA_VERSION,
			'gate' => 'T099C',
			'mode' => 'single_authorized_apply_verify_immediate_rollback',
			'generated_at' => gmdate( 'c' ),
			'environment' => array( 'wordpress' => get_bloginfo( 'version' ), 'php' => PHP_VERSION, 'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '', 'gutenberg_plugin_dependency' => false ),
			'authorization' => array( 'authorized' => true, 'post_id' => $post_id, 'authorization_id' => self::AUTHORIZATION_ID, 'computed_authorization_id' => (string) ( $identity['authorization_id'] ?? '' ), 'authorization_matches' => isset( $identity['authorization_id'] ) && hash_equals( self::AUTHORIZATION_ID, (string) $identity['authorization_id'] ) ),
			'target' => array( 'post_id' => $post_id, 'post_title' => sanitize_text_field( (string) ( $post->post_title ?? '' ) ), 'source_kind' => 'legacy_html' ),
			'preflight' => array( 'manage_options' => current_user_can( 'manage_options' ), 'edit_post' => current_user_can( 'edit_post', $post_id ), 'unfiltered_html' => current_user_can( 'unfiltered_html' ), 'preexisting_journal_count' => $preexisting_journal_count, 'initial_lock_status' => is_array( $initial_lock ) ? (string) ( $initial_lock['status'] ?? '' ) : 'error' ),
			'journal' => array( 'prepared_event_id' => $prepared_event_id, 'applied_event_id' => $applied_event_id, 'rolled_back_event_id' => $rolled_back_event_id, 'event_count_after' => $journal_event_count, 'latest_state' => ! ( $latest_journal instanceof \WP_Error ) ? (string) ( $latest_journal['record']['state'] ?? '' ) : '', 'audit_preserved' => $journal_event_count >= 3 ),
			'apply' => array( 'attempted' => $write_attempted, 'verified' => $apply_verified, 'expected_serialized_sha256' => hash( 'sha256', $serialized_content ), 'actual_post_content_sha256' => is_array( $after_apply ) ? hash( 'sha256', (string) $after_apply['post_content'] ) : '', 'expected_block_name' => 'core/freeform', 'original_payload_sha256' => is_array( $before ) ? hash( 'sha256', (string) $before['post_content'] ) : '', 'elementor_data_unchanged_at_apply' => is_array( $after_apply ) && is_array( $before ) ? hash_equals( hash( 'sha256', (string) $before['elementor_data'] ), hash( 'sha256', (string) $after_apply['elementor_data'] ) ) : false ),
			'rollback' => array( 'attempted' => $rollback_attempted, 'verified' => $rollback_verified, 'final_post_content_equals_original' => $final_content_equal, 'final_elementor_data_equals_original' => $final_elementor_equal, 'lock_free_after' => $lock_free, 'journal_latest_state_rolled_back' => $latest_is_rolled_back ),
			'wordpress_side_effects' => array( 'uses_wp_update_post' => true, 'normal_post_modified_timestamp_change_possible' => true, 'normal_wordpress_revision_creation_possible' => true, 'journal_audit_intentionally_persisted' => true ),
			'errors' => $errors,
			'safety' => array( 'scope_one_post_only' => true, 'writes_post_content_temporarily' => true, 'writes_elementor_data_as_destination' => false, 'immediate_rollback_required' => true, 'executes_shortcodes' => false, 'renders_blocks' => false, 'calls_external_network' => false, 'exports_editorial_body' => false, 'exports_urls' => false ),
			'gate_result' => array( 't099c_canary_pass' => $pass ),
			'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
		);
	}

	private static function authorization_identity( array $dry ) {
		$payload = array( 'gate' => 'T099C', 'post_id' => (int) ( $dry['post_id'] ?? 0 ), 'fidelity_hash_before' => (string) ( $dry['fidelity_hash_before'] ?? '' ), 'serialization_hash' => (string) ( $dry['serialization_hash'] ?? '' ), 'dry_run_hash' => (string) ( $dry['dry_run_hash'] ?? '' ), 'serialized_post_content_sha256' => (string) ( $dry['serialized_post_content_sha256'] ?? '' ) );
		try { $id = Canonical_JSON::hash( $payload ); } catch ( \JsonException $error ) { return new \WP_Error( 'bdc_kb_t099c_auth_hash_failed', 'Falha ao calcular authorization_id.' ); }
		return array( 'authorization_id' => $id, 'fidelity_hash_before' => $payload['fidelity_hash_before'], 'serialization_hash' => $payload['serialization_hash'], 'dry_run_hash' => $payload['dry_run_hash'], 'serialized_post_content_sha256' => $payload['serialized_post_content_sha256'] );
	}

	private static function snapshot( int $post_id ) {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) { return new \WP_Error( 'bdc_kb_t099c_snapshot_post', 'Post ausente no snapshot.' ); }
		$elementor = get_post_meta( $post_id, '_elementor_data', true );
		if ( ! is_string( $elementor ) ) { $json = wp_json_encode( $elementor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); $elementor = is_string( $json ) ? $json : ''; }
		return array( 'post_content' => (string) ( $post->post_content ?? '' ), 'elementor_data' => $elementor, 'post_modified' => (string) ( $post->post_modified ?? '' ), 'post_modified_gmt' => (string) ( $post->post_modified_gmt ?? '' ), 'post_status' => (string) ( $post->post_status ?? '' ), 'post_title_sha256' => hash( 'sha256', (string) ( $post->post_title ?? '' ) ), 'post_name_sha256' => hash( 'sha256', (string) ( $post->post_name ?? '' ) ) );
	}

	private static function safe_stage( string $message ): string {
		$known = array( 'DRY_RUN_RECHECK_FAILED', 'AUTHORIZATION_RECHECK_STALE', 'SOURCE_RECHECK_FAILED', 'STALE_SOURCE_RECHECK_FAILED', 'JOURNAL_PREPARE_FAILED', 'JOURNAL_PERSIST_FAILED', 'JOURNAL_PERSIST_READBACK_INVALID', 'FINAL_SOURCE_RECHECK_FAILED', 'FINAL_STALE_SOURCE_BLOCK', 'FINAL_AUTHORIZATION_ID_STALE', 'WP_UPDATE_POST_APPLY_FAILED', 'APPLY_SNAPSHOT_FAILED', 'JOURNAL_APPLY_TRANSITION_FAILED', 'JOURNAL_APPLY_PERSIST_FAILED', 'ROLLBACK_DECISION_BLOCKED', 'WP_UPDATE_POST_ROLLBACK_FAILED', 'ROLLBACK_SNAPSHOT_FAILED', 'ROLLBACK_HASH_MISMATCH', 'JOURNAL_ROLLBACK_TRANSITION_FAILED', 'JOURNAL_ROLLBACK_PERSIST_FAILED' );
		foreach ( $known as $prefix ) { if ( str_starts_with( $message, $prefix ) ) { return $prefix; } }
		return 'UNCLASSIFIED';
	}

	private static function failure( string $code, float $started, array $extra = array() ): array {
		return array_merge( array( 'schema_version' => self::SCHEMA_VERSION, 'gate' => 'T099C', 'mode' => 'single_authorized_apply_verify_immediate_rollback', 'generated_at' => gmdate( 'c' ), 'authorization' => array( 'authorized' => true, 'post_id' => self::AUTHORIZED_POST_ID, 'authorization_id' => self::AUTHORIZATION_ID ), 'preflight_failure_code' => $code, 'safety' => array( 'write_attempted' => false, 'writes_elementor_data_as_destination' => false, 'calls_external_network' => false ), 'gate_result' => array( 't099c_canary_pass' => false ), 'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ) ), $extra );
	}
}
