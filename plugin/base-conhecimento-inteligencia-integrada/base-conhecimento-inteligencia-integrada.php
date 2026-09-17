<?php
/**
 * Plugin Name: Base de Conhecimento com Inteligência Integrada
 * Description: Base de Conhecimento com Summary narrativo, Classificação, Review & Governança, Content Extractor e Knowledge Document determinísticos.
 * Version: 0.4.0-smoke.2
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: BDC
 * Text Domain: bdc-knowledge-base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BDC_KB_VERSION', '0.4.0-smoke.2' );
define( 'BDC_KB_SPEC004_G245_PROJECTION_PLAN_BUILD', true );
define( 'BDC_KB_SPEC004_G245_ELEMENTOR_GATEWAY_BUILD', true );
define( 'BDC_KB_SPEC004_G245_DRY_RUN_BUILD', true );
define( 'BDC_KB_SPEC004_G245_STALE_GUARD_BUILD', true );
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
require_once BDC_KB_DIR . 'includes/class-content-normalizer.php';
require_once BDC_KB_DIR . 'includes/class-shortcode-inspector.php';
require_once BDC_KB_DIR . 'includes/class-legacy-html-adapter.php';
require_once BDC_KB_DIR . 'includes/class-content-source.php';
require_once BDC_KB_DIR . 'includes/class-elementor-adapter.php';
require_once BDC_KB_DIR . 'includes/class-gutenberg-adapter.php';
require_once BDC_KB_DIR . 'includes/class-content-extractor.php';
require_once BDC_KB_DIR . 'includes/class-canonical-json.php';
require_once BDC_KB_DIR . 'includes/class-knowledge-document.php';
require_once BDC_KB_DIR . 'includes/class-admin-page.php';
require_once BDC_KB_DIR . 'includes/class-plugin.php';

if ( defined( 'BDC_KB_SPEC004_G245_PROJECTION_PLAN_BUILD' ) && BDC_KB_SPEC004_G245_PROJECTION_PLAN_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-projection-plan.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_ELEMENTOR_GATEWAY_BUILD' ) && BDC_KB_SPEC004_G245_ELEMENTOR_GATEWAY_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-elementor-gateway.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_DRY_RUN_BUILD' ) && BDC_KB_SPEC004_G245_DRY_RUN_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-migration-dry-run.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_STALE_GUARD_BUILD' ) && BDC_KB_SPEC004_G245_STALE_GUARD_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-stale-source-guard.php';
}
	if ( defined( 'BDC_KB_SPEC004_G245_ELEMENTOR_GATEWAY_BUILD' ) && BDC_KB_SPEC004_G245_ELEMENTOR_GATEWAY_BUILD ) {
		require_once BDC_KB_DIR . 'includes/class-elementor-projection-builder.php';
	}

\BDC\KnowledgeBase\Plugin::register();
