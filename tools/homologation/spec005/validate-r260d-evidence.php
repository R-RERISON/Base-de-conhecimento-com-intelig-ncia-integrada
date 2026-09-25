<?php

declare(strict_types=1);

if ( $argc < 2 ) {
	fwrite( STDERR, "Uso: php validate-r260d-evidence.php <evidence.json>\n" );
	exit( 2 );
}

$path = (string) $argv[1];
if ( ! is_readable( $path ) ) {
	fwrite( STDERR, "Arquivo não encontrado: {$path}\n" );
	exit( 2 );
}

$raw = file_get_contents( $path );
$data = is_string( $raw ) ? json_decode( $raw, true ) : null;
if ( ! is_array( $data ) ) {
	fwrite( STDERR, "JSON inválido.\n" );
	exit( 2 );
}

$get = static function ( array $source, array $path, mixed $default = null ): mixed {
	$current = $source;
	foreach ( $path as $key ) {
		if ( ! is_array( $current ) || ! array_key_exists( $key, $current ) ) {
			return $default;
		}
		$current = $current[ $key ];
	}
	return $current;
};

$r260c = (array) $get( $data, array( 'coverage', 'r260_discovery', 'shadow_projection', 'anchor_feasibility' ), array() );
$r260d = (array) $get( $data, array( 'coverage', 'r260_discovery', 'shadow_projection', 'contextual_anchor_feasibility' ), array() );
$c_states = (array) ( $r260c['states'] ?? array() );
$d_states = (array) ( $r260d['states'] ?? array() );
$c_promotable = (int) ( $r260c['promotable_count'] ?? -1 );
$d_promotable = (int) ( $r260d['promotable_count'] ?? -2 );
$d_partition = array_sum( array_map( 'intval', $d_states ) );
$d_effective = (int) ( $r260d['effective_unique_count'] ?? -1 );
$d_context_unique = (int) ( $d_states['context_unique'] ?? -1 );
$d_title_unique = (int) ( $d_states['title_unique'] ?? -1 );
$d_remaining = (int) ( $r260d['unresolved_remaining_count'] ?? -1 );

$checks = array(
	'gate_is_g590' => 'G-590' === (string) ( $data['gate'] ?? '' ),
	'mode_is_environmental' => 'spec005_section_retrieval_deeplink_environmental' === (string) ( $data['mode'] ?? '' ),
	'product_version_is_rc10' => '0.5.1-rc.10' === (string) $get( $data, array( 'environment', 'plugin' ), '' ),
	'build_id_is_g590_10' => 1 === preg_match( '/^g590\.10-[a-f0-9]{12}$/', (string) $get( $data, array( 'environment', 'build_id' ), '' ) ),
	'coverage_complete' => (int) $get( $data, array( 'coverage', 'corpus_count' ), -1 )
		=== (int) $get( $data, array( 'coverage', 'posts_analyzed' ), -2 ),
	'no_extractor_errors' => 0 === (int) $get( $data, array( 'coverage', 'extractor_error_count' ), -1 ),
	'r260c_present' => 'r260-anchor-feasibility-v1.0.0' === (string) ( $r260c['version'] ?? '' ),
	'r260d_present' => 'r260-contextual-anchor-feasibility-v1.0.0' === (string) ( $r260d['version'] ?? '' ),
	'promotable_partition_preserved' => $c_promotable > 0
		&& $c_promotable === $d_promotable
		&& $d_promotable === $d_partition,
	'title_unique_inherits_r260c' => (int) ( $c_states['paragraph_unique'] ?? -2 ) === $d_title_unique,
	'no_regression_to_missing_target' => 0 === (int) ( $d_states['title_not_rendered'] ?? -1 ),
	'effective_unique_identity' => $d_effective === $d_title_unique + max( 0, $d_context_unique ),
	'unresolved_identity' => $d_remaining === $d_promotable - $d_effective,
	'inherited_t59014' => true === (bool) $get( $data, array( 'gate_result', 't59014_cross_spec_regression_pass' ), false ),
	't59015_still_not_redefined' => false === (bool) $get( $data, array( 'gate_result', 't59015_coverage_audit_pass' ), true ),
	'inherited_t59016' => true === (bool) $get( $data, array( 'gate_result', 't59016_section_golden_deeplink_pass' ), false ),
	'inherited_t59017' => true === (bool) $get( $data, array( 'gate_result', 't59017_lifecycle_schema_pass' ), false ),
	'inherited_t59018' => true === (bool) $get( $data, array( 'gate_result', 't59018_security_performance_safety_pass' ), false ),
	'g590_still_open' => false === (bool) $get( $data, array( 'gate_result', 't59019_g590_pass' ), true ),
	'performance_pass' => true === (bool) $get( $data, array( 'performance', 'pass' ), false ),
	'editorial_fingerprint_equal' => true === (bool) $get( $data, array( 'safety', 'editorial_fingerprint_equal' ), false ),
	'no_editorial_write' => true === (bool) $get( $data, array( 'safety', 'runtime_source', 'no_editorial_write' ), false ),
	'no_network' => true === (bool) $get( $data, array( 'safety', 'runtime_source', 'no_network' ), false ),
	'no_asi' => true === (bool) $get( $data, array( 'safety', 'runtime_source', 'no_asi' ), false ),
	'no_errors' => empty( $data['errors'] ?? array() ),
	'no_throwables' => empty( $data['throwables'] ?? array() ),
);

$failed = array();
foreach ( $checks as $name => $pass ) {
	echo ( $pass ? 'PASS ' : 'FAIL ' ) . $name . PHP_EOL;
	if ( ! $pass ) {
		$failed[] = $name;
	}
}

echo PHP_EOL . sprintf(
	"R260D promotable=%d title_unique=%d context_unique=%d effective_unique=%d unresolved=%d\n",
	$d_promotable,
	$d_title_unique,
	max( 0, $d_context_unique ),
	$d_effective,
	$d_remaining
);
echo 'RESULT passed=' . ( count( $checks ) - count( $failed ) ) . ' failed=' . count( $failed ) . PHP_EOL;
if ( ! empty( $failed ) ) {
	echo 'FAILED_CHECKS=' . implode( ',', $failed ) . PHP_EOL;
	exit( 1 );
}

exit( 0 );
