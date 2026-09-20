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
		echo '<p>' . esc_html__( 'Esta superfície não altera a Home nem os artigos públicos. Ela serve para validar o novo frontend BDC antes do cutover.', 'bdc-knowledge-base' ) . '</p>';

		echo '<div class="bdc-kb-overview-grid">';
		echo '<section class="bdc-kb-overview-card">';
		echo '<span class="bdc-kb-card-icon"><span class="dashicons dashicons-admin-home" aria-hidden="true"></span></span>';
		echo '<h4>' . esc_html__( 'Home BDC', 'bdc-knowledge-base' ) . '</h4>';
		echo '<p>' . esc_html__( 'Prévia do shell plugin-owned com Header, Search lexical, categorias, Últimas, Populares e Word Cloud provisória.', 'bdc-knowledge-base' ) . '</p>';
		echo '<a class="button button-primary bdc-kb-button-with-icon" target="_blank" rel="noopener" href="' . esc_url( self::home_preview_url() ) . '"><span class="dashicons dashicons-visibility" aria-hidden="true"></span><span>' . esc_html__( 'Abrir prévia da Home', 'bdc-knowledge-base' ) . '</span></a>';
		echo '</section>';

		echo '<section class="bdc-kb-overview-card">';
		echo '<span class="bdc-kb-card-icon"><span class="dashicons dashicons-media-document" aria-hidden="true"></span></span>';
		echo '<h4>' . esc_html__( 'Article Reader BDC', 'bdc-knowledge-base' ) . '</h4>';
		echo '<p>' . esc_html__( 'Amostras representativas do corpus. GAC e WP Unified Indexer permanecem no pipeline; Tips/Rail GRE são substituídos somente nesta prévia.', 'bdc-knowledge-base' ) . '</p>';
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

		// In preview only, replace GRE Tips/Rail with BDC equivalents.
		remove_filter(
			'the_content',
			array( 'BDC\\ExecutiveSummary\\Helpful_Tips_Renderer', 'prepend_to_content' ),
			15
		);
		remove_filter(
			'the_content',
			array( 'BDC\\ExecutiveSummary\\Frontend_Renderer', 'append_side_panel' ),
			30
		);
		remove_action(
			'wp_footer',
			array( 'BDC\\ExecutiveSummary\\Frontend_Renderer', 'render_side_panel' )
		);
	}

	public static function enqueue_assets(): void {
		$kind = self::preview_kind();
		if ( null === $kind ) {
			return;
		}

		wp_enqueue_style(
			'bdc-kb-public-foundation',
			BDC_KB_URL . 'assets/css/public-foundation.css',
			array(),
			BDC_KB_VERSION
		);
		wp_enqueue_style(
			'bdc-kb-public-header',
			BDC_KB_URL . 'assets/css/public-header.css',
			array( 'bdc-kb-public-foundation' ),
			BDC_KB_VERSION
		);

		if ( 'home' === $kind ) {
			wp_enqueue_style(
				'bdc-kb-public-home',
				BDC_KB_URL . 'assets/css/public-home.css',
				array( 'bdc-kb-public-header' ),
				BDC_KB_VERSION
			);
		} else {
			wp_enqueue_style(
				'bdc-kb-public-article',
				BDC_KB_URL . 'assets/css/public-article.css',
				array( 'bdc-kb-public-header' ),
				BDC_KB_VERSION
			);
		}
	}

	/**
	 * @param array<int,string> $classes
	 * @return array<int,string>
	 */
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
		$user = wp_get_current_user();
		$display = $user instanceof \WP_User && $user->exists() ? (string) $user->display_name : __( 'Visitante', 'bdc-knowledge-base' );
		$initials = self::initials( $display );

		$items = apply_filters(
			'bdc_kb_public_nav_items',
			array(
				array( 'label' => 'Página Inicial', 'url' => home_url( '/' ) ),
				array( 'label' => 'Consulta Avançada', 'url' => self::resolve_page_url( 'Consulta Avançada' ) ),
				array( 'label' => 'Telefones', 'url' => self::resolve_page_url( 'Telefones' ) ),
				array( 'label' => 'Links Úteis', 'url' => self::resolve_page_url( 'Links Úteis' ) ),
				array( 'label' => 'POSTI', 'url' => self::resolve_page_url( 'POSTI' ) ),
			)
		);

		echo '<header class="bdc-public-header"><div class="bdc-public-header__inner">';
		echo '<a class="bdc-public-brand" href="' . esc_url( home_url( '/' ) ) . '"><span class="bdc-public-brand__mark" aria-hidden="true">BDC</span><span><strong>Base de Conhecimento</strong><small>Central de apoio operacional</small></span></a>';
		echo '<nav class="bdc-public-nav" aria-label="' . esc_attr__( 'Navegação principal da Base de Conhecimento', 'bdc-knowledge-base' ) . '">';
		foreach ( (array) $items as $item ) {
			$label = is_array( $item ) ? (string) ( $item['label'] ?? '' ) : '';
			$url = is_array( $item ) ? (string) ( $item['url'] ?? '' ) : '';
			if ( '' === $label ) {
				continue;
			}
			if ( '' === $url ) {
				echo '<span class="bdc-public-nav__item is-unavailable" aria-disabled="true">' . esc_html( $label ) . '</span>';
				continue;
			}
			echo '<a class="bdc-public-nav__item" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
		}
		echo '</nav>';
		echo '<div class="bdc-public-profile"><span class="bdc-public-profile__avatar">' . esc_html( $initials ) . '</span><span class="bdc-public-profile__name">' . esc_html( $display ) . '</span></div>';
		echo '</div></header>';
	}

	public static function render_preview_banner( string $label ): void {
		echo '<div class="bdc-public-preview-banner" role="status"><strong>' . esc_html__( 'Prévia administrativa', 'bdc-knowledge-base' ) . '</strong><span>' . esc_html( $label ) . ' — ' . esc_html__( 'nenhuma rota pública foi substituída.', 'bdc-knowledge-base' ) . '</span></div>';
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

	private static function resolve_page_url( string $title ): string {
		$pages = get_posts(
			array(
				'post_type' => 'page',
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'title' => $title,
				'fields' => 'ids',
				'no_found_rows' => true,
				'suppress_filters' => false,
			)
		);
		if ( empty( $pages ) ) {
			return '';
		}
		$url = get_permalink( (int) $pages[0] );
		return is_string( $url ) ? $url : '';
	}

	private static function initials( string $name ): string {
		$parts = preg_split( '/\s+/u', trim( $name ), -1, PREG_SPLIT_NO_EMPTY );
		if ( empty( $parts ) ) {
			return 'U';
		}

		$char = static function ( string $value ): string {
			return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, 1 ) : substr( $value, 0, 1 );
		};
		$upper = static function ( string $value ): string {
			return function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $value ) : strtoupper( $value );
		};

		$first = $char( (string) $parts[0] );
		$last = count( $parts ) > 1 ? $char( (string) $parts[ count( $parts ) - 1 ] ) : '';
		return $upper( $first . $last );
	}
}