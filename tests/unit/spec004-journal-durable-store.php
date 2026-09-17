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
	$GLOBALS['spec004_comments'] = array();
	$GLOBALS['spec004_next_comment_id'] = 1;
	$GLOBALS['spec004_posts'] = array( 123 => (object) array( 'ID' => 123 ) );
	$GLOBALS['spec004_user_id'] = 7;
	$GLOBALS['spec004_manage_options'] = true;

	function is_wp_error( mixed $value ): bool {
		return $value instanceof \WP_Error;
	}
	function get_post( int $post_id ): mixed {
		return $GLOBALS['spec004_posts'][ $post_id ] ?? null;
	}
	function get_current_user_id(): int {
		return (int) $GLOBALS['spec004_user_id'];
	}
	function current_user_can( string $cap ): bool {
		return 'manage_options' === $cap && true === $GLOBALS['spec004_manage_options'];
	}
	function wp_json_encode( mixed $value, int $flags = 0 ): string|false {
		return json_encode( $value, $flags );
	}
	function error_log( string $message ): bool {
		return true;
	}
	function wp_insert_comment( array $data ): int {
		$id = $GLOBALS['spec004_next_comment_id']++;
		$GLOBALS['spec004_comments'][ $id ] = (object) array(
			'comment_ID' => $id,
			'comment_post_ID' => (int) $data['comment_post_ID'],
			'comment_content' => (string) $data['comment_content'],
			'comment_type' => (string) $data['comment_type'],
			'comment_approved' => (int) $data['comment_approved'],
			'user_id' => (int) $data['user_id'],
			'comment_date_gmt' => '2026-09-17 15:00:00',
		);
		return $id;
	}
	function get_comment( int $id ): mixed {
		return $GLOBALS['spec004_comments'][ $id ] ?? null;
	}
	function wp_delete_comment( int $id, bool $force = false ): bool {
		unset( $GLOBALS['spec004_comments'][ $id ] );
		return true;
	}
	function get_comments( array $args ): array {
		$rows = array_values(
			array_filter(
				$GLOBALS['spec004_comments'],
				static function ( object $comment ) use ( $args ): bool {
					return (int) $comment->comment_post_ID === (int) $args['post_id']
						&& (string) $comment->comment_type === (string) $args['type'];
				}
			)
		);
		usort( $rows, static fn( object $a, object $b ): int => (int) $b->comment_ID <=> (int) $a->comment_ID );
		return array_slice( $rows, 0, (int) ( $args['number'] ?? 1 ) );
	}

	function spec004_sort_recursive( array &$array ): void {
		foreach ( $array as &$value ) {
			if ( is_array( $value ) ) {
				spec004_sort_recursive( $value );
			}
		}
		ksort( $array );
	}

	if ( ! class_exists( Canonical_JSON::class ) ) {
		final class Canonical_JSON {
			public static function hash( array $value ): string {
				spec004_sort_recursive( $value );
				return hash( 'sha256', json_encode( $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR ) );
			}
		}
	}

	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-elementor-migration-journal.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-elementor-migration-journal-store.php';

	$assertions = 0;
	function assert_store( bool $condition, string $message ): void {
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
	$post_content = '<h2>Teste "áéí"</h2><p>C:\\temp\\x / slash</p>';
	$elementor_data = '[{"id":"abc","settings":{"html":"<b>Olá</b>\\nlinha"}}]';

	$record = Elementor_Migration_Journal::prepare(
		array(
			'run_id' => 'run-20260917-store-001',
			'post_id' => 123,
			'source_hash_before' => $before_hash,
			'projection_hash' => $projection_hash,
			'recorded_at' => '2026-09-17T15:00:00Z',
			'before' => array(
				'post_content' => $post_content,
				'elementor_data' => $elementor_data,
			),
		)
	);
	assert_store( is_array( $record ), 'prepare returns record' );
	assert_store( true === Elementor_Migration_Journal::validate_record( $record, false ), 'unpersisted record integrity validates' );
	assert_store( Elementor_Migration_Journal::validate_record( $record, true ) instanceof \WP_Error, 'unpersisted record cannot satisfy durable requirement' );

	$prepared_event = Elementor_Migration_Journal_Store::persist_prepared( $record );
	assert_store( is_array( $prepared_event ), 'prepared event persisted' );
	assert_store( true === $prepared_event['record']['journal_persisted'], 'persisted record flag true' );
	assert_store( $post_content === $prepared_event['record']['rollback_payload']['post_content'], 'post_content round-trips byte-exact' );
	assert_store( $elementor_data === $prepared_event['record']['rollback_payload']['elementor_data'], 'elementor_data round-trips byte-exact' );
	assert_store( true === Elementor_Migration_Journal::validate_record( $prepared_event['record'], true ), 'readback persisted record integrity validates' );

	$latest = Elementor_Migration_Journal_Store::latest_for_post( 123 );
	assert_store( is_array( $latest ) && $prepared_event['event_id'] === $latest['event_id'], 'latest_for_post returns prepared event' );

	$applied = Elementor_Migration_Journal::mark_applied( $prepared_event['record'], $after_hash );
	assert_store( is_array( $applied ), 'mark applied succeeds only from persisted prepared record' );
	$applied_event = Elementor_Migration_Journal_Store::persist_transition( $applied, $prepared_event['event_id'] );
	assert_store( is_array( $applied_event ), 'applied transition persisted' );
	assert_store( 'applied' === $applied_event['record']['state'], 'applied state round-trips' );
	assert_store( $prepared_event['event_id'] === $applied_event['parent_event_id'], 'applied event references prepared parent' );

	$rolled = Elementor_Migration_Journal::mark_rolled_back( $applied_event['record'], $before_hash );
	assert_store( is_array( $rolled ), 'mark rolled back succeeds' );
	$rolled_event = Elementor_Migration_Journal_Store::persist_transition( $rolled, $applied_event['event_id'] );
	assert_store( is_array( $rolled_event ), 'rolled-back transition persisted' );
	assert_store( 'rolled_back' === $rolled_event['record']['state'], 'rolled-back state round-trips' );

	$fork_attempt = Elementor_Migration_Journal_Store::persist_transition( $applied, $prepared_event['event_id'] );
	assert_store( $fork_attempt instanceof \WP_Error, 'non-linear/forked transition fails closed' );

	$bad_parent = Elementor_Migration_Journal_Store::persist_transition( $rolled, 999 );
	assert_store( $bad_parent instanceof \WP_Error, 'missing parent fails closed' );

	$tampered = $prepared_event['record'];
	$tampered['rollback_payload']['post_content'] .= 'tamper';
	assert_store( Elementor_Migration_Journal::validate_record( $tampered, true ) instanceof \WP_Error, 'tampered rollback capsule fails integrity' );

	$tampered_hash = $prepared_event['record'];
	$tampered_hash['journal_hash'] = str_repeat( 'f', 64 );
	assert_store( Elementor_Migration_Journal::validate_record( $tampered_hash, true ) instanceof \WP_Error, 'tampered journal hash fails integrity' );

	$GLOBALS['spec004_manage_options'] = false;
	$record2 = Elementor_Migration_Journal::prepare(
		array(
			'run_id' => 'run-20260917-store-002',
			'post_id' => 123,
			'source_hash_before' => $before_hash,
			'projection_hash' => $projection_hash,
			'recorded_at' => '2026-09-17T15:01:00Z',
			'before' => array( 'post_content' => 'x', 'elementor_data' => '[]' ),
		)
	);
	assert_store( Elementor_Migration_Journal_Store::persist_prepared( $record2 ) instanceof \WP_Error, 'manage_options required for durable persistence' );
	$GLOBALS['spec004_manage_options'] = true;

	$latest2 = Elementor_Migration_Journal_Store::latest_for_post( 123 );
	assert_store( is_array( $latest2 ) && $rolled_event['event_id'] === $latest2['event_id'], 'latest_for_post advances to rolled-back event' );

	echo "ALL PASS {$assertions}\n";
}
