<?php
/**
 * Post-scoped Core Blocks operational preparation for the canonical Workspace.
 *
 * T100C is read-only. It computes readiness and a deterministic authorization
 * identity for one post, and can export a safe Authorization Pack JSON.
 *
 * @package BDC_Knowledge_Base
 */
namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Post_Core_Blocks_Activity {
	public const SCHEMA_VERSION = '1.0.0';
	public const ACTION_CONTRACT = 'core_blocks_migrate_v1';
	public const ACTION = 'bdc_kb_workspace_core_blocks_authorization_pack';
	public const NONCE_FIELD = 'bdc_kb_core_blocks_auth_nonce';

	public static function nonce_action( int $post_id ): string {
		return 'bdc_kb_core_blocks_auth_' . $post_id;
	}

	/** @return array<string,mixed>|\WP_Error */
	public static function assess( int $post_id ) {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) || Meta_Contract::POST_TYPE !== (string) ( $post->post_type ?? '' ) ) {
			return new \WP_Error( 'bdc_kb_core_activity_invalid_post', 'Post inválido para Core Blocks Activity.' );
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new \WP_Error( 'bdc_kb_core_activity_forbidden', 'Sem permissão para avaliar este post.' );
		}

		$source = Migration_Fidelity_Source::build( $post_id );
		if ( $source instanceof \WP_Error ) { return $source; }

		$serialization = Core_Block_Lossless_Serializer::serialize_source( $source );
		if ( $serialization instanceof \WP_Error ) { return $serialization; }

		$dry = Block_Migration_Dry_Run::build( $post_id );
		if ( $dry instanceof \WP_Error ) { return $dry; }

		$lock = Block_Migration_Lock::inspect( $post_id );
		if ( $lock instanceof \WP_Error ) { return $lock; }

		$journal_count = count( get_post_meta( $post_id, Block_Migration_Journal_Store::META_KEY, false ) );
		$latest_journal_state = '';
		if ( $journal_count > 0 ) {
			$latest = Block_Migration_Journal_Store::latest_for_post( $post_id );
			if ( $latest instanceof \WP_Error ) { return $latest; }
			$latest_journal_state = (string) ( $latest['record']['state'] ?? '' );
		}

		$block_names = array();
		foreach ( (array) ( $serialization['blocks'] ?? array() ) as $block ) {
			if ( is_array( $block ) ) {
				$name = (string) ( $block['blockName'] ?? '' );
				if ( '' !== $name ) { $block_names[] = $name; }
			}
		}
		$block_names = array_values( array_unique( $block_names ) );

		$state = self::operational_state(
			(string) ( $dry['dry_run_status'] ?? 'unknown' ),
			(string) ( $source['source_kind'] ?? 'unknown' ),
			(string) ( $lock['status'] ?? 'unknown' ),
			$journal_count,
			$latest_journal_state
		);

		$authorization_id = '';
		if ( true === $state['authorization_ready'] ) {
			try {
				$authorization_id = Canonical_JSON::hash( self::authorization_payload( $dry ) );
			} catch ( \JsonException $error ) {
				return new \WP_Error( 'bdc_kb_core_activity_auth_hash', 'Falha ao calcular authorization_id.' );
			}
		}

		return array(
			'schema_version' => self::SCHEMA_VERSION,
			'action_contract' => self::ACTION_CONTRACT,
			'post_id' => $post_id,
			'source_kind' => (string) ( $source['source_kind'] ?? '' ),
			'source_status' => (string) ( $source['status'] ?? '' ),
			'serializer_status' => (string) ( $serialization['status'] ?? '' ),
			'dry_run_status' => (string) ( $dry['dry_run_status'] ?? '' ),
			'dry_run_reasons' => array_values( array_map( 'strval', (array) ( $dry['reasons'] ?? array() ) ) ),
			'fidelity_hash_before' => (string) ( $dry['fidelity_hash_before'] ?? '' ),
			'serialization_hash' => (string) ( $dry['serialization_hash'] ?? '' ),
			'dry_run_hash' => (string) ( $dry['dry_run_hash'] ?? '' ),
			'serialized_post_content_sha256' => (string) ( $dry['serialized_post_content_sha256'] ?? '' ),
			'expected_block_names' => $block_names,
			'journal_event_count' => $journal_count,
			'latest_journal_state' => $latest_journal_state,
			'lock_status' => (string) ( $lock['status'] ?? '' ),
			'operational_status' => (string) $state['status'],
			'operational_reasons' => $state['reasons'],
			'authorization_ready' => (bool) $state['authorization_ready'],
			'authorization_id' => $authorization_id,
			'writer_enabled' => false,
			'migration_execution_enabled' => false,
			'explicit_authorization_required' => true,
			'safety' => array(
				'read_only_design' => true,
				'persists_state' => false,
				'acquires_lock' => false,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'calls_external_network' => false,
				'executes_shortcodes' => false,
				'renders_blocks' => false,
				'exports_editorial_body' => false,
				'exports_urls' => false,
			),
		);
	}

	/** @return array<string,mixed> */
	public static function authorization_payload( array $dry ): array {
		return array(
			'action_contract' => self::ACTION_CONTRACT,
			'post_id' => (int) ( $dry['post_id'] ?? 0 ),
			'fidelity_hash_before' => (string) ( $dry['fidelity_hash_before'] ?? '' ),
			'serialization_hash' => (string) ( $dry['serialization_hash'] ?? '' ),
			'dry_run_hash' => (string) ( $dry['dry_run_hash'] ?? '' ),
			'serialized_post_content_sha256' => (string) ( $dry['serialized_post_content_sha256'] ?? '' ),
		);
	}

	/** @return array{status:string,reasons:array<int,string>,authorization_ready:bool} */
	public static function operational_state( string $dry_status, string $source_kind, string $lock_status, int $journal_count, string $latest_journal_state ): array {
		$reasons = array();

		if ( 'mixed' === $source_kind ) {
			return array( 'status' => 'human_review_required', 'reasons' => array( 'MIXED_SOURCE_REQUIRES_HUMAN' ), 'authorization_ready' => false );
		}
		if ( 'review_required' === $dry_status ) {
			return array( 'status' => 'human_review_required', 'reasons' => array( 'DRY_RUN_REQUIRES_HUMAN' ), 'authorization_ready' => false );
		}
		if ( 'noop' === $dry_status ) {
			return array( 'status' => 'no_action_required', 'reasons' => array( 'CORE_BLOCKS_NOOP' ), 'authorization_ready' => false );
		}
		if ( 'ready' !== $dry_status ) {
			return array( 'status' => 'blocked', 'reasons' => array( 'DRY_RUN_NOT_READY:' . $dry_status ), 'authorization_ready' => false );
		}
		if ( 'free' !== $lock_status ) {
			return array( 'status' => 'blocked', 'reasons' => array( 'LOCK_NOT_FREE:' . $lock_status ), 'authorization_ready' => false );
		}
		if ( $journal_count > 0 && ! in_array( $latest_journal_state, array( Block_Migration_Journal::STATE_ROLLED_BACK ), true ) ) {
			return array( 'status' => 'blocked', 'reasons' => array( 'OPEN_OR_NONTERMINAL_JOURNAL:' . $latest_journal_state ), 'authorization_ready' => false );
		}
		if ( $journal_count > 0 ) { $reasons[] = 'TERMINAL_ROLLBACK_HISTORY_PRESENT'; }

		return array(
			'status' => 'ready_for_authorization',
			'reasons' => $reasons,
			'authorization_ready' => true,
		);
	}

	public static function handle_download(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( 'Método não permitido.', '', array( 'response' => 405 ) );
		}
		$post_id = isset( $_POST['post_id'] ) && is_scalar( $_POST['post_id'] )
			? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
		if ( $post_id <= 0 || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( 'Permissão insuficiente.', '', array( 'response' => 403 ) );
		}
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] )
			? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] ) : '';
		if ( ! wp_verify_nonce( $nonce, self::nonce_action( $post_id ) ) ) {
			wp_die( 'Nonce inválido.', '', array( 'response' => 403 ) );
		}

		$assessment = self::assess( $post_id );
		if ( $assessment instanceof \WP_Error ) {
			wp_die( esc_html( $assessment->get_error_message() ), '', array( 'response' => 409 ) );
		}

		$post = get_post( $post_id );
		$pack = array(
			'schema_version' => self::SCHEMA_VERSION,
			'mode' => 'workspace_single_post_core_blocks_authorization_pack_read_only',
			'generated_at' => gmdate( 'c' ),
			'action_contract' => self::ACTION_CONTRACT,
			'target' => array(
				'post_id' => $post_id,
				'post_title' => is_object( $post ) ? sanitize_text_field( (string) ( $post->post_title ?? '' ) ) : '',
				'post_status' => is_object( $post ) ? (string) ( $post->post_status ?? '' ) : '',
				'source_kind' => (string) ( $assessment['source_kind'] ?? '' ),
			),
			'readiness' => array(
				'operational_status' => (string) ( $assessment['operational_status'] ?? '' ),
				'operational_reasons' => (array) ( $assessment['operational_reasons'] ?? array() ),
				'dry_run_status' => (string) ( $assessment['dry_run_status'] ?? '' ),
				'journal_event_count' => (int) ( $assessment['journal_event_count'] ?? 0 ),
				'latest_journal_state' => (string) ( $assessment['latest_journal_state'] ?? '' ),
				'lock_status' => (string) ( $assessment['lock_status'] ?? '' ),
			),
			'identity' => array(
				'fidelity_hash_before' => (string) ( $assessment['fidelity_hash_before'] ?? '' ),
				'serialization_hash' => (string) ( $assessment['serialization_hash'] ?? '' ),
				'dry_run_hash' => (string) ( $assessment['dry_run_hash'] ?? '' ),
				'serialized_post_content_sha256_expected' => (string) ( $assessment['serialized_post_content_sha256'] ?? '' ),
				'expected_block_names' => (array) ( $assessment['expected_block_names'] ?? array() ),
				'authorization_id' => (string) ( $assessment['authorization_id'] ?? '' ),
			),
			'authorization' => array(
				'authorized' => false,
				'authorization_ready' => true === ( $assessment['authorization_ready'] ?? false ),
				'explicit_human_authorization_required' => true,
				'authorization_id' => (string) ( $assessment['authorization_id'] ?? '' ),
			),
			'safety' => $assessment['safety'] ?? array(),
		);

		$json = wp_json_encode( $pack, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( 'Falha JSON.', '', array( 'response' => 500 ) );
		}
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-core-blocks-auth-post-' . $post_id . '-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json;
		exit;
	}
}
