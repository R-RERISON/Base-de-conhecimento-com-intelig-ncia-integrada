<?php
/**
 * BDC public Article Reader clean preview template.
 *
 * @package BDC_Knowledge_Base
 */

use BDC\KnowledgeBase\Public_Article_Content;
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
<?php
$content_html = '';
while ( have_posts() ) {
	the_post();
	$content_html = Public_Article_Content::capture_current_loop();
	break;
}
?>
<div class="bdc-public">
	<?php Public_Experience::render_header(); ?>
	<?php Public_Experience::render_preview_banner( 'UX-005 / reader-premium v6 · ' . (string) $model['source_kind'] ); ?>

	<main class="bdc-public-main bdc-reader">
		<nav class="bdc-reader-breadcrumb" aria-label="Caminho do artigo"><a href="<?php echo esc_url( Public_Experience::home_preview_url() ); ?>">Base</a><span>/</span><?php if ( '' !== (string) $model['category'] ) : ?><span><?php echo esc_html( (string) $model['category'] ); ?></span><span>/</span><?php endif; ?><strong><?php echo esc_html( (string) $model['title'] ); ?></strong></nav>

		<header class="bdc-reader-heading">
			<div class="bdc-reader-heading__main">
				<?php if ( '' !== (string) $model['category'] ) : ?><span class="bdc-reader-category"><?php echo esc_html( (string) $model['category'] ); ?></span><?php endif; ?>
				<h1><?php echo esc_html( (string) $model['title'] ); ?></h1>
				<div class="bdc-reader-meta"><span>Responsável <strong><?php echo esc_html( (string) $model['author'] ); ?></strong></span><span>Publicado <strong><?php echo esc_html( (string) $model['published'] ); ?></strong></span><span>Atualizado <strong><?php echo esc_html( (string) $model['updated'] ); ?></strong></span></div>
			</div>
			<a class="bdc-reader-back" href="<?php echo esc_url( Public_Experience::home_preview_url() ); ?>"><span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>Base</a>
		</header>

		<div class="bdc-reader-layout<?php echo empty( $model['summary_items'] ) ? ' bdc-reader-layout--single' : ''; ?>">
			<article class="bdc-reader-content bdc-reader-content--premium">
				<?php if ( ! empty( $model['tips'] ) ) : ?>
					<section class="bdc-reader-tips" aria-labelledby="bdc-reader-tips-title">
						<header><span class="dashicons dashicons-lightbulb" aria-hidden="true"></span><div><h2 id="bdc-reader-tips-title">Dicas úteis</h2><p>Antes de começar</p></div></header>
						<div class="bdc-reader-tips__grid">
							<?php foreach ( (array) $model['tips'] as $tip ) : ?><div class="bdc-reader-tip"><strong><?php echo esc_html( (string) ( $tip['title'] ?? '' ) ); ?></strong><p><?php echo nl2br( esc_html( (string) ( $tip['content'] ?? '' ) ) ); ?></p></div><?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<section class="bdc-reader-document" aria-label="Conteúdo do artigo">
					<?php if ( '' === trim( $content_html ) ) : ?><div class="bdc-public-empty">O conteúdo deste artigo não pôde ser apresentado nesta prévia.</div><?php else : ?><?php echo $content_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php endif; ?>
				</section>
			</article>

			<?php if ( ! empty( $model['summary_items'] ) ) : ?>
				<div class="bdc-reader-summary-slot" data-bdc-summary-slot aria-label="Contexto do artigo">
					<aside class="bdc-reader-summary" data-bdc-summary-rail aria-labelledby="bdc-reader-summary-title"><header><h2 id="bdc-reader-summary-title">Resumo Executivo</h2><small>Contexto rápido</small></header><div class="bdc-reader-summary__body"><dl>
						<?php foreach ( (array) $model['summary_items'] as $item ) : ?><div class="bdc-reader-summary__item bdc-reader-summary__item--<?php echo esc_attr( sanitize_html_class( (string) ( $item['kind'] ?? 'fact' ) ) ); ?>"><dt><?php echo esc_html( (string) ( $item['label'] ?? '' ) ); ?></dt><dd><?php echo nl2br( esc_html( (string) ( $item['value'] ?? '' ) ) ); ?></dd></div><?php endforeach; ?>
					</dl></div></aside>
				</div>
			<?php endif; ?>
		</div>
	</main>
</div>
<?php wp_footer(); ?>
</body>
</html>
