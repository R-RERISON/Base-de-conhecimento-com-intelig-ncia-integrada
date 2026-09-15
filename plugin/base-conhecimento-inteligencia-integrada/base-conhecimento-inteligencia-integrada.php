<?php
/**
 * Plugin Name: Base de Conhecimento com Inteligência Integrada
 * Description: Base de Conhecimento com Summary narrativo e Classificação de Conhecimento governados.
 * Version: 0.2.0-dev.2
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: BDC
 * Text Domain: bdc-knowledge-base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BDC_KB_VERSION', '0.2.0-dev.2' );
define( 'BDC_KB_FILE', __FILE__ );
define( 'BDC_KB_DIR', plugin_dir_path( __FILE__ ) );
define( 'BDC_KB_URL', plugin_dir_url( __FILE__ ) );
define( 'BDC_KB_HOMOLOGATION_BUILD', true );

require_once BDC_KB_DIR . 'includes/class-meta-contract.php';
require_once BDC_KB_DIR . 'includes/class-summary-store.php';
require_once BDC_KB_DIR . 'includes/class-classification-contract.php';
require_once BDC_KB_DIR . 'includes/class-classification-store.php';
require_once BDC_KB_DIR . 'includes/class-classification-admin.php';
require_once BDC_KB_DIR . 'includes/class-admin-page.php';
require_once BDC_KB_DIR . 'includes/class-plugin.php';

if ( defined( 'BDC_KB_HOMOLOGATION_BUILD' ) && BDC_KB_HOMOLOGATION_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-classification-diagnostics.php';
}

\BDC\KnowledgeBase\Plugin::register();
