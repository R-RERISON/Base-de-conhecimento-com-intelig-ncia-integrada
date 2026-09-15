<?php
/**
 * Store canônico de Review & Governança da SPEC-003.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Usa eventos append-only em WordPress Comments API como fonte da verdade.
 */
final class Review_Store {

	public const STATUS_SUCCESS                  = 'SUCCESS';
	public const STATUS_NO_CHANGE                = 'NO_CHANGE';
	public const STATUS_FAIL_SAFE                = 'FAIL_SAFE';
	public const STATUS_PARTIAL_FAILURE_CRITICAL = 'PARTIAL_FAILURE_CRITICAL';

	/**
	 * @return array<string,mixed>|\WP_Error
	 */
	public static function read( int $post_id ) {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) || Review_Contract::POST_TYPE !== $post->post_type ) {
			return new \WP_Error( 'bdc_review_invalid_post', 'Artigo inválido ou fora do escopo.' );
		}

		$comments = get_comments(
			array(
				'post_id' => $post_id,
				'type'    => Review_Contract::COMMENT_TYPE,
				'status'  => 'approve',
				'number'  => 1,
				'orderby' => 'comment_ID',
				'order'   => 'DESC',
			)
		);

		if ( ! is_array( $comments ) || empty( $comments ) ) {
			return array(
				'post_id'          => $post_id,
				'state'            => Review_Contract::STATE_UNREVIEWED,
				'last_event_id'    => 0,
				'last_actor_id'    => 0,
				'last_decision_at' => null,
				'last_event'       => null,
			);
		}

		$comment = $comments[0];
		$event   = self::parse_event( $comment );
		if ( is_wp_error( $event ) ) {
			return $event;
		}

		return array(
			'post_id'          => $post_id,
			'state'            => $event['to'],
			'last_event_id'    => (int) $comment->comment_ID,
			'last_actor_id'    => (int) $comment->user_id,
			'last_decision_at' => (string) $comment->comment_date_gmt,
			'last_event'       => $event,
		);
	}

	/**
	 * @return array<int,array<string,mixed>>|\WP_Error
	 */
	public static function history( int $post_id, int $limit = 20, int $offset = 0 ) {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) || Review_Contract::POST_TYPE !== $post->post_type ) {
			return new \WP_Error( 'bdc_review_invalid_post', 'Artigo inválido ou fora do escopo.' );
		}

		$limit  = max( 1, min( 100, $limit ) );
		$offset = max( 0, $offset );
		$comments = get_comments(
			array(
				'post_id' => $post_id,
				'type'    => Review_Contract::COMMENT_TYPE,
				'status'  => 'approve',
				'number'  => $limit,
				'offset'  => $offset,
				'orderby' => 'comment_ID',
				'order'   => 'DESC',
			)
		);

		if ( ! is_array( $comments ) ) {
			return new \WP_Error( 'bdc_review_history_read_failed', 'Não foi possível ler o histórico de governança.' );
		}

		$result = array();
		foreach ( $comments as $comment ) {
			$event = self::parse_event( $comment );
			if ( is_wp_error( $event ) ) {
				return $event;
			}
			$result[] = array(
				'event_id'    => (int) $comment->comment_ID,
				'actor_id'    => (int) $comment->user_id,
				'decision_at' => (string) $comment->comment_date_gmt,
				'from'        => $event['from'],
				'to'          => $event['to'],
				'note'        => $event['note'],
			);
		}

		return $result;
	}

	/**
	 * @return array<string,mixed>|\WP_Error
	 */
	public static function transition( int $post_id, string $target_state, string $note = '' ) {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) || Review_Contract::POST_TYPE !== $post->post_type ) {
			return new \WP_Error( 'bdc_review_invalid_post', 'Artigo inválido ou fora do escopo.' );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new \WP_Error( 'bdc_review_forbidden', 'Permissão insuficiente para o artigo.' );
		}

		$target_state = sanitize_key( $target_state );
		if ( ! Review_Contract::is_state( $target_state ) || Review_Contract::STATE_UNREVIEWED === $target_state ) {
			return new \WP_Error( 'bdc_review_invalid_target', 'Estado de destino inválido.' );
		}

		if ( strlen( $note ) > Review_Contract::MAX_NOTE_BYTES ) {
			return new \WP_Error( 'bdc_review_note_too_large', 'A nota excede o limite permitido.' );
		}
		$note = trim( sanitize_textarea_field( $note ) );

		$current = self::read( $post_id );
		if ( is_wp_error( $current ) ) {
			return $current;
		}

		$from = (string) $current['state'];
		if ( $from === $target_state ) {
			return array(
				'status'   => self::STATUS_NO_CHANGE,
				'post_id'  => $post_id,
				'from'     => $from,
				'to'       => $target_state,
				'event_id' => 0,
				'changed'  => false,
			);
		}

		if ( ! Review_Contract::is_transition_allowed( $from, $target_state ) ) {
			return new \WP_Error( 'bdc_review_invalid_transition', 'Transição de governança não permitida.' );
		}

		if ( Review_Contract::note_required( $target_state ) && '' === $note ) {
			return new \WP_Error( 'bdc_review_note_required', 'Esta decisão exige uma justificativa.' );
		}

		if ( Review_Contract::requires_reviewer_capability( $from, $target_state ) && ! current_user_can( 'edit_others_posts' ) ) {
			return new \WP_Error( 'bdc_review_reviewer_forbidden', 'Esta decisão exige permissão de revisor.' );
		}

		$actor_id = (int) get_current_user_id();
		if ( $actor_id <= 0 ) {
			return new \WP_Error( 'bdc_review_missing_actor', 'Usuário autenticado inválido.' );
		}

		$payload = wp_json_encode(
			array(
				'schema_version' => Review_Contract::EVENT_SCHEMA,
				'from'           => $from,
				'to'             => $target_state,
				'note'           => $note,
			),
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		);

		if ( ! is_string( $payload ) || '' === $payload ) {
			return new \WP_Error( 'bdc_review_encode_failed', 'Não foi possível serializar a decisão.' );
		}

		$event_id = wp_insert_comment(
			array(
				'comment_post_ID'  => $post_id,
				'comment_content'  => $payload,
				'comment_type'     => Review_Contract::COMMENT_TYPE,
				'comment_approved' => 1,
				'user_id'          => $actor_id,
			)
		);

		$event_id = (int) $event_id;
		if ( $event_id <= 0 ) {
			return new \WP_Error(
				'bdc_review_write_failed',
				'A decisão não foi persistida.',
				array( 'status' => self::STATUS_FAIL_SAFE )
			);
		}

		$after = self::read( $post_id );
		if ( ! is_wp_error( $after ) && self::matches_expected( $after, $event_id, $actor_id, $from, $target_state, $note ) ) {
			return array(
				'status'   => self::STATUS_SUCCESS,
				'post_id'  => $post_id,
				'from'     => $from,
				'to'       => $target_state,
				'event_id' => $event_id,
				'changed'  => true,
			);
		}

		$deleted  = wp_delete_comment( $event_id, true );
		$restored = self::read( $post_id );
		if ( $deleted && ! is_wp_error( $restored ) && self::matches_snapshot( $restored, $current ) ) {
			return new \WP_Error(
				'bdc_review_fail_safe',
				'A releitura divergiu, mas o evento recém-criado foi removido e o estado anterior foi restaurado.',
				array( 'status' => self::STATUS_FAIL_SAFE )
			);
		}

		error_log( sprintf( '[BDC-KB][REVIEW_PARTIAL_FAILURE_CRITICAL] post_id=%d event_id=%d', $post_id, $event_id ) );
		return new \WP_Error(
			'bdc_review_partial_failure_critical',
			'Falha crítica de consistência em Review & Governança.',
			array( 'status' => self::STATUS_PARTIAL_FAILURE_CRITICAL )
		);
	}

	/**
	 * @param object $comment WP_Comment-like object.
	 * @return array{schema_version:int,from:string,to:string,note:string}|\WP_Error
	 */
	private static function parse_event( object $comment ) {
		$decoded = json_decode( (string) $comment->comment_content, true );
		if ( ! is_array( $decoded ) ) {
			return new \WP_Error( 'bdc_review_integrity_error', 'Evento de governança malformado.' );
		}

		$schema = isset( $decoded['schema_version'] ) ? (int) $decoded['schema_version'] : 0;
		$from   = isset( $decoded['from'] ) && is_scalar( $decoded['from'] ) ? sanitize_key( (string) $decoded['from'] ) : '';
		$to     = isset( $decoded['to'] ) && is_scalar( $decoded['to'] ) ? sanitize_key( (string) $decoded['to'] ) : '';
		$note   = isset( $decoded['note'] ) && is_scalar( $decoded['note'] ) ? (string) $decoded['note'] : '';

		if ( Review_Contract::EVENT_SCHEMA !== $schema || ! Review_Contract::is_state( $from ) || ! Review_Contract::is_state( $to ) ) {
			return new \WP_Error( 'bdc_review_integrity_error', 'Evento de governança viola o contrato canônico.' );
		}

		if ( Review_Contract::STATE_UNREVIEWED !== $from && Review_Contract::STATE_UNREVIEWED === $to ) {
			return new \WP_Error( 'bdc_review_integrity_error', 'Evento de governança contém transição inválida.' );
		}

		if ( ! Review_Contract::is_transition_allowed( $from, $to ) || $from === $to ) {
			return new \WP_Error( 'bdc_review_integrity_error', 'Evento de governança contém transição não autorizada.' );
		}

		return array(
			'schema_version' => $schema,
			'from'           => $from,
			'to'             => $to,
			'note'           => $note,
		);
	}

	/** @param array<string,mixed> $after */
	private static function matches_expected( array $after, int $event_id, int $actor_id, string $from, string $to, string $note ): bool {
		$event = $after['last_event'] ?? null;
		return (int) ( $after['last_event_id'] ?? 0 ) === $event_id
			&& (int) ( $after['last_actor_id'] ?? 0 ) === $actor_id
			&& (string) ( $after['state'] ?? '' ) === $to
			&& is_array( $event )
			&& (string) ( $event['from'] ?? '' ) === $from
			&& (string) ( $event['to'] ?? '' ) === $to
			&& (string) ( $event['note'] ?? '' ) === $note;
	}

	/** @param array<string,mixed> $restored @param array<string,mixed> $snapshot */
	private static function matches_snapshot( array $restored, array $snapshot ): bool {
		return (string) ( $restored['state'] ?? '' ) === (string) ( $snapshot['state'] ?? '' )
			&& (int) ( $restored['last_event_id'] ?? 0 ) === (int) ( $snapshot['last_event_id'] ?? 0 );
	}
}
