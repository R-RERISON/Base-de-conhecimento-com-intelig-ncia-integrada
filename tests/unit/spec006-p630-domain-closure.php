<?php
/**
 * Static contract checks for SPEC-006 / P-630.
 *
 * @package BDC_Knowledge_Base
 */

declare(strict_types=1);

$root   = dirname( __DIR__, 2 );
$plugin = $root . '/plugin/base-conhecimento-inteligencia-integrada';

$read = static function ( string $relative ) use ( $plugin ): string {
	$contents = file_get_contents( $plugin . '/' . $relative );
	if ( ! is_string( $contents ) ) {
		fwrite( STDERR, 'Unable to read ' . $relative . PHP_EOL );
		exit( 1 );
	}
	return $contents;
};

$contract   = $read( 'includes/class-knowledge-facts-contract.php' );
$store      = $read( 'includes/class-knowledge-facts-store.php' );
$tips       = $read( 'includes/class-helpful-tips-store.php' );
$coverage   = $read( 'includes/class-coverage-read-model.php' );
$details    = $read( 'includes/class-knowledge-details-admin.php' );
$public     = $read( 'includes/class-public-article-read-model.php' );
$plugin_php = $read( 'includes/class-plugin.php' );
$bootstrap  = $read( 'base-conhecimento-inteligencia-integrada.php' );
$registry   = $read( 'includes/class-post-activity-registry.php' );

$checks = array(
	'facts_contract_affected_service_owner' => str_contains( $contract, "'key'           => '_bdc_es_affected_service'" ),
	'facts_contract_service_read_fallback' => str_contains( $contract, "'fallback_keys' => array( '_kb2ops_service' )" ),
	'facts_contract_systems_owner' => str_contains( $contract, "'key'           => '_bdc_es_systems_involved'" ),
	'facts_contract_technologies_owner' => str_contains( $contract, "'key'           => '_kb2ops_technologies'" ),
	'facts_contract_keywords_owner' => str_contains( $contract, "'key'           => '_kb2ops_keywords'" ),
	'facts_contract_versions_owner' => str_contains( $contract, "'key'           => '_kb2ops_versions'" ),
	'facts_store_no_kb2ops_service_write' => ! str_contains( $store, "update_post_meta( $id, '_kb2ops_service'" ),
	'facts_store_read_after_write' => str_contains( $store, 'self::read_key( $id, $definitions[ $field ][\'key\'] ) !== $value' ),
	'facts_store_rollback' => str_contains( $store, 'O estado anterior foi restaurado.' ),
	'tips_owner_physical_key' => str_contains( $tips, "public const META_KEY          = '_bdc_es_helpful_tips';" ),
	'tips_has_update' => str_contains( $tips, 'public static function update(' ),
	'tips_read_after_write' => str_contains( $tips, '$confirmed = self::read( $id );' ),
	'tips_rollback' => str_contains( $tips, 'update_post_meta( $id, self::META_KEY, $snapshot );' ),
	'coverage_has_eight_fields' => 8 === preg_match_all( "/\n\t\t\t'[a-z_]+'.*=>/", $coverage ),
	'coverage_states' => str_contains( $coverage, "public const STATE_EMPTY    = 'EMPTY';" )
		&& str_contains( $coverage, "public const STATE_PARTIAL  = 'PARTIAL';" )
		&& str_contains( $coverage, "public const STATE_COMPLETE = 'COMPLETE';" ),
	'details_nonce' => str_contains( $details, 'wp_verify_nonce' ),
	'details_capability' => str_contains( $details, "current_user_can( 'edit_post', $post_id )" ),
	'details_writes_facts' => str_contains( $details, 'Knowledge_Facts_Store::update' ),
	'details_writes_tips' => str_contains( $details, 'Helpful_Tips_Store::update' ),
	'public_reader_uses_facts_owner' => str_contains( $public, 'Knowledge_Facts_Store::read( $post_id )' ),
	'public_reader_no_direct_legacy_fact_reader' => ! str_contains( $public, 'legacy_meta_string' ),
	'plugin_registers_fact_meta' => str_contains( $plugin_php, "Knowledge_Facts_Contract::class, 'register'" ),
	'plugin_registers_tips_meta' => str_contains( $plugin_php, "Helpful_Tips_Store::class, 'register'" ),
	'bootstrap_loads_domain_owners' => str_contains( $bootstrap, 'class-knowledge-facts-store.php' )
		&& str_contains( $bootstrap, 'class-helpful-tips-store.php' )
		&& str_contains( $bootstrap, 'class-coverage-read-model.php' )
		&& str_contains( $bootstrap, 'class-knowledge-details-admin.php' ),
	'workspace_has_details_activity' => str_contains( $registry, "'details' => array(" ),
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
