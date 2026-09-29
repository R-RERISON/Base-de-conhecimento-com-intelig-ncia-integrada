<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';
$bootstrap = file_get_contents( $root . 'base-conhecimento-inteligencia-integrada.php' );
$service = file_get_contents( $root . 'includes/class-search-service.php' );
$repository = file_get_contents( $root . 'includes/class-search-projection-repository.php' );
$normalizer = file_get_contents( $root . 'includes/class-search-query-normalizer.php' );
$runner = file_get_contents( $root . 'includes/class-search-security-performance-runner-g570.php' );

foreach ( compact( 'bootstrap', 'service', 'repository', 'normalizer', 'runner' ) as $name => $content ) {
	if ( ! is_string( $content ) ) {
		fwrite( STDERR, "Falha ao ler {$name}.\n" );
		exit( 1 );
	}
}

$checks = array(
	'version_g570' => str_contains( $bootstrap, 'Version: 0.5.0-g570.1' ),
	'g530_engine_on' => str_contains( $bootstrap, "BDC_KB_SPEC005_G530_SEARCH_ENGINE_BUILD', true" ),
	'g560_runner_off' => str_contains( $bootstrap, "BDC_KB_SPEC005_G560_SEARCH_UX_RUNNER_BUILD', false" ),
	'g570_runner_on' => str_contains( $bootstrap, "BDC_KB_SPEC005_G570_SECURITY_PERFORMANCE_BUILD', true" ),
	'g570_require_present' => str_contains( $bootstrap, 'class-search-security-performance-runner-g570.php' ),
	'g570_register_present' => str_contains( $bootstrap, 'Search_Security_Performance_Runner_G570::register' ),

	'service_requires_edit_posts' => str_contains( $service, "current_user_can( 'edit_posts' )" ),
	'service_revalidates_edit_post' => str_contains( $service, "current_user_can( 'edit_post', \$post_id )" ),
	'authorization_before_rank' => (
		false !== strpos( $service, '$authorized = self::authorized_documents' )
		&& false !== strpos( $service, 'Lexical_Ranker::rank' )
		&& strpos( $service, '$authorized = self::authorized_documents' ) < strpos( $service, 'Lexical_Ranker::rank' )
	),
	'fallback_perm_editable' => str_contains( $service, "'perm' => 'editable'" ),

	'candidate_cap_200' => str_contains( $repository, 'public const CANDIDATE_CAP = 200;' ),
	'result_cap_50' => str_contains( $service, 'public const MAX_LIMIT = 50;' ),
	'default_limit_20' => str_contains( $service, 'public const DEFAULT_LIMIT = 20;' ),
	'query_chars_256' => str_contains( $normalizer, 'public const MAX_QUERY_CHARS = 256;' ),
	'query_bytes_1024' => str_contains( $normalizer, 'public const MAX_QUERY_BYTES = 1024;' ),
	'query_tokens_16' => str_contains( $normalizer, 'public const MAX_TOKENS = 16;' ),
	'token_chars_128' => str_contains( $normalizer, 'public const MAX_TOKEN_CHARS = 128;' ),
	'repository_prepare' => str_contains( $repository, '$wpdb->prepare' ),
	'repository_esc_like' => str_contains( $repository, '$wpdb->esc_like' ),
	'no_request_globals_repository' => ! str_contains( $repository, '$_GET' ) && ! str_contains( $repository, '$_POST' ),
	'no_fulltext' => 1 !== preg_match( '/\\bFULLTEXT\\b|(?<![A-Za-z0-9_])MATCH\\s*\\(|(?<![A-Za-z0-9_])AGAINST\\s*\\(/i', $repository ),

	'runner_post_nonce' => str_contains( $runner, 'wp_verify_nonce' ) && str_contains( $runner, "'POST' !== strtoupper" ),
	'runner_manage_options' => str_contains( $runner, "current_user_can( 'manage_options' )" ),
	'runner_read_only_projection' => ! str_contains( $runner, 'Search_Projection_Repository::write_state' )
		&& ! str_contains( $runner, 'Search_Projection_Repository::upsert' )
		&& ! str_contains( $runner, 'Search_Projection_Repository::ensure_schema' ),
	'runner_no_editorial_write' => 1 !== preg_match( '/wp_update_post\s*\(|wp_insert_post\s*\(|update_post_meta\s*\(|wp_set_object_terms\s*\(/i', $runner ),
	'runner_no_network' => 1 !== preg_match( '/wp_remote_[A-Za-z0-9_]*\\s*\\(|curl_[A-Za-z0-9_]+\\s*\\(|(?<![A-Za-z0-9_])fsockopen\\s*\\(|(?<![A-Za-z0-9_])stream_socket_client\\s*\\(/i', $runner ),
	'runner_no_asi' => 1 !== preg_match( '/\basi(?:4)?_/i', $runner ),

	'benchmark_repeats_5' => str_contains( $runner, 'public const BENCHMARK_REPEATS = 5;' ),
	'benchmark_warmups_1' => str_contains( $runner, 'public const BENCHMARK_WARMUPS = 1;' ),
	'budget_p95_750' => str_contains( $runner, 'public const PERF_P95_BUDGET_MS = 750.0;' ),
	'budget_max_1500' => str_contains( $runner, 'public const PERF_MAX_BUDGET_MS = 1500.0;' ),
	'benchmark_six_queries' => substr_count( $runner, "'Windows 11'," ) >= 1
		&& substr_count( $runner, "'SCCM'," ) >= 1
		&& substr_count( $runner, "'Termo de assinatura'," ) >= 1
		&& substr_count( $runner, "'essencialmente'," ) >= 1
		&& substr_count( $runner, "'phising'," ) >= 1
		&& substr_count( $runner, "'bdczzzznomatch20260919'," ) >= 1,
	'capability_negative_filters' => str_contains( $runner, "add_filter( 'user_has_cap'" )
		&& str_contains( $runner, "add_filter( 'map_meta_cap'" )
		&& str_contains( $runner, "remove_filter( 'user_has_cap'" )
		&& str_contains( $runner, "remove_filter( 'map_meta_cap'" ),
	'abuse_cases_present' => str_contains( $runner, "'257_chars'" )
		&& str_contains( $runner, "'17_tokens'" )
		&& str_contains( $runner, "'129_char_token'" )
		&& str_contains( $runner, "'html_script_sanitized'" )
		&& str_contains( $runner, "'control_characters_removed'" ),
	'fingerprint_includes_elementor' => str_contains( $runner, "'elementor_data' => get_post_meta" ),
	'fingerprint_includes_taxonomies' => str_contains( $runner, 'Classification_Contract::fields()' ),
	'gate_requires_all_t570_t573' => str_contains( $runner, "'t570_scope_capability_pass'" )
		&& str_contains( $runner, "'t571_sql_bounds_pass'" )
		&& str_contains( $runner, "'t572_abuse_long_query_pass'" )
		&& str_contains( $runner, "'t573_performance_pass'" )
		&& str_contains( $runner, "'t574_g570_pass'" ),
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
