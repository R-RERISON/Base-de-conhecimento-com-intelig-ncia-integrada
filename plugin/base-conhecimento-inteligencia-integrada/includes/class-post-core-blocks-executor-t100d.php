<?php
/**
 * T100D — authorized single-post persistent Core Blocks executor.
 *
 * Hard-scoped to one post_id + authorization_id issued by T100C.
 * Success remains applied. Any post-write verification failure triggers
 * automatic rollback to the durable pre-write snapshot.
 *
 * @package BDC_Knowledge_Base
 */
namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Post_Core_Blocks_Executor_T100D {
	public const SCHEMA_VERSION = '1.0.0';
	public const ACTION = 'bdc_kb_workspace_core_blocks_execute_t100d';
	public const NONCE_FIELD = 'bdc_kb_core_blocks_t100d_nonce';
	public const AUTH_FIELD = 'bdc_kb_core_blocks_t100d_authorization_id';
	public const AUTHORIZED_POST_ID = 358;
	public const AUTHORIZATION_ID = '17c002d3ccc770c6ef154fdbed28cbd0c8d198411c84e168fe9aadb1b7a41af0';
	public const EXPECTED_BLOCK_NAME = 'core/freeform';

	public static function nonce_action(): string {
		return 'bdc_kb_core_blocks_t100d_' . self::AUTHORIZED_POST_ID . '_' . substr( self::AUTHORIZATION_ID, 0, 16 );
	}

	public static function can_render( int $post_id, string $authorization_id ): bool {
		return self::AUTHORIZED_POST_ID === $post_id
			&& '' !== $authorization_id
			&& hash_equals( self::AUTHORIZATION_ID, $authorization_id );
	}

	public static function handle_execute(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( 'Método não permitido.', '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'manage_options obrigatório.', '', array( 'response' => 403 ) );
		}

		$post_id = isset( $_POST['post_id'] ) && is_scalar( $_POST['post_id'] )
			? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
		$authorization_id = isset( $_POST[ self::AUTH_FIELD ] ) && is_scalar( $_POST[ self::AUTH_FIELD ] )
			? strtolower( trim( wp_unslash( (string) $_POST[ self::AUTH_FIELD ] ) ) ) : '';
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] )
			? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] ) : '';

		if ( self::AUTHORIZED_POST_ID !== $post_id || ! hash_equals( self::AUTHORIZATION_ID, $authorization_id ) ) {
			wp_die( 'Escopo/autorização divergente.', '', array( 'response' => 409 ) );
		}
		if ( ! wp_verify_nonce( $nonce, self::nonce_action() ) ) {
			wp_die( 'Nonce inválido.', '', array( 'response' => 403 ) );
		}

		$report = self::run();
		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( 'Falha JSON.', '', array( 'response' => 500 ) );
		}
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-t100d-post-358-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json;
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$post_id = self::AUTHORIZED_POST_ID;
		$run_id = 't100d-' . gmdate( 'YmdHis' ) . '-' . substr( self::AUTHORIZATION_ID, 0, 12 );

		$errors = array();
		$lock_token = '';
		$lock_acquired = false;
		$prepared_event_id = 0;
		$applied_event_id = 0;
		$rolled_back_event_id = 0;
		$prepared_record = null;
		$write_attempted = false;
		$write_verified = false;
		$rollback_attempted = false;
		$rollback_verified = false;
		$prepared_cleaned_prewrite = false;
		$before = array();
		$after_apply = array();
		$final = array();

		$post = get_post( $post_id );
		if ( ! is_object( $post ) || Meta_Contract::POST_TYPE !== (string) ( $post->post_type ?? '' ) ) {
			return self::preflight_failure( 'TARGET_NOT_FOUND', $started );
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return self::preflight_failure( 'EDIT_POST_CAPABILITY_REQUIRED', $started );
		}
		if ( ! current_user_can( 'unfiltered_html' ) ) {
			return self::preflight_failure( 'UNFILTERED_HTML_CAPABILITY_REQUIRED', $started );
		}

		$assessment = Post_Core_Blocks_Activity::assess( $post_id );
		if ( $assessment instanceof \WP_Error ) {
			return self::preflight_failure( 'T100C_ASSESSMENT_FAILED', $started, array( 'wp_error_code' => $assessment->get_error_code() ) );
		}
		if ( 'ready_for_authorization' !== (string) ( $assessment['operational_status'] ?? '' )
			|| true !== ( $assessment['authorization_ready'] ?? false )
			|| ! hash_equals( self::AUTHORIZATION_ID, (string) ( $assessment['authorization_id'] ?? '' ) ) ) {
			return self::preflight_failure( 'AUTHORIZATION_ID_STALE_OR_NOT_READY', $started, array(
				'computed_authorization_id' => (string) ( $assessment['authorization_id'] ?? '' ),
				'operational_status' => (string) ( $assessment['operational_status'] ?? '' ),
			) );
		}
		if ( ! in_array( self::EXPECTED_BLOCK_NAME, (array) ( $assessment['expected_block_names'] ?? array() ), true ) ) {
			return self::preflight_failure( 'EXPECTED_BLOCK_CHANGED', $started );
		}

		$planned_source = Migration_Fidelity_Source::build( $post_id );
		if ( $planned_source instanceof \WP_Error ) {
			return self::preflight_failure( 'SOURCE_BUILD_FAILED', $started, array( 'wp_error_code' => $planned_source->get_error_code() ) );
		}
		$serialization = Core_Block_Lossless_Serializer::serialize_source( $planned_source );
		if ( $serialization instanceof \WP_Error || 'serialized_in_memory' !== (string) ( $serialization['status'] ?? '' ) ) {
			return self::preflight_failure( 'SERIALIZATION_NOT_READY', $started );
		}
		$serialized_content = (string) ( $serialization['serialized_post_content'] ?? '' );
		$expected_serialized_hash = (string) ( $assessment['serialized_post_content_sha256'] ?? '' );
		if ( '' === $expected_serialized_hash || ! hash_equals( $expected_serialized_hash, hash( 'sha256', $serialized_content ) ) ) {
			return self::preflight_failure( 'SERIALIZED_HASH_DIVERGED', $started );
		}

		$before = self::snapshot( $post_id );
		if ( $before instanceof \WP_Error ) {
			return self::preflight_failure( 'SNAPSHOT_FAILED', $started );
		}

		$lock = Block_Migration_Lock::acquire( $post_id, $run_id, 300 );
		if ( $lock instanceof \WP_Error ) {
			return self::preflight_failure( 'LOCK_ACQUIRE_FAILED', $started, array( 'wp_error_code' => $lock->get_error_code() ) );
		}
		$lock_token = (string) ( $lock['token'] ?? '' );
		$lock_acquired = true;

		try {
			$current_source = Migration_Fidelity_Source::build( $post_id );
			if ( $current_source instanceof \WP_Error ) {
				throw new \RuntimeException( 'SOURCE_RECHECK_FAILED' );
			}
			$stale = Block_Migration_Stale_Source_Guard::assess( $planned_source, $current_source );
			if ( 'fresh' !== (string) ( $stale['status'] ?? '' ) ) {
				throw new \RuntimeException( 'STALE_SOURCE_RECHECK_FAILED' );
			}

			$current_dry = Block_Migration_Dry_Run::build( $post_id );
			if ( $current_dry instanceof \WP_Error || 'ready' !== (string) ( $current_dry['dry_run_status'] ?? '' ) ) {
				throw new \RuntimeException( 'DRY_RUN_RECHECK_FAILED' );
			}
			$current_auth = self::authorization_id_from_dry( $current_dry );
			if ( $current_auth instanceof \WP_Error || ! hash_equals( self::AUTHORIZATION_ID, $current_auth ) ) {
				throw new \RuntimeException( 'AUTHORIZATION_RECHECK_STALE' );
			}

			$journal = Block_Migration_Journal::prepare( array(
				'run_id' => $run_id,
				'post_id' => $post_id,
				'fidelity_hash_before' => (string) ( $current_dry['fidelity_hash_before'] ?? '' ),
				'serialization_hash' => (string) ( $current_dry['serialization_hash'] ?? '' ),
				'recorded_at' => gmdate( 'c' ),
				'source_kind' => (string) ( $current_dry['source_kind'] ?? '' ),
				'before' => array(
					'post_content' => (string) $before['post_content'],
					'elementor_data' => (string) $before['elementor_data'],
				),
			) );
			if ( $journal instanceof \WP_Error ) {
				throw new \RuntimeException( 'JOURNAL_PREPARE_FAILED:' . $journal->get_error_code() );
			}
			$persisted = Block_Migration_Journal_Store::persist_prepared( $journal );
			if ( $persisted instanceof \WP_Error ) {
				throw new \RuntimeException( 'JOURNAL_PERSIST_FAILED:' . $persisted->get_error_code() );
			}
			$prepared_event_id = (int) ( $persisted['event_id'] ?? 0 );
			$prepared_record = is_array( $persisted['record'] ?? null ) ? $persisted['record'] : null;
			if ( $prepared_event_id <= 0 || ! is_array( $prepared_record ) ) {
				throw new \RuntimeException( 'JOURNAL_PERSIST_READBACK_INVALID' );
			}

			$final_source = Migration_Fidelity_Source::build( $post_id );
			if ( $final_source instanceof \WP_Error ) {
				throw new \RuntimeException( 'FINAL_SOURCE_RECHECK_FAILED' );
			}
			$final_stale = Block_Migration_Stale_Source_Guard::assess( $planned_source, $final_source );
			if ( 'fresh' !== (string) ( $final_stale['status'] ?? '' ) ) {
				throw new \RuntimeException( 'FINAL_STALE_SOURCE_BLOCK' );
			}
			$final_dry = Block_Migration_Dry_Run::build( $post_id );
			if ( $final_dry instanceof \WP_Error || 'ready' !== (string) ( $final_dry['dry_run_status'] ?? '' ) ) {
				throw new \RuntimeException( 'FINAL_DRY_RUN_NOT_READY' );
			}
			$final_auth = self::authorization_id_from_dry( $final_dry );
			if ( $final_auth instanceof \WP_Error || ! hash_equals( self::AUTHORIZATION_ID, $final_auth ) ) {
				throw new \RuntimeException( 'FINAL_AUTHORIZATION_ID_STALE' );
			}

			$write_attempted = true;
			$update = wp_update_post(
				wp_slash( array(
					'ID' => $post_id,
					'post_content' => $serialized_content,
				) ),
				true
			);
			if ( $update instanceof \WP_Error || (int) $update !== $post_id ) {
				throw new \RuntimeException( 'WP_UPDATE_POST_APPLY_FAILED' );
			}

			$after_apply = self::snapshot( $post_id );
			if ( $after_apply instanceof \WP_Error ) {
				throw new \RuntimeException( 'APPLY_SNAPSHOT_FAILED' );
			}
			$verification = self::verify_applied( $before, $after_apply, $expected_serialized_hash );
			$write_verified = true === $verification['pass'];

			$content_hash = hash( 'sha256', (string) $after_apply['post_content'] );
			$elementor_hash = hash( 'sha256', (string) $after_apply['elementor_data'] );
			$transition = $write_verified
				? Block_Migration_Journal::mark_applied( $prepared_record, $content_hash, $elementor_hash )
				: Block_Migration_Journal::mark_partial_failure( $prepared_record, $content_hash, $elementor_hash );
			if ( $transition instanceof \WP_Error ) {
				throw new \RuntimeException( 'JOURNAL_AFTER_WRITE_TRANSITION_FAILED:' . $transition->get_error_code() );
			}
			$transition_event = Block_Migration_Journal_Store::persist_transition( $transition, $prepared_event_id );
			if ( $transition_event instanceof \WP_Error ) {
				throw new \RuntimeException( 'JOURNAL_AFTER_WRITE_PERSIST_FAILED:' . $transition_event->get_error_code() );
			}

			if ( ! $write_verified ) {
				throw new \RuntimeException( 'POST_WRITE_VERIFICATION_FAILED:' . implode( ',', (array) $verification['reasons'] ) );
			}
			$applied_event_id = (int) ( $transition_event['event_id'] ?? 0 );
		} catch ( \Throwable $error ) {
			$errors[] = self::error_record( $error );

			if ( $write_attempted ) {
				$rollback_attempted = true;
				$rollback_result = self::rollback_after_failure( $post_id, $before, $prepared_event_id );
				$rollback_verified = true === ( $rollback_result['verified'] ?? false );
				$rolled_back_event_id = (int) ( $rollback_result['rolled_back_event_id'] ?? 0 );
				if ( ! empty( $rollback_result['error'] ) ) {
					$errors[] = array(
						'class' => 'Rollback',
						'stage' => (string) $rollback_result['error'],
						'code' => substr( hash( 'sha256', (string) $rollback_result['error'] ), 0, 16 ),
					);
				}
			} elseif ( $prepared_event_id > 0 ) {
				$prepared_cleaned_prewrite = self::cleanup_prepared_event( $post_id, $prepared_event_id );
				if ( ! $prepared_cleaned_prewrite ) {
					$errors[] = array(
						'class' => 'JournalCleanup',
						'stage' => 'PREWRITE_PREPARED_CLEANUP_FAILED',
						'code' => substr( hash( 'sha256', 'PREWRITE_PREPARED_CLEANUP_FAILED' ), 0, 16 ),
					);
				}
			}
		} finally {
			if ( $lock_acquired && '' !== $lock_token ) {
				$release = Block_Migration_Lock::release( $post_id, $lock_token );
				if ( $release instanceof \WP_Error ) {
					$errors[] = array(
						'class' => 'WP_Error',
						'stage' => 'LOCK_RELEASE_FAILED',
						'code' => substr( hash( 'sha256', $release->get_error_code() ), 0, 16 ),
					);
				}
			}
		}

		$final = self::snapshot( $post_id );
		$final_lock = Block_Migration_Lock::inspect( $post_id );
		$latest = Block_Migration_Journal_Store::latest_for_post( $post_id );

		$final_is_applied = empty( $errors )
			&& $write_attempted
			&& $write_verified
			&& ! $rollback_attempted
			&& is_array( $final )
			&& hash_equals( $expected_serialized_hash, hash( 'sha256', (string) $final['post_content'] ) )
			&& hash_equals( hash( 'sha256', (string) $before['elementor_data'] ), hash( 'sha256', (string) $final['elementor_data'] ) )
			&& ! ( $final_lock instanceof \WP_Error )
			&& 'free' === (string) ( $final_lock['status'] ?? '' )
			&& ! ( $latest instanceof \WP_Error )
			&& Block_Migration_Journal::STATE_APPLIED === (string) ( $latest['record']['state'] ?? '' )
			&& $applied_event_id > 0;

		return array(
			'schema_version' => self::SCHEMA_VERSION,
			'gate' => 'T100D',
			'mode' => 'single_post_persistent_core_blocks_migration',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
			),
			'authorization' => array(
				'authorized' => true,
				'action_contract' => Post_Core_Blocks_Activity::ACTION_CONTRACT,
				'post_id' => $post_id,
				'authorization_id' => self::AUTHORIZATION_ID,
			),
			'target' => array(
				'post_id' => $post_id,
				'post_title' => sanitize_text_field( (string) ( $post->post_title ?? '' ) ),
				'source_kind_before' => (string) ( $assessment['source_kind'] ?? '' ),
				'expected_block_name' => self::EXPECTED_BLOCK_NAME,
			),
			'journal' => array(
				'prepared_event_id' => $prepared_event_id,
				'applied_event_id' => $applied_event_id,
				'rolled_back_event_id' => $rolled_back_event_id,
				'prepared_cleaned_prewrite' => $prepared_cleaned_prewrite,
				'latest_state' => ! ( $latest instanceof \WP_Error ) ? (string) ( $latest['record']['state'] ?? '' ) : '',
			),
			'apply' => array(
				'attempted' => $write_attempted,
				'verified' => $write_verified,
				'persistent' => $final_is_applied,
				'expected_post_content_sha256' => $expected_serialized_hash,
				'final_post_content_sha256' => is_array( $final ) ? hash( 'sha256', (string) $final['post_content'] ) : '',
				'elementor_data_unchanged' => is_array( $final ) && is_array( $before )
					? hash_equals( hash( 'sha256', (string) $before['elementor_data'] ), hash( 'sha256', (string) $final['elementor_data'] ) ) : false,
			),
			'rollback' => array(
				'attempted' => $rollback_attempted,
				'verified' => $rollback_verified,
				'policy' => 'automatic_on_post_write_failure_only',
			),
			'errors' => $errors,
			'safety' => array(
				'scope_one_post_only' => true,
				'writes_post_content' => true,
				'writes_elementor_data_as_destination' => false,
				'rollback_on_failure' => true,
				'executes_shortcodes' => false,
				'renders_blocks' => false,
				'calls_external_network' => false,
				'exports_editorial_body' => false,
				'exports_urls' => false,
			),
			'gate_result' => array(
				't100d_persistent_migration_pass' => $final_is_applied,
			),
			'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
		);
	}

	/** @return string|\WP_Error */
	private static function authorization_id_from_dry( array $dry ) {
		try {
			return Canonical_JSON::hash( Post_Core_Blocks_Activity::authorization_payload( $dry ) );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_t100d_auth_hash_failed', 'Falha ao calcular authorization_id.' );
		}
	}

	/** @return array<string,mixed>|\WP_Error */
	private static function snapshot( int $post_id ) {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) {
			return new \WP_Error( 'bdc_kb_t100d_snapshot_post', 'Post ausente no snapshot.' );
		}
		$elementor = get_post_meta( $post_id, '_elementor_data', true );
		if ( ! is_string( $elementor ) ) {
			$json = wp_json_encode( $elementor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			$elementor = is_string( $json ) ? $json : '';
		}
		return array(
			'post_content' => (string) ( $post->post_content ?? '' ),
			'elementor_data' => $elementor,
			'post_status' => (string) ( $post->post_status ?? '' ),
			'post_title_sha256' => hash( 'sha256', (string) ( $post->post_title ?? '' ) ),
			'post_name_sha256' => hash( 'sha256', (string) ( $post->post_name ?? '' ) ),
		);
	}

	/** @return array{pass:bool,reasons:array<int,string>} */
	private static function verify_applied( array $before, array $after, string $expected_serialized_hash ): array {
		$reasons = array();
		if ( ! hash_equals( $expected_serialized_hash, hash( 'sha256', (string) ( $after['post_content'] ?? '' ) ) ) ) {
			$reasons[] = 'POST_CONTENT_HASH_MISMATCH';
		}
		if ( ! hash_equals( hash( 'sha256', (string) ( $before['elementor_data'] ?? '' ) ), hash( 'sha256', (string) ( $after['elementor_data'] ?? '' ) ) ) ) {
			$reasons[] = 'ELEMENTOR_DATA_CHANGED';
		}
		foreach ( array( 'post_status', 'post_title_sha256', 'post_name_sha256' ) as $field ) {
			if ( (string) ( $before[ $field ] ?? '' ) !== (string) ( $after[ $field ] ?? '' ) ) {
				$reasons[] = 'UNEXPECTED_POST_FIELD_CHANGED:' . $field;
			}
		}

		if ( ! function_exists( 'parse_blocks' ) ) {
			$reasons[] = 'PARSE_BLOCKS_UNAVAILABLE';
		} else {
			$parsed = parse_blocks( (string) ( $after['post_content'] ?? '' ) );
			if ( ! is_array( $parsed ) || 1 !== count( $parsed ) || ! is_array( $parsed[0] ?? null ) ) {
				$reasons[] = 'PARSED_BLOCK_COUNT_MISMATCH';
			} else {
				if ( self::EXPECTED_BLOCK_NAME !== (string) ( $parsed[0]['blockName'] ?? '' ) ) {
					$reasons[] = 'BLOCK_NAME_MISMATCH';
				}
				$inner = isset( $parsed[0]['innerHTML'] ) && is_string( $parsed[0]['innerHTML'] ) ? $parsed[0]['innerHTML'] : '';
				if ( ! hash_equals( hash( 'sha256', (string) ( $before['post_content'] ?? '' ) ), hash( 'sha256', $inner ) ) ) {
					$reasons[] = 'LOSSLESS_INNER_HTML_MISMATCH';
				}
			}
		}
		return array( 'pass' => empty( $reasons ), 'reasons' => array_values( array_unique( $reasons ) ) );
	}

	/** @return array<string,mixed> */
	private static function rollback_after_failure( int $post_id, array $before, int $prepared_event_id ): array {
		$current = self::snapshot( $post_id );
		if ( $current instanceof \WP_Error ) {
			return array( 'verified' => false, 'rolled_back_event_id' => 0, 'error' => 'ROLLBACK_CURRENT_SNAPSHOT_FAILED' );
		}

		$latest = Block_Migration_Journal_Store::latest_for_post( $post_id );
		if ( $latest instanceof \WP_Error ) {
			return array( 'verified' => false, 'rolled_back_event_id' => 0, 'error' => 'ROLLBACK_JOURNAL_LATEST_FAILED' );
		}
		$record = is_array( $latest['record'] ?? null ) ? $latest['record'] : array();
		$event_id = (int) ( $latest['event_id'] ?? 0 );

		if ( Block_Migration_Journal::STATE_PREPARED === (string) ( $record['state'] ?? '' ) ) {
			$partial = Block_Migration_Journal::mark_partial_failure(
				$record,
				hash( 'sha256', (string) $current['post_content'] ),
				hash( 'sha256', (string) $current['elementor_data'] )
			);
			if ( $partial instanceof \WP_Error ) {
				return array( 'verified' => false, 'rolled_back_event_id' => 0, 'error' => 'ROLLBACK_PARTIAL_TRANSITION_FAILED' );
			}
			$persisted = Block_Migration_Journal_Store::persist_transition( $partial, $event_id );
			if ( $persisted instanceof \WP_Error ) {
				return array( 'verified' => false, 'rolled_back_event_id' => 0, 'error' => 'ROLLBACK_PARTIAL_PERSIST_FAILED' );
			}
			$record = (array) ( $persisted['record'] ?? array() );
			$event_id = (int) ( $persisted['event_id'] ?? 0 );
		}

		$decision = Block_Migration_Journal::rollback_decision(
			$record,
			hash( 'sha256', (string) $current['post_content'] ),
			hash( 'sha256', (string) $current['elementor_data'] )
		);
		if ( true !== ( $decision['allowed'] ?? false ) ) {
			return array( 'verified' => false, 'rolled_back_event_id' => 0, 'error' => 'ROLLBACK_DECISION_BLOCKED' );
		}

		$rollback = wp_update_post(
			wp_slash( array(
				'ID' => $post_id,
				'post_content' => (string) $before['post_content'],
			) ),
			true
		);
		if ( $rollback instanceof \WP_Error || (int) $rollback !== $post_id ) {
			return array( 'verified' => false, 'rolled_back_event_id' => 0, 'error' => 'WP_UPDATE_POST_ROLLBACK_FAILED' );
		}

		$after = self::snapshot( $post_id );
		if ( $after instanceof \WP_Error ) {
			return array( 'verified' => false, 'rolled_back_event_id' => 0, 'error' => 'ROLLBACK_SNAPSHOT_FAILED' );
		}
		$verified = hash_equals( hash( 'sha256', (string) $before['post_content'] ), hash( 'sha256', (string) $after['post_content'] ) )
			&& hash_equals( hash( 'sha256', (string) $before['elementor_data'] ), hash( 'sha256', (string) $after['elementor_data'] ) );
		if ( ! $verified ) {
			return array( 'verified' => false, 'rolled_back_event_id' => 0, 'error' => 'ROLLBACK_HASH_MISMATCH' );
		}

		$rolled = Block_Migration_Journal::mark_rolled_back(
			$record,
			hash( 'sha256', (string) $after['post_content'] ),
			hash( 'sha256', (string) $after['elementor_data'] )
		);
		if ( $rolled instanceof \WP_Error ) {
			return array( 'verified' => false, 'rolled_back_event_id' => 0, 'error' => 'ROLLBACK_JOURNAL_TRANSITION_FAILED' );
		}
		$persisted_rolled = Block_Migration_Journal_Store::persist_transition( $rolled, $event_id );
		if ( $persisted_rolled instanceof \WP_Error ) {
			return array( 'verified' => false, 'rolled_back_event_id' => 0, 'error' => 'ROLLBACK_JOURNAL_PERSIST_FAILED' );
		}
		return array(
			'verified' => true,
			'rolled_back_event_id' => (int) ( $persisted_rolled['event_id'] ?? 0 ),
			'error' => '',
		);
	}

	private static function cleanup_prepared_event( int $post_id, int $event_id ): bool {
		$latest = Block_Migration_Journal_Store::latest_for_post( $post_id );
		if ( $latest instanceof \WP_Error
			|| $event_id !== (int) ( $latest['event_id'] ?? 0 )
			|| Block_Migration_Journal::STATE_PREPARED !== (string) ( $latest['record']['state'] ?? '' ) ) {
			return false;
		}
		return delete_metadata_by_mid( 'post', $event_id );
	}

	/** @return array<string,string> */
	private static function error_record( \Throwable $error ): array {
		$message = $error->getMessage();
		$stage = '' !== $message ? explode( ':', $message, 2 )[0] : 'UNCLASSIFIED';
		return array(
			'class' => get_class( $error ),
			'stage' => $stage,
			'code' => substr( hash( 'sha256', $message ), 0, 16 ),
		);
	}

	/** @return array<string,mixed> */
	private static function preflight_failure( string $code, float $started, array $extra = array() ): array {
		return array_merge( array(
			'schema_version' => self::SCHEMA_VERSION,
			'gate' => 'T100D',
			'mode' => 'single_post_persistent_core_blocks_migration',
			'generated_at' => gmdate( 'c' ),
			'authorization' => array(
				'authorized' => true,
				'post_id' => self::AUTHORIZED_POST_ID,
				'authorization_id' => self::AUTHORIZATION_ID,
			),
			'preflight_failure_code' => $code,
			'safety' => array(
				'write_attempted' => false,
				'writes_elementor_data_as_destination' => false,
				'calls_external_network' => false,
			),
			'gate_result' => array( 't100d_persistent_migration_pass' => false ),
			'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
		), $extra );
	}
}
