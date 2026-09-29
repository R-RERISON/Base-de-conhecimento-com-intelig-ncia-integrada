<?php
/**
 * Plugin Name: Base de Conhecimento com Inteligência Integrada
 * Description: Base de Conhecimento com sumário, classificação, revisão, governança, estrutura editorial e recursos de inteligência integrados.
 * Plugin URI: https://github.com/R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada
 * Version: 0.6.0-dev
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: BDC
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://github.com/R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada
 * Text Domain: bdc-knowledge-base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BDC_KB_VERSION', '0.6.0-dev' );
define( 'BDC_KB_SPEC004_PROFILE_BUILD', false );
define( 'BDC_KB_SPEC004_G220_SMOKE_BUILD', false );
define( 'BDC_KB_SPEC004_G230_SMOKE_BUILD', false );
define( 'BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD', false );
define( 'BDC_KB_SPEC004_KD_V2_SMOKE_BUILD', false );
define( 'BDC_KB_SPEC004_FINAL_DIAG_BUILD', false );
define( 'BDC_KB_SPEC004_PIPELINE_DIAG_BUILD', false );
define( 'BDC_KB_SPEC004_G245_PREFLIGHT_BUILD', false );
define( 'BDC_KB_SPEC004_G245_PROJECTION_SMOKE_BUILD', false );
define( 'BDC_KB_SPEC004_G245_JOURNAL_SMOKE_BUILD', false );
define( 'BDC_KB_SPEC004_G245_BLOCK_PROJECTION_SMOKE_BUILD', false );
define( 'BDC_KB_SPEC004_G245_EDITORIAL_FIDELITY_SMOKE_BUILD', false );
define( 'BDC_KB_SPEC004_G245_LOSSLESS_ROUNDTRIP_SMOKE_BUILD', false );
define( 'BDC_KB_SPEC004_G245_EDITORIAL_PARITY_SMOKE_BUILD', false );
define( 'BDC_KB_SPEC004_G245_BLOCK_MIGRATION_READINESS_SMOKE_BUILD', false );
define( 'BDC_KB_SPEC004_G245_STORAGE_LOCK_SMOKE_BUILD', false );
define( 'BDC_KB_SPEC004_G245_AUTHORIZATION_PACK_SMOKE_BUILD', false );
define( 'BDC_KB_SPEC004_G245_T099C_CANARY_BUILD', false );
define( 'BDC_KB_SPEC004_G245_T100A_BATCH_AUTHORIZATION_PACK_BUILD', false );
define( 'BDC_KB_SPEC004_G245_T100A_POST_WORKSPACE_BUILD', true );
define( 'BDC_KB_SPEC004_G245_T100C_CORE_BLOCKS_ACTIVITY_BUILD', true );
define( 'BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD', false );
define( 'BDC_KB_SPEC004_T100E_E6_REGRESSION_BUILD', false );
define( 'BDC_KB_SPEC004_G250_LIFECYCLE_BUILD', false );
define( 'BDC_KB_SPEC005_R500_DIAGNOSTIC_BUILD', false );
define( 'BDC_KB_SPEC005_R510_LEGACY_GOLDEN_BUILD', false );
define( 'BDC_KB_SPEC005_R510_GOLDEN_BASELINE_BUILD', false );
define( 'BDC_KB_SPEC005_R510_GOLDEN_AUTO_VALIDATOR_BUILD', false );
define( 'BDC_KB_SPEC005_G530_SEARCH_ENGINE_BUILD', true );
define( 'BDC_KB_SPEC005_G540_CORPUS_RUNNER_BUILD', false );
define( 'BDC_KB_SPEC005_G550_GOLDEN_RUNNER_BUILD', false );
define( 'BDC_KB_SPEC005_G560_SEARCH_UX_RUNNER_BUILD', false );
define( 'BDC_KB_SPEC005_G570_SECURITY_PERFORMANCE_BUILD', false );
define( 'BDC_KB_SPEC005_G580_LIFECYCLE_BUILD', false );
define( 'BDC_KB_SPEC005_G585_ASI_INDEPENDENCE_BUILD', false );
define( 'BDC_KB_SPEC005_G590_SECTION_BUILD', false );
define( 'BDC_KB_P580_PUBLIC_INVENTORY_BUILD', false );
define( 'BDC_KB_PUBLIC_EXPERIENCE_PREVIEW_BUILD', true );
define( 'BDC_KB_UX004_H030_TECHNICAL_BUILD', true );
define( 'BDC_KB_WORD_CLOUD_BUILD', true );
define( 'BDC_KB_SPEC006_P630_ENVIRONMENTAL_BUILD', false );
if ( ! defined( 'BDC_KB_SEARCH_ENABLED' ) ) {
	define( 'BDC_KB_SEARCH_ENABLED', true );
}
if ( ! defined( 'BDC_KB_ELEMENTOR_WRITER_ENABLED' ) ) {
	define( 'BDC_KB_ELEMENTOR_WRITER_ENABLED', false );
}
define( 'BDC_KB_FILE', __FILE__ );
define( 'BDC_KB_DIR', plugin_dir_path( __FILE__ ) );
define( 'BDC_KB_URL', plugin_dir_url( __FILE__ ) );

require_once BDC_KB_DIR . 'includes/class-core-runtime-loader.php';
require_once BDC_KB_DIR . 'includes/class-runtime-module-registry.php';
require_once BDC_KB_DIR . 'includes/class-engineering-module-loader.php';
require_once BDC_KB_DIR . 'includes/class-plugin.php';

\BDC\KnowledgeBase\Core_Runtime_Loader::load();
\BDC\KnowledgeBase\Runtime_Module_Registry::load( 'word_cloud' );

\BDC\KnowledgeBase\Runtime_Module_Registry::load( 'search' );
\BDC\KnowledgeBase\Engineering_Module_Loader::load_enabled();
\BDC\KnowledgeBase\Runtime_Module_Registry::load( 'public_experience_preview' );

\BDC\KnowledgeBase\Plugin::register();
\BDC\KnowledgeBase\Visual_Foundation::register();
\BDC\KnowledgeBase\Runtime_Module_Registry::register( 'word_cloud' );
\BDC\KnowledgeBase\Runtime_Module_Registry::register( 'search' );
\BDC\KnowledgeBase\Engineering_Module_Loader::register_enabled();
\BDC\KnowledgeBase\Runtime_Module_Registry::register( 'public_experience_preview' );