<?php
/**
 * Plugin Name: Base de Conhecimento com Inteligência Integrada
 * Description: Core mínimo da Base de Conhecimento com Summary narrativo governado.
 * Version: 0.1.0-dev.2
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: BDC
 * Text Domain: bdc-knowledge-base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BDC_KB_VERSION', '0.1.0-dev.2' );
define( 'BDC_KB_FILE', __FILE__ );
define( 'BDC_KB_DIR', plugin_dir_path( __FILE__ ) );
define( 'BDC_KB_URL', plugin_dir_url( __FILE__ ) );

/**
 * Build temporário de homologação da SPEC-001.
 *
 * IMPORTANTE: este marcador e as ferramentas de diagnóstico devem ser removidos
 * integralmente em T044/G-130 antes do package/release final.
 */
define( 'BDC_KB_HOMOLOGATION_BUILD', true );

require_once BDC_KB_DIR . 'includes/class-meta-contract.php';
require_once BDC_KB_DIR . 'includes/class-summary-store.php';
require_once BDC_KB_DIR . 'includes/class-admin-page.php';

if ( defined( 'BDC_KB_HOMOLOGATION_BUILD' ) && true === BDC_KB_HOMOLOGATION_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-diagnostics-runner.php';
	require_once BDC_KB_DIR . 'includes/class-browser-acceptance.php';
}

require_once BDC_KB_DIR . 'includes/class-plugin.php';

\BDC\KnowledgeBase\Plugin::register();
