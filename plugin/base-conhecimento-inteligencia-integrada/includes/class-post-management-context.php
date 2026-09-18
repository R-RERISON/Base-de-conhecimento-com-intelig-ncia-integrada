<?php
/**
 * Read-only, post-scoped context contract for the canonical Knowledge Workspace.
 *
 * @package BDC_Knowledge_Base
 */
namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Post_Management_Context {
	public const SCHEMA_VERSION = '1.0.0';

	/** @return array<string,mixed>|\WP_Error */
	public static function build( int $post_id ) {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) || Meta_Contract::POST_TYPE !== (string) ( $post->post_type ?? '' ) ) {
			return new \WP_Error( 'bdc_kb_workspace_invalid_post', 'Artigo inválido para a área de gerenciamento.' );
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new \WP_Error( 'bdc_kb_workspace_forbidden', 'Sem permissão para o post.' );
		}

		$source = Migration_Fidelity_Source::build( $post_id );
		$core_activity = Post_Core_Blocks_Activity::assess( $post_id );

		$summary = Summary_Store::read( $post_id );
		$review = Review_Store::read( $post_id );
		$classification_count = 0;
		foreach ( Classification_Contract::fields() as $definition ) {
			$terms = wp_get_object_terms( $post_id, (string) $definition['taxonomy'], array( 'fields' => 'ids' ) );
			if ( ! is_wp_error( $terms ) ) { $classification_count += count( $terms ); }
		}

		$source_kind = $source instanceof \WP_Error ? 'unavailable' : (string) ( $source['source_kind'] ?? 'unknown' );
		$strategy = $source instanceof \WP_Error ? '' : (string) ( $source['strategy'] ?? '' );
		$source_material = $source instanceof \WP_Error || ! is_array( $source['source_material'] ?? null ) ? array() : $source['source_material'];
		$unit_count = $source instanceof \WP_Error ? 0 : count( (array) ( $source['units'] ?? array() ) );
		$source_bytes = 0;
		if ( ! ( $source instanceof \WP_Error ) ) {
			foreach ( (array) ( $source['units'] ?? array() ) as $unit ) {
				if ( is_array( $unit ) ) { $source_bytes += (int) ( $unit['bytes'] ?? 0 ); }
			}
		}

		return array(
			'schema_version' => self::SCHEMA_VERSION,
			'post_id' => $post_id,
			'post_status' => (string) ( $post->post_status ?? '' ),
			'post_title' => (string) ( $post->post_title ?? '' ),
			'source' => array(
				'kind' => $source_kind,
				'label' => self::source_label( $source_kind ),
				'strategy' => $strategy,
				'unit_count' => $unit_count,
				'bytes' => $source_bytes,
				'fidelity_hash' => $source instanceof \WP_Error ? '' : (string) ( $source['fidelity_hash'] ?? '' ),
				'post_content_sha256' => (string) ( $source_material['post_content_sha256'] ?? '' ),
				'elementor_data_sha256' => (string) ( $source_material['elementor_data_sha256'] ?? '' ),
				'available' => ! ( $source instanceof \WP_Error ),
			),
			'knowledge' => array(
				'summary_available' => ! ( $summary instanceof \WP_Error ),
				'classification_term_count' => $classification_count,
				'intelligence_activity_registered' => true,
				'intelligence_execution_enabled' => false,
			),
			'governance' => array(
				'review_state' => $review instanceof \WP_Error ? Review_Contract::STATE_UNREVIEWED : (string) ( $review['state'] ?? Review_Contract::STATE_UNREVIEWED ),
			),
			'core_blocks' => $core_activity instanceof \WP_Error
				? array(
					'dry_run_status' => 'unavailable',
					'journal_event_count' => 0,
					'latest_journal_state' => '',
					'lock_status' => 'unavailable',
					'operational_status' => 'blocked',
					'operational_reasons' => array( 'CORE_ACTIVITY_ERROR:' . $core_activity->get_error_code() ),
					'authorization_ready' => false,
					'authorization_id' => '',
					'expected_block_names' => array(),
					'writer_enabled' => false,
					'migration_execution_enabled' => false,
				)
				: $core_activity,
			'safety' => array(
				'read_only_context' => true,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'persists_state' => false,
				'calls_external_network' => false,
			),
		);
	}

	public static function source_label( string $source_kind ): string {
		$labels = array(
			'gutenberg' => 'Blocos do WordPress',
			'legacy_html' => 'HTML legado',
			'plain_text' => 'Texto simples',
			'elementor' => 'Conteúdo legado do Elementor',
			'mixed' => 'Conteúdo misto — requer revisão',
			'empty' => 'Sem conteúdo editorial',
			'unavailable' => 'Indisponível',
		);
		return $labels[ $source_kind ] ?? $source_kind;
	}
}
