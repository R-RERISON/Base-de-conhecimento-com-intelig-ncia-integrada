<?php
/**
 * BDC public Home search-first preview template.
 *
 * @package BDC_Knowledge_Base
 */

use BDC\KnowledgeBase\Public_Experience;
use BDC\KnowledgeBase\Public_Home_Read_Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$category_id = isset( $_GET['bdc_category'] ) && is_scalar( $_GET['bdc_category'] ) ? absint( wp_unslash( (string) $_GET['bdc_category'] ) ) : 0;
$query_value = isset( $_GET['bdc_global_q'] ) && is_scalar( $_GET['bdc_global_q'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['bdc_global_q'] ) ) : '';
$categories = Public_Home_Read_Model::categories();
$latest = Public_Home_Read_Model::latest( $category_id, 5 );
$popular = Public_Home_Read_Model::popular( $category_id, 5 );
$search = Public_Home_Read_Model::preview_search( $query_value );
$cloud = Public_Home_Read_Model::preview_word_cloud();
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
	<?php Public_Experience::render_preview_banner( 'UX-004 / Word Cloud BDC v1.1' ); ?>

	<main class="bdc-public-main bdc-home">
		<section class="bdc-home-search-stage" aria-labelledby="bdc-home-title">
			<p class="bdc-home-overline">BASE DE CONHECIMENTO</p>
			<h1 id="bdc-home-title">O que você precisa encontrar?</h1>
			<p class="bdc-home-lead">Pesquise procedimentos, sistemas, erros e orientações operacionais.</p>

			<form class="bdc-home-search" method="get" role="search" data-bdc-live-search-form data-bdc-search-context="home">
				<input type="hidden" name="bdc_kb_preview" value="home">
				<input type="hidden" name="bdc_kb_preview_nonce" value="<?php echo esc_attr( wp_create_nonce( 'bdc_kb_public_preview_home' ) ); ?>">
				<?php if ( $category_id > 0 ) : ?><input type="hidden" name="bdc_category" value="<?php echo esc_attr( (string) $category_id ); ?>"><?php endif; ?>
				<span class="dashicons dashicons-search" aria-hidden="true"></span>
				<label class="screen-reader-text" for="bdc-home-query">Buscar na Base de Conhecimento</label>
				<input id="bdc-home-query" data-bdc-primary-search data-bdc-live-search-input type="search" name="bdc_global_q" value="<?php echo esc_attr( $query_value ); ?>" placeholder="Ex.: Windows, certificado, mensageria, acesso..." autocomplete="off">
				<kbd>Ctrl K</kbd>
				<button type="submit" aria-label="Pesquisar"><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></button>
			</form>

			<div class="bdc-home-live-panel" data-bdc-live-search-panel<?php echo is_array( $search ) ? '' : ' hidden'; ?>>
				<div class="bdc-home-search-results-head"><strong data-bdc-live-search-title><?php echo is_array( $search ) ? esc_html( (string) ( $search['count'] ?? 0 ) ) . ' resultado(s) para “' . esc_html( $query_value ) . '”' : 'Resultados'; ?></strong><a href="<?php echo esc_url( Public_Experience::home_preview_url( $category_id ) ); ?>" data-bdc-live-search-clear>Limpar</a></div>
				<div data-bdc-live-search-results aria-live="polite" aria-busy="false">
					<?php if ( is_array( $search ) ) : ?><?php Public_Experience::render_search_results( $search, 'bdc-search-results bdc-search-results--home' ); ?><?php endif; ?>
				</div>
			</div>
			<?php if ( ! is_array( $search ) && ! empty( $cloud ) ) : ?>
				<div class="bdc-home-suggestions" aria-label="Assuntos sugeridos">
					<span>Assuntos em destaque</span>
					<?php foreach ( array_slice( $cloud, 0, 12 ) as $term ) : ?>
						<a data-bdc-consult-term="<?php echo esc_attr( (string) $term['term'] ); ?>" data-bdc-consult-source="topic_click" href="<?php echo esc_url( Public_Experience::home_preview_url( 0, (string) $term['term'] ) ); ?>"><span><?php echo esc_html( (string) $term['term'] ); ?></span><b aria-label="<?php echo esc_attr( (string) absint( $term['consultation_count'] ?? 0 ) . ' consultas' ); ?>"><?php echo esc_html( (string) absint( $term['consultation_count'] ?? 0 ) ); ?></b></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>

		<details class="bdc-home-explore"<?php echo $category_id > 0 ? ' open' : ''; ?>>
			<summary><span class="dashicons dashicons-grid-view" aria-hidden="true"></span><span>Explorar a Base</span><small>Categorias, recentes e populares</small><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></summary>
			<div class="bdc-home-explore__body">
				<nav class="bdc-home-categories" aria-label="Categorias da Base de Conhecimento">
					<a class="<?php echo 0 === $category_id ? 'is-active' : ''; ?>" href="<?php echo esc_url( Public_Experience::home_preview_url() ); ?>">Todos</a>
					<?php foreach ( $categories as $category ) : ?>
						<a class="<?php echo (int) $category['id'] === $category_id ? 'is-active' : ''; ?>" href="<?php echo esc_url( Public_Experience::home_preview_url( (int) $category['id'] ) ); ?>"><?php echo esc_html( (string) $category['name'] ); ?></a>
					<?php endforeach; ?>
				</nav>

				<div class="bdc-home-explore-grid">
					<section><div class="bdc-home-explore-title"><span>Recentes</span><h2>Últimas atualizações</h2></div><div class="bdc-home-compact-list">
						<?php foreach ( $latest as $row ) : ?><a href="<?php echo esc_url( Public_Experience::article_preview_url( (int) $row['post_id'] ) ); ?>"><span><strong><?php echo esc_html( (string) $row['title'] ); ?></strong><small><?php echo esc_html( (string) $row['category'] ); ?> · <?php echo esc_html( (string) $row['date'] ); ?></small></span><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></a><?php endforeach; ?>
					</div></section>
					<section><div class="bdc-home-explore-title"><span>Referências</span><h2>Instruções populares</h2></div><div class="bdc-home-compact-list bdc-home-compact-list--numbered">
						<?php $position = 0; foreach ( $popular as $row ) : ++$position; ?><a href="<?php echo esc_url( Public_Experience::article_preview_url( (int) $row['post_id'] ) ); ?>"><b><?php echo esc_html( str_pad( (string) $position, 2, '0', STR_PAD_LEFT ) ); ?></b><span><strong><?php echo esc_html( (string) $row['title'] ); ?></strong><small><?php echo esc_html( (string) $row['category'] ); ?> · <?php echo esc_html( (string) $row['date'] ); ?></small></span><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></a><?php endforeach; ?>
					</div></section>
				</div>
			</div>
		</details>
	</main>
</div>
<?php wp_footer(); ?>
</body>
</html>