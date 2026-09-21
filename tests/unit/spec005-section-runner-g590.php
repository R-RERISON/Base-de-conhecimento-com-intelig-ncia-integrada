<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';
$bootstrap = file_get_contents( $root . 'base-conhecimento-inteligencia-integrada.php' );
$service = file_get_contents( $root . 'includes/class-search-service.php' );
$repository = file_get_contents( $root . 'includes/class-search-projection-repository.php' );
$projector = file_get_contents( $root . 'includes/class-search-section-projector.php' );
$section_ranker = file_get_contents( $root . 'includes/class-search-section-ranker.php' );
$anchor = file_get_contents( $root . 'includes/class-search-anchor-manager.php' );
$runner = file_get_contents( $root . 'includes/class-search-section-runner-g590.php' );
$golden_loader = file_get_contents( $root . 'includes/class-golden-suite-loader.php' );
$parent_ranker = file_get_contents( $root . 'includes/class-lexical-ranker.php' );

foreach ( compact( 'bootstrap', 'service', 'repository', 'projector', 'section_ranker', 'anchor', 'runner', 'golden_loader', 'parent_ranker' ) as $name => $content ) {
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

$runner_code = $strip_comments( $runner );
$anchor_code = $strip_comments( $anchor );
$projector_code = $strip_comments( $projector );
$service_code = $strip_comments( $service );

$query_start = strpos( $repository, 'private static function query_candidates' );
$section_start = strpos( $repository, 'public static function sections_for_posts' );
$candidate_source = false !== $query_start
	? substr( $repository, $query_start, false !== $section_start ? $section_start - $query_start : null )
	: '';

$checks = array(
	'g590_flag_declared' => str_contains( $bootstrap, "define( 'BDC_KB_SPEC005_G590_SECTION_BUILD'," ),
	'g590_runner_require_guarded' => str_contains( $bootstrap, "BDC_KB_SPEC005_G590_SECTION_BUILD" )
		&& str_contains( $bootstrap, "class-search-section-runner-g590.php" ),
	'g590_runner_register_guarded' => str_contains( $bootstrap, 'Search_Section_Runner_G590::register' ),

	'canonical_section_facade_exists' => str_contains( $service, 'public static function search_sections' ),
	'canonical_section_facade_reuses_parent_search' => str_contains( $service_code, '$parents = self::search( $query_value, $parent_limit )' ),
	'canonical_section_facade_does_not_bypass_repository' => ! str_contains( $service_code, 'SELECT ' ),

	'single_search_table_preserved' => substr_count( $repository, "bdc_kb_search_documents" ) >= 1
		&& ! str_contains( $repository, 'bdc_kb_search_sections' ),
	'schema_1_1' => str_contains( $repository, "public const SCHEMA_VERSION = '1.1.0';" ),
	'schema_contract_runtime' => str_contains( $repository, 'public static function schema_contract' )
		&& str_contains( $repository, "REQUIRED_INDEXES = array( 'PRIMARY', 'document_state', 'document_version', 'source_hash' )" ),
	'sections_json_column' => str_contains( $repository, 'sections_json LONGTEXT NOT NULL' ),
	'section_version_column' => str_contains( $repository, 'section_projection_version VARCHAR(32) NOT NULL' ),
	'candidate_query_does_not_load_sections' => ! str_contains( $candidate_source, 'sections_json' ),
	'section_parent_cap_20' => str_contains( $repository, 'public const SECTION_PARENT_CAP = 20;' ),
	'section_cap_64' => str_contains( $projector, 'public const MAX_SECTIONS = 64;' ),
	'section_text_cap_4000' => str_contains( $projector, 'public const MAX_TEXT_CHARS = 4000;' ),
	'section_result_cap_5' => str_contains( $section_ranker, 'min( 5, $limit )' ),

	'duplicate_heading_fail_closed' => str_contains( $projector, "'anchor_state'] = 'unresolved'" )
		&& str_contains( $projector, "'anchor_id'] = ''" ),
	'anchor_does_not_replace_editorial_id' => ! str_contains( $anchor_code, 'preg_replace' )
		&& str_contains( $anchor, '<span id="' ),
	'anchor_runtime_unique_match' => str_contains( $anchor, '1 !== count( $candidates )' ),
	'anchor_no_editorial_write' => 1 !== preg_match( '/wp_update_post\s*\(|wp_insert_post\s*\(|update_post_meta\s*\(|delete_post_meta\s*\(/i', $anchor_code ),

	'parent_ranker_version_frozen' => str_contains( $parent_ranker, "public const VERSION = 'lexical-ranker-v1.0.0';" ),
	'golden_document_v11_addendum_applied' => str_contains( $golden_loader, "EXPECTED_DOCUMENT_VERSION = 'search-document-v1.1.0'" ),
	'golden_parent_algorithm_still_v1' => str_contains( $golden_loader, "EXPECTED_ALGORITHM_VERSION = 'lexical-ranker-v1.0.0'" ),
	'golden_result_still_v1' => str_contains( $golden_loader, "EXPECTED_RESULT_VERSION = 'search-result-v1.0.0'" ),

	'runner_post_nonce' => str_contains( $runner, 'wp_verify_nonce' )
		&& str_contains( $runner, "'POST' !== strtoupper" ),
	'runner_manage_options' => str_contains( $runner, "current_user_can( 'manage_options' )" ),
	'runner_explicit_rebuild' => str_contains( $runner, 'Search_Rebuild_Service::rebuild' ),
	'runner_runs_closed_golden' => str_contains( $runner, 'Golden_Gate_Runner_G550::run' ),
	'runner_editorial_fingerprint' => str_contains( $runner, "'editorial_fingerprint_before'" )
		&& str_contains( $runner, "'editorial_fingerprint_after'" ),
	'runner_numbered_gap_detection' => str_contains( $runner, 'numbered_without_heading_context' )
		&& str_contains( $runner, 'Numbered_Hierarchy_Resolver::resolve' ),
	'runner_performance_budget' => str_contains( $runner, 'PERF_P95_BUDGET_MS = 900.0' )
		&& str_contains( $runner, 'PERF_MAX_BUDGET_MS = 1500.0' )
		&& str_contains( $runner, 'performance_benchmark' ),
	'runner_lifecycle_transition' => str_contains( $runner, 'state_versions_before_current' )
		&& str_contains( $runner, 'version_transition_safe' ),
	'runner_deep_link_materialization' => str_contains( $runner, 'Search_Anchor_Manager::inject_for_sections' )
		&& str_contains( $runner, "'anchor_materialized'" ),
	'runner_section_query_probe' => str_contains( $runner, 'Search_Service::search_sections' )
		&& str_contains( $runner, "'section_query_found_expected'" ),
	'runner_no_editorial_write' => 1 !== preg_match( '/wp_update_post\s*\(|wp_insert_post\s*\(|update_post_meta\s*\(|delete_post_meta\s*\(|wp_set_object_terms\s*\(/i', $runner_code ),
	'runner_no_external_network' => 1 !== preg_match( '/wp_remote_[A-Za-z0-9_]*\s*\(|curl_[A-Za-z0-9_]+\s*\(|(?<![A-Za-z0-9_])fsockopen\s*\(|(?<![A-Za-z0-9_])stream_socket_client\s*\(/i', $runner_code ),
	'runner_gate_t59014_t59019' => str_contains( $runner, "'t59014_cross_spec_regression_pass'" )
		&& str_contains( $runner, "'t59015_coverage_audit_pass'" )
		&& str_contains( $runner, "'t59016_section_golden_deeplink_pass'" )
		&& str_contains( $runner, "'t59017_lifecycle_schema_pass'" )
		&& str_contains( $runner, "'t59018_security_performance_safety_pass'" )
		&& str_contains( $runner, "'t59019_g590_pass'" ),
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
