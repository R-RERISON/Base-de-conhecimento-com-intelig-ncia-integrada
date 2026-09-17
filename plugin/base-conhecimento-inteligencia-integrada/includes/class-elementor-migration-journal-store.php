<?php
/**
 * Durable append-only WordPress-first store for Elementor migration journals.
 * SPEC-004 / G-245 / T083B.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Elementor_Migration_Journal_Store {

	public const COMMENT_TYPE = 'bdc_kb_migration_journal';
	public const EVENT_SCHEMA = 1;
	public const MAX_EVENT_BYTES = 16777216; // 16 MiB safety ceiling for v1.

	/** @return array<string,mixed>|\WP_Error */
	public static function persist_prepared( array $record ) {
		$record = Elementor_Migration_Journal::mark_persisted( $record );
		if ( is_wp_error( $record ) ) {
			return $record;
		}
		if ( Elementor_Migration_Journal::STATE_PREPARED !== (string) ( $record['state'] ?? '' ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_invalid_prepared_state', 'Somente journal prepared pode iniciar a trilha durável.' );
		}

		return self::append_event( $record, 0 );
	}

	/** @return array<string,mixed>|\WP_Error */
	public static function persist_transition( array $record, int $parent_event_id ) {
		if ( $parent_event_id <= 0 ) {
			return new \WP_Error( 'bdc_kb_journal_store_invalid_parent', 'parent_event_id inválido.' );
		}

		$valid = Elementor_Migration_Journal::validate_record( $record, true );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$state = (string) ( $record['state'] ?? '' );
		if ( ! in_array( $state, array( Elementor_Migration_Journal::STATE_APPLIED, Elementor_Migration_Journal::STATE_PARTIAL_FAILURE, Elementor_Migration_Journal::STATE_ROLLED_BACK ), true ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_invalid_transition_state', 'Estado de transição não persistível.' );
		}

		$parent = self::read_event( $parent_event_id );
		if ( is_wp_error( $parent ) ) {
			return $parent;
		}
		$parent_record = $parent['record'];
		if ( (string) ( $parent_record['journal_id'] ?? '' ) !== (string) ( $record['journal_id'] ?? '' ) || (int) ( $parent_record['post_id'] ?? 0 ) !== (int) ( $record['post_id'] ?? 0 ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_parent_identity_mismatch', 'Evento pai pertence a outro journal/post.' );
		}
		if ( ! self::transition_allowed( (string) ( $parent_record['state'] ?? '' ), $state ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_parent_state_mismatch', 'Transição durável incompatível com o estado do evento pai.' );
		}

		$latest = self::latest_for_post( (int) ( $record['post_id'] ?? 0 ) );
		if ( is_wp_error( $latest ) || (int) ( $latest['event_id'] ?? 0 ) !== $parent_event_id || (string) ( $latest['record']['journal_id'] ?? '' ) !== (string) ( $record['journal_id'] ?? '' ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_non_linear_chain', 'Transição bloqueada: evento pai não é o último evento durável do journal/post.' );
		}

		return self::append_event( $record, $parent_event_id );
	}

	/** @return array<string,mixed>|\WP_Error */
	public static function read_event( int $event_id ) {
		if ( $event_id <= 0 ) {
			return new \WP_Error( 'bdc_kb_journal_store_invalid_event_id', 'event_id inválido.' );
		}

		$comment = get_comment( $event_id );
		if ( ! is_object( $comment ) || self::COMMENT_TYPE !== (string) ( $comment->comment_type ?? '' ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_event_not_found', 'Evento de journal não encontrado.' );
		}
		if ( '1' !== (string) ( $comment->comment_approved ?? '' ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_unapproved_event', 'Evento de journal não está aprovado/ativo.' );
		}

		$decoded = json_decode( (string) ( $comment->comment_content ?? '' ), true );
		if ( ! is_array( $decoded ) || self::EVENT_SCHEMA !== (int) ( $decoded['event_schema'] ?? 0 ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_malformed_event', 'Evento de journal malformado.' );
		}

		$record = self::decode_record( is_array( $decoded['record'] ?? null ) ? $decoded['record'] : array() );
		if ( is_wp_error( $record ) ) {
			return $record;
		}
		$valid = Elementor_Migration_Journal::validate_record( $record, true );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}
		if ( (int) ( $record['post_id'] ?? 0 ) !== (int) ( $comment->comment_post_ID ?? 0 ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_post_mismatch', 'Evento de journal diverge do post associado.' );
		}
		if ( (string) ( $decoded['state'] ?? '' ) !== (string) ( $record['state'] ?? '' ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_state_mismatch', 'Estado externo do evento diverge do record.' );
		}
		if ( (string) ( $decoded['journal_id'] ?? '' ) !== (string) ( $record['journal_id'] ?? '' ) || (string) ( $decoded['run_id'] ?? '' ) !== (string) ( $record['run_id'] ?? '' ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_identity_mismatch', 'Identidade externa do evento diverge do record.' );
		}

		return array(
			'event_id' => (int) ( $comment->comment_ID ?? 0 ),
			'parent_event_id' => (int) ( $decoded['parent_event_id'] ?? 0 ),
			'actor_id' => (int) ( $comment->user_id ?? 0 ),
			'created_at_gmt' => (string) ( $comment->comment_date_gmt ?? '' ),
			'record' => $record,
		);
	}

	/** @return array<string,mixed>|\WP_Error */
	public static function latest_for_post( int $post_id ) {
		if ( $post_id <= 0 ) {
			return new \WP_Error( 'bdc_kb_journal_store_invalid_post_id', 'post_id inválido.' );
		}
		$comments = get_comments(
			array(
				'post_id' => $post_id,
				'type' => self::COMMENT_TYPE,
				'status' => 'approve',
				'number' => 1,
				'orderby' => 'comment_ID',
				'order' => 'DESC',
			)
		);
		if ( ! is_array( $comments ) || empty( $comments ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_no_events', 'Nenhum evento durável encontrado para o post.' );
		}
		return self::read_event( (int) $comments[0]->comment_ID );
	}

	/** @return array<string,mixed>|\WP_Error */
	private static function append_event( array $record, int $parent_event_id ) {
		$valid = Elementor_Migration_Journal::validate_record( $record, true );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}
		$post_id = (int) ( $record['post_id'] ?? 0 );
		if ( $post_id <= 0 || ! get_post( $post_id ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_invalid_post', 'Post do journal não existe.' );
		}
		$actor_id = (int) get_current_user_id();
		if ( $actor_id <= 0 ) {
			return new \WP_Error( 'bdc_kb_journal_store_missing_actor', 'Usuário autenticado obrigatório para persistir journal.' );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_forbidden', 'Persistência de journal exige manage_options.' );
		}

		$portable = self::encode_record( $record );
		if ( is_wp_error( $portable ) ) {
			return $portable;
		}
		$payload = wp_json_encode(
			array(
				'event_schema' => self::EVENT_SCHEMA,
				'parent_event_id' => $parent_event_id,
				'journal_id' => (string) ( $record['journal_id'] ?? '' ),
				'run_id' => (string) ( $record['run_id'] ?? '' ),
				'state' => (string) ( $record['state'] ?? '' ),
				'record' => $portable,
			),
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		);
		if ( ! is_string( $payload ) || '' === $payload || strlen( $payload ) > self::MAX_EVENT_BYTES ) {
			return new \WP_Error( 'bdc_kb_journal_store_payload_invalid', 'Payload do journal é inválido ou excede o limite de segurança.' );
		}

		$event_id = (int) wp_insert_comment(
			array(
				'comment_post_ID' => $post_id,
				'comment_content' => $payload,
				'comment_type' => self::COMMENT_TYPE,
				'comment_approved' => 1,
				'user_id' => $actor_id,
			)
		);
		if ( $event_id <= 0 ) {
			return new \WP_Error( 'bdc_kb_journal_store_write_failed', 'Falha ao persistir evento de journal.' );
		}

		$readback = self::read_event( $event_id );
		if ( ! is_wp_error( $readback ) && self::readback_matches( $readback, $record, $parent_event_id, $actor_id ) ) {
			return $readback;
		}

		$deleted = wp_delete_comment( $event_id, true );
		if ( $deleted ) {
			return new \WP_Error( 'bdc_kb_journal_store_fail_safe', 'Persistência divergiu na releitura; evento recém-criado foi removido.' );
		}

		error_log( sprintf( '[BDC-KB][JOURNAL_PARTIAL_FAILURE_CRITICAL] post_id=%d event_id=%d', $post_id, $event_id ) );
		return new \WP_Error( 'bdc_kb_journal_store_partial_failure_critical', 'Falha crítica: evento divergente não pôde ser removido.' );
	}

	/** @return array<string,mixed>|\WP_Error */
	private static function encode_record( array $record ) {
		$payload = $record['rollback_payload'] ?? null;
		if ( ! is_array( $payload ) || ! is_string( $payload['post_content'] ?? null ) || ! is_string( $payload['elementor_data'] ?? null ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_missing_capsule', 'Rollback capsule ausente ou inválida.' );
		}
		$portable = $record;
		$portable['rollback_payload'] = array(
			'encoding' => 'base64',
			'post_content' => base64_encode( $payload['post_content'] ),
			'elementor_data' => base64_encode( $payload['elementor_data'] ),
		);
		return $portable;
	}

	/** @return array<string,mixed>|\WP_Error */
	private static function decode_record( array $portable ) {
		$capsule = $portable['rollback_payload'] ?? null;
		if ( ! is_array( $capsule ) || 'base64' !== (string) ( $capsule['encoding'] ?? '' ) ) {
			return new \WP_Error( 'bdc_kb_journal_store_capsule_encoding', 'Encoding da rollback capsule inválido.' );
		}
		$post_content = base64_decode( (string) ( $capsule['post_content'] ?? '' ), true );
		$elementor_data = base64_decode( (string) ( $capsule['elementor_data'] ?? '' ), true );
		if ( false === $post_content || false === $elementor_data ) {
			return new \WP_Error( 'bdc_kb_journal_store_capsule_decode', 'Falha ao decodificar rollback capsule.' );
		}
		$portable['rollback_payload'] = array(
			'post_content' => $post_content,
			'elementor_data' => $elementor_data,
		);
		return $portable;
	}

	private static function transition_allowed( string $from, string $to ): bool {
		if ( Elementor_Migration_Journal::STATE_PREPARED === $from ) {
			return in_array( $to, array( Elementor_Migration_Journal::STATE_APPLIED, Elementor_Migration_Journal::STATE_PARTIAL_FAILURE ), true );
		}
		if ( in_array( $from, array( Elementor_Migration_Journal::STATE_APPLIED, Elementor_Migration_Journal::STATE_PARTIAL_FAILURE ), true ) ) {
			return Elementor_Migration_Journal::STATE_ROLLED_BACK === $to;
		}
		return false;
	}

	private static function readback_matches( array $readback, array $record, int $parent_event_id, int $actor_id ): bool {
		$actual = $readback['record'] ?? null;
		return is_array( $actual )
			&& (int) ( $readback['parent_event_id'] ?? -1 ) === $parent_event_id
			&& (int) ( $readback['actor_id'] ?? 0 ) === $actor_id
			&& (string) ( $actual['journal_id'] ?? '' ) === (string) ( $record['journal_id'] ?? '' )
			&& (string) ( $actual['journal_hash'] ?? '' ) === (string) ( $record['journal_hash'] ?? '' )
			&& (string) ( $actual['rollback_payload_hash'] ?? '' ) === (string) ( $record['rollback_payload_hash'] ?? '' );
	}
}
