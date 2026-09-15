<?php
/**
 * Plugin Name: Base de Conhecimento com Inteligência Integrada
 * Description: Base de Conhecimento com Summary narrativo, Classificação e Review & Governança governados.
 * Version: 0.3.0-dev.5
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: BDC
 * Text Domain: bdc-knowledge-base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BDC_KB_VERSION', '0.3.0-dev.5' );
define( 'BDC_KB_REVIEW_HTTP_DIAGNOSTICS_BUILD', true );
define( 'BDC_KB_FILE', __FILE__ );
define( 'BDC_KB_DIR', plugin_dir_path( __FILE__ ) );
define( 'BDC_KB_URL', plugin_dir_url( __FILE__ ) );

require_once BDC_KB_DIR . 'includes/class-meta-contract.php';
require_once BDC_KB_DIR . 'includes/class-summary-store.php';
require_once BDC_KB_DIR . 'includes/class-classification-contract.php';
require_once BDC_KB_DIR . 'includes/class-classification-store.php';
require_once BDC_KB_DIR . 'includes/class-classification-admin.php';
require_once BDC_KB_DIR . 'includes/class-review-contract.php';
require_once BDC_KB_DIR . 'includes/class-review-store.php';
require_once BDC_KB_DIR . 'includes/class-review-admin.php';
require_once BDC_KB_DIR . 'includes/class-review-http-cache-coherence.php';
require_once BDC_KB_DIR . 'includes/class-review-http-diagnostics.php';
require_once BDC_KB_DIR . 'includes/class-admin-page.php';
require_once BDC_KB_DIR . 'includes/class-plugin.php';

\BDC\KnowledgeBase\Plugin::register();
