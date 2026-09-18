<?php
/**
 * Superfície administrativa da Base de Conhecimento.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lista posts editáveis e compõe o Knowledge Workspace canônico.
 */
final class Admin_Page {

	public const PAGE_SLUG = 'bdc-knowledge-summary';
	public const ACTION    = 'bdc_kb_save_summary';

	private const NONCE_FIELD  = 'bdc_kb_nonce';
	private const NONCE_PREFIX = 'bdc_kb_save_summary_';
	private const PER_PAGE     = 20;
	private const DEFAULT_TAB  = 'overview';

	public static function register_menu(): void {
		add_menu_page(
			'Base de Conhecimento',
			'Base de Conhecimento',
			'edit_posts',
			self::PAGE_SLUG,
			array( self::class, 'render' ),
			'dashicons-welcome-learn-more',
			58
		);
	}

	public static function enqueue_assets( string $hook_suffix ): void {
		if ( 'toplevel_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'bdc-kb-admin',
			BDC_KB_URL . 'assets/css/admin.css',
			array(),
			BDC_KB_VERSION
		);

		wp_enqueue_style(
			'bdc-kb-workspace',
			BDC_KB_URL . 'assets/css/workspace.css',
			array( 'bdc-kb-admin' ),
			BDC_KB_VERSION
		);

		wp_enqueue_style(
			'bdc-kb-history',
			BDC_KB_URL . 'assets/css/history.css',
			array( 'bdc-kb-workspace' ),
			BDC_KB_VERSION
		);

		wp_enqueue_script(
			'bdc-kb-workspace',
			BDC_KB_URL . 'assets/js/workspace.js',
			array(),
			BDC_KB_VERSION,
			true
		);
	}

	public static function render(): void {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'Você não tem permissão para acessar esta página.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		$post_id = self::get_request_post_id();

		echo '<div class="wrap bdc-kb-admin">';
		if ( $post_id > 0 ) {
			self::render_workspace( $post_id );
		} else {
			self::render_list_header();
			self::render_feedback();
			Classification_Admin::render_feedback();
			Review_Admin::render_feedback();
			self::render_list();
		}

		echo '</div>';
	}

	public static function handle_save(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( esc_html__( 'Método HTTP não permitido.', 'bdc-knowledge-base' ), '', array( 'response' => 405 ) );
		}

		$post_id = isset( $_POST['post_id'] ) && is_scalar( $_POST['post_id'] )
			? absint( wp_unslash( (string) $_POST['post_id'] ) )
			: 0;

		$post = $post_id > 0 ? get_post( $post_id ) : null;
		if ( ! is_object( $post ) || Meta_Contract::POST_TYPE !== $post->post_type ) {
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

		if ( ! isset( $_POST['summary'] ) || ! is_array( $_POST['summary'] ) ) {
			self::redirect( $post_id, 'invalid_payload' );
		}

		$changes = wp_unslash( $_POST['summary'] );
		$result  = Summary_Store::update( $post_id, $changes );

		if ( ! is_wp_error( $result ) ) {
			self::redirect( $post_id, 'saved' );
		}

		$data   = $result->get_error_data();
		$status = is_array( $data ) ? (string) ( $data['status'] ?? '' ) : '';

		if ( Summary_Store::STATUS_PARTIAL_FAILURE_CRITICAL === $status ) {
			self::redirect( $post_id, 'critical' );
		}

		if ( Summary_Store::STATUS_FAIL_SAFE === $status ) {
			self::redirect( $post_id, 'fail_safe' );
		}

		self::redirect( $post_id, 'validation_error' );
	}

	private static function render_workspace( int $post_id ): void {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) || Meta_Contract::POST_TYPE !== $post->post_type ) {
			self::render_inline_error( 'O artigo informado não existe ou não pertence ao escopo da Base de Conhecimento.' );
			self::render_back_link();
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			self::render_inline_error( 'Você não tem permissão para editar este artigo.' );
			self::render_back_link();
			return;
		}

		$summary = Summary_Store::read( $post_id );
		if ( is_wp_error( $summary ) ) {
			self::render_inline_error( 'Não foi possível carregar o contexto deste artigo.' );
			self::render_back_link();
			return;
		}

		$context = Post_Management_Context::build( $post_id );
		if ( is_wp_error( $context ) ) {
			self::render_inline_error( 'Não foi possível carregar as informações deste artigo.' );
			self::render_back_link();
			return;
		}

		$tab = self::get_request_tab();

		self::render_context_header( $post, $summary );
		self::render_feedback();
		Classification_Admin::render_feedback();
		Review_Admin::render_feedback();
		self::render_tabs( $post_id, $tab );

		$layout_class = 'bdc-kb-workspace-layout' . ( 'overview' === $tab ? ' bdc-kb-workspace-layout--full' : '' );
		echo '<div class="' . esc_attr( $layout_class ) . '">';
		echo '<main class="bdc-kb-workspace-main" id="bdc-kb-workspace-main">';
		switch ( $tab ) {
			case 'content':
			case 'intelligence':
			case 'core_blocks':
				Post_Management_Activities::render( $tab, $post_id, $context );
				break;
			case 'summary':
				self::render_summary_panel( $post_id, $summary );
				break;
			case 'classification':
				Classification_Admin::render_panel( $post_id );
				break;
			case 'review':
				Review_Admin::render_panel( $post_id );
				break;
			case 'history':
				Review_Admin::render_history_panel( $post_id );
				break;
			case 'overview':
			default:
				self::render_overview( $post_id, $post, $summary, $context );
				break;
		}
		echo '</main>';
		if ( 'overview' !== $tab ) {
			self::render_context_sidebar( $post_id, $post, $summary, $context );
		}
		echo '</div>';
	}

	/** @param object $post WP_Post-like object. @param array<string,mixed> $summary */
	private static function render_context_header( object $post, array $summary ): void {
		$status_object = get_post_status_object( (string) $post->post_status );
		$status_label  = is_object( $status_object ) ? (string) $status_object->label : (string) $post->post_status;
		$edit_url      = get_edit_post_link( (int) $post->ID, 'raw' );

		echo '<header class="bdc-kb-context bdc-kb-workspace-header">';
		echo '<div class="bdc-kb-hero-copy">';
		echo '<p class="bdc-kb-eyebrow">' . esc_html__( 'Base de Conhecimento / Artigo', 'bdc-knowledge-base' ) . '</p>';
		echo '<h1>' . esc_html( (string) $summary['title'] ) . '</h1>';
		echo '<div class="bdc-kb-context-meta">';
		echo '<span><strong>' . esc_html__( 'ID:', 'bdc-knowledge-base' ) . '</strong> ' . esc_html( (string) $post->ID ) . '</span>';
		echo '<span><strong>' . esc_html__( 'Status:', 'bdc-knowledge-base' ) . '</strong> ' . esc_html( $status_label ) . '</span>';
		echo '</div>';
		echo '</div>';
		echo '<div class="bdc-kb-hero-actions">';
		echo '<a class="button bdc-kb-button-with-icon" href="' . esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ) . '"><span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span><span>' . esc_html__( 'Artigos', 'bdc-knowledge-base' ) . '</span></a>';
		if ( is_string( $edit_url ) && '' !== $edit_url ) {
			echo '<a class="button button-primary bdc-kb-button-with-icon" href="' . esc_url( $edit_url ) . '"><span class="dashicons dashicons-edit" aria-hidden="true"></span><span>' . esc_html__( 'Abrir no WordPress', 'bdc-knowledge-base' ) . '</span></a>';
		}
		echo '</div>';
		echo '</header>';
	}

	/** @param object $post WP_Post-like object. @param array<string,mixed> $summary @param array<string,mixed> $context */
	private static function render_context_sidebar( int $post_id, object $post, array $summary, array $context ): void {
		$status_object = get_post_status_object( (string) $post->post_status );
		$status_label  = is_object( $status_object ) ? (string) $status_object->label : (string) $post->post_status;
		$filled        = self::summary_filled_count( $summary );
		$term_count    = self::classification_term_count( $post_id );
		$review        = Review_Store::read( $post_id );
		$review_state  = is_wp_error( $review ) ? Review_Contract::STATE_UNREVIEWED : (string) ( $review['state'] ?? Review_Contract::STATE_UNREVIEWED );
		$review_label  = Review_Contract::states()[ $review_state ] ?? $review_state;

		echo '<aside class="bdc-kb-context-panel" aria-label="' . esc_attr__( 'Contexto do artigo', 'bdc-knowledge-base' ) . '">';
		echo '<div class="bdc-kb-domain-heading"><h3>' . esc_html__( 'Contexto do artigo', 'bdc-knowledge-base' ) . '</h3><p>' . esc_html__( 'Informação editorial e governança em leitura.', 'bdc-knowledge-base' ) . '</p></div>';
		echo '<dl class="bdc-kb-context-list">';
		echo '<div><dt>' . esc_html__( 'Status editorial', 'bdc-knowledge-base' ) . '</dt><dd><span class="bdc-kb-badge bdc-kb-badge--success">' . esc_html( $status_label ) . '</span></dd></div>';
		$source_context = is_array( $context['source'] ?? null ) ? $context['source'] : array();
		echo '<div><dt>' . esc_html__( 'Fonte editorial', 'bdc-knowledge-base' ) . '</dt><dd><strong>' . esc_html( (string) ( $source_context['label'] ?? 'Indisponível' ) ) . '</strong></dd></div>';
		echo '<div><dt>' . esc_html__( 'Sumário', 'bdc-knowledge-base' ) . '</dt><dd>' . esc_html( $filled . '/' . count( Meta_Contract::fields() ) . ' campos preenchidos' ) . '</dd></div>';
		echo '<div><dt>' . esc_html__( 'Classificação', 'bdc-knowledge-base' ) . '</dt><dd>' . esc_html( $term_count > 0 ? $term_count . ' conceito(s)' : 'Sem termos canônicos' ) . '</dd></div>';
		echo '<div><dt>' . esc_html__( 'Revisão', 'bdc-knowledge-base' ) . '</dt><dd><span class="bdc-kb-state-badge bdc-kb-state-' . esc_attr( $review_state ) . '">' . esc_html( $review_label ) . '</span></dd></div>';
		echo '<div><dt>' . esc_html__( 'Atualizado', 'bdc-knowledge-base' ) . '</dt><dd>' . esc_html( get_the_modified_date( '', $post ) ) . '</dd></div>';
		echo '</dl>';
		echo '<p class="bdc-kb-context-note">' . esc_html__( 'Esta área reúne as informações de conhecimento e governança do artigo. A edição do conteúdo permanece no WordPress.', 'bdc-knowledge-base' ) . '</p>';
		echo '</aside>';
	}

	private static function render_tabs( int $post_id, string $active_tab ): void {
		$tabs = Post_Activity_Registry::definitions();

		echo '<nav class="bdc-kb-tabs" aria-label="' . esc_attr__( 'Áreas do artigo', 'bdc-knowledge-base' ) . '" data-bdc-workspace-tabs>';
		foreach ( $tabs as $tab => $definition ) {
			$url = self::workspace_url( $post_id, $tab );
			$class = 'bdc-kb-tab' . ( $tab === $active_tab ? ' is-active' : '' );
			$current = $tab === $active_tab ? ' aria-current="page"' : '';
			echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '"' . $current . '><span class="dashicons dashicons-' . esc_attr( (string) $definition['icon'] ) . '" aria-hidden="true"></span><span>' . esc_html( (string) $definition['label'] ) . '</span></a>';
		}
		echo '</nav>';
	}

	private static function render_overview( int $post_id, object $post, array $summary, array $context ): void {
		$review = Review_Store::read( $post_id );
		if ( is_wp_error( $review ) ) {
			self::render_inline_error( 'Não foi possível carregar o estado de revisão e governança.' );
			return;
		}

		$status_object = get_post_status_object( (string) $post->post_status );
		$status_label  = is_object( $status_object ) ? (string) $status_object->label : (string) $post->post_status;
		$filled        = self::summary_filled_count( $summary );
		$total_fields  = count( Meta_Contract::fields() );
		$term_count    = self::classification_term_count( $post_id );
		$review_state  = (string) ( $review['state'] ?? Review_Contract::STATE_UNREVIEWED );
		$review_label  = Review_Contract::states()[ $review_state ] ?? $review_state;
		$source        = is_array( $context['source'] ?? null ) ? $context['source'] : array();
		$core          = is_array( $context['core_blocks'] ?? null ) ? $context['core_blocks'] : array();

		echo '<section class="bdc-kb-overview" aria-labelledby="bdc-kb-overview-title">';
		echo '<div class="bdc-kb-domain-heading">';
		echo '<h3 id="bdc-kb-overview-title">' . esc_html__( 'Visão geral', 'bdc-knowledge-base' ) . '</h3>';
		echo '<p>' . esc_html__( 'Acompanhe a situação atual do artigo sem repetir a navegação disponível acima.', 'bdc-knowledge-base' ) . '</p>';
		echo '</div>';

		echo '<div class="bdc-kb-overview-status-grid">';
		self::render_overview_status( 'Situação editorial', $status_label, 'Estado atual do artigo no WordPress.', 'yes-alt' );
		self::render_overview_status( 'Conteúdo', (string) ( $source['label'] ?? 'Indisponível' ), 'Origem editorial identificada.', 'text-page' );
		self::render_overview_status( 'Sumário', $filled . ' de ' . $total_fields . ' campos preenchidos', 'Completude das informações resumidas.', 'media-text' );
		self::render_overview_status( 'Classificação', $term_count > 0 ? $term_count . ' conceito(s)' : 'Ainda não classificado', 'Organização por conceitos padronizados.', 'tag' );
		self::render_overview_status( 'Revisão', $review_label, 'Estado atual de revisão e governança.', 'yes' );
		self::render_overview_status( 'Blocos do WordPress', self::overview_core_status( (string) ( $core['operational_status'] ?? '' ) ), 'Situação da estrutura editorial.', 'block-default' );
		echo '</div>';
		echo '</section>';
	}

	private static function render_overview_status( string $label, string $value, string $description, string $icon ): void {
		echo '<article class="bdc-kb-overview-status">';
		echo '<div class="bdc-kb-card-icon" aria-hidden="true"><span class="dashicons dashicons-' . esc_attr( $icon ) . '"></span></div>';
		echo '<span class="bdc-kb-overview-status__label">' . esc_html( $label ) . '</span>';
		echo '<strong class="bdc-kb-overview-status__value">' . esc_html( $value ) . '</strong>';
		echo '<p>' . esc_html( $description ) . '</p>';
		echo '</article>';
	}

	private static function overview_core_status( string $status ): string {
		$labels = array(
			'no_action_required'      => 'Atualizado',
			'ready_for_authorization' => 'Preparação disponível',
			'human_review_required'   => 'Revisão manual necessária',
			'blocked'                 => 'Ação indisponível',
		);
		return $labels[ $status ] ?? 'Em avaliação';
	}

	/** @param array<string,mixed> $snapshot */
	private static function render_summary_panel( int $post_id, array $snapshot ): void {
		echo '<section class="bdc-kb-domain-panel" aria-labelledby="bdc-kb-summary-title">';
		echo '<div class="bdc-kb-domain-heading">';
		echo '<h3 id="bdc-kb-summary-title">' . esc_html__( 'Sumário', 'bdc-knowledge-base' ) . '</h3>';
		echo '<p>' . esc_html__( 'Informações resumidas que ajudam a compreender rapidamente o objetivo e os pontos essenciais do artigo.', 'bdc-knowledge-base' ) . '</p>';
		echo '</div>';

		echo '<form class="bdc-kb-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		echo '<input type="hidden" name="post_id" value="' . esc_attr( (string) $post_id ) . '">';
		wp_nonce_field( self::NONCE_PREFIX . $post_id, self::NONCE_FIELD );

		foreach ( Meta_Contract::fields() as $field => $definition ) {
			$field_id = 'bdc-kb-' . $field;
			echo '<div class="bdc-kb-field">';
			echo '<label for="' . esc_attr( $field_id ) . '"><strong>' . esc_html( $definition['label'] ) . '</strong></label>';
			echo '<textarea class="large-text" rows="5" id="' . esc_attr( $field_id ) . '" name="summary[' . esc_attr( $field ) . ']" maxlength="32768">' . esc_textarea( (string) $snapshot[ $field ] ) . '</textarea>';
			echo '<p class="description">' . esc_html__( 'Preencha com texto objetivo. Deixe em branco para remover o conteúdo deste campo.', 'bdc-knowledge-base' ) . '</p>';
			echo '</div>';
		}

		submit_button( __( 'Salvar sumário', 'bdc-knowledge-base' ) );
		echo '</form>';
		echo '</section>';
	}

	private static function render_list_header(): void {
		echo '<header class="bdc-kb-list-hero">';
		echo '<div class="bdc-kb-hero-copy">';
		echo '<p class="bdc-kb-eyebrow">' . esc_html__( 'Base de Conhecimento', 'bdc-knowledge-base' ) . '</p>';
		echo '<h1>' . esc_html__( 'Artigos da Base de Conhecimento', 'bdc-knowledge-base' ) . '</h1>';
		echo '<p>' . esc_html__( 'Localize artigos e acompanhe conteúdo, sumário, classificação, inteligência e governança em um único lugar.', 'bdc-knowledge-base' ) . '</p>';
		echo '</div>';
		echo '<div class="bdc-kb-hero-actions"><a class="button button-primary bdc-kb-button-with-icon" href="' . esc_url( admin_url( 'post-new.php' ) ) . '"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span><span>' . esc_html__( 'Abrir novo artigo no WordPress', 'bdc-knowledge-base' ) . '</span></a></div>';
		echo '</header>';
	}

	private static function render_list(): void {
		$paged = isset( $_GET['paged'] ) && is_scalar( $_GET['paged'] )
			? max( 1, absint( wp_unslash( (string) $_GET['paged'] ) ) )
			: 1;
		$search = isset( $_GET['s'] ) && is_scalar( $_GET['s'] )
			? sanitize_text_field( wp_unslash( (string) $_GET['s'] ) )
			: '';

		$args = array(
			'post_type'           => Meta_Contract::POST_TYPE,
			'post_status'         => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page'      => self::PER_PAGE,
			'paged'               => $paged,
			'orderby'             => 'modified',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'perm'                => 'editable',
		);
		if ( '' !== $search ) {
			$args['s'] = $search;
		}
		$query = new \WP_Query( $args );

		echo '<form class="bdc-kb-toolbar" method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '">';
		echo '<input type="hidden" name="page" value="' . esc_attr( self::PAGE_SLUG ) . '">';
		echo '<label class="bdc-kb-search"><span class="screen-reader-text">' . esc_html__( 'Pesquisar artigos', 'bdc-knowledge-base' ) . '</span><input type="search" name="s" value="' . esc_attr( $search ) . '" placeholder="' . esc_attr__( 'Pesquisar título ou conteúdo…', 'bdc-knowledge-base' ) . '"></label>';
		echo '<button class="button bdc-kb-button-with-icon" type="submit"><span class="dashicons dashicons-search" aria-hidden="true"></span><span>' . esc_html__( 'Pesquisar', 'bdc-knowledge-base' ) . '</span></button>';
		echo '</form>';

		$counts = wp_count_posts( Meta_Contract::POST_TYPE );
		$total  = 0;
		foreach ( array( 'publish', 'draft', 'pending', 'private', 'future' ) as $status ) {
			$total += isset( $counts->{$status} ) ? (int) $counts->{$status} : 0;
		}
		echo '<div class="bdc-kb-metrics" aria-label="' . esc_attr__( 'Resumo da Base de Conhecimento', 'bdc-knowledge-base' ) . '">';
		self::render_metric( (string) $total, 'artigos no escopo editorial' );
		self::render_metric( (string) count( Meta_Contract::fields() ), 'campos do sumário' );
		self::render_metric( (string) count( Classification_Contract::fields() ), 'conceitos de classificação' );
		echo '</div>';

		echo '<table class="widefat fixed bdc-kb-table">';
		echo '<thead><tr><th>' . esc_html__( 'Artigo', 'bdc-knowledge-base' ) . '</th><th>' . esc_html__( 'Sumário', 'bdc-knowledge-base' ) . '</th><th>' . esc_html__( 'Classificação', 'bdc-knowledge-base' ) . '</th><th>' . esc_html__( 'Atualizado', 'bdc-knowledge-base' ) . '</th><th>' . esc_html__( 'Ações', 'bdc-knowledge-base' ) . '</th></tr></thead><tbody>';

		$rendered = 0;
		foreach ( $query->posts as $post ) {
			if ( ! current_user_can( 'edit_post', (int) $post->ID ) ) {
				continue;
			}
			$status_object  = get_post_status_object( (string) $post->post_status );
			$status_label   = is_object( $status_object ) ? (string) $status_object->label : (string) $post->post_status;
			$workspace_url  = self::workspace_url( (int) $post->ID, self::DEFAULT_TAB );
			$summary        = Summary_Store::read( (int) $post->ID );
			$filled         = is_wp_error( $summary ) ? 0 : self::summary_filled_count( $summary );
			$summary_total  = count( Meta_Contract::fields() );
			$term_count     = self::classification_term_count( (int) $post->ID );
			$summary_class  = $filled === $summary_total ? 'bdc-kb-badge--success' : ( $filled > 0 ? 'bdc-kb-badge--info' : 'bdc-kb-badge--warning' );
			$summary_label  = $filled === $summary_total ? 'Completo' : ( $filled > 0 ? $filled . '/' . $summary_total . ' preenchidos' : 'Pendente' );
			$class_label    = $term_count > 0 ? $term_count . ' conceito(s)' : 'Sem termos';

			echo '<tr>';
			echo '<td><a class="bdc-kb-article-link" href="' . esc_url( $workspace_url ) . '">' . esc_html( get_the_title( $post ) ) . '</a><span class="bdc-kb-row-meta">#' . esc_html( (string) $post->ID ) . ' · ' . esc_html( $status_label ) . '</span></td>';
			echo '<td><span class="bdc-kb-badge ' . esc_attr( $summary_class ) . '">' . esc_html( $summary_label ) . '</span></td>';
			echo '<td><span class="bdc-kb-badge' . ( $term_count > 0 ? ' bdc-kb-badge--info' : '' ) . '">' . esc_html( $class_label ) . '</span></td>';
			echo '<td>' . esc_html( get_the_modified_date( '', $post ) ) . '</td>';
			echo '<td><a class="button bdc-kb-button-with-icon" href="' . esc_url( $workspace_url ) . '"><span class="dashicons dashicons-edit-page" aria-hidden="true"></span><span>' . esc_html__( 'Gerenciar', 'bdc-knowledge-base' ) . '</span></a></td>';
			echo '</tr>';
			++$rendered;
		}

		if ( 0 === $rendered ) {
			echo '<tr><td colspan="5"><div class="bdc-kb-empty-state"><div><strong>' . esc_html__( 'Nenhum artigo encontrado', 'bdc-knowledge-base' ) . '</strong><p>' . esc_html__( 'Ajuste a pesquisa ou verifique suas permissões de edição.', 'bdc-knowledge-base' ) . '</p></div></div></td></tr>';
		}
		echo '</tbody></table>';

		$base_args = array( 'page' => self::PAGE_SLUG, 'paged' => '%#%' );
		if ( '' !== $search ) {
			$base_args['s'] = $search;
		}
		$pagination = paginate_links(
			array(
				'base'      => add_query_arg( $base_args, admin_url( 'admin.php' ) ),
				'format'    => '',
				'current'   => $paged,
				'total'     => max( 1, (int) $query->max_num_pages ),
				'type'      => 'list',
				'prev_text' => __( 'Anterior', 'bdc-knowledge-base' ),
				'next_text' => __( 'Próxima', 'bdc-knowledge-base' ),
			)
		);
		if ( is_string( $pagination ) && '' !== $pagination ) {
			echo '<nav class="bdc-kb-pagination" aria-label="' . esc_attr__( 'Paginação de artigos', 'bdc-knowledge-base' ) . '">' . wp_kses_post( $pagination ) . '</nav>';
		}
		wp_reset_postdata();
	}

	private static function render_metric( string $value, string $label ): void {
		echo '<div class="bdc-kb-metric"><strong>' . esc_html( $value ) . '</strong><span>' . esc_html( $label ) . '</span></div>';
	}

	/** @param array<string,mixed> $summary */
	private static function summary_filled_count( array $summary ): int {
		$filled = 0;
		foreach ( Meta_Contract::fields() as $field => $definition ) {
			unset( $definition );
			if ( isset( $summary[ $field ] ) && '' !== trim( (string) $summary[ $field ] ) ) {
				++$filled;
			}
		}
		return $filled;
	}

	private static function classification_term_count( int $post_id ): int {
		$count = 0;
		foreach ( Classification_Contract::fields() as $definition ) {
			$terms = wp_get_object_terms( $post_id, (string) $definition['taxonomy'], array( 'fields' => 'ids' ) );
			if ( ! is_wp_error( $terms ) ) {
				$count += count( $terms );
			}
		}
		return $count;
	}

	private static function render_feedback(): void {
		$status = isset( $_GET['bdc_summary_status'] ) && is_scalar( $_GET['bdc_summary_status'] )
			? sanitize_key( wp_unslash( (string) $_GET['bdc_summary_status'] ) )
			: '';

		$messages = array(
			'saved'            => array( 'success', 'Sumário salvo e confirmado.' ),
			'fail_safe'        => array( 'error', 'Não foi possível concluir a gravação. As informações anteriores foram preservadas.' ),
			'critical'         => array( 'error', 'Não foi possível restaurar integralmente as informações anteriores. Evite novas alterações e solicite uma verificação técnica.' ),
			'invalid_post'     => array( 'error', 'Artigo inválido ou fora do escopo da Base de Conhecimento.' ),
			'forbidden'        => array( 'error', 'Você não tem permissão para editar o artigo solicitado.' ),
			'invalid_nonce'    => array( 'error', 'A validação de segurança expirou ou é inválida. Reabra o formulário e tente novamente.' ),
			'invalid_payload'  => array( 'error', 'O formulário recebido é inválido.' ),
			'validation_error' => array( 'error', 'O sumário não foi salvo porque os dados informados são inválidos.' ),
		);

		if ( ! isset( $messages[ $status ] ) ) {
			return;
		}

		list( $type, $message ) = $messages[ $status ];
		echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
	}

	private static function render_inline_error( string $message ): void {
		echo '<div class="notice notice-error"><p>' . esc_html( $message ) . '</p></div>';
	}

	private static function render_back_link(): void {
		$url = admin_url( 'admin.php?page=' . self::PAGE_SLUG );
		echo '<p class="bdc-kb-back"><a href="' . esc_url( $url ) . '">&larr; ' . esc_html__( 'Voltar para artigos', 'bdc-knowledge-base' ) . '</a></p>';
	}

	private static function get_request_post_id(): int {
		if ( ! isset( $_GET['post_id'] ) || ! is_scalar( $_GET['post_id'] ) ) {
			return 0;
		}

		return absint( wp_unslash( (string) $_GET['post_id'] ) );
	}

	private static function get_request_tab(): string {
		if ( isset( $_GET['tab'] ) && is_scalar( $_GET['tab'] ) ) {
			return Post_Activity_Registry::normalize( wp_unslash( (string) $_GET['tab'] ) );
		}

		if ( isset( $_GET['bdc_summary_status'] ) ) {
			return 'summary';
		}
		if ( isset( $_GET['bdc_classification_status'] ) ) {
			return 'classification';
		}
		if ( isset( $_GET['bdc_review_status'] ) ) {
			return 'review';
		}

		return self::DEFAULT_TAB;
	}

	public static function workspace_url( int $post_id, string $tab = self::DEFAULT_TAB ): string {
		return add_query_arg(
			array(
				'page'    => self::PAGE_SLUG,
				'post_id' => $post_id,
				'tab'     => sanitize_key( $tab ),
			),
			admin_url( 'admin.php' )
		);
	}

	private static function redirect( int $post_id, string $status ): never {
		$args = array(
			'page'               => self::PAGE_SLUG,
			'bdc_summary_status' => sanitize_key( $status ),
			'tab'                => 'summary',
		);

		if ( $post_id > 0 ) {
			$args['post_id'] = $post_id;
		}

		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}
}
