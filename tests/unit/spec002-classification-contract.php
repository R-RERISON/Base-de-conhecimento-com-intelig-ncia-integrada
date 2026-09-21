<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/includes/';
$contract = file_get_contents( $root . 'class-classification-contract.php' );
$store = file_get_contents( $root . 'class-classification-store.php' );

foreach ( compact( 'contract', 'store' ) as $name => $content ) {
	if ( ! is_string( $content ) ) {
		fwrite( STDERR, "Falha ao ler {$name}.\n" );
		exit( 1 );
	}
}

$strip_comments = static function ( string $source ): string {
	$tokens = token_get_all( $source );
	$out = '';
	foreach ( $tokens as $token ) {
		if ( is_array( $token ) && in_array( $token[0], array( T_COMMENT, T_DOC_COMMENT ), true ) ) {
			continue;
		}
		$out .= is_array( $token ) ? $token[1] : $token;
	}
	return $out;
};

$store_code = $strip_comments( $store );

$checks = array(
	'post_type_is_post' => str_contains( $contract, "public const POST_TYPE = 'post';" ),
	'max_terms_50' => str_contains( $contract, 'public const MAX_TERMS_PER_FIELD = 50;' ),

	'audience_taxonomy' => str_contains( $contract, "'taxonomy'    => 'bdc_kb_audience'" ),
	'responsible_team_taxonomy' => str_contains( $contract, "'taxonomy'    => 'bdc_kb_responsible_team'" ),
	'knowledge_type_taxonomy' => str_contains( $contract, "'taxonomy'    => 'bdc_kb_knowledge_type'" ),
	'catalog_item_taxonomy' => str_contains( $contract, "'taxonomy'    => 'bdc_kb_catalog_item'" ),
	'exact_four_canonical_taxonomies' => 4 === substr_count( $contract, "'taxonomy'    => 'bdc_kb_" ),

	'audience_multi' => false !== strpos( $contract, "'audience' => array(" )
		&& false !== strpos( $contract, "'multiple'    => true", strpos( $contract, "'audience' => array(" ) ),
	'knowledge_type_single' => false !== strpos( $contract, "'knowledge_type' => array(" )
		&& false !== strpos( $contract, "'multiple'    => false", strpos( $contract, "'knowledge_type' => array(" ) ),

	'private_taxonomies' => str_contains( $contract, "'public'              => false" )
		&& str_contains( $contract, "'publicly_queryable'  => false" ),
	'rest_disabled' => str_contains( $contract, "'show_in_rest'        => false" ),
	'no_rewrite' => str_contains( $contract, "'rewrite'             => false" ),
	'assign_cap_edit_posts' => str_contains( $contract, "'assign_terms' => 'edit_posts'" ),

	'update_requires_object_capability' => str_contains( $store, "current_user_can( 'edit_post', $id )" ),
	'unknown_field_rejected' => str_contains( $store, 'bdc_kb_classification_unknown_field' ),
	'non_array_rejected' => str_contains( $store, 'bdc_kb_classification_invalid_value' ),
	'too_many_terms_rejected' => str_contains( $store, 'bdc_kb_classification_too_many_terms' ),
	'single_cardinality_enforced' => str_contains( $store, 'bdc_kb_classification_single_only' ),
	'taxonomy_must_exist' => str_contains( $store, 'taxonomy_exists( $taxonomy )' ),
	'term_must_exist' => str_contains( $store, 'term_exists( $term_id, $taxonomy )' ),

	'diff_before_write' => false !== strpos( $store, '$diff[ $field ]' )
		&& false !== strpos( $store, 'foreach ( $diff as $field => $term_ids )' )
		&& strpos( $store, '$diff[ $field ]' ) < strpos( $store, 'foreach ( $diff as $field => $term_ids )' ),
	'canonical_writer_only' => str_contains( $store, 'wp_set_object_terms( $post_id, $term_ids, $taxonomy, false )' ),
	'read_after_write' => substr_count( $store, 'self::read_field_values( $id )' ) >= 3,
	'rollback_present' => str_contains( $store, 'self::restore_snapshot' ),
	'fail_safe_status' => str_contains( $store, "STATUS_FAIL_SAFE                = 'FAIL_SAFE'" ),
	'critical_status' => str_contains( $store, "STATUS_PARTIAL_FAILURE_CRITICAL = 'PARTIAL_FAILURE_CRITICAL'" ),

	'no_legacy_meta_write' => 1 !== preg_match( '/update_post_meta\s*\([^;]*(?:_bdc_es_|_kb2ops_)/i', $store_code ),
	'no_editorial_write' => 1 !== preg_match( '/wp_update_post\s*\(|wp_insert_post\s*\(/i', $store_code ),
	'no_network' => 1 !== preg_match( '/wp_remote_[A-Za-z0-9_]*\s*\(|curl_[A-Za-z0-9_]+\s*\(/i', $store_code ),
);

$failed = 0;
foreach ( $checks as $name => $pass ) {
	echo ( $pass ? 'PASS ' : 'FAIL ' ) . $name . PHP_EOL;
	if ( ! $pass ) {
		++$failed;
	}
}

echo PHP_EOL . 'RESULT passed=' . ( count( $checks ) - $failed ) . ' failed=' . $failed . PHP_EOL;
exit( $failed > 0 ? 1 : 0 );
