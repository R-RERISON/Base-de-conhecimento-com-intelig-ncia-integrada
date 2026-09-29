<?php
/**
 * Contrato estático do runtime modular / P640-02..05.
 *
 * @package BDC_Knowledge_Base
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );
$plugin = $root . '/plugin/base-conhecimento-inteligencia-integrada';

$read = static function ( string $relative ) use ( $plugin ): string {
	$content = file_get_contents( $plugin . '/' . $relative );
	if ( ! is_string( $content ) ) {
		fwrite( STDERR, 'Unable to read ' . $relative . PHP_EOL );
		exit( 1 );
	}
	return $content;
};

$bootstrap = $read( 'base-conhecimento-inteligencia-integrada.php' );
$core = $read( 'includes/class-core-runtime-loader.php' );
$registry = $read( 'includes/class-runtime-module-registry.php' );
$engineering = $read( 'includes/class-engineering-module-loader.php' );

preg_match_all( "/require_once BDC_KB_DIR \. '([^']+)'/", $bootstrap, $direct_matches );
$direct_requires = $direct_matches[1] ?? array();

$checks = array(
	'composition_root_four_direct_requires' => 4 === count( $direct_requires ),
	'composition_root_loaders_only' => $direct_requires === array(
		'includes/class-core-runtime-loader.php',
		'includes/class-runtime-module-registry.php',
		'includes/class-engineering-module-loader.php',
		'includes/class-plugin.php',
	),
	'core_loader_active' => str_contains( $bootstrap, '\BDC\KnowledgeBase\Core_Runtime_Loader::load();' ),
	'product_modules_routed' => str_contains( $bootstrap, "\BDC\KnowledgeBase\Runtime_Module_Registry::load( 'search' );" )
		&& str_contains( $bootstrap, "\BDC\KnowledgeBase\Runtime_Module_Registry::load( 'public_experience_preview' );" )
		&& str_contains( $bootstrap, "\BDC\KnowledgeBase\Runtime_Module_Registry::load( 'word_cloud' );" ),
	'engineering_loader_routed' => str_contains( $bootstrap, '\BDC\KnowledgeBase\Engineering_Module_Loader::load_enabled();' )
		&& str_contains( $bootstrap, '\BDC\KnowledgeBase\Engineering_Module_Loader::register_enabled();' ),
	'no_runner_requires_in_bootstrap' => ! str_contains( $bootstrap, '-runner-' )
		&& ! str_contains( $bootstrap, '-smoke.php' )
		&& ! str_contains( $bootstrap, '-profiler.php' ),
	'core_loader_no_discovery' => ! str_contains( $core, 'glob(' ) && ! str_contains( $core, 'RecursiveDirectoryIterator' ),
	'product_registry_no_discovery' => ! str_contains( $registry, 'glob(' ) && ! str_contains( $registry, 'RecursiveDirectoryIterator' ),
	'engineering_loader_no_discovery' => ! str_contains( $engineering, 'glob(' ) && ! str_contains( $engineering, 'RecursiveDirectoryIterator' ),
	'no_runtime_database_module_state' => ! str_contains( $registry, 'get_option(' )
		&& ! str_contains( $engineering, 'get_option(' )
		&& ! str_contains( $core, 'get_option(' ),
	'explicit_core_failure' => str_contains( $core, 'Arquivo obrigatório do core ausente:' ),
	'explicit_product_failure' => str_contains( $registry, 'Arquivo obrigatório do módulo ausente:' ),
	'explicit_engineering_failure' => str_contains( $engineering, 'Arquivo obrigatório de engenharia ausente:' ),
	'legacy_elementor_reader_preserved' => str_contains( $core, 'includes/class-elementor-adapter.php' ),
	'core_blocks_activity_preserved' => str_contains( $core, 'includes/class-post-core-blocks-activity.php' ),
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
