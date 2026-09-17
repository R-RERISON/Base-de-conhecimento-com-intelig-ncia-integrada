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
		echo '<div class="bdc-kb-domain-heading"><h3 id="bdc-kb-intelligence-title">' . esc_html__( 'Inteligência', 'bdc-knowledge-base' ) . '</h3><p>' . esc_html__( 'Camada central para análises e sugestões de IA deste artigo. T100A registra a atividade, mas não executa modelo nem rede externa.', 'bdc-knowledge-base' ) . '</p></div>';
		echo '<div class="notice notice-info inline"><p><strong>' . esc_html__( 'T100A — somente leitura.', 'bdc-knowledge-base' ) . '</strong> ' . esc_html__( 'A integração de IA será adicionada como atividade deste mesmo Workspace, nunca como tela paralela.', 'bdc-knowledge-base' ) . '</p></div>';
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
		unset( $post_id );
		$core = is_array( $context['core_blocks'] ?? null ) ? $context['core_blocks'] : array();
		$source = is_array( $context['source'] ?? null ) ? $context['source'] : array();
		echo '<section class="bdc-kb-domain-panel" aria-labelledby="bdc-kb-core-blocks-title">';
		echo '<div class="bdc-kb-domain-heading"><h3 id="bdc-kb-core-blocks-title">' . esc_html__( 'Core Blocks / Migração', 'bdc-knowledge-base' ) . '</h3><p>' . esc_html__( 'Estado de prontidão e auditoria do artigo para o destino editorial canônico. Nenhuma migração é executada por esta aba.', 'bdc-knowledge-base' ) . '</p></div>';
		self::table( array(
			'Fonte atual' => (string) ( $source['label'] ?? 'Indisponível' ),
			'Dry-run' => (string) ( $core['dry_run_status'] ?? 'unavailable' ),
			'Eventos de journal' => (string) ( $core['journal_event_count'] ?? 0 ),
			'Último estado de journal' => '' !== (string) ( $core['latest_journal_state'] ?? '' ) ? (string) $core['latest_journal_state'] : 'Sem journal',
			'Lock' => (string) ( $core['lock_status'] ?? 'unavailable' ),
			'Writer desta aba' => ! empty( $core['writer_enabled'] ) ? 'Habilitado' : 'Desabilitado',
			'Execução de migração' => ! empty( $core['migration_execution_enabled'] ) ? 'Habilitada' : 'Desabilitada',
		) );
		echo '<p class="bdc-kb-context-note">' . esc_html__( 'Ações de write permanecem protegidas pelos gates de engenharia e por autorização específica. A UX final ficará centralizada aqui somente após novos testes ambientais.', 'bdc-knowledge-base' ) . '</p>';
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
