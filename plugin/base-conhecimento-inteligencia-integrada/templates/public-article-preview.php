<?php
/**
 * BDC public Article Reader preview template.
 *
 * @package BDC_Knowledge_Base
 */

use BDC\KnowledgeBase\Public_Article_Read_Model;
use BDC\KnowledgeBase\Public_Experience;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = (int) get_queried_object_id();
$model = Public_Article_Read_Model::read( $post_id );
if ( is_wp_error( $model ) ) {
	wp_die( esc_html( $model->get_error_message() ), '', array( 'response' => 404 ) );
}

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'bdc-reader-source-' . sanitize_html_class( (string) $model['source_kind'] ) ); ?>>
<?php wp_body_open(); ?>
<div class="bdc-public">
	<?php Public_Experience::render_header(); ?>
	<?php Public_Experience::render_preview_banner( 'UX-005 / A-020 preview · source=' . (string) $model['source_kind'] ); ?>

	<main class="bdc-public-main bdc-reader">
		<header class="bdc-reader-hero">
			<div>
				<?php if ( '' !== (string) $model['category'] ) : ?><span class="bdc-reader-category"><?php echo esc_html( (string) $model['category'] ); ?></span><?php endif; ?>
				<h1><?php echo esc_html( (string) $model['title'] ); ?></h1>
				<div class="bdc-reader-meta">
					<span>Responsável <strong><?php echo esc_html( (string) $model['author'] ); ?></strong></span>
					<span>Publicado <strong><?php echo esc_html( (string) $model['published'] ); ?></strong></span>
					<span>Atualizado <strong><?php echo esc_html( (string) $model['updated'] ); ?></strong></span>
				</div>
			</div>
			<a class="bdc-reader-back" href="<?php echo esc_url( Public_Experience::home_preview_url() ); ?>">Voltar para a base</a>
		</header>

		<div class="bdc-reader-grid">
			<article class="bdc-reader-content">
				<?php if ( ! empty( $model['tips'] ) ) : ?>
					<section class="bdc-reader-tips" aria-labelledby="bdc-reader-tips-title">
						<div class="bdc-reader-section-heading">
							<h2 id="bdc-reader-tips-title">Dicas úteis</h2>
							<p>Orientações rápidas e pontos de atenção para agilizar o atendimento.</p>
						</div>
						<div class="bdc-reader-tips__grid">
							<?php foreach ( (array) $model['tips'] as $tip ) : ?>
								<div class="bdc-reader-tip">
									<h3><?php echo esc_html( (string) ( $tip['title'] ?? '' ) ); ?></h3>
									<p><?php echo nl2br( esc_html( (string) ( $tip['content'] ?? '' ) ) ); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<div class="bdc-reader-body">
					<?php
					while ( have_posts() ) {
						the_post();
						the_content();
					}
					?>
				</div>
			</article>

			<?php if ( ! empty( $model['summary_items'] ) ) : ?>
				<aside class="bdc-reader-summary" aria-labelledby="bdc-reader-summary-title">
					<header><span>BASE DE CONHECIMENTO</span><h2 id="bdc-reader-summary-title">Resumo Executivo</h2></header>
					<div class="bdc-reader-summary__body">
						<dl>
							<div class="bdc-reader-summary__item bdc-reader-summary__item--title"><dt>Título</dt><dd><?php echo esc_html( (string) $model['title'] ); ?></dd></div>
							<?php foreach ( (array) $model['summary_items'] as $item ) : ?>
								<div class="bdc-reader-summary__item bdc-reader-summary__item--<?php echo esc_attr( sanitize_html_class( (string) ( $item['kind'] ?? 'fact' ) ) ); ?>">
									<dt><?php echo esc_html( (string) ( $item['label'] ?? '' ) ); ?></dt>
									<dd><?php echo nl2br( esc_html( (string) ( $item['value'] ?? '' ) ) ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					</div>
				</aside>
			<?php endif; ?>
		</div>
	</main>
</div>
<?php wp_footer(); ?>
</body>
</html>
