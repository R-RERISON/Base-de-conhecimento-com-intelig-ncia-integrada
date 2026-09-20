<?php
/**
 * BDC public Home redesign preview template.
 *
 * @package BDC_Knowledge_Base
 */

use BDC\KnowledgeBase\Public_Experience;
use BDC\KnowledgeBase\Public_Home_Read_Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$category_id = isset( $_GET['bdc_category'] ) && is_scalar( $_GET['bdc_category'] )
	? absint( wp_unslash( (string) $_GET['bdc_category'] ) )
	: 0;
$query_value = isset( $_GET['bdc_q'] ) && is_scalar( $_GET['bdc_q'] )
	? sanitize_text_field( wp_unslash( (string) $_GET['bdc_q'] ) )
	: '';

$categories = Public_Home_Read_Model::categories();
$latest = Public_Home_Read_Model::latest( $category_id, 5 );
$popular = Public_Home_Read_Model::popular( $category_id, 5 );
$search = Public_Home_Read_Model::preview_search( $query_value );
$cloud = Public_Home_Read_Model::preview_word_cloud();
$published_count = Public_Home_Read_Model::published_count();
$latest_date = ! empty( $latest ) ? (string) ( $latest[0]['date'] ?? '' ) : '';

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="bdc-public">
	<?php Public_Experience::render_header(); ?>
	<?php Public_Experience::render_preview_banner( 'UX-004 / redesign v2' ); ?>

	<main class="bdc-public-main bdc-home">
		<section class="bdc-home-command" aria-labelledby="bdc-home-title">
			<div class="bdc-home-command__copy">
				<span class="bdc-home-command__badge"><span class="dashicons dashicons-welcome-learn-more" aria-hidden="true"></span> Conhecimento operacional</span>
				<h1 id="bdc-home-title">Encontre a orientação certa,<br><span>sem perder tempo.</span></h1>
				<p>Acesse procedimentos, instruções e referências da Base de Conhecimento em uma experiência única e pesquisável.</p>
				<div class="bdc-home-metrics" aria-label="Resumo da Base de Conhecimento">
					<div><strong><?php echo esc_html( number_format_i18n( $published_count ) ); ?></strong><span>artigos publicados</span></div>
					<div><strong><?php echo esc_html( (string) count( $categories ) ); ?></strong><span>áreas principais</span></div>
					<?php if ( '' !== $latest_date ) : ?><div><strong><?php echo esc_html( $latest_date ); ?></strong><span>última publicação</span></div><?php endif; ?>
				</div>
			</div>

			<div class="bdc-home-search-card">
				<div class="bdc-home-search-card__heading">
					<span class="dashicons dashicons-search" aria-hidden="true"></span>
					<div><strong>O que você precisa resolver?</strong><small>Pesquise por sistema, erro, procedimento, serviço ou palavra-chave.</small></div>
				</div>
				<form class="bdc-home-search" method="get" role="search">
					<input type="hidden" name="bdc_kb_preview" value="home">
					<input type="hidden" name="bdc_kb_preview_nonce" value="<?php echo esc_attr( wp_create_nonce( 'bdc_kb_public_preview_home' ) ); ?>">
					<?php if ( $category_id > 0 ) : ?><input type="hidden" name="bdc_category" value="<?php echo esc_attr( (string) $category_id ); ?>"><?php endif; ?>
					<label class="screen-reader-text" for="bdc-home-query">Pesquisar na Base de Conhecimento</label>
					<div class="bdc-home-search__row">
						<input id="bdc-home-query" type="search" name="bdc_q" value="<?php echo esc_attr( $query_value ); ?>" placeholder="Ex.: acesso, Windows, certificado, mensageria..." autocomplete="off">
						<button type="submit"><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span><span>Pesquisar</span></button>
					</div>
				</form>
				<div class="bdc-home-search-card__hint"><span class="dashicons dashicons-lightbulb" aria-hidden="true"></span><span>Use termos curtos e objetivos. O ranking lexical prioriza correspondência no conteúdo da Base.</span></div>
			</div>
		</section>

		<?php if ( is_array( $search ) ) : ?>
			<section class="bdc-home-results" aria-live="polite" aria-labelledby="bdc-home-results-title">
				<div class="bdc-home-section-heading">
					<div><span class="bdc-home-section-kicker">BUSCA</span><h2 id="bdc-home-results-title">Resultados para “<?php echo esc_html( $query_value ); ?>”</h2><p><?php echo esc_html( (string) ( $search['count'] ?? 0 ) ); ?> resultado(s)</p></div>
					<a href="<?php echo esc_url( Public_Experience::home_preview_url( $category_id ) ); ?>">Limpar busca</a>
				</div>
				<?php if ( empty( $search['results'] ) ) : ?>
					<div class="bdc-public-empty">Nenhuma correspondência encontrada. Tente outro termo ou navegue pelas categorias.</div>
				<?php else : ?>
					<div class="bdc-home-results__grid">
					<?php foreach ( (array) $search['results'] as $result ) : ?>
						<a class="bdc-home-result" href="<?php echo esc_url( (string) ( $result['official_url'] ?? '' ) ); ?>">
							<span class="bdc-home-result__rank"><?php echo esc_html( (string) ( $result['rank'] ?? '' ) ); ?></span>
							<span><strong><?php echo esc_html( (string) ( $result['title'] ?? '' ) ); ?></strong><small>Abrir artigo</small></span>
							<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
						</a>
					<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<section class="bdc-home-topics" aria-labelledby="bdc-home-topics-title">
			<div class="bdc-home-section-heading">
				<div><span class="bdc-home-section-kicker">DESCOBERTA</span><h2 id="bdc-home-topics-title">Assuntos em destaque</h2><p>Termos frequentes para começar uma pesquisa rapidamente.</p></div>
				<span class="bdc-public-badge">preview</span>
			</div>
			<div class="bdc-home-topic-list">
				<?php foreach ( array_slice( $cloud, 0, 14 ) as $term ) : ?>
					<a href="<?php echo esc_url( Public_Experience::home_preview_url( $category_id, (string) $term['term'] ) ); ?>"><span>#</span><?php echo esc_html( (string) $term['term'] ); ?></a>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="bdc-home-explore" aria-labelledby="bdc-home-explore-title">
			<div class="bdc-home-section-heading">
				<div><span class="bdc-home-section-kicker">NAVEGAÇÃO</span><h2 id="bdc-home-explore-title">Explore por categoria</h2><p>Filtre o conteúdo por área de atendimento.</p></div>
				<?php if ( $category_id > 0 ) : ?><a href="<?php echo esc_url( Public_Experience::home_preview_url() ); ?>">Ver todas</a><?php endif; ?>
			</div>
			<nav class="bdc-home-categories" aria-label="Categorias da Base de Conhecimento">
				<a class="bdc-home-category <?php echo 0 === $category_id ? 'is-active' : ''; ?>" href="<?php echo esc_url( Public_Experience::home_preview_url() ); ?>"><span class="bdc-home-category__icon"><span class="dashicons dashicons-grid-view" aria-hidden="true"></span></span><span><strong>Todos</strong><small>Visão geral</small></span></a>
				<?php foreach ( $categories as $category ) : ?>
					<a class="bdc-home-category <?php echo (int) $category['id'] === $category_id ? 'is-active' : ''; ?>" href="<?php echo esc_url( Public_Experience::home_preview_url( (int) $category['id'] ) ); ?>">
						<span class="bdc-home-category__icon"><span class="dashicons <?php echo esc_attr( sanitize_html_class( (string) $category['icon'] ) ); ?>" aria-hidden="true"></span></span>
						<span><strong><?php echo esc_html( (string) $category['name'] ); ?></strong><small>Explorar artigos</small></span>
					</a>
				<?php endforeach; ?>
			</nav>
		</section>

		<div class="bdc-home-content-grid">
			<section class="bdc-home-feed" aria-labelledby="bdc-home-latest-title">
				<div class="bdc-home-section-heading"><div><span class="bdc-home-section-kicker">RECENTES</span><h2 id="bdc-home-latest-title">Últimas atualizações</h2><p>Conteúdo publicado recentemente na Base.</p></div></div>
				<div class="bdc-home-feed__list">
					<?php foreach ( $latest as $row ) : ?>
						<a class="bdc-home-feed-card" href="<?php echo esc_url( (string) $row['url'] ); ?>">
							<div class="bdc-home-feed-card__main"><span class="bdc-home-feed-card__category"><?php echo esc_html( (string) $row['category'] ); ?></span><strong><?php echo esc_html( (string) $row['title'] ); ?></strong><small>Atualizado em <?php echo esc_html( (string) $row['date'] ); ?></small></div>
							<span class="bdc-home-feed-card__action"><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>

			<aside class="bdc-home-popular-panel" aria-labelledby="bdc-home-popular-title">
				<div class="bdc-home-section-heading"><div><span class="bdc-home-section-kicker">MAIS ACESSADOS</span><h2 id="bdc-home-popular-title">Instruções populares</h2><p>Referências recorrentes da Base.</p></div></div>
				<ol class="bdc-home-popular">
					<?php $position = 0; foreach ( $popular as $row ) : ++$position; ?>
						<li><a href="<?php echo esc_url( (string) $row['url'] ); ?>"><span class="bdc-home-popular__rank"><?php echo esc_html( str_pad( (string) $position, 2, '0', STR_PAD_LEFT ) ); ?></span><span class="bdc-home-popular__copy"><strong><?php echo esc_html( (string) $row['title'] ); ?></strong><small><?php echo esc_html( (string) $row['category'] ); ?> · <?php echo esc_html( (string) $row['date'] ); ?></small></span><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></a></li>
					<?php endforeach; ?>
				</ol>
			</aside>
		</div>
	</main>
</div>
<?php wp_footer(); ?>
</body>
</html>