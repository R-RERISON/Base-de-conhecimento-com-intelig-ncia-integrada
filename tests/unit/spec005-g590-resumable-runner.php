<?php
/**
 * Structural contract for the resumable G-590 environmental runner.
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );
$runner_path = $root . '/plugin/base-conhecimento-inteligencia-integrada/includes/class-search-section-runner-g590.php';
$runner = file_get_contents( $runner_path );

if ( ! is_string( $runner ) ) {
	fwrite( STDERR, "Falha ao ler runner G-590.\n" );
	exit( 1 );
}

$code = preg_replace( '#/\*.*?\*/#s', '', $runner ) ?? $runner;
$code = preg_replace( '#//.*$#m', '', $code ) ?? $code;

$checks = array(
	'job_version_contract' => str_contains( $runner, "JOB_VERSION = 'g590-resumable-v1.0.0'" ),
	'durable_job_option' => str_contains( $runner, "JOB_OPTION = 'bdc_kb_spec005_g590_job'" )
		&& str_contains( $runner, 'update_option( self::JOB_OPTION, $job, false )' ),
	'bounded_snapshot_batch' => str_contains( $runner, 'SNAPSHOT_BATCH_SIZE = 50' )
		&& str_contains( $runner, 'array_slice( $post_ids, $cursor, self::SNAPSHOT_BATCH_SIZE )' ),
	'bounded_coverage_batch' => str_contains( $runner, 'COVERAGE_BATCH_SIZE = 25' )
		&& str_contains( $runner, 'array_slice( $post_ids, $cursor, self::COVERAGE_BATCH_SIZE )' ),
	'ajax_start_step_status' => str_contains( $runner, "wp_ajax_' . self::AJAX_START" )
		&& str_contains( $runner, "wp_ajax_' . self::AJAX_STEP" )
		&& str_contains( $runner, "wp_ajax_' . self::AJAX_STATUS" ),
	'ajax_nonce_and_capability' => str_contains( $runner, 'check_ajax_referer( self::AJAX_NONCE_ACTION' )
		&& str_contains( $runner, "current_user_can( 'manage_options' )" ),
	'step_lock' => str_contains( $runner, 'JOB_LOCK_PREFIX' )
		&& str_contains( $runner, 'get_transient( $lock )' )
		&& str_contains( $runner, 'set_transient( $lock' )
		&& str_contains( $runner, 'delete_transient( $lock )' ),
	'resume_after_network_failure' => str_contains( $runner, 'Consultando estado persistido para retomar sem duplicar trabalho' )
		&& str_contains( $runner, 'return recover(jobId)' ),
	'synchronous_admin_post_not_registered' => ! str_contains(
		$runner,
		"add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) )"
	),
	'rebuild_remains_explicit' => str_contains( $runner, "case 'preflight_rebuild':" )
		&& str_contains( $runner, 'Search_Rebuild_Service::rebuild()' ),
	'coverage_semantics_preserved' => str_contains( $runner, 'coverage_accumulator_empty' )
		&& str_contains( $runner, 'coverage_accumulate' )
		&& str_contains( $runner, 'coverage_finalize' )
		&& str_contains( $runner, "'title_plus_rare_section_token'" ),
	'download_requires_complete_job' => str_contains( $runner, "'complete' !== (string) ( \$job['status'] ?? '' )" )
		&& str_contains( $runner, "self::DOWNLOAD_ACTION . '_' . \$job_id" ),
	'report_marks_resumable_model' => str_contains( $runner, "'execution_model' => 'resumable_ajax_v1'" )
		&& str_contains( $runner, "'orchestration' => self::JOB_VERSION" ),
	'no_editorial_write' => 1 !== preg_match(
		'/wp_update_post\s*\(|wp_insert_post\s*\(|update_post_meta\s*\(|delete_post_meta\s*\(|wp_set_object_terms\s*\(/i',
		$code
	),
	'no_external_network' => 1 !== preg_match(
		'/wp_remote_[A-Za-z0-9_]*\s*\(|curl_[A-Za-z0-9_]+\s*\(|(?<![A-Za-z0-9_])fsockopen\s*\(|(?<![A-Za-z0-9_])stream_socket_client\s*\(/i',
		$code
	),
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
