<?php
/**
 * Contrato estático do Runtime Module Registry / P640-02.
 *
 * @package BDC_Knowledge_Base
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );
$plugin = $root . '/plugin/base-conhecimento-inteligencia-integrada';
$registry_path = $plugin . '/includes/class-runtime-module-registry.php';
$bootstrap_path = $plugin . '/base-conhecimento-inteligencia-integrada.php';

$registry = file_get_contents( $registry_path );
$bootstrap = file_get_contents( $bootstrap_path );

if ( ! is_string( $registry ) || ! is_string( $bootstrap ) ) {
	fwrite( STDERR, 'Unable to read P640-02 files.' . PHP_EOL );
	exit( 1 );
}

$checks = array(
	'registry_class' => str_contains( $registry, 'final class Runtime_Module_Registry' ),
	'no_filesystem_discovery' => ! str_contains( $registry, 'glob(' ) && ! str_contains( $registry, 'RecursiveDirectoryIterator' ),
	'no_reflection' => ! str_contains( $registry, 'ReflectionClass' ),
	'no_database_state' => ! str_contains( $registry, 'get_option(' ) && ! str_contains( $registry, 'update_option(' ),
	'search_module' => str_contains( $registry, "\$definitions['search'] = self::module(" )
		&& str_contains( $registry, "'BDC_KB_SPEC005_G530_SEARCH_ENGINE_BUILD'" )
		&& str_contains( $registry, 'Search_Lifecycle::class' )
		&& str_contains( $registry, 'Search_Anchor_Manager::class' ),
	'public_preview_module' => str_contains( $registry, "\$definitions['public_experience_preview'] = self::module(" )
		&& str_contains( $registry, "'BDC_KB_PUBLIC_EXPERIENCE_PREVIEW_BUILD'" )
		&& str_contains( $registry, 'Public_Search_Facade::class' )
		&& str_contains( $registry, 'Public_Experience::class' ),
	'word_cloud_module' => str_contains( $registry, "\$definitions['word_cloud'] = self::module(" )
		&& str_contains( $registry, "'BDC_KB_WORD_CLOUD_BUILD'" )
		&& str_contains( $registry, 'Word_Cloud_Service::class' )
		&& str_contains( $registry, 'Word_Cloud_Admin::class' ),
	'explicit_missing_file_failure' => str_contains( $registry, 'Arquivo obrigatório do módulo ausente: %s' ),
	'unknown_module_failure' => str_contains( $registry, 'Módulo de runtime desconhecido: %s' ),
	'bootstrap_loads_registry' => str_contains( $bootstrap, "require_once BDC_KB_DIR . 'includes/class-runtime-module-registry.php';" ),
	'bootstrap_routes_search' => str_contains( $bootstrap, "\\BDC\\KnowledgeBase\\Runtime_Module_Registry::load( 'search' );" )
		&& str_contains( $bootstrap, "\\BDC\\KnowledgeBase\\Runtime_Module_Registry::register( 'search' );" ),
	'bootstrap_routes_public_preview' => str_contains( $bootstrap, "\\BDC\\KnowledgeBase\\Runtime_Module_Registry::load( 'public_experience_preview' );" )
		&& str_contains( $bootstrap, "\\BDC\\KnowledgeBase\\Runtime_Module_Registry::register( 'public_experience_preview' );" ),
	'bootstrap_routes_word_cloud' => str_contains( $bootstrap, "\\BDC\\KnowledgeBase\\Runtime_Module_Registry::load( 'word_cloud' );" )
		&& str_contains( $bootstrap, "\\BDC\\KnowledgeBase\\Runtime_Module_Registry::register( 'word_cloud' );" ),
	'bootstrap_no_direct_product_requires' => ! str_contains( $bootstrap, "require_once BDC_KB_DIR . 'includes/class-search-query-normalizer.php';" )
		&& ! str_contains( $bootstrap, "require_once BDC_KB_DIR . 'includes/class-public-experience.php';" )
		&& ! str_contains( $bootstrap, "require_once BDC_KB_DIR . 'includes/class-word-cloud-service.php';" ),
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
