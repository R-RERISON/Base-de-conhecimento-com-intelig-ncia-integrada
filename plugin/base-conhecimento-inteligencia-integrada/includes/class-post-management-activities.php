<?php
/**
 * Read-only activity renderers for the post-centric Knowledge Workspace.
 *
 * @package BDC_Knowledge_Base
 */
namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Post_Management_Activities {
	/** @param array<string,mixed> $context */
	public static function render( string $activity, int $post_id, array $context ): void {
		switch ( $activity ) {
			case 'content': self::render_content( $post_id, $context ); break;
			case 'intelligence': self::render_intelligence( $post_id, $context ); break;
			case 'core_blocks': self::render_core_blocks( $post_id, $context ); break;
			default:
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Atividade não suportada pelo Workspace.', 'bdc-knowledge-base' ) . '</p></div>';
		}
	}

	/** @param array<string,mixed> $context */
	private static function render_content( int $post_id, array $context ): void {
		$source = is_array( $context['source'] ?? null ) ? $context['source'] : array();
		echo '<section class="bdc-kb-domain-panel" aria-labelledby="bdc-kb-content-title">';
		echo '<div class="bdc-kb-domain-heading"><h3 id="bdc-kb-content-title">' . esc_html__( 'Conteúdo e estrutura', 'bdc-knowledge-base' ) . '</h3><p>' . esc_html__( 'Leitura técnica da fonte editorial do artigo. Nenhum corpo editorial é alterado nesta atividade.', 'bdc-knowledge-base' ) . '</p></div>';
		self::table( array(
			'Post' => '#' . $post_id,
			'Fonte detectada' => (string) ( $source['label'] ?? 'Indisponível' ),
			'Estratégia de fidelidade' => (string) ( $source['strategy'] ?? '' ),
			'Unidades editoriais' => (string) ( $source['unit_count'] ?? 0 ),
			'Bytes analisados' => (string) ( $source['bytes'] ?? 0 ),
			'Fidelity hash' => self::hash_display( (string) ( $source['fidelity_hash'] ?? '' ) ),
			'post_content SHA-256' => self::hash_display( (string) ( $source['post_content_sha256'] ?? '' ) ),
			'Elementor SHA-256' => self::hash_display( (string) ( $source['elementor_data_sha256'] ?? '' ) ),
		) );
		echo '</section>';
	}

	/** @param array<string,mixed> $context */
	private static function render_intelligence( int $post_id, array $context ): void {
		unset( $post_id );
		$knowledge = is_array( $context['knowledge'] ?? null ) ? $context['knowledge'] : array();
		echo '<section class="bdc-kb-domain-panel" aria-labelledby="bdc-kb-intelligence-title">';
		echo '<div class="bdc-kb-domain-heading"><h3 id="bdc-kb-intelligence-title">' . esc_html__( 'Inteligência', 'bdc-knowledge-base' ) . '</h3><p>' . esc_html__( 'Camada central para análises e sugestões de IA deste artigo. A execução de modelo e rede externa permanece desabilitada neste gate.', 'bdc-knowledge-base' ) . '</p></div>';
		echo '<div class="notice notice-info inline"><p><strong>' . esc_html__( 'Inteligência — somente leitura.', 'bdc-knowledge-base' ) . '</strong> ' . esc_html__( 'A integração de IA será adicionada como atividade deste mesmo Workspace, nunca como tela paralela.', 'bdc-knowledge-base' ) . '</p></div>';
		self::table( array(
			'Summary disponível' => ! empty( $knowledge['summary_available'] ) ? 'Sim' : 'Não',
			'Conceitos classificados' => (string) ( $knowledge['classification_term_count'] ?? 0 ),
			'Atividade IA registrada' => ! empty( $knowledge['intelligence_activity_registered'] ) ? 'Sim' : 'Não',
			'Execução IA neste gate' => ! empty( $knowledge['intelligence_execution_enabled'] ) ? 'Habilitada' : 'Desabilitada',
		) );
		echo '</section>';
	}

	/** @param array<string,mixed> $context */
	private static function render_core_blocks( int $post_id, array $context ): void {
		$core = is_array( $context['core_blocks'] ?? null ) ? $context['core_blocks'] : array();
		$source = is_array( $context['source'] ?? null ) ? $context['source'] : array();
		$status = (string) ( $core['operational_status'] ?? 'blocked' );
		$reasons = array_values( array_map( 'strval', (array) ( $core['operational_reasons'] ?? array() ) ) );
		$authorization_ready = true === ( $core['authorization_ready'] ?? false );
		$authorization_id = (string) ( $core['authorization_id'] ?? '' );
		$block_names = array_values( array_map( 'strval', (array) ( $core['expected_block_names'] ?? array() ) ) );
		$t100d_enabled = defined( 'BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD' )
			&& BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD
			&& class_exists( Post_Core_Blocks_Executor_T100D::class )
			&& Post_Core_Blocks_Executor_T100D::can_render( $post_id, $authorization_id );

		echo '<section class="bdc-kb-domain-panel" aria-labelledby="bdc-kb-core-blocks-title">';
		echo '<div class="bdc-kb-domain-heading"><h3 id="bdc-kb-core-blocks-title">' . esc_html__( 'Core Blocks / Migração', 'bdc-knowledge-base' ) . '</h3><p>' . esc_html__( 'Diagnóstico operacional e preparação de autorização deste artigo para o destino editorial canônico.', 'bdc-knowledge-base' ) . '</p></div>';

		self::table( array(
			'Post' => '#' . $post_id,
			'Fonte atual' => (string) ( $source['label'] ?? 'Indisponível' ),
			'Dry-run' => (string) ( $core['dry_run_status'] ?? 'unavailable' ),
			'Estado operacional' => $status,
			'Blocos esperados' => ! empty( $block_names ) ? implode( ', ', $block_names ) : 'n/a',
			'Eventos de journal' => (string) ( $core['journal_event_count'] ?? 0 ),
			'Último estado de journal' => '' !== (string) ( $core['latest_journal_state'] ?? '' ) ? (string) $core['latest_journal_state'] : 'Sem journal',
			'Lock' => (string) ( $core['lock_status'] ?? 'unavailable' ),
			'Authorization ID' => '' !== $authorization_id ? $authorization_id : 'Ainda não disponível',
			'Preparação T100C' => 'Somente leitura',
			'Executor T100D' => $t100d_enabled ? 'Autorizado para este post' : 'Não autorizado',
			'Persistência em sucesso' => $t100d_enabled ? 'Sim' : 'Não',
		) );

		if ( ! empty( $reasons ) ) {
			echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Motivos / observações:', 'bdc-knowledge-base' ) . '</strong> ' . esc_html( implode( ' · ', $reasons ) ) . '</p></div>';
		}

		echo '<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:18px">';
		if ( $authorization_ready && '' !== $authorization_id ) {
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
			echo '<input type="hidden" name="action" value="' . esc_attr( Post_Core_Blocks_Activity::ACTION ) . '">';
			echo '<input type="hidden" name="post_id" value="' . esc_attr( (string) $post_id ) . '">';
			wp_nonce_field( Post_Core_Blocks_Activity::nonce_action( $post_id ), Post_Core_Blocks_Activity::NONCE_FIELD );
			submit_button( __( 'Baixar Authorization Pack deste post', 'bdc-knowledge-base' ), 'secondary', 'submit', false );
			echo '</form>';
		}
		if ( $authorization_ready && $t100d_enabled ) {
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
			echo '<input type="hidden" name="action" value="' . esc_attr( Post_Core_Blocks_Executor_T100D::ACTION ) . '">';
			echo '<input type="hidden" name="post_id" value="' . esc_attr( (string) $post_id ) . '">';
			echo '<input type="hidden" name="' . esc_attr( Post_Core_Blocks_Executor_T100D::AUTH_FIELD ) . '" value="' . esc_attr( $authorization_id ) . '">';
			wp_nonce_field( Post_Core_Blocks_Executor_T100D::nonce_action(), Post_Core_Blocks_Executor_T100D::NONCE_FIELD );
			submit_button( __( 'Migrar este post para Core Blocks — persistente', 'bdc-knowledge-base' ), 'primary', 'submit', false );
			echo '</form>';
		} else {
			echo '<button type="button" class="button button-primary" disabled aria-disabled="true">' . esc_html__( 'Migrar para Core Blocks — sem autorização executável', 'bdc-knowledge-base' ) . '</button>';
		}
		echo '</div>';

		if ( $authorization_ready && $t100d_enabled ) {
			echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'T100D autorizado para este artigo.', 'bdc-knowledge-base' ) . '</strong> ' . esc_html__( 'Se todas as verificações passarem, a migração será persistente. Qualquer falha pós-write aciona rollback automático para o snapshot anterior.', 'bdc-knowledge-base' ) . '</p></div>';
		} elseif ( $authorization_ready ) {
			echo '<p class="bdc-kb-context-note">' . esc_html__( 'O Authorization Pack congela a identidade atual deste único artigo. Qualquer drift posterior invalida a autorização.', 'bdc-knowledge-base' ) . '</p>';
		} else {
			echo '<p class="bdc-kb-context-note">' . esc_html__( 'Este artigo ainda não está elegível para autorização de migração. O estado acima indica a causa sem executar qualquer write.', 'bdc-knowledge-base' ) . '</p>';
		}
		echo '</section>';
	}

	/** @param array<string,string> $rows */
	private static function table( array $rows ): void {
		echo '<table class="widefat striped" style="margin-top:18px"><tbody>';
		foreach ( $rows as $label => $value ) {
			echo '<tr><th style="width:240px">' . esc_html( $label ) . '</th><td><code>' . esc_html( $value ) . '</code></td></tr>';
		}
		echo '</tbody></table>';
	}

	private static function hash_display( string $hash ): string {
		return preg_match( '/^[a-f0-9]{64}$/', $hash ) ? $hash : 'n/a';
	}
}
