<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}

	final class WP_Error {
		public function __construct( private string $code ) {}
		public function get_error_code(): string { return $this->code; }
	}

	$GLOBALS['bdc_guard_post'] = (object) array(
		'ID' => 42,
		'post_type' => 'post',
		'post_modified_gmt' => '2026-09-16 20:00:00',
	);

	function get_post( int $post_id ): ?object { return 42 === $post_id ? $GLOBALS['bdc_guard_post'] : null; }
}

namespace BDC\KnowledgeBase {
	function is_wp_error( mixed $value ): bool { return $value instanceof \WP_Error; }

	final class Knowledge_Document {
		public static function build( int $post_id ): array|\WP_Error {
			if ( 42 !== $post_id ) { return new \WP_Error( 'invalid' ); }
			return array( 'source_hash' => str_repeat( 'a', 64 ) );
		}
	}

	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-stale-source-guard.php';

	$fresh = Stale_Source_Guard::check( 42, array( 'source_hash_before' => str_repeat( 'a', 64 ), 'post_modified_gmt_before' => '2026-09-16 20:00:00' ) );
	if ( $fresh['status'] !== 'FRESH' || $fresh['write_allowed'] !== false ) { throw new \RuntimeException( 'Fonte fresca deveria ser reconhecida sem autorizar write.' ); }
	$GLOBALS['bdc_guard_post']->post_modified_gmt = '2026-09-16 21:00:00';
	$stale = Stale_Source_Guard::check( 42, array( 'source_hash_before' => str_repeat( 'a', 64 ), 'post_modified_gmt_before' => '2026-09-16 20:00:00' ) );
	if ( $stale['status'] !== 'STALE_SOURCE' ) { throw new \RuntimeException( 'Fonte divergente deveria bloquear aplicação.' ); }

	echo "PASS stale_source_guard\n";
	echo "RESULT passed=1 failed=0\n";
}