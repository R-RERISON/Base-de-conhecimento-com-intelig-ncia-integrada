<?php
/**
 * Handler HTTP permanente de Review & Governança da SPEC-003.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Expõe somente a fronteira POST/PRG do domínio; a UI entra no G-110.
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

	private static function redirect( int $post_id, string $status ): never {
		$args = array(
			'page'              => Admin_Page::PAGE_SLUG,
			'bdc_review_status' => sanitize_key( $status ),
		);

		if ( $post_id > 0 ) {
			$args['post_id'] = $post_id;
		}

		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}
}
