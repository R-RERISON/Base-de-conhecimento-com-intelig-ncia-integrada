<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
	if ( ! class_exists( 'WP_Error' ) ) {
		class WP_Error {
			public function __construct(
				public string $code = '',
				public string $message = '',
				public mixed $data = null
			) {}
		}
	}
}

namespace BDC\KnowledgeBase {
	function batch_sort_recursive( array &$array ): void {
		foreach ( $array as &$value ) {
			if ( is_array( $value ) ) {
				batch_sort_recursive( $value );
			}
		}
		ksort( $array );
	}

	final class Canonical_JSON {
		public static function hash( array $value ): string {
			batch_sort_recursive( $value );
			return hash( 'sha256', json_encode( $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR ) );
		}
	}

	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-elementor-migration-batch-plan.php';

	$assertions = 0;
	function assert_batch( bool $condition, string $message ): void {
		global $assertions;
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
		++$assertions;
		echo "PASS {$message}\n";
	}

	function batch_dry_run( int $post_id, string $status = 'ready', string $suffix = '' ): array {
		return array(
			'post_id' => $post_id,
			'dry_run_status' => $status,
			'source_hash_before' => hash( 'sha256', 'source-' . $post_id . $suffix ),
			'projection_hash' => hash( 'sha256', 'projection-' . $post_id . $suffix ),
			'dry_run_hash' => hash( 'sha256', 'dry-' . $post_id . $suffix ),
			'execution_allowed' => false,
			'writer_allowed' => false,
			'migration_execution_allowed' => false,
		);
	}

	$input = array(
		batch_dry_run( 5 ),
		batch_dry_run( 1 ),
		batch_dry_run( 3 ),
		batch_dry_run( 2 ),
		batch_dry_run( 4 ),
		batch_dry_run( 3 ),
		batch_dry_run( 9, 'review_required' ),
	);

	$first = Elementor_Migration_Batch_Plan::plan( $input, 2 );
	assert_batch( is_array( $first ), 'first batch built' );
	assert_batch( array( 1, 2 ) === array_column( $first['items'], 'post_id' ), 'candidates sorted deterministic' );
	assert_batch( 5 === $first['eligible_total'], 'identical duplicate deduped' );
	assert_batch( 1 === $first['excluded_total'], 'non-ready candidate excluded' );
	assert_batch( 2 === $first['item_count'], 'batch size respected' );
	assert_batch( false === $first['done'], 'first batch not done' );
	assert_batch( is_array( $first['next_cursor'] ), 'next cursor emitted' );
	assert_batch( false === $first['execution_allowed'], 'execution stays false' );
	assert_batch( false === $first['writer_allowed'], 'writer stays false' );
	assert_batch( false === $first['persists_checkpoint'], 'checkpoint is not persisted' );

	$second = Elementor_Migration_Batch_Plan::plan( $input, 2, $first['next_cursor'] );
	assert_batch( array( 3, 4 ) === array_column( $second['items'], 'post_id' ), 'resume starts after completed batch' );
	assert_batch( 2 === $second['offset'], 'resume offset correct' );
	assert_batch( $first['cohort_hash'] === $second['cohort_hash'], 'cohort stable across resume' );
	assert_batch( $first['batch_hash'] !== $second['batch_hash'], 'batch hashes differ by slice' );

	$third = Elementor_Migration_Batch_Plan::plan( $input, 2, $second['next_cursor'] );
	assert_batch( array( 5 ) === array_column( $third['items'], 'post_id' ), 'final batch contains remainder' );
	assert_batch( true === $third['done'], 'final batch done' );
	assert_batch( null === $third['next_cursor'], 'no cursor after final batch' );

	$ids = array_merge( array_column( $first['items'], 'post_id' ), array_column( $second['items'], 'post_id' ), array_column( $third['items'], 'post_id' ) );
	assert_batch( count( $ids ) === count( array_unique( $ids ) ), 'no duplicate IDs across batches' );
	assert_batch( array( 1, 2, 3, 4, 5 ) === $ids, 'all eligible IDs covered exactly once' );

	$repeat = Elementor_Migration_Batch_Plan::plan( array_reverse( $input ), 2 );
	assert_batch( $first['cohort_hash'] === $repeat['cohort_hash'], 'input order does not change cohort hash' );
	assert_batch( $first['batch_hash'] === $repeat['batch_hash'], 'input order does not change batch hash' );

	$tampered = $first['next_cursor'];
	$tampered['next_offset'] = 4;
	assert_batch( Elementor_Migration_Batch_Plan::plan( $input, 2, $tampered ) instanceof \WP_Error, 'tampered cursor blocked' );

	$changed = $input;
	$changed[] = batch_dry_run( 6 );
	assert_batch( Elementor_Migration_Batch_Plan::plan( $changed, 2, $first['next_cursor'] ) instanceof \WP_Error, 'stale cursor blocked when cohort changes' );

	assert_batch( Elementor_Migration_Batch_Plan::plan( $input, 0 ) instanceof \WP_Error, 'zero batch size blocked' );
	assert_batch( Elementor_Migration_Batch_Plan::plan( $input, 101 ) instanceof \WP_Error, 'oversize batch blocked' );

	$conflicting = array( batch_dry_run( 1 ), batch_dry_run( 1, 'ready', '-changed' ) );
	assert_batch( Elementor_Migration_Batch_Plan::plan( $conflicting, 2 ) instanceof \WP_Error, 'conflicting duplicate blocked' );

	$unsafe = batch_dry_run( 1 );
	$unsafe['writer_allowed'] = true;
	assert_batch( Elementor_Migration_Batch_Plan::plan( array( $unsafe ), 2 ) instanceof \WP_Error, 'unsafe ready candidate blocked' );

	$invalid = batch_dry_run( 1 );
	$invalid['dry_run_hash'] = 'bad';
	assert_batch( Elementor_Migration_Batch_Plan::plan( array( $invalid ), 2 ) instanceof \WP_Error, 'invalid ready hash blocked' );

	$empty = Elementor_Migration_Batch_Plan::plan( array( batch_dry_run( 1, 'review_required' ) ), 10 );
	assert_batch( is_array( $empty ), 'empty eligible cohort built' );
	assert_batch( 0 === $empty['eligible_total'], 'empty cohort has zero eligible' );
	assert_batch( true === $empty['done'], 'empty cohort done' );
	assert_batch( array() === $empty['items'], 'empty cohort no items' );

	foreach ( array( 'persists_state', 'writes_post_content', 'writes_elementor_data', 'calls_external_network', 'executes_shortcodes' ) as $key ) {
		assert_batch( false === $first['safety'][ $key ], "safety {$key} false" );
	}

	echo "ALL PASS {$assertions}\n";
}
