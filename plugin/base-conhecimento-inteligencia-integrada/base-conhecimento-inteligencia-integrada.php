<?php
/**
 * Plugin Name: Base de Conhecimento com Inteligência Integrada
 * Description: Base de Conhecimento com Summary narrativo, Classificação, Review & Governança, Content Extractor e Knowledge Document determinísticos.
 * Version: 0.4.0-acceptance.5
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: BDC
 * Text Domain: bdc-knowledge-base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BDC_KB_VERSION', '0.4.0-acceptance.5' );
define( 'BDC_KB_SPEC004_PROFILE_BUILD', false );
define( 'BDC_KB_SPEC004_G220_SMOKE_BUILD', false );
define( 'BDC_KB_SPEC004_G230_SMOKE_BUILD', false );
define( 'BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD', true );
define( 'BDC_KB_SPEC004_KD_V2_SMOKE_BUILD', true );
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
require_once BDC_KB_DIR . 'includes/class-semantic-structure.php';
require_once BDC_KB_DIR . 'includes/class-knowledge-document.php';
require_once BDC_KB_DIR . 'includes/class-admin-page.php';
require_once BDC_KB_DIR . 'includes/class-plugin.php';

if ( defined( 'BDC_KB_SPEC004_PROFILE_BUILD' ) && BDC_KB_SPEC004_PROFILE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-content-profile.php';
}
if ( defined( 'BDC_KB_SPEC004_G220_SMOKE_BUILD' ) && BDC_KB_SPEC004_G220_SMOKE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-content-extractor-smoke.php';
}
if ( defined( 'BDC_KB_SPEC004_G230_SMOKE_BUILD' ) && BDC_KB_SPEC004_G230_SMOKE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-knowledge-document-smoke.php';
}
if ( defined( 'BDC_KB_SPEC004_KD_V2_SMOKE_BUILD' ) && BDC_KB_SPEC004_KD_V2_SMOKE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-knowledge-document-v2-smoke.php';
}
if ( defined( 'BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD' ) && BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-real-content-acceptance-v2.php';
}

\BDC\KnowledgeBase\Plugin::register();
if ( defined( 'BDC_KB_SPEC004_PROFILE_BUILD' ) && BDC_KB_SPEC004_PROFILE_BUILD ) {
	\BDC\KnowledgeBase\Content_Profile::register();
}
if ( defined( 'BDC_KB_SPEC004_G220_SMOKE_BUILD' ) && BDC_KB_SPEC004_G220_SMOKE_BUILD ) {
	\BDC\KnowledgeBase\Content_Extractor_Smoke::register();
}
if ( defined( 'BDC_KB_SPEC004_G230_SMOKE_BUILD' ) && BDC_KB_SPEC004_G230_SMOKE_BUILD ) {
	\BDC\KnowledgeBase\Knowledge_Document_Smoke::register();
}
if ( defined( 'BDC_KB_SPEC004_KD_V2_SMOKE_BUILD' ) && BDC_KB_SPEC004_KD_V2_SMOKE_BUILD ) {
	\BDC\KnowledgeBase\Knowledge_Document_V2_Smoke::register();
}
if ( defined( 'BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD' ) && BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD ) {
	\BDC\KnowledgeBase\Real_Content_Acceptance_V2::register();
}
