<?php

declare(strict_types=1);

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "CLI only.\n" );
	exit( 2 );
}

$path = $argv[1] ?? '';
$expectedCount = isset( $argv[2] ) ? (int) $argv[2] : 622;
if ( '' === $path || $expectedCount <= 0 ) {
	fwrite( STDERR, "Usage: php validate-g245-projection-evidence.php <report.json> [expected-corpus-count]\n" );
	exit( 2 );
}
if ( ! is_file( $path ) || ! is_readable( $path ) ) {
	fwrite( STDERR, "Evidence file not readable: {$path}\n" );
	exit( 2 );
}

$raw = file_get_contents( $path );
if ( false === $raw ) {
	fwrite( STDERR, "Failed to read evidence file.\n" );
	exit( 2 );
}

try {
	$report = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );
} catch ( JsonException $error ) {
	fwrite( STDERR, "Invalid JSON: {$error->getMessage()}\n" );
	exit( 2 );
}
if ( ! is_array( $report ) ) {
	fwrite( STDERR, "Evidence root must be a JSON object.\n" );
	exit( 2 );
}

$get = static function ( array $source, string $path, mixed $default = null ): mixed {
	$value = $source;
	foreach ( explode( '.', $path ) as $segment ) {
		if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
			return $default;
		}
		$value = $value[ $segment ];
	}
	return $value;
};

$checks = array();
$check = static function ( string $name, bool $passed, mixed $actual, mixed $expected ) use ( &$checks ): void {
	$checks[] = array(
		'name' => $name,
		'passed' => $passed,
		'actual' => $actual,
		'expected' => $expected,
	);
};

$check( 'schema_version', '1.1.0' === $get( $report, 'schema_version' ), $get( $report, 'schema_version' ), '1.1.0' );
$check( 'mode', 'spec004_g245_projection_plan_read_only_smoke' === $get( $report, 'mode' ), $get( $report, 'mode' ), 'spec004_g245_projection_plan_read_only_smoke' );
$check( 'projection_plan_schema', '1.0.0' === $get( $report, 'environment.projection_plan_schema' ), $get( $report, 'environment.projection_plan_schema' ), '1.0.0' );
$check( 'matrix_version', 'g245-compatibility-matrix-v1' === $get( $report, 'environment.matrix_version' ), $get( $report, 'environment.matrix_version' ), 'g245-compatibility-matrix-v1' );
$check( 'total_posts', $expectedCount === (int) $get( $report, 'plans.total_posts', -1 ), $get( $report, 'plans.total_posts' ), $expectedCount );
$check( 'first_pass_plans', $expectedCount === (int) $get( $report, 'plans.first_pass_plans', -1 ), $get( $report, 'plans.first_pass_plans' ), $expectedCount );
$check( 'second_pass_plans', $expectedCount === (int) $get( $report, 'plans.second_pass_plans', -1 ), $get( $report, 'plans.second_pass_plans' ), $expectedCount );

foreach ( array(
	'plans.first_pass_errors',
	'plans.second_pass_errors',
	'plans.first_pass_throwables',
	'plans.second_pass_throwables',
	'plans.projection_hash_mismatches',
	'plans.canonical_json_mismatches',
	'plans.first_pass_projection_hash_violations',
	'plans.second_pass_projection_hash_violations',
	'plans.first_pass_writer_allowed_violations',
	'plans.second_pass_writer_allowed_violations',
	'plans.first_pass_safety_violations',
	'plans.second_pass_safety_violations',
	'plans.first_pass_legacy_shortcode_review_violations',
	'plans.second_pass_legacy_shortcode_review_violations',
	'plans.first_pass_migration_warning_review_violations',
	'plans.second_pass_migration_warning_review_violations',
	'safety.changed_posts_during_run',
) as $zeroPath ) {
	$actual = $get( $report, $zeroPath, null );
	$check( $zeroPath, 0 === $actual, $actual, 0 );
}

$check( 'safety.read_only_design', true === $get( $report, 'safety.read_only_design' ), $get( $report, 'safety.read_only_design' ), true );
$check( 'safety.exports_editorial_content', false === $get( $report, 'safety.exports_editorial_content' ), $get( $report, 'safety.exports_editorial_content' ), false );
$check( 'safety.exports_post_ids', false === $get( $report, 'safety.exports_post_ids' ), $get( $report, 'safety.exports_post_ids' ), false );
$check( 'safety.exports_titles_or_urls', false === $get( $report, 'safety.exports_titles_or_urls' ), $get( $report, 'safety.exports_titles_or_urls' ), false );
$check( 'safety.persists_plans', false === $get( $report, 'safety.persists_plans' ), $get( $report, 'safety.persists_plans' ), false );
$check( 'safety.writer_allowed', false === $get( $report, 'safety.writer_allowed' ), $get( $report, 'safety.writer_allowed' ), false );
$check( 'safety.migration_execution_allowed', false === $get( $report, 'safety.migration_execution_allowed' ), $get( $report, 'safety.migration_execution_allowed' ), false );
$check( 'safety.corpus_count_before', $expectedCount === (int) $get( $report, 'safety.corpus_count_before', -1 ), $get( $report, 'safety.corpus_count_before' ), $expectedCount );
$check( 'safety.corpus_count_after', $expectedCount === (int) $get( $report, 'safety.corpus_count_after', -1 ), $get( $report, 'safety.corpus_count_after' ), $expectedCount );
$check( 'safety.corpus_unchanged', true === $get( $report, 'safety.corpus_unchanged' ), $get( $report, 'safety.corpus_unchanged' ), true );
$check( 'safety.editorial_fingerprint_equal', true === $get( $report, 'safety.editorial_fingerprint_equal' ), $get( $report, 'safety.editorial_fingerprint_equal' ), true );
$check( 'gate.t081_pass', true === $get( $report, 'gate.t081_pass' ), $get( $report, 'gate.t081_pass' ), true );

$beforeFingerprint = (string) $get( $report, 'safety.editorial_fingerprint_before', '' );
$afterFingerprint = (string) $get( $report, 'safety.editorial_fingerprint_after', '' );
$check( 'editorial_fingerprint_format_before', 1 === preg_match( '/^[a-f0-9]{64}$/', $beforeFingerprint ), $beforeFingerprint, '64-char lowercase SHA-256' );
$check( 'editorial_fingerprint_format_after', 1 === preg_match( '/^[a-f0-9]{64}$/', $afterFingerprint ), $afterFingerprint, '64-char lowercase SHA-256' );
$check( 'editorial_fingerprint_exact_match', '' !== $beforeFingerprint && hash_equals( $beforeFingerprint, $afterFingerprint ), $afterFingerprint, $beforeFingerprint );

$failed = array_values( array_filter( $checks, static fn ( array $item ): bool => ! $item['passed'] ) );
$summary = array(
	'schema_version' => '1.0.0',
	'mode' => 'spec004_g245_projection_evidence_validation',
	'evidence_file' => basename( $path ),
	'evidence_sha256' => hash( 'sha256', $raw ),
	'expected_corpus_count' => $expectedCount,
	'checks_total' => count( $checks ),
	'checks_passed' => count( $checks ) - count( $failed ),
	'checks_failed' => count( $failed ),
	'gate_pass' => 0 === count( $failed ),
	'failed_checks' => $failed,
);

echo json_encode( $summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR ) . PHP_EOL;
exit( $summary['gate_pass'] ? 0 : 1 );
