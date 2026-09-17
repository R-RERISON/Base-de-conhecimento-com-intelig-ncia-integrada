<?php
/**
 * Superfície administrativa da Classificação de Conhecimento.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renderiza e salva a classificação em formulário independente do Summary.
 */
final class Classification_Admin {

	public const ACTION = 'bdc_kb_save_classification';

	private const NONCE_FIELD  = 'bdc_kb_classification_nonce';
	private const NONCE_PREFIX = 'bdc_kb_save_classification_';

	public static function handle_save(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( esc_html__( 'Método HTTP não permitido.', 'bdc-knowledge-base' ), '', array( 'response' => 405 ) );
		}

		$post_id = isset( $_POST['post_id'] ) && is_scalar( $_POST['post_id'] )
			? absint( wp_unslash( (string) $_POST['post_id'] ) )
			: 0;

		$post = $post_id > 0 ? get_post( $post_id ) : null;
		if ( ! is_object( $post ) || Classification_Contract::POST_TYPE !== $post->post_type ) {
			self::redirect( $post_id, 'invalid_post' );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			self::redirect( $post_id, 'forbidden' );
		}

		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] )
			? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] )
			: '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_PREFIX . $post_id ) ) {
			self::redirect( $post_id, 'invalid_nonce' );
		}

		if ( ! isset( $_POST['classification_present'] ) || ! is_array( $_POST['classification_present'] ) ) {
			self::redirect( $post_id, 'invalid_payload' );
		}

		$raw_present = wp_unslash( $_POST['classification_present'] );

		if ( isset( $_POST['classification'] ) && ! is_array( $_POST['classification'] ) ) {
			self::redirect( $post_id, 'invalid_payload' );
		}

		$raw_classification = isset( $_POST['classification'] )
			? wp_unslash( $_POST['classification'] )
			: array();

		$changes = self::normalize_form_payload( $raw_present, $raw_classification );
		if ( is_wp_error( $changes ) ) {
			self::redirect( $post_id, 'validation_error' );
		}

		$result = Classification_Store::update( $post_id, $changes );
		if ( ! is_wp_error( $result ) ) {
			self::redirect( $post_id, 'saved' );
		}

		$data   = $result->get_error_data();
		$status = is_array( $data ) ? (string) ( $data['status'] ?? '' ) : '';

		if ( Classification_Store::STATUS_PARTIAL_FAILURE_CRITICAL === $status ) {
			self::redirect( $post_id, 'critical' );
		}

		if ( Classification_Store::STATUS_FAIL_SAFE === $status ) {
			self::redirect( $post_id, 'fail_safe' );
		}

		self::redirect( $post_id, 'validation_error' );
	}

	public static function render_feedback(): void {
		$status = isset( $_GET['bdc_classification_status'] ) && is_scalar( $_GET['bdc_classification_status'] )
			? sanitize_key( wp_unslash( (string) $_GET['bdc_classification_status'] ) )
			: '';

		$messages = array(
			'saved'            => array( 'success', 'Classificação salva e confirmada por releitura.' ),
			'fail_safe'        => array( 'error', 'A classificação falhou, mas o estado anterior foi restaurado.' ),
			'critical'         => array( 'error', 'Falha crítica: a classificação anterior não foi restaurada integralmente.' ),
			'invalid_post'     => array( 'error', 'Artigo inválido ou fora do escopo da classificação.' ),
			'forbidden'        => array( 'error', 'Você não tem permissão para classificar o artigo solicitado.' ),
			'invalid_nonce'    => array( 'error', 'A validação de segurança expirou ou é inválida. Reabra o formulário.' ),
			'invalid_payload'  => array( 'error', 'O formulário de classificação recebido é inválido.' ),
			'validation_error' => array( 'error', 'A classificação não foi salva porque o payload violou o contrato.' ),
		);

		if ( ! isset( $messages[ $status ] ) ) {
			return;
		}

		list( $type, $message ) = $messages[ $status ];
		echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
	}

	public static function render_panel( int $post_id ): void {
		$snapshot = Classification_Store::read( $post_id );
		if ( is_wp_error( $snapshot ) ) {
			echo '<div class="notice notice-error inline"><p>' . esc_html__( 'Não foi possível carregar a classificação canônica deste artigo.', 'bdc-knowledge-base' ) . '</p></div>';
			return;
		}

		echo '<section class="bdc-kb-domain-panel bdc-kb-classification" aria-labelledby="bdc-kb-classification-title">';
		echo '<div class="bdc-kb-domain-heading">';
		echo '<h3 id="bdc-kb-classification-title">' . esc_html__( 'Classificação de Conhecimento', 'bdc-knowledge-base' ) . '</h3>';
		echo '<p>' . esc_html__( 'Selecione somente termos canônicos existentes. Valores legados aparecem apenas como referência e nunca são migrados automaticamente.', 'bdc-knowledge-base' ) . '</p>';
		echo '</div>';

		self::render_vocabulary_links();

		echo '<form class="bdc-kb-form bdc-kb-classification-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		echo '<input type="hidden" name="post_id" value="' . esc_attr( (string) $post_id ) . '">';
		wp_nonce_field( self::NONCE_PREFIX . $post_id, self::NONCE_FIELD );

		foreach ( Classification_Contract::fields() as $field => $definition ) {
			self::render_field( $post_id, $field, $definition, $snapshot['terms'][ $field ] ?? array() );
		}

		submit_button( __( 'Salvar Classificação', 'bdc-knowledge-base' ), 'secondary' );
		echo '</form>';
		echo '</section>';
	}

	/**
	 * @param array<string,mixed> $definition Contrato do campo.
	 * @param array<int,int>      $selected_ids IDs atuais.
	 */
	private static function render_field( int $post_id, string $field, array $definition, array $selected_ids ): void {
		$taxonomy = $definition['taxonomy'];
		$terms    = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		$field_id = 'bdc-kb-classification-' . $field;
		echo '<div class="bdc-kb-field bdc-kb-classification-field">';
		echo '<label for="' . esc_attr( $field_id ) . '"><strong>' . esc_html( (string) $definition['singular'] ) . '</strong></label>';
		echo '<input type="hidden" name="classification_present[' . esc_attr( $field ) . ']" value="1">';

		if ( is_wp_error( $terms ) ) {
			echo '<p class="notice notice-error inline"><span>' . esc_html__( 'Não foi possível carregar o vocabulário canônico.', 'bdc-knowledge-base' ) . '</span></p>';
		} else {
			$multiple = (bool) $definition['multiple'];
			$name     = 'classification[' . $field . '][]';
			$attrs    = $multiple ? ' multiple size="6"' : '';
			echo '<select class="regular-text bdc-kb-term-select" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $name ) . '"' . $attrs . '>';

			if ( ! $multiple ) {
				echo '<option value="">' . esc_html__( '— Sem classificação —', 'bdc-knowledge-base' ) . '</option>';
			}

			foreach ( $terms as $term ) {
				$term_id  = (int) $term->term_id;
				$selected = in_array( $term_id, $selected_ids, true ) ? ' selected' : '';
				echo '<option value="' . esc_attr( (string) $term_id ) . '"' . $selected . '>' . esc_html( (string) $term->name ) . '</option>';
			}
			echo '</select>';

			if ( array() === $terms ) {
				echo '<p class="description">' . esc_html__( 'Nenhum termo canônico cadastrado ainda. Um administrador de vocabulário deve criar termos antes do assignment.', 'bdc-knowledge-base' ) . '</p>';
			} elseif ( $multiple ) {
				echo '<p class="description">' . esc_html__( 'Use Ctrl/Cmd para selecionar ou remover múltiplos termos.', 'bdc-knowledge-base' ) . '</p>';
			}
		}

		self::render_legacy_reference( $post_id, $definition['legacy_keys'] );
		echo '</div>';
	}

	/**
	 * @param array<int,string> $legacy_keys Chaves históricas.
	 */
	private static function render_legacy_reference( int $post_id, array $legacy_keys ): void {
		$lines = array();
		foreach ( $legacy_keys as $key ) {
			$value = get_post_meta( $post_id, $key, true );
			if ( '' === $value || null === $value || array() === $value ) {
				continue;
			}

			if ( is_scalar( $value ) ) {
				$text = (string) $value;
			} else {
				$encoded = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
				$text    = is_string( $encoded ) ? $encoded : '[valor estruturado]';
			}

			$lines[] = $key . ': ' . $text;
		}

		if ( array() === $lines ) {
			return;
		}

		echo '<details class="bdc-kb-legacy-reference">';
		echo '<summary>' . esc_html__( 'Referência legada — não canônica', 'bdc-knowledge-base' ) . '</summary>';
		echo '<pre>' . esc_html( implode( "\n", $lines ) ) . '</pre>';
		echo '</details>';
	}

	private static function render_vocabulary_links(): void {
		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}

		echo '<div class="bdc-kb-vocabulary-links"><strong>' . esc_html__( 'Gerenciar vocabulários:', 'bdc-knowledge-base' ) . '</strong> ';
		$links = array();
		foreach ( Classification_Contract::fields() as $definition ) {
			$url = add_query_arg(
				array(
					'taxonomy' => $definition['taxonomy'],
					'post_type' => Classification_Contract::POST_TYPE,
				),
				admin_url( 'edit-tags.php' )
			);
			$links[] = '<a href="' . esc_url( $url ) . '">' . esc_html( (string) $definition['label'] ) . '</a>';
		}
		echo wp_kses_post( implode( ' · ', $links ) );
		echo '</div>';
	}

	/**
	 * @param array<string,mixed> $present Campos declarados pelo formulário.
	 * @param array<string,mixed> $classification Valores enviados.
	 * @return array<string,array<int,int|string>>|\WP_Error
	 */
	private static function normalize_form_payload( array $present, array $classification ): array|\WP_Error {
		$fields = Classification_Contract::fields();

		foreach ( array_keys( $present ) as $field ) {
			if ( ! is_string( $field ) || ! array_key_exists( $field, $fields ) ) {
				return new \WP_Error( 'bdc_kb_classification_unknown_field' );
			}
		}

		foreach ( array_keys( $classification ) as $field ) {
			if ( ! is_string( $field ) || ! array_key_exists( $field, $fields ) || ! array_key_exists( $field, $present ) ) {
				return new \WP_Error( 'bdc_kb_classification_unknown_field' );
			}
		}

		$changes = array();
		foreach ( $present as $field => $marker ) {
			unset( $marker );
			$raw = $classification[ $field ] ?? array();
			if ( ! is_array( $raw ) ) {
				return new \WP_Error( 'bdc_kb_classification_invalid_value' );
			}

			$filtered = array();
			foreach ( $raw as $value ) {
				if ( '' === $value ) {
					continue;
				}
				$filtered[] = $value;
			}
			$changes[ $field ] = $filtered;
		}

		return $changes;
	}

	private static function redirect( int $post_id, string $status ): never {
		$args = array(
			'page'                      => Admin_Page::PAGE_SLUG,
			'tab'                       => 'classification',
			'bdc_classification_status' => sanitize_key( $status ),
		);

		if ( $post_id > 0 ) {
			$args['post_id'] = $post_id;
		}

		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}
}
