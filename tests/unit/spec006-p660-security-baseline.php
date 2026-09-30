<?php
/**
 * Baseline estático de segurança P-660.
 *
 * @package BDC_Knowledge_Base
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );
$plugin = $root . '/plugin/base-conhecimento-inteligencia-integrada';

$auth = file_get_contents( $plugin . '/includes/class-public-auth-bridge.php' );
$admin = file_get_contents( $plugin . '/includes/class-admin-page.php' );
$classify = file_get_contents( $plugin . '/includes/class-classification-admin.php' );
$details = file_get_contents( $plugin . '/includes/class-knowledge-details-admin.php' );
$review = file_get_contents( $plugin . '/includes/class-review-admin.php' );
$word_cloud = file_get_contents( $plugin . '/includes/class-word-cloud-consultations.php' );
$search_repo = file_get_contents( $plugin . '/includes/class-search-projection-repository.php' );
$public_search = file_get_contents( $plugin . '/includes/class-public-search-facade.php' );
$public_experience = file_get_contents( $plugin . '/includes/class-public-experience.php' );
$core_blocks = file_get_contents( $plugin . '/includes/class-post-core-blocks-activity.php' );

$files = array( $auth, $admin, $classify, $details, $review, $word_cloud, $search_repo, $public_search, $public_experience, $core_blocks );
if ( in_array( false, $files, true ) ) {
	fwrite( STDERR, 'Unable to read P660 baseline files.' . PHP_EOL );
	exit( 1 );
}

$mutation_files = array( $admin, $classify, $details, $review );
$mutation_contract = true;
foreach ( $mutation_files as $source ) {
	$mutation_contract = $mutation_contract
		&& str_contains( $source, "'POST' !== strtoupper" )
		&& str_contains( $source, 'current_user_can' )
		&& str_contains( $source, 'wp_verify_nonce' )
		&& str_contains( $source, 'wp_unslash' );
}

$checks = array(
	'no_http_host_trust_in_public_auth' => ! str_contains( $auth, "\$_SERVER['HTTP_HOST']" ),
	'public_auth_uses_home_url' => str_contains( $auth, 'return home_url( $path . $query );' ),
	'core_mutations_post_capability_nonce_unslash' => $mutation_contract,
	'word_cloud_ajax_nonce_capability' => str_contains( $word_cloud, 'check_ajax_referer' )
		&& str_contains( $word_cloud, "current_user_can( 'manage_options' )" ),
	'word_cloud_dynamic_delete_prepared' => str_contains( $word_cloud, '$wpdb->prepare(' ),
	'search_dynamic_queries_prepared' => str_contains( $search_repo, '$wpdb->prepare(' ),
	'no_curl_runtime' => ! str_contains( implode( "\n", array_map( 'strval', $files ) ), 'curl_' ),
	'public_search_preview_sanitized' => str_contains( $public_search, "sanitize_key( wp_unslash( (string) \$_POST['preview'] ) )" ),
	'public_search_remote_addr_sanitized' => str_contains( $public_search, "sanitize_text_field( wp_unslash( (string) \$_SERVER['REMOTE_ADDR'] ) )" ),
	'public_preview_nonce_sanitized' => str_contains( $public_experience, "sanitize_text_field( wp_unslash( (string) \$_GET[ self::NONCE_KEY ] ) )" ),
	'core_blocks_nonce_sanitized' => str_contains( $core_blocks, "sanitize_text_field( wp_unslash( (string) \$_POST[ self::NONCE_FIELD ] ) )" ),
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
