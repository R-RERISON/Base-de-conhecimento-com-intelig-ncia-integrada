<?php
/**
 * Handler HTTP e superfície administrativa de Review & Governança da SPEC-003.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Expõe a fronteira POST/PRG e a projection server-rendered do domínio.
 */
final class Review_Admin {

	public const ACTION      = 'bdc_kb_save_review';
	public const NONCE_FIELD = 'bdc_kb_review_nonce';

	private const NONCE_PREFIX = 'bdc_kb_save_review_';

	public static function nonce_action( int $post_id ): string {
		return self::NONCE_PREFIX . $post_id;
	}

	public static function handle_save(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( esc_html__( 'Método HTTP não permitido.', 'bdc-knowledge-base' ), '', array( 'response' => 405 ) );
		}

		$post_id = isset( $_POST['post_id'] ) && is_scalar( $_POST['post_id'] )
			? absint( wp_unslash( (string) $_POST['post_id'] ) )
			: 0;

		$post = $post_id > 0 ? get_post( $post_id ) : null;
		if ( ! is_object( $post ) || Review_Contract::POST_TYPE !== $post->post_type ) {
			self::redirect( $post_id, 'invalid_post' );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			self::redirect( $post_id, 'forbidden' );
		}

		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] )
			? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] )
			: '';

		if ( ! wp_verify_nonce( $nonce, self::nonce_action( $post_id ) ) ) {
			self::redirect( $post_id, 'invalid_nonce' );
		}

		if ( ! isset( $_POST['review'] ) || ! is_array( $_POST['review'] ) ) {
			self::redirect( $post_id, 'invalid_payload' );
		}

		$payload = wp_unslash( $_POST['review'] );
		$allowed = array( 'target_state', 'note' );
		foreach ( array_keys( $payload ) as $key ) {
			if ( ! is_string( $key ) || ! in_array( $key, $allowed, true ) ) {
				self::redirect( $post_id, 'invalid_payload' );
			}
		}

		if ( ! array_key_exists( 'target_state', $payload ) || ! is_scalar( $payload['target_state'] ) ) {
			self::redirect( $post_id, 'invalid_payload' );
		}

		if ( array_key_exists( 'note', $payload ) && ! is_scalar( $payload['note'] ) ) {
			self::redirect( $post_id, 'invalid_payload' );
		}

		$target_state = (string) $payload['target_state'];
		$note         = array_key_exists( 'note', $payload ) ? (string) $payload['note'] : '';
		$result       = Review_Store::transition( $post_id, $target_state, $note );

		if ( ! is_wp_error( $result ) ) {
			$status = (string) ( $result['status'] ?? '' );
			if ( Review_Store::STATUS_NO_CHANGE === $status ) {
				self::redirect( $post_id, 'no_change' );
			}

			self::redirect( $post_id, 'saved' );
		}

		$data   = $result->get_error_data();
		$status = is_array( $data ) ? (string) ( $data['status'] ?? '' ) : '';
		$code   = (string) $result->get_error_code();

		if ( Review_Store::STATUS_PARTIAL_FAILURE_CRITICAL === $status ) {
			self::redirect( $post_id, 'critical' );
		}

		if ( Review_Store::STATUS_FAIL_SAFE === $status ) {
			self::redirect( $post_id, 'fail_safe' );
		}

		if ( in_array( $code, array( 'bdc_review_forbidden', 'bdc_review_reviewer_forbidden' ), true ) ) {
			self::redirect( $post_id, 'forbidden' );
		}

		self::redirect( $post_id, 'validation_error' );
	}

	public static function render_feedback(): void {
		$status = isset( $_GET['bdc_review_status'] ) && is_scalar( $_GET['bdc_review_status'] )
			? sanitize_key( wp_unslash( (string) $_GET['bdc_review_status'] ) )
			: '';

		$messages = array(
			'saved'            => array( 'success', 'Decisão de governança salva e confirmada por releitura.' ),
			'no_change'        => array( 'info', 'O artigo já estava no estado solicitado; nenhum novo evento foi criado.' ),
			'fail_safe'        => array( 'error', 'A decisão não foi confirmada, mas o estado anterior foi restaurado.' ),
			'critical'         => array( 'error', 'Falha crítica de consistência em Review & Governança.' ),
			'invalid_post'     => array( 'error', 'Artigo inválido ou fora do escopo de Review & Governança.' ),
			'forbidden'        => array( 'error', 'Você não possui permissão para executar esta decisão.' ),
			'invalid_nonce'    => array( 'error', 'A validação de segurança expirou ou é inválida. Reabra o Workspace.' ),
			'invalid_payload'  => array( 'error', 'O formulário de Review recebido é inválido.' ),
			'validation_error' => array( 'error', 'A decisão não foi salva porque viola o contrato de Review & Governança.' ),
		);

		if ( ! isset( $messages[ $status ] ) ) {
			return;
		}

		list( $type, $message ) = $messages[ $status ];
		echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
	}

	public static function render_panel( int $post_id ): void {
		$snapshot = Review_Store::read( $post_id );
		if ( is_wp_error( $snapshot ) ) {
			echo '<div class="notice notice-error inline"><p>' . esc_html__( 'Não foi possível carregar o estado canônico de Review & Governança.', 'bdc-knowledge-base' ) . '</p></div>';
			return;
		}

		$state       = (string) ( $snapshot['state'] ?? Review_Contract::STATE_UNREVIEWED );
		$state_label = Review_Contract::states()[ $state ] ?? $state;
		$actor_id    = (int) ( $snapshot['last_actor_id'] ?? 0 );
		$actor_label = self::actor_label( $actor_id );
		$decision_at = self::decision_label( $snapshot['last_decision_at'] ?? null );
		$last_event  = is_array( $snapshot['last_event'] ?? null ) ? $snapshot['last_event'] : array();
		$last_note   = isset( $last_event['note'] ) && is_string( $last_event['note'] ) ? $last_event['note'] : '';
		$targets     = self::available_targets( $post_id, $state );

		echo '<section class="bdc-kb-review" aria-labelledby="bdc-kb-review-title">';
		echo '<div class="bdc-kb-domain-heading">';
		echo '<h3 id="bdc-kb-review-title">' . esc_html__( 'Review & Governança', 'bdc-knowledge-base' ) . '</h3>';
		echo '<p>' . esc_html__( 'Decisões humanas de governança. O estado editorial do WordPress permanece independente.', 'bdc-knowledge-base' ) . '</p>';
		echo '</div>';

		echo '<div class="bdc-kb-review-status-card">';
		echo '<div class="bdc-kb-review-status-row">';
		echo '<span class="bdc-kb-review-label">' . esc_html__( 'Estado atual', 'bdc-knowledge-base' ) . '</span>';
		echo '<strong class="bdc-kb-state-badge bdc-kb-state-' . esc_attr( $state ) . '">' . esc_html( $state_label ) . '</strong>';
		echo '</div>';

		if ( (int) ( $snapshot['last_event_id'] ?? 0 ) > 0 ) {
			echo '<dl class="bdc-kb-review-meta">';
			echo '<div><dt>' . esc_html__( 'Última decisão', 'bdc-knowledge-base' ) . '</dt><dd>' . esc_html( $actor_label ) . '</dd></div>';
			echo '<div><dt>' . esc_html__( 'Data', 'bdc-knowledge-base' ) . '</dt><dd>' . esc_html( $decision_at ) . '</dd></div>';
			echo '</dl>';
			if ( '' !== $last_note ) {
				echo '<div class="bdc-kb-last-note"><strong>' . esc_html__( 'Nota da última decisão', 'bdc-knowledge-base' ) . '</strong><p>' . nl2br( esc_html( $last_note ) ) . '</p></div>';
			}
		} else {
			echo '<p class="description">' . esc_html__( 'Nenhuma decisão de governança foi registrada ainda.', 'bdc-knowledge-base' ) . '</p>';
		}
		echo '</div>';

		if ( empty( $targets ) ) {
			echo '<div class="notice notice-info inline"><p>' . esc_html__( 'Não há transições de governança disponíveis para o seu usuário neste estado.', 'bdc-knowledge-base' ) . '</p></div>';
			echo '</section>';
			return;
		}

		echo '<form class="bdc-kb-form bdc-kb-review-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		echo '<input type="hidden" name="post_id" value="' . esc_attr( (string) $post_id ) . '">';
		wp_nonce_field( self::nonce_action( $post_id ), self::NONCE_FIELD );

		echo '<div class="bdc-kb-field">';
		echo '<label for="bdc-kb-review-target"><strong>' . esc_html__( 'Nova decisão', 'bdc-knowledge-base' ) . '</strong></label>';
		echo '<select id="bdc-kb-review-target" name="review[target_state]" class="regular-text bdc-kb-term-select" required>';
		echo '<option value="">' . esc_html__( '— Selecione uma transição —', 'bdc-knowledge-base' ) . '</option>';
		foreach ( $targets as $target ) {
			$label = Review_Contract::states()[ $target ] ?? $target;
			echo '<option value="' . esc_attr( $target ) . '">' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__( 'A interface mostra apenas transições compatíveis com o estado atual e suas capabilities; o servidor revalida tudo no POST.', 'bdc-knowledge-base' ) . '</p>';
		echo '</div>';

		echo '<div class="bdc-kb-field">';
		echo '<label for="bdc-kb-review-note"><strong>' . esc_html__( 'Nota da decisão', 'bdc-knowledge-base' ) . '</strong></label>';
		echo '<textarea id="bdc-kb-review-note" name="review[note]" rows="5" maxlength="' . esc_attr( (string) Review_Contract::MAX_NOTE_BYTES ) . '"></textarea>';
		echo '<p class="description">' . esc_html__( 'Obrigatória para “Requer ajustes” e “Excluído da base governada”. Máximo de 2000 bytes.', 'bdc-knowledge-base' ) . '</p>';
		echo '</div>';

		submit_button( __( 'Registrar decisão', 'bdc-knowledge-base' ), 'primary' );
		echo '</form>';
		echo '</section>';
	}

	/** @return array<int,string> */
	private static function available_targets( int $post_id, string $state ): array {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return array();
		}

		$result = array();
		foreach ( Review_Contract::allowed_targets( $state ) as $target ) {
			if ( Review_Contract::requires_reviewer_capability( $state, $target ) && ! current_user_can( 'edit_others_posts' ) ) {
				continue;
			}
			$result[] = $target;
		}
		return $result;
	}

	private static function actor_label( int $actor_id ): string {
		if ( $actor_id <= 0 ) {
			return '—';
		}
		$user = get_user_by( 'id', $actor_id );
		if ( is_object( $user ) && isset( $user->display_name ) && '' !== (string) $user->display_name ) {
			return (string) $user->display_name;
		}
		return '#' . $actor_id;
	}

	private static function decision_label( mixed $gmt ): string {
		if ( ! is_string( $gmt ) || '' === $gmt ) {
			return '—';
		}
		$local = get_date_from_gmt( $gmt, 'Y-m-d H:i:s' );
		return is_string( $local ) && '' !== $local ? $local : $gmt;
	}

	private static function redirect( int $post_id, string $status ): never {
		$args = array(
			'page'              => Admin_Page::PAGE_SLUG,
			'bdc_review_status' => sanitize_key( $status ),
			'tab'               => 'review',
		);

		if ( $post_id > 0 ) {
			$args['post_id'] = $post_id;
		}

		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}
}
