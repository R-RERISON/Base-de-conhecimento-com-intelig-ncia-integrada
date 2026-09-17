<?php
/**
 * Dry-run read-only de migration editorial da SPEC-004/G-245.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calcula elegibilidade sem persistir plano, journal ou conteúdo.
 */
final class Migration_Dry_Run {

	public const SCHEMA_VERSION = '1.0.0';

	/** @return array<string,mixed> */
	public static function run( int $limit = 0 ): array {
		$ids = get_posts(
			array(
				'post_type' => 'post',
				'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => $limit > 0 ? min( 1000, $limit ) : -1,
				'fields' => 'ids',
				'orderby' => 'ID',
				'order' => 'ASC',
				'no_found_rows' => true,
				'suppress_filters' => false,
			)
		);
		$counts = array(
			'total' => 0,
			'native' => 0,
			'projectable' => 0,
			'review_required' => 0,
			'blocked' => 0,
			'errors' => 0,
		);
		$strategies = array();
		$warnings = array();
		if ( ! is_array( $ids ) ) {
			$ids = array();
		}

		foreach ( $ids as $post_id ) {
			$plan = Projection_Plan::build( (int) $post_id );
			if ( is_wp_error( $plan ) ) {
				++$counts['errors'];
				continue;
			}
			++$counts['total'];
			$status = (string) $plan['elementor_compatibility']['status'];
			if ( array_key_exists( $status, $counts ) ) {
				++$counts[ $status ];
			} else {
				++$counts['review_required'];
			}
			$strategy = (string) $plan['projection_strategy'];
			$strategies[ $strategy ] = (int) ( $strategies[ $strategy ] ?? 0 ) + 1;
			foreach ( (array) $plan['warnings'] as $warning ) {
				$warning = (string) $warning;
				$warnings[ $warning ] = (int) ( $warnings[ $warning ] ?? 0 ) + 1;
			}
		}

		arsort( $strategies, SORT_NUMERIC );
		arsort( $warnings, SORT_NUMERIC );
		return array(
			'schema_version' => self::SCHEMA_VERSION,
			'mode' => 'temporary_spec004_g245_migration_dry_run_read_only',
			'generated_at' => gmdate( 'c' ),
			'counts' => $counts,
			'strategies' => $strategies,
			'warnings' => $warnings,
			'estimates' => array(
				'journal_bytes' => 0,
				'batches' => 0,
				'batch_size' => 0,
				'journal_status' => 'NOT_CONFIGURED',
			),
			'safety' => array(
				'read_only_design' => true,
				'editorial_writes' => false,
				'elementor_writes' => false,
				'journal_writes' => false,
				'network_calls' => false,
				'persistent_storage' => false,
			),
		);
	}
}