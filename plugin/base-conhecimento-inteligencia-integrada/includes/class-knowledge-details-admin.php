<?php
/**
 * Knowledge facts and Helpful Tips editor for the canonical Workspace.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides one bounded writer surface for inherited knowledge details.
 */
final class Knowledge_Details_Admin {

	public const ACTION        = 'bdc_kb_save_knowledge_details';
	private const NONCE_FIELD  = 'bdc_kb_knowledge_details_nonce';
	private const NONCE_PREFIX = 'bdc_kb_save_knowledge_details_';
	private const UI_TIP_ROWS  = 8;

	/**
	 * Handle the canonical writer.
	 */
	public static function handle_save(): void {
		$request_method = isset( $_SERVER['REQUEST_METHOD'] )
			? sanitize_key( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) )
			: '';

		if ( 'post' !== strtolower( $request_method ) ) {
			wp_die( esc_html__( 'Método HTTP não permitido.', 'bdc-knowledge-base' ), '', array( 'response' => 405 ) );
		}

		$post_id = isset( $_POST['post_id'] ) && is_scalar( $_POST['post_id'] )
			? absint( wp_unslash( (string) $_POST['post_id'] ) )
			: 0;

		$post = $post_id > 0 ? get_post( $post_id ) : null;
		if ( ! is_object( $post ) || Knowledge_Facts_Contract::POST_TYPE !== (string) $post->post_type ) {
			self::redirect( $post_id, 'invalid_post' );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			self::redirect( $post_id, 'forbidden' );
		}

		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] )
			? sanitize_text_field( wp_unslash( (string) $_POST[ self::NONCE_FIELD ] ) )
			: '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_PREFIX . $post_id ) ) {
			self::redirect( $post_id, 'invalid_nonce' );
		}

		// Raw arrays are unslashed here; canonical stores validate and sanitize every field before writing.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized field-by-field by Knowledge_Facts_Store.
		$facts_raw = isset( $_POST['facts'] ) && is_array( $_POST['facts'] ) ? wp_unslash( $_POST['facts'] ) : null;
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized item-by-item by Helpful_Tips_Store.
		$tips_raw = isset( $_POST['tips'] ) && is_array( $_POST['tips'] ) ? wp_unslash( $_POST['tips'] ) : null;

		if ( ! is_array( $facts_raw ) || ! is_array( $tips_raw ) ) {
			self::redirect( $post_id, 'invalid_payload' );
		}

		$facts = array();
		foreach ( Knowledge_Facts_Contract::fields() as $field => $definition ) {
			unset( $definition );
			$value = $facts_raw[ $field ] ?? '';
			if ( ! is_scalar( $value ) ) {
				self::redirect( $post_id, 'invalid_payload' );
			}
			$facts[ $field ] = (string) $value;
		}

		$tips = array();
		foreach ( array_slice( $tips_raw, 0, Helpful_Tips_Store::MAX_ITEMS ) as $row ) {
			if ( ! is_array( $row ) ) {
				self::redirect( $post_id, 'invalid_payload' );
			}
			$title   = isset( $row['title'] ) && is_scalar( $row['title'] ) ? (string) $row['title'] : '';
			$content = isset( $row['content'] ) && is_scalar( $row['content'] ) ? (string) $row['content'] : '';
			$tips[]  = array(
				'title'   => $title,
				'content' => $content,
			);
		}

		$facts_result = Knowledge_Facts_Store::update( $post_id, $facts );
		if ( is_wp_error( $facts_result ) ) {
			self::redirect( $post_id, 'facts_error' );
		}

		$tips_result = Helpful_Tips_Store::update( $post_id, $tips );
		if ( is_wp_error( $tips_result ) ) {
			self::redirect( $post_id, 'tips_error' );
		}

		self::redirect( $post_id, 'saved' );
	}

	/**
	 * Render the details panel.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function render_panel( int $post_id ): void {
		$facts = Knowledge_Facts_Store::read( $post_id );
		$tips  = Helpful_Tips_Store::read( $post_id );

		if ( is_wp_error( $facts ) || is_wp_error( $tips ) ) {
			echo '<div class="notice notice-error inline"><p>' . esc_html__( 'Não foi possível carregar os detalhes de conhecimento.', 'bdc-knowledge-base' ) . '</p></div>';
			return;
		}

		$values  = (array) ( $facts['values'] ?? array() );
		$sources = (array) ( $facts['sources'] ?? array() );

		echo '<section class="bdc-kb-domain-panel" aria-labelledby="bdc-kb-knowledge-details-title">';
		echo '<div class="bdc-kb-domain-heading">';
		echo '<h3 id="bdc-kb-knowledge-details-title">' . esc_html__( 'Detalhes de conhecimento', 'bdc-knowledge-base' ) . '</h3>';
		echo '<p>' . esc_html__( 'Informações operacionais e dicas estruturadas usadas pela experiência pública e pela curadoria.', 'bdc-knowledge-base' ) . '</p>';
		echo '</div>';

		self::render_feedback();

		echo '<form class="bdc-kb-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		echo '<input type="hidden" name="post_id" value="' . esc_attr( (string) $post_id ) . '">';
		wp_nonce_field( self::NONCE_PREFIX . $post_id, self::NONCE_FIELD );

		echo '<div class="bdc-kb-domain-heading"><h4>' . esc_html__( 'Contexto operacional', 'bdc-knowledge-base' ) . '</h4></div>';
		foreach ( Knowledge_Facts_Contract::fields() as $field => $definition ) {
			$field_id = 'bdc-kb-fact-' . $field;
			$value    = (string) ( $values[ $field ] ?? '' );
			$source   = (string) ( $sources[ $field ] ?? $definition['key'] );

			echo '<div class="bdc-kb-field">';
			echo '<label for="' . esc_attr( $field_id ) . '"><strong>' . esc_html( $definition['label'] ) . '</strong></label>';
			echo '<textarea class="large-text" rows="3" id="' . esc_attr( $field_id ) . '" name="facts[' . esc_attr( $field ) . ']" maxlength="' . esc_attr( (string) Knowledge_Facts_Contract::MAX_BYTES ) . '">' . esc_textarea( $value ) . '</textarea>';

			if ( $source !== $definition['key'] && '' !== $value ) {
				echo '<p class="description">' . esc_html__( 'Valor carregado de uma chave legada compatível. Ao salvar, o BDC passa a gravar somente no owner canônico.', 'bdc-knowledge-base' ) . '</p>';
			} else {
				echo '<p class="description">' . esc_html__( 'Owner canônico BDC. Deixe em branco para remover o valor.', 'bdc-knowledge-base' ) . '</p>';
			}
			echo '</div>';
		}

		echo '<hr>';
		echo '<div class="bdc-kb-domain-heading"><h4>' . esc_html__( 'Helpful Tips', 'bdc-knowledge-base' ) . '</h4><p>' . esc_html__( 'Dicas ordenadas exibidas na experiência pública do artigo.', 'bdc-knowledge-base' ) . '</p></div>';

		$row_count = max( self::UI_TIP_ROWS, min( Helpful_Tips_Store::MAX_ITEMS, count( $tips ) + 1 ) );
		for ( $index = 0; $index < $row_count; ++$index ) {
			$row     = $tips[ $index ] ?? array(
				'title'   => '',
				'content' => '',
			);
			$title   = (string) ( $row['title'] ?? '' );
			$content = (string) ( $row['content'] ?? '' );
			$number  = $index + 1;

			echo '<fieldset class="bdc-kb-field">';
			/* translators: %d: Helpful Tip position in the ordered list. */
			echo '<legend><strong>' . esc_html( sprintf( __( 'Dica %d', 'bdc-knowledge-base' ), $number ) ) . '</strong></legend>';
			echo '<label for="bdc-kb-tip-title-' . esc_attr( (string) $index ) . '">' . esc_html__( 'Título', 'bdc-knowledge-base' ) . '</label>';
			echo '<input class="regular-text" id="bdc-kb-tip-title-' . esc_attr( (string) $index ) . '" type="text" name="tips[' . esc_attr( (string) $index ) . '][title]" maxlength="' . esc_attr( (string) Helpful_Tips_Store::MAX_TITLE_BYTES ) . '" value="' . esc_attr( $title ) . '">';
			echo '<label for="bdc-kb-tip-content-' . esc_attr( (string) $index ) . '">' . esc_html__( 'Conteúdo', 'bdc-knowledge-base' ) . '</label>';
			echo '<textarea class="large-text" rows="3" id="bdc-kb-tip-content-' . esc_attr( (string) $index ) . '" name="tips[' . esc_attr( (string) $index ) . '][content]" maxlength="' . esc_attr( (string) Helpful_Tips_Store::MAX_CONTENT_BYTES ) . '">' . esc_textarea( $content ) . '</textarea>';
			echo '</fieldset>';
		}

		submit_button( __( 'Salvar detalhes de conhecimento', 'bdc-knowledge-base' ) );
		echo '</form>';
		echo '</section>';
	}

	/**
	 * Render feedback for the details writer.
	 */
	public static function render_feedback(): void {
		$status = '';
		// Read-only redirect status; no state mutation occurs on this request.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['bdc_details_status'] ) && is_scalar( $_GET['bdc_details_status'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$status = sanitize_key( wp_unslash( (string) $_GET['bdc_details_status'] ) );
		}

		$messages = array(
			'saved'           => array( 'success', 'Detalhes de conhecimento salvos e confirmados.' ),
			'invalid_post'    => array( 'error', 'Artigo inválido ou fora do escopo.' ),
			'forbidden'       => array( 'error', 'Você não tem permissão para editar este artigo.' ),
			'invalid_nonce'   => array( 'error', 'A validação de segurança expirou ou é inválida.' ),
			'invalid_payload' => array( 'error', 'Os dados enviados são inválidos.' ),
			'facts_error'     => array( 'error', 'Não foi possível salvar os fatos de conhecimento.' ),
			'tips_error'      => array( 'error', 'Não foi possível salvar as Helpful Tips.' ),
		);

		if ( ! isset( $messages[ $status ] ) ) {
			return;
		}

		list( $type, $message ) = $messages[ $status ];
		echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
	}

	/**
	 * Redirect back to the bounded details activity.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $status  Safe status key.
	 */
	private static function redirect( int $post_id, string $status ): never {
		$args = array(
			'page'               => Admin_Page::PAGE_SLUG,
			'post_id'            => $post_id,
			'tab'                => 'details',
			'bdc_details_status' => sanitize_key( $status ),
		);

		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}
}
