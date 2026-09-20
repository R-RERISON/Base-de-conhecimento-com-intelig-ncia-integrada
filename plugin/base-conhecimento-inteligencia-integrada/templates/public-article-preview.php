<?php
/**
 * BDC public Article Reader redesign preview template.
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
$content_html = Public_Article_Content::render( $post_id );

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
	<?php Public_Experience::render_preview_banner( 'UX-005 / redesign v2 · ' . (string) $model['source_kind'] ); ?>

	<main class="bdc-public-main bdc-reader">
		<nav class="bdc-reader-breadcrumb" aria-label="Caminho do artigo">
			<a href="<?php echo esc_url( Public_Experience::home_preview_url() ); ?>">Base de Conhecimento</a>
			<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
			<?php if ( '' !== (string) $model['category'] ) : ?><span><?php echo esc_html( (string) $model['category'] ); ?></span><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span><?php endif; ?>
			<strong><?php echo esc_html( (string) $model['title'] ); ?></strong>
		</nav>

		<header class="bdc-reader-hero">
			<div class="bdc-reader-hero__main">
				<div class="bdc-reader-hero__eyebrow">
					<?php if ( '' !== (string) $model['category'] ) : ?><span class="bdc-reader-category"><?php echo esc_html( (string) $model['category'] ); ?></span><?php endif; ?>
					<span class="bdc-reader-source-badge"><?php echo esc_html( strtoupper( (string) $model['source_kind'] ) ); ?></span>
				</div>
				<h1><?php echo esc_html( (string) $model['title'] ); ?></h1>
				<p class="bdc-reader-hero__subtitle">Instrução operacional da Base de Conhecimento.</p>
				<div class="bdc-reader-meta">
					<span><span class="dashicons dashicons-admin-users" aria-hidden="true"></span><small>Responsável</small><strong><?php echo esc_html( (string) $model['author'] ); ?></strong></span>
					<span><span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span><small>Publicado</small><strong><?php echo esc_html( (string) $model['published'] ); ?></strong></span>
					<span><span class="dashicons dashicons-update" aria-hidden="true"></span><small>Atualizado</small><strong><?php echo esc_html( (string) $model['updated'] ); ?></strong></span>
				</div>
			</div>
			<a class="bdc-reader-back" href="<?php echo esc_url( Public_Experience::home_preview_url() ); ?>"><span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span><span>Voltar para a base</span></a>
		</header>

		<div class="bdc-reader-layout">
			<article class="bdc-reader-content">
				<?php if ( ! empty( $model['tips'] ) ) : ?>
					<section class="bdc-reader-tips" aria-labelledby="bdc-reader-tips-title">
						<div class="bdc-reader-tips__intro">
							<span class="bdc-reader-tips__icon"><span class="dashicons dashicons-lightbulb" aria-hidden="true"></span></span>
							<div><span class="bdc-reader-section-kicker">ANTES DE COMEÇAR</span><h2 id="bdc-reader-tips-title">Dicas úteis</h2><p>Pontos de atenção para executar este procedimento com mais segurança e agilidade.</p></div>
						</div>
						<div class="bdc-reader-tips__grid">
							<?php foreach ( (array) $model['tips'] as $tip ) : ?>
								<div class="bdc-reader-tip">
									<span class="bdc-reader-tip__marker" aria-hidden="true"></span>
									<h3><?php echo esc_html( (string) ( $tip['title'] ?? '' ) ); ?></h3>
									<p><?php echo nl2br( esc_html( (string) ( $tip['content'] ?? '' ) ) ); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<section class="bdc-reader-document" aria-label="Conteúdo do artigo">
					<?php if ( '' === trim( $content_html ) ) : ?>
						<div class="bdc-public-empty">O conteúdo deste artigo não pôde ser apresentado nesta prévia.</div>
					<?php else : ?>
						<?php echo $content_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- canonical WP content pipeline output. ?>
					<?php endif; ?>
				</section>
			</article>

			<?php if ( ! empty( $model['summary_items'] ) ) : ?>
				<aside class="bdc-reader-summary" aria-labelledby="bdc-reader-summary-title">
					<header>
						<div><span class="bdc-reader-section-kicker">CONTEXTO RÁPIDO</span><h2 id="bdc-reader-summary-title">Resumo Executivo</h2></div>
						<span class="bdc-reader-summary__icon"><span class="dashicons dashicons-index-card" aria-hidden="true"></span></span>
					</header>
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