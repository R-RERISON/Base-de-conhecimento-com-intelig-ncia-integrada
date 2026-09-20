<?php
/**
 * Non-disruptive public experience preview orchestration.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Public_Experience {

	public const PAGE_SLUG = 'bdc-kb-public-experience-preview';
	private const QUERY_KEY = 'bdc_kb_preview';
	private const NONCE_KEY = 'bdc_kb_preview_nonce';

	/** @var array<int,int> */
	private const ARTICLE_SAMPLES = array(
		359,
		367,
		385,
		515,
		358,
		36431,
		45782,
	);

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_admin_page' ), 55 );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_admin_assets' ), 60 );
		add_filter( 'template_include', array( self::class, 'template_include' ), 99 );
		add_action( 'template_redirect', array( self::class, 'prepare_preview_request' ), 1 );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_assets' ), 40 );
		add_filter( 'body_class', array( self::class, 'body_class' ) );
	}

	public static function register_admin_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Prévia da Experiência Pública',
			'Prévia Pública',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_admin_page' )
		);
	}

	public static function enqueue_admin_assets( string $hook_suffix ): void {
		if ( 'base-de-conhecimento_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}
		wp_enqueue_style(
			'bdc-kb-public-preview-admin',
			BDC_KB_URL . 'assets/css/visual-foundation.css',
			array(),
			BDC_KB_VERSION
		);
		wp_enqueue_style( 'dashicons' );
	}

	public static function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		echo '<div class="wrap bdc-kb-admin">';
		echo '<h1>' . esc_html__( 'Prévia da Experiência Pública', 'bdc-knowledge-base' ) . '</h1>';
		echo '<p>' . esc_html__( 'Redesign em homologação. A Home e os artigos públicos atuais permanecem intactos até aceite explícito.', 'bdc-knowledge-base' ) . '</p>';

		echo '<div class="bdc-kb-overview-grid">';
		echo '<section class="bdc-kb-overview-card">';
		echo '<span class="bdc-kb-card-icon"><span class="dashicons dashicons-admin-home" aria-hidden="true"></span></span>';
		echo '<h4>' . esc_html__( 'Home BDC — Redesign', 'bdc-knowledge-base' ) . '</h4>';
		echo '<p>' . esc_html__( 'Portal plugin-owned com busca dominante, categorias, assuntos em destaque, últimas, populares, navegação robusta e Auth Bridge.', 'bdc-knowledge-base' ) . '</p>';
		echo '<a class="button button-primary bdc-kb-button-with-icon" target="_blank" rel="noopener" href="' . esc_url( self::home_preview_url() ) . '"><span class="dashicons dashicons-visibility" aria-hidden="true"></span><span>' . esc_html__( 'Abrir nova Home', 'bdc-knowledge-base' ) . '</span></a>';
		echo '</section>';

		echo '<section class="bdc-kb-overview-card">';
		echo '<span class="bdc-kb-card-icon"><span class="dashicons dashicons-media-document" aria-hidden="true"></span></span>';
		echo '<h4>' . esc_html__( 'Article Reader — Redesign', 'bdc-knowledge-base' ) . '</h4>';
		echo '<p>' . esc_html__( 'Reader com chrome legado sanitizado, Dicas úteis responsivas, Summary Rail ampliado e pipeline GAC/WPUI preservado.', 'bdc-knowledge-base' ) . '</p>';
		echo '<div class="bdc-kb-vocabulary-actions">';
		foreach ( self::ARTICLE_SAMPLES as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) || 'post' !== (string) ( $post->post_type ?? '' ) || 'publish' !== (string) ( $post->post_status ?? '' ) ) {
				continue;
			}
			echo '<a class="bdc-kb-vocabulary-action" target="_blank" rel="noopener" href="' . esc_url( self::article_preview_url( $post_id ) ) . '"><span class="dashicons dashicons-external" aria-hidden="true"></span><span>#' . esc_html( (string) $post_id ) . ' — ' . esc_html( wp_trim_words( (string) $post->post_title, 7 ) ) . '</span></a>';
		}
		echo '</div></section></div>';

		echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'Sem cutover:', 'bdc-knowledge-base' ) . '</strong> ' . esc_html__( 'page_on_front, post_content, _elementor_data, ASI, GRE, GAC, WPUI e Astra permanecem inalterados.', 'bdc-knowledge-base' ) . '</p></div>';
		echo '</div>';
	}

	public static function home_preview_url( int $category_id = 0, string $query = '' ): string {
		$front_id = absint( get_option( 'page_on_front', 0 ) );
		$url = $front_id > 0 ? get_permalink( $front_id ) : home_url( '/' );
		if ( ! is_string( $url ) || '' === $url ) {
			$url = home_url( '/' );
		}

		$args = array(
			self::QUERY_KEY => 'home',
			self::NONCE_KEY => wp_create_nonce( 'bdc_kb_public_preview_home' ),
		);
		if ( $category_id > 0 ) {
			$args['bdc_category'] = $category_id;
		}
		if ( '' !== trim( $query ) ) {
			$args['bdc_q'] = trim( $query );
		}
		return add_query_arg( $args, $url );
	}

	public static function article_preview_url( int $post_id ): string {
		$url = get_permalink( $post_id );
		if ( ! is_string( $url ) || '' === $url ) {
			$url = home_url( '/' );
		}
		return add_query_arg(
			array(
				self::QUERY_KEY => 'article',
				self::NONCE_KEY => wp_create_nonce( 'bdc_kb_public_preview_article' ),
			),
			$url
		);
	}

	public static function template_include( string $template ): string {
		$kind = self::preview_kind();
		if ( 'home' === $kind ) {
			$candidate = BDC_KB_DIR . 'templates/public-home-preview.php';
			return is_file( $candidate ) ? $candidate : $template;
		}
		if ( 'article' === $kind && is_singular( 'post' ) ) {
			$candidate = BDC_KB_DIR . 'templates/public-article-preview.php';
			return is_file( $candidate ) ? $candidate : $template;
		}
		return $template;
	}

	public static function prepare_preview_request(): void {
		$kind = self::preview_kind();
		if ( null === $kind ) {
			return;
		}

		nocache_headers();
		if ( 'article' !== $kind ) {
			return;
		}

		// Preview-only replacement: prevent duplicate GRE visual blocks while keeping
		// the canonical content pipeline and other integrations intact.
		remove_filter( 'the_content', array( 'BDC\\ExecutiveSummary\\Helpful_Tips_Renderer', 'prepend_to_content' ), 15 );
		remove_filter( 'the_content', array( 'BDC\\ExecutiveSummary\\Frontend_Renderer', 'append_side_panel' ), 30 );
		remove_action( 'wp_footer', array( 'BDC\\ExecutiveSummary\\Frontend_Renderer', 'render_side_panel' ) );
	}

	public static function enqueue_assets(): void {
		$kind = self::preview_kind();
		if ( null === $kind ) {
			return;
		}

		Public_Auth_Bridge::prime();
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'bdc-kb-public-foundation', BDC_KB_URL . 'assets/css/public-foundation.css', array(), BDC_KB_VERSION );
		wp_enqueue_style( 'bdc-kb-public-header', BDC_KB_URL . 'assets/css/public-header.css', array( 'bdc-kb-public-foundation' ), BDC_KB_VERSION );

		if ( 'home' === $kind ) {
			wp_enqueue_style( 'bdc-kb-public-home', BDC_KB_URL . 'assets/css/public-home.css', array( 'bdc-kb-public-header' ), BDC_KB_VERSION );
		} else {
			wp_enqueue_style( 'bdc-kb-public-article', BDC_KB_URL . 'assets/css/public-article.css', array( 'bdc-kb-public-header' ), BDC_KB_VERSION );
		}
	}

	/** @param array<int,string> $classes @return array<int,string> */
	public static function body_class( array $classes ): array {
		$kind = self::preview_kind();
		if ( null === $kind ) {
			return $classes;
		}
		$classes[] = 'bdc-public-preview';
		$classes[] = 'bdc-public-preview--' . $kind;
		return $classes;
	}

	public static function render_header(): void {
		$items = Public_Navigation::items();

		echo '<header class="bdc-public-header"><div class="bdc-public-header__inner">';
		echo '<a class="bdc-public-brand" href="' . esc_url( home_url( '/' ) ) . '">';
		self::render_brand_visual();
		echo '<span class="bdc-public-brand__copy"><strong>' . esc_html__( 'Base de Conhecimento', 'bdc-knowledge-base' ) . '</strong><small>' . esc_html__( 'Central de apoio operacional', 'bdc-knowledge-base' ) . '</small></span>';
		echo '</a>';

		echo '<nav class="bdc-public-nav" aria-label="' . esc_attr__( 'Navegação principal da Base de Conhecimento', 'bdc-knowledge-base' ) . '">';
		foreach ( $items as $item ) {
			$label = (string) ( $item['label'] ?? '' );
			$url = (string) ( $item['url'] ?? '' );
			$icon = sanitize_html_class( (string) ( $item['icon'] ?? 'dashicons-admin-links' ) );
			if ( '' === $label ) {
				continue;
			}
			if ( '' === $url ) {
				echo '<span class="bdc-public-nav__item is-unavailable" aria-disabled="true"><span class="dashicons ' . esc_attr( $icon ) . '" aria-hidden="true"></span><span>' . esc_html( $label ) . '</span></span>';
				continue;
			}
			echo '<a class="bdc-public-nav__item" href="' . esc_url( $url ) . '"><span class="dashicons ' . esc_attr( $icon ) . '" aria-hidden="true"></span><span>' . esc_html( $label ) . '</span></a>';
		}
		echo '</nav>';

		Public_Auth_Bridge::render();
		echo '</div></header>';
	}

	public static function render_preview_banner( string $label ): void {
		echo '<div class="bdc-public-preview-banner" role="status"><span class="dashicons dashicons-hammer" aria-hidden="true"></span><strong>' . esc_html__( 'Prévia', 'bdc-knowledge-base' ) . '</strong><span>' . esc_html( $label ) . ' · ' . esc_html__( 'sem cutover', 'bdc-knowledge-base' ) . '</span></div>';
	}

	private static function render_brand_visual(): void {
		$logo_id = absint( get_theme_mod( 'custom_logo', 0 ) );
		if ( $logo_id > 0 ) {
			$url = wp_get_attachment_image_url( $logo_id, 'thumbnail' );
			if ( is_string( $url ) && '' !== $url ) {
				echo '<span class="bdc-public-brand__logo"><img src="' . esc_url( $url ) . '" alt="" aria-hidden="true"></span>';
				return;
			}
		}
		echo '<span class="bdc-public-brand__mark" aria-hidden="true">BDC</span>';
	}

	private static function preview_kind(): ?string {
		if ( is_admin() || ! current_user_can( 'manage_options' ) ) {
			return null;
		}
		$kind = isset( $_GET[ self::QUERY_KEY ] ) && is_scalar( $_GET[ self::QUERY_KEY ] )
			? sanitize_key( wp_unslash( (string) $_GET[ self::QUERY_KEY ] ) )
			: '';
		if ( ! in_array( $kind, array( 'home', 'article' ), true ) ) {
			return null;
		}
		$nonce = isset( $_GET[ self::NONCE_KEY ] ) && is_scalar( $_GET[ self::NONCE_KEY ] )
			? wp_unslash( (string) $_GET[ self::NONCE_KEY ] )
			: '';
		if ( ! wp_verify_nonce( $nonce, 'bdc_kb_public_preview_' . $kind ) ) {
			return null;
		}
		return $kind;
	}
}