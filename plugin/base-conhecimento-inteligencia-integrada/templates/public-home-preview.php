<?php
/**
 * BDC public Home preview template.
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
$latest = Public_Home_Read_Model::latest( $category_id, 4 );
$popular = Public_Home_Read_Model::popular( $category_id, 4 );
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
	<?php Public_Experience::render_preview_banner( 'UX-004 / H-020 preview' ); ?>

	<main class="bdc-public-main">
		<section class="bdc-home-hero" aria-labelledby="bdc-home-title">
			<p class="bdc-public-eyebrow">PORTAL DE CONHECIMENTO</p>
			<h1 id="bdc-home-title">Base de Conhecimento</h1>
			<p>Encontre rapidamente instruções, procedimentos internos e materiais de apoio para atendimento.</p>

			<form class="bdc-home-search" method="get" role="search">
				<input type="hidden" name="bdc_kb_preview" value="home">
				<input type="hidden" name="bdc_kb_preview_nonce" value="<?php echo esc_attr( wp_create_nonce( 'bdc_kb_public_preview_home' ) ); ?>">
				<?php if ( $category_id > 0 ) : ?>
					<input type="hidden" name="bdc_category" value="<?php echo esc_attr( (string) $category_id ); ?>">
				<?php endif; ?>
				<label for="bdc-home-query">O que você precisa resolver?</label>
				<div class="bdc-home-search__row">
					<input id="bdc-home-query" type="search" name="bdc_q" value="<?php echo esc_attr( $query_value ); ?>" placeholder="Pesquise por produto, erro, procedimento ou serviço">
					<button type="submit">Buscar</button>
				</div>
			</form>

			<?php if ( is_array( $search ) ) : ?>
				<div class="bdc-home-search-results" aria-live="polite">
					<div class="bdc-home-section-heading">
						<div><h2>Resultados da busca</h2><p><?php echo esc_html( (string) ( $search['count'] ?? 0 ) ); ?> resultado(s) — <?php echo esc_html( (string) ( $search['retrieval_mode'] ?? 'none' ) ); ?></p></div>
					</div>
					<?php if ( empty( $search['results'] ) ) : ?>
						<div class="bdc-public-empty">Nenhuma correspondência encontrada.</div>
					<?php else : ?>
						<div class="bdc-home-search-results__grid">
						<?php foreach ( (array) $search['results'] as $result ) : ?>
							<a class="bdc-home-search-result" href="<?php echo esc_url( (string) ( $result['official_url'] ?? '' ) ); ?>">
								<span class="bdc-home-search-result__rank">#<?php echo esc_html( (string) ( $result['rank'] ?? '' ) ); ?></span>
								<strong><?php echo esc_html( (string) ( $result['title'] ?? '' ) ); ?></strong>
							</a>
						<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="bdc-home-cloud" aria-labelledby="bdc-cloud-title">
				<div class="bdc-home-cloud__heading">
					<div><h2 id="bdc-cloud-title">Nuvem de conhecimento</h2><p>Prévia BDC content-only. Quality/telemetry/vocabulary do ASI permanecem pendentes antes do cutover.</p></div>
					<span class="bdc-public-badge">PREVIEW</span>
				</div>
				<div class="bdc-home-cloud__terms">
					<?php foreach ( $cloud as $term ) : ?>
						<a href="<?php echo esc_url( Public_Experience::home_preview_url( $category_id, (string) $term['term'] ) ); ?>" style="--bdc-cloud-weight: <?php echo esc_attr( (string) min( 6, max( 1, (int) $term['count'] ) ) ); ?>;">
							<?php echo esc_html( (string) $term['term'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<nav class="bdc-home-categories" aria-label="Categorias da Base de Conhecimento">
			<a class="<?php echo 0 === $category_id ? 'is-active' : ''; ?>" href="<?php echo esc_url( Public_Experience::home_preview_url() ); ?>">Todos</a>
			<?php foreach ( $categories as $category ) : ?>
				<a class="<?php echo (int) $category['id'] === $category_id ? 'is-active' : ''; ?>" href="<?php echo esc_url( Public_Experience::home_preview_url( (int) $category['id'] ) ); ?>"><?php echo esc_html( (string) $category['name'] ); ?></a>
			<?php endforeach; ?>
		</nav>

		<div class="bdc-home-columns">
			<section>
				<div class="bdc-home-section-heading"><div><h2>Últimas Atualizações</h2><p>Publicações recentes da base.</p></div></div>
				<div class="bdc-home-list">
					<?php foreach ( $latest as $row ) : ?>
						<a class="bdc-home-card" href="<?php echo esc_url( (string) $row['url'] ); ?>">
							<strong><?php echo esc_html( (string) $row['title'] ); ?></strong>
							<span><em><?php echo esc_html( (string) $row['category'] ); ?></em><?php echo esc_html( (string) $row['date'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>

			<section>
				<div class="bdc-home-section-heading"><div><h2>Instruções Populares</h2><p>Compatibilidade inicial baseada no sinal legado de popularidade.</p></div></div>
				<ol class="bdc-home-popular">
					<?php $position = 0; foreach ( $popular as $row ) : ++$position; ?>
						<li><a href="<?php echo esc_url( (string) $row['url'] ); ?>"><span class="bdc-home-popular__rank"><?php echo esc_html( (string) $position ); ?></span><span><strong><?php echo esc_html( (string) $row['title'] ); ?></strong><small><?php echo esc_html( (string) $row['category'] ); ?> · <?php echo esc_html( (string) $row['date'] ); ?></small></span></a></li>
					<?php endforeach; ?>
				</ol>
			</section>
		</div>
	</main>
</div>
<?php wp_footer(); ?>
</body>
</html>