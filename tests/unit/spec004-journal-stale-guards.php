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
	function spec004_ksort_recursive( array &$array ): void {
		foreach ( $array as &$value ) {
			if ( is_array( $value ) ) {
				spec004_ksort_recursive( $value );
			}
		}
		ksort( $array );
	}

	if ( ! class_exists( Canonical_JSON::class ) ) {
		final class Canonical_JSON {
			public static function hash( array $value ): string {
				spec004_ksort_recursive( $value );
				return hash( 'sha256', json_encode( $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR ) );
			}
		}
	}

	if ( ! class_exists( Knowledge_Document::class ) ) {
		final class Knowledge_Document {
			public static array $doc = array();
			public static function build( int $post_id ): array|\WP_Error {
				return self::$doc;
			}
		}
	}

	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-elementor-migration-journal.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-elementor-stale-source-guard.php';

	$assertions = 0;
	function assert_guard( bool $condition, string $message ): void {
		global $assertions;
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
		++$assertions;
		echo "PASS {$message}\n";
	}

	$before_hash = str_repeat( 'a', 64 );
	$after_hash = str_repeat( 'b', 64 );
	$projection_hash = str_repeat( 'c', 64 );

	$entry = Elementor_Migration_Journal::prepare(
		array(
			'run_id' => 'run-20260917-001',
			'post_id' => 123,
			'source_hash_before' => $before_hash,
			'projection_hash' => $projection_hash,
			'recorded_at' => '2026-09-17T14:10:00Z',
			'before' => array(
				'post_content' => 'abc',
				'elementor_data' => '{"x":1}',
			),
		)
	);
	assert_guard( is_array( $entry ), 'journal prepared' );
	assert_guard( 'prepared' === $entry['state'], 'journal state prepared' );
	assert_guard( false === $entry['writer_allowed'], 'journal writer stays false' );
	assert_guard( false === $entry['journal_persisted'], 'T083 does not persist journal' );
	assert_guard( true === $entry['journal_must_be_persisted_before_write'], 'write-ahead journal required before future write' );
	assert_guard( 64 === strlen( (string) $entry['journal_hash'] ), 'journal integrity hash present' );
	assert_guard( 64 === strlen( (string) $entry['rollback_payload_hash'] ), 'rollback capsule hash present' );

	$public = Elementor_Migration_Journal::public_record( $entry );
	assert_guard( ! isset( $public['rollback_payload'] ), 'public journal strips editorial rollback payload' );
	assert_guard( false === $public['contains_editorial_payload'], 'public journal identifies payload absence' );

	$invalid = Elementor_Migration_Journal::prepare( array( 'run_id' => 'x', 'post_id' => 0 ) );
	assert_guard( $invalid instanceof \WP_Error, 'invalid journal preparation fails closed' );

	$applied = Elementor_Migration_Journal::mark_applied( $entry, $after_hash );
	assert_guard( is_array( $applied ), 'journal applied transition succeeds' );
	assert_guard( 'applied' === $applied['state'], 'journal applied state recorded' );
	assert_guard( $after_hash === $applied['source_hash_after'], 'source_hash_after recorded' );

	$rollback = Elementor_Migration_Journal::rollback_decision( $applied, $after_hash );
	assert_guard( true === $rollback['rollback_allowed'], 'rollback allowed only against unchanged post-write source' );
	assert_guard( 'restore_before_snapshot' === $rollback['action'], 'rollback restores before snapshot' );

	$stale_rollback = Elementor_Migration_Journal::rollback_decision( $applied, str_repeat( 'd', 64 ) );
	assert_guard( false === $stale_rollback['rollback_allowed'], 'rollback blocks changed target' );
	assert_guard( in_array( 'ROLLBACK_TARGET_CHANGED', $stale_rollback['reasons'], true ), 'rollback changed-target reason emitted' );

	$tampered = $applied;
	$tampered['rollback_payload']['post_content'] = 'tampered';
	$tampered_rollback = Elementor_Migration_Journal::rollback_decision( $tampered, $after_hash );
	assert_guard( false === $tampered_rollback['rollback_allowed'], 'tampered rollback capsule blocked' );

	$rolled = Elementor_Migration_Journal::mark_rolled_back( $applied, $before_hash );
	assert_guard( is_array( $rolled ), 'rolled-back transition succeeds' );
	assert_guard( 'rolled_back' === $rolled['state'], 'rolled-back state recorded' );
	assert_guard( false === $rolled['rollback_required'], 'rollback requirement cleared after restoration' );
	$noop = Elementor_Migration_Journal::rollback_decision( $rolled, $before_hash );
	assert_guard( true === $noop['idempotent_noop'], 'repeated rollback becomes idempotent noop' );

	$partial = Elementor_Migration_Journal::mark_partial_failure( $entry, $after_hash );
	assert_guard( is_array( $partial ), 'partial failure transition succeeds' );
	assert_guard( 'partial_failure' === $partial['state'], 'partial failure state recorded' );
	assert_guard( true === Elementor_Migration_Journal::rollback_decision( $partial, $after_hash )['rollback_allowed'], 'partial failure remains rollback eligible' );

	$fresh = Elementor_Stale_Source_Guard::assess( $before_hash, $before_hash );
	assert_guard( 'fresh' === $fresh['status'], 'matching source hash is fresh' );
	assert_guard( true === $fresh['is_fresh'], 'fresh flag true' );
	assert_guard( false === $fresh['writer_allowed'], 'T084 never authorizes writer' );
	assert_guard( true === Elementor_Stale_Source_Guard::assert_fresh( $fresh ), 'fresh assessment passes guard' );

	$stale = Elementor_Stale_Source_Guard::assess( $before_hash, $after_hash );
	assert_guard( 'stale' === $stale['status'], 'changed source is stale' );
	assert_guard( true === $stale['is_stale'], 'stale flag true' );
	assert_guard( Elementor_Stale_Source_Guard::assert_fresh( $stale ) instanceof \WP_Error, 'stale assessment blocks execution' );

	$invalid_hash = Elementor_Stale_Source_Guard::assess( 'bad', $after_hash );
	assert_guard( 'blocking' === $invalid_hash['status'], 'invalid expected hash blocks execution' );

	Knowledge_Document::$doc = array( 'source_hash' => $before_hash );
	$inspect = Elementor_Stale_Source_Guard::inspect_post( 123, $before_hash );
	assert_guard( is_array( $inspect ) && true === $inspect['is_fresh'], 'read-only post inspection uses current Knowledge Document hash' );

	foreach ( array( 'persists_state', 'writes_post_content', 'writes_elementor_data', 'calls_external_network', 'executes_shortcodes' ) as $key ) {
		assert_guard( false === $fresh['safety'][ $key ], "stale guard safety {$key} stays false" );
	}

	echo "ALL PASS {$assertions}\n";
}
