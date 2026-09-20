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
	private const GLOBAL_QUERY_KEY = 'bdc_global_q';

	/** @var array<int,int> */
	private const ARTICLE_SAMPLES = array( 359, 367, 385, 515, 358, 36431, 45782 );

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_admin_page' ), 55 );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_admin_assets' ), 60 );
		add_filter( 'template_include', array( self::class, 'template_include' ), 99 );
		add_action( 'template_redirect', array( self::class, 'prepare_preview_request' ), 1 );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_assets' ), 40 );
		add_filter( 'body_class', array( self::class, 'body_class' ) );
		add_action( 'wp_ajax_bdc_kb_public_search_preview', array( self::class, 'ajax_search_preview' ) );
	}

	public static function register_admin_page(): void {
		add_submenu_page( Admin_Page::PAGE_SLUG, 'Prévia da Experiência Pública', 'Prévia Pública', 'manage_options', self::PAGE_SLUG, array( self::class, 'render_admin_page' ) );
	}

	public static function enqueue_admin_assets( string $hook_suffix ): void {
		if ( 'base-de-conhecimento_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}
		wp_enqueue_style( 'bdc-kb-public-preview-admin', BDC_KB_URL . 'assets/css/visual-foundation.css', array(), BDC_KB_VERSION );
		wp_enqueue_style( 'dashicons' );
	}

	public static function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}
		echo '<div class="wrap bdc-kb-admin">';
		echo '<h1>' . esc_html__( 'Prévia da Experiência Pública', 'bdc-knowledge-base' ) . '</h1>';
		echo '<p>' . esc_html__( 'Exploração visual em homologação. A Home e os artigos públicos atuais permanecem intactos até aceite explícito.', 'bdc-knowledge-base' ) . '</p>';
		echo '<div class="bdc-kb-overview-grid">';
		echo '<section class="bdc-kb-overview-card"><span class="bdc-kb-card-icon"><span class="dashicons dashicons-search" aria-hidden="true"></span></span><h4>Home Search-first</h4><p>Home minimalista, busca como ação principal e navegação secundária sob demanda.</p><a class="button button-primary bdc-kb-button-with-icon" target="_blank" rel="noopener" href="' . esc_url( self::home_preview_url() ) . '"><span class="dashicons dashicons-visibility" aria-hidden="true"></span><span>Abrir Home</span></a></section>';
		echo '<section class="bdc-kb-overview-card"><span class="bdc-kb-card-icon"><span class="dashicons dashicons-media-document" aria-hidden="true"></span></span><h4>Article Reader</h4><p>Leitura limpa com busca persistente, Dicas úteis, Resumo Executivo e pipeline legado preservado.</p><div class="bdc-kb-vocabulary-actions">';
		foreach ( self::ARTICLE_SAMPLES as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) || 'post' !== (string) ( $post->post_type ?? '' ) || 'publish' !== (string) ( $post->post_status ?? '' ) ) { continue; }
			echo '<a class="bdc-kb-vocabulary-action" target="_blank" rel="noopener" href="' . esc_url( self::article_preview_url( $post_id ) ) . '"><span class="dashicons dashicons-external" aria-hidden="true"></span><span>#' . esc_html( (string) $post_id ) . ' — ' . esc_html( wp_trim_words( (string) $post->post_title, 7 ) ) . '</span></a>';
		}
		echo '</div></section></div>';
		echo '<div class="notice notice-info"><p><strong>Sem cutover:</strong> page_on_front, post_content, _elementor_data, ASI, GRE, GAC, WPUI e Astra permanecem inalterados.</p></div></div>';
	}

	public static function home_preview_url( int $category_id = 0, string $query = '' ): string {
		$front_id = absint( get_option( 'page_on_front', 0 ) );
		$url = $front_id > 0 ? get_permalink( $front_id ) : home_url( '/' );
		if ( ! is_string( $url ) || '' === $url ) { $url = home_url( '/' ); }
		$args = array( self::QUERY_KEY => 'home', self::NONCE_KEY => wp_create_nonce( 'bdc_kb_public_preview_home' ) );
		if ( $category_id > 0 ) { $args['bdc_category'] = $category_id; }
		if ( '' !== trim( $query ) ) { $args[ self::GLOBAL_QUERY_KEY ] = trim( $query ); }
		return add_query_arg( $args, $url );
	}

	public static function article_preview_url( int $post_id, string $query = '' ): string {
		$url = get_permalink( $post_id );
		if ( ! is_string( $url ) || '' === $url ) { $url = home_url( '/' ); }
		$args = array( self::QUERY_KEY => 'article', self::NONCE_KEY => wp_create_nonce( 'bdc_kb_public_preview_article' ) );
		if ( '' !== trim( $query ) ) { $args[ self::GLOBAL_QUERY_KEY ] = trim( $query ); }
		return add_query_arg( $args, $url );
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
		if ( null === $kind ) { return; }
		nocache_headers();
		if ( 'article' !== $kind ) { return; }
		remove_filter( 'the_content', array( 'BDC\\ExecutiveSummary\\Helpful_Tips_Renderer', 'prepend_to_content' ), 15 );
		remove_filter( 'the_content', array( 'BDC\\ExecutiveSummary\\Frontend_Renderer', 'append_side_panel' ), 30 );
		remove_action( 'wp_footer', array( 'BDC\\ExecutiveSummary\\Frontend_Renderer', 'render_side_panel' ) );
	}

	public static function enqueue_assets(): void {
		$kind = self::preview_kind();
		if ( null === $kind ) { return; }
		Public_Auth_Bridge::prime();
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'bdc-kb-public-foundation', BDC_KB_URL . 'assets/css/public-foundation.css', array(), BDC_KB_VERSION );
		wp_enqueue_style( 'bdc-kb-public-header', BDC_KB_URL . 'assets/css/public-header.css', array( 'bdc-kb-public-foundation' ), BDC_KB_VERSION );
		wp_enqueue_style( 'bdc-kb-public-' . $kind, BDC_KB_URL . 'assets/css/public-' . $kind . '.css', array( 'bdc-kb-public-header' ), BDC_KB_VERSION );
		wp_enqueue_script( 'bdc-kb-public-search', BDC_KB_URL . 'assets/js/public-search.js', array(), BDC_KB_VERSION, true );
		wp_localize_script(
			'bdc-kb-public-search',
			'BDC_KB_PUBLIC_SEARCH',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'bdc_kb_public_search_preview' ),
				'minChars' => 2,
				'debounceMs' => 180,
			)
		);
	}

	/** @param array<int,string> $classes @return array<int,string> */
	public static function body_class( array $classes ): array {
		$kind = self::preview_kind();
		if ( null === $kind ) { return $classes; }
		$classes[] = 'bdc-public-preview';
		$classes[] = 'bdc-public-preview--' . $kind;
		return $classes;
	}

	public static function render_header(): void {
		$kind = self::preview_kind() ?? 'home';
		$links = array(
			array( 'label' => 'Página Inicial', 'url' => self::home_preview_url(), 'icon' => 'dashicons-admin-home', 'external' => false ),
			array( 'label' => 'Consulta Avançada', 'url' => home_url( '/consulta-avancada/' ), 'icon' => 'dashicons-media-document', 'external' => false ),
			array( 'label' => 'Telefones', 'url' => home_url( '/telefones-importantes/' ), 'icon' => 'dashicons-phone', 'external' => false ),
			array( 'label' => 'Links Úteis', 'url' => home_url( '/links-uteis/' ), 'icon' => 'dashicons-admin-links', 'external' => false ),
			array( 'label' => 'POSTI', 'url' => 'https://vok-smb2.cloud-p.bcnet.bcb.gov.br/app/manual/posti/publico', 'icon' => 'dashicons-external', 'external' => true ),
		);

		echo '<header class="bdc-public-header" data-bdc-header><div class="bdc-public-header__inner">';
		echo '<a class="bdc-public-brand" href="' . esc_url( self::home_preview_url() ) . '" aria-label="Página inicial da Base de Conhecimento">';
		self::render_brand_visual();
		echo '<span class="bdc-public-brand__copy"><strong>Base de Conhecimento</strong><small>Central de apoio operacional</small></span></a>';

		echo '<nav class="bdc-public-quicknav" aria-label="Links rápidos"><span class="bdc-public-quicknav__label">Links rápidos</span><div class="bdc-public-quicknav__links">';
		foreach ( $links as $item ) {
			$external = ! empty( $item['external'] );
			echo '<a class="bdc-public-quicknav__link' . ( $external ? ' is-external' : '' ) . '" href="' . esc_url( (string) $item['url'] ) . '"' . ( $external ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>';
			echo '<span class="dashicons ' . esc_attr( (string) $item['icon'] ) . '" aria-hidden="true"></span><span>' . esc_html( (string) $item['label'] ) . '</span></a>';
		}
		echo '</div></nav>';

		echo '<div class="bdc-public-header__profile">';
		Public_Auth_Bridge::render();
		echo '</div>';
		echo '<button class="bdc-public-header__toggle" type="button" data-bdc-header-toggle aria-label="Abrir links rápidos" aria-expanded="false"><span></span><span></span><span></span></button>';
		echo '</div>';

		if ( 'article' === $kind ) {
			echo '<div class="bdc-public-header__searchrow"><div class="bdc-public-header__searchinner">';
			self::render_global_search( true );
			echo '</div></div>';
			self::render_global_search_results();
		}
		echo '</header>';
	}

	public static function render_global_search( bool $compact = false ): void {
		$query = self::global_query();
		$kind = self::preview_kind() ?? 'home';
		$post_id = 'article' === $kind ? (int) get_queried_object_id() : 0;
		$action = 'article' === $kind && $post_id > 0 ? get_permalink( $post_id ) : get_permalink( absint( get_option( 'page_on_front', 0 ) ) );
		if ( ! is_string( $action ) || '' === $action ) { $action = home_url( '/' ); }
		echo '<form class="bdc-global-search' . ( $compact ? ' bdc-global-search--compact' : '' ) . '" method="get" action="' . esc_url( $action ) . '" role="search" data-bdc-live-search-form data-bdc-search-context="article">';
		echo '<input type="hidden" name="' . esc_attr( self::QUERY_KEY ) . '" value="' . esc_attr( $kind ) . '">';
		echo '<input type="hidden" name="' . esc_attr( self::NONCE_KEY ) . '" value="' . esc_attr( wp_create_nonce( 'bdc_kb_public_preview_' . $kind ) ) . '">';
		echo '<span class="dashicons dashicons-search" aria-hidden="true"></span>';
		echo '<label class="screen-reader-text" for="bdc-global-search-input">Buscar na Base de Conhecimento</label>';
		echo '<input id="bdc-global-search-input" data-bdc-global-search data-bdc-live-search-input type="search" name="' . esc_attr( self::GLOBAL_QUERY_KEY ) . '" value="' . esc_attr( $query ) . '" placeholder="Buscar na Base de Conhecimento">';
		echo '<kbd>Ctrl K</kbd><button type="submit" aria-label="Pesquisar"><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></button></form>';
	}

	public static function global_search_results(): ?array {
		$query = self::global_query();
		return '' === $query ? null : Public_Home_Read_Model::preview_search( $query );
	}

	public static function render_search_results( ?array $search, string $class = 'bdc-search-results' ): void {
		if ( ! is_array( $search ) ) { return; }
		$results = (array) ( $search['results'] ?? array() );
		echo '<div class="' . esc_attr( $class ) . '" aria-live="polite">';
		if ( empty( $results ) ) {
			echo '<div class="bdc-search-empty">Nenhum resultado encontrado.</div></div>';
			return;
		}
		foreach ( $results as $result ) {
			$post_id = (int) ( $result['post_id'] ?? 0 );
			if ( $post_id <= 0 ) { continue; }
			echo '<a class="bdc-search-result" href="' . esc_url( self::article_preview_url( $post_id ) ) . '"><span class="dashicons dashicons-media-document" aria-hidden="true"></span><span><strong>' . esc_html( (string) ( $result['title'] ?? '' ) ) . '</strong><small>Abrir no novo leitor</small></span><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></a>';
		}
		echo '</div>';
	}

	private static function render_global_search_results(): void {
		$search = self::global_search_results();
		$hidden = is_array( $search ) ? '' : ' hidden';
		echo '<div class="bdc-global-search-panel" data-bdc-live-search-panel' . $hidden . '><div class="bdc-global-search-panel__inner">';
		echo '<div class="bdc-global-search-panel__head"><strong data-bdc-live-search-title>Resultados</strong><a href="' . esc_url( self::article_preview_url( (int) get_queried_object_id() ) ) . '" data-bdc-live-search-clear>Limpar</a></div>';
		echo '<div data-bdc-live-search-results>';
		if ( is_array( $search ) ) {
			self::render_search_results( $search, 'bdc-search-results bdc-search-results--header' );
		}
		echo '</div></div></div>';
	}

	public static function ajax_search_preview(): void {
		check_ajax_referer( 'bdc_kb_public_search_preview', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permissão insuficiente.' ), 403 );
		}

		$query = isset( $_POST['query'] ) && is_scalar( $_POST['query'] )
			? sanitize_text_field( wp_unslash( (string) $_POST['query'] ) )
			: '';
		$query = trim( $query );
		$length = function_exists( 'mb_strlen' ) ? mb_strlen( $query ) : strlen( $query );
		if ( $length < 2 ) {
			wp_send_json_success( array( 'query' => $query, 'count' => 0, 'results' => array(), 'state' => 'min_chars' ) );
		}

		$search = Public_Home_Read_Model::preview_search( $query );
		$rows = array();
		foreach ( (array) ( is_array( $search ) ? ( $search['results'] ?? array() ) : array() ) as $result ) {
			$post_id = (int) ( $result['post_id'] ?? 0 );
			$post = $post_id > 0 ? get_post( $post_id ) : null;
			if ( ! is_object( $post ) || 'publish' !== (string) ( $post->post_status ?? '' ) ) {
				continue;
			}
			$categories = get_the_category( $post_id );
			$category = ! empty( $categories ) && is_object( $categories[0] ) ? (string) $categories[0]->name : '';
			$raw_excerpt = '' !== trim( (string) $post->post_excerpt ) ? (string) $post->post_excerpt : (string) $post->post_content;
			$rows[] = array(
				'postId' => $post_id,
				'title' => (string) $post->post_title,
				'category' => $category,
				'excerpt' => wp_trim_words( wp_strip_all_tags( strip_shortcodes( $raw_excerpt ) ), 22, '…' ),
				'url' => self::article_preview_url( $post_id ),
				'rank' => (int) ( $result['rank'] ?? 0 ),
			);
		}

		wp_send_json_success(
			array(
				'query' => $query,
				'count' => count( $rows ),
				'results' => $rows,
				'state' => empty( $rows ) ? 'empty' : 'ready',
			)
		);
	}

	public static function render_preview_banner( string $label ): void {
		echo '<div class="bdc-public-preview-banner" role="status"><strong>Prévia</strong><span>' . esc_html( $label ) . ' · sem cutover</span></div>';
	}

	private static function render_brand_visual(): void {
		$logo_id = absint( get_theme_mod( 'custom_logo', 0 ) );
		if ( $logo_id > 0 ) {
			$url = wp_get_attachment_image_url( $logo_id, 'thumbnail' );
			if ( is_string( $url ) && '' !== $url ) { echo '<span class="bdc-public-brand__logo"><img src="' . esc_url( $url ) . '" alt="" aria-hidden="true"></span>'; return; }
		}
		echo '<span class="bdc-public-brand__mark" aria-hidden="true">BDC</span>';
	}

	private static function global_query(): string {
		return isset( $_GET[ self::GLOBAL_QUERY_KEY ] ) && is_scalar( $_GET[ self::GLOBAL_QUERY_KEY ] ) ? sanitize_text_field( wp_unslash( (string) $_GET[ self::GLOBAL_QUERY_KEY ] ) ) : '';
	}

	private static function preview_kind(): ?string {
		if ( is_admin() || ! current_user_can( 'manage_options' ) ) { return null; }
		$kind = isset( $_GET[ self::QUERY_KEY ] ) && is_scalar( $_GET[ self::QUERY_KEY ] ) ? sanitize_key( wp_unslash( (string) $_GET[ self::QUERY_KEY ] ) ) : '';
		if ( ! in_array( $kind, array( 'home', 'article' ), true ) ) { return null; }
		$nonce = isset( $_GET[ self::NONCE_KEY ] ) && is_scalar( $_GET[ self::NONCE_KEY ] ) ? wp_unslash( (string) $_GET[ self::NONCE_KEY ] ) : '';
		return wp_verify_nonce( $nonce, 'bdc_kb_public_preview_' . $kind ) ? $kind : null;
	}
}
