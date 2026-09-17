<?php
/**
 * Fundação visual compartilhada do produto BDC.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Carrega a linguagem visual BDC somente nas superfícies pertencentes ao produto.
 * Mantém WordPress como shell e primitives nativas como implementação funcional.
 */
final class Visual_Foundation {

	private const WORKSPACE_HOOK = 'toplevel_page_bdc-knowledge-summary';
	private const TAXONOMY_HOOK  = 'edit-tags.php';

	public static function register(): void {
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ), 20 );
		add_filter( 'admin_body_class', array( self::class, 'body_class' ) );
		add_action( 'admin_notices', array( self::class, 'render_vocabulary_header' ), 20 );
	}

	public static function enqueue( string $hook_suffix ): void {
		if ( self::WORKSPACE_HOOK === $hook_suffix ) {
			wp_enqueue_style(
				'bdc-kb-visual-foundation',
				BDC_KB_URL . 'assets/css/visual-foundation.css',
				array( 'bdc-kb-history' ),
				BDC_KB_VERSION
			);
			return;
		}

		if ( self::TAXONOMY_HOOK !== $hook_suffix || null === self::current_vocabulary() ) {
			return;
		}

		wp_enqueue_style(
			'bdc-kb-visual-foundation',
			BDC_KB_URL . 'assets/css/visual-foundation.css',
			array(),
			BDC_KB_VERSION
		);
	}

	public static function body_class( string $classes ): string {
		if ( null === self::current_vocabulary() ) {
			return $classes;
		}

		return trim( $classes . ' bdc-kb-vocabulary-screen' );
	}

	public static function render_vocabulary_header(): void {
		$definition = self::current_vocabulary();
		if ( null === $definition ) {
			return;
		}

		echo '<div class="bdc-kb-vocabulary-hero">';
		echo '<div class="bdc-kb-vocabulary-hero__copy">';
		echo '<p class="bdc-kb-vocabulary-eyebrow">' . esc_html__( 'Knowledge Studio / Vocabulários', 'bdc-knowledge-base' ) . '</p>';
		echo '<h1>' . esc_html( (string) $definition['label'] ) . '</h1>';
		echo '<p>' . esc_html__( 'Gerencie termos canônicos usando a Taxonomy API nativa do WordPress. Alterações afetam somente este vocabulário.', 'bdc-knowledge-base' ) . '</p>';
		echo '</div>';
		$return_post_id = self::return_post_id();
		$return_url     = $return_post_id > 0
			? Admin_Page::workspace_url( $return_post_id, 'classification' )
			: admin_url( 'admin.php?page=' . Admin_Page::PAGE_SLUG );
		$return_label   = $return_post_id > 0 ? __( 'Voltar à Classificação', 'bdc-knowledge-base' ) : __( 'Base de Conhecimento', 'bdc-knowledge-base' );

		echo '<div class="bdc-kb-vocabulary-hero__actions"><a class="button bdc-kb-button-with-icon" href="' . esc_url( $return_url ) . '"><span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span><span>' . esc_html( $return_label ) . '</span></a></div>';
		echo '</div>';
	}

	private static function return_post_id(): int {
		if ( ! isset( $_GET['bdc_return_post_id'] ) || ! is_scalar( $_GET['bdc_return_post_id'] ) ) {
			return 0;
		}

		$post_id = absint( wp_unslash( (string) $_GET['bdc_return_post_id'] ) );
		if ( $post_id <= 0 || ! current_user_can( 'edit_post', $post_id ) ) {
			return 0;
		}

		$post = get_post( $post_id );
		if ( ! is_object( $post ) || Classification_Contract::POST_TYPE !== (string) $post->post_type ) {
			return 0;
		}

		return $post_id;
	}

	/**
	 * @return array<string,mixed>|null
	 */
	private static function current_vocabulary(): ?array {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return null;
		}

		$screen = get_current_screen();
		if ( ! is_object( $screen ) || 'edit-tags' !== (string) ( $screen->base ?? '' ) ) {
			return null;
		}

		$taxonomy = (string) ( $screen->taxonomy ?? '' );
		if ( '' === $taxonomy ) {
			return null;
		}

		foreach ( Classification_Contract::fields() as $definition ) {
			if ( $taxonomy === (string) $definition['taxonomy'] ) {
				return $definition;
			}
		}

		return null;
	}
}
