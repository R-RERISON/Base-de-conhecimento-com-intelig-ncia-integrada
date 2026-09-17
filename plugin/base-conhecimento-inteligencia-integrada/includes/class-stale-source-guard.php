<?php
/**
 * Stale Source Guard read-only da SPEC-004/G-245.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Impede uma futura aplicação quando a fonte divergiu do plano calculado.
 */
final class Stale_Source_Guard {

	/** @return array<string,mixed>|\WP_Error */
	public static function check( int $post_id, array $plan ): array|\WP_Error {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) {
			return new \WP_Error( 'bdc_kb_stale_post_not_found', 'Post não encontrado para stale-source guard.' );
		}

		$current = Knowledge_Document::build( $post_id );
		if ( is_wp_error( $current ) ) {
			return $current;
		}

		$expected_hash = (string) ( $plan['source_hash_before'] ?? '' );
		$actual_hash   = (string) $current['source_hash'];
		$expected_modified = (string) ( $plan['post_modified_gmt_before'] ?? '' );
		$actual_modified   = (string) $post->post_modified_gmt;
		$hash_equal = '' !== $expected_hash && hash_equals( $expected_hash, $actual_hash );
		$modified_equal = '' !== $expected_modified && $expected_modified === $actual_modified;

		return array(
			'status' => $hash_equal && $modified_equal ? 'FRESH' : 'STALE_SOURCE',
			'post_id' => $post_id,
			'source_hash_before' => $expected_hash,
			'source_hash_current' => $actual_hash,
			'post_modified_gmt_before' => $expected_modified,
			'post_modified_gmt_current' => $actual_modified,
			'hash_equal' => $hash_equal,
			'modified_equal' => $modified_equal,
			'write_allowed' => false,
		);
	}
}