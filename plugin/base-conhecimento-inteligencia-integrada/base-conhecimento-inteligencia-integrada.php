<?php
/**
 * Plugin Name: Base de Conhecimento com Inteligência Integrada
 * Description: Base de Conhecimento com sumário, classificação, revisão, governança, estrutura editorial e recursos de inteligência integrados.
 * Version: 0.5.0-h030.1
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: BDC
 * Text Domain: bdc-knowledge-base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BDC_KB_VERSION', '0.5.0-h030.1' );
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
if ( ! defined( 'BDC_KB_SEARCH_ENABLED' ) ) {
	define( 'BDC_KB_SEARCH_ENABLED', true );
}
if ( ! defined( 'BDC_KB_ELEMENTOR_WRITER_ENABLED' ) ) {
	define( 'BDC_KB_ELEMENTOR_WRITER_ENABLED', false );
}
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
require_once BDC_KB_DIR . 'includes/class-hierarchy-relationships.php';
require_once BDC_KB_DIR . 'includes/class-numbered-hierarchy-resolver.php';
require_once BDC_KB_DIR . 'includes/class-legacy-html-adapter.php';
require_once BDC_KB_DIR . 'includes/class-semantic-dom-expectation.php';
require_once BDC_KB_DIR . 'includes/class-content-source.php';
require_once BDC_KB_DIR . 'includes/class-elementor-adapter.php';
require_once BDC_KB_DIR . 'includes/class-gutenberg-adapter.php';
require_once BDC_KB_DIR . 'includes/class-content-extractor.php';
require_once BDC_KB_DIR . 'includes/class-canonical-json.php';
require_once BDC_KB_DIR . 'includes/class-semantic-structure.php';
require_once BDC_KB_DIR . 'includes/class-knowledge-document.php';
require_once BDC_KB_DIR . 'includes/class-block-projection-plan.php';
require_once BDC_KB_DIR . 'includes/class-migration-fidelity-source.php';
require_once BDC_KB_DIR . 'includes/class-core-block-lossless-serializer.php';
require_once BDC_KB_DIR . 'includes/class-block-migration-stale-source-guard.php';
require_once BDC_KB_DIR . 'includes/class-core-block-editorial-parity.php';
require_once BDC_KB_DIR . 'includes/class-block-migration-journal.php';
require_once BDC_KB_DIR . 'includes/class-block-migration-journal-store.php';
require_once BDC_KB_DIR . 'includes/class-block-migration-dry-run.php';
require_once BDC_KB_DIR . 'includes/class-block-migration-batch-plan.php';
require_once BDC_KB_DIR . 'includes/class-block-migration-lock.php';
require_once BDC_KB_DIR . 'includes/class-post-activity-registry.php';
require_once BDC_KB_DIR . 'includes/class-post-management-context.php';
require_once BDC_KB_DIR . 'includes/class-post-management-activities.php';
require_once BDC_KB_DIR . 'includes/class-post-core-blocks-activity.php';
// Historical Elementor migration contracts remain in source/evidence only.
// The product runtime keeps Elementor_Adapter for legacy reads, but no longer
// loads the obsolete Elementor-target migration family.
require_once BDC_KB_DIR . 'includes/class-admin-page.php';
require_once BDC_KB_DIR . 'includes/class-visual-foundation.php';
require_once BDC_KB_DIR . 'includes/class-plugin.php';
if ( defined( 'BDC_KB_WORD_CLOUD_BUILD' ) && BDC_KB_WORD_CLOUD_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-word-cloud-contract.php';
	require_once BDC_KB_DIR . 'includes/class-word-cloud-quality.php';
	require_once BDC_KB_DIR . 'includes/class-word-cloud-service.php';
	require_once BDC_KB_DIR . 'includes/class-word-cloud-consultations.php';
	require_once BDC_KB_DIR . 'includes/class-word-cloud-admin.php';
}

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
if ( defined( 'BDC_KB_SPEC004_FINAL_DIAG_BUILD' ) && BDC_KB_SPEC004_FINAL_DIAG_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-final-structure-diagnostic.php';
}
if ( defined( 'BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD' ) && BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-real-content-acceptance-v2.php';
}
if ( defined( 'BDC_KB_SPEC004_PIPELINE_DIAG_BUILD' ) && BDC_KB_SPEC004_PIPELINE_DIAG_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-pipeline-structure-diagnostic.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_PREFLIGHT_BUILD' ) && BDC_KB_SPEC004_G245_PREFLIGHT_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-production-preflight.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_PROJECTION_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_PROJECTION_SMOKE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-elementor-projection-plan-smoke.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_JOURNAL_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_JOURNAL_SMOKE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-elementor-migration-journal-smoke.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_BLOCK_PROJECTION_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_BLOCK_PROJECTION_SMOKE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-block-projection-plan-smoke.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_EDITORIAL_FIDELITY_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_EDITORIAL_FIDELITY_SMOKE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-editorial-fidelity-inventory-smoke.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_LOSSLESS_ROUNDTRIP_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_LOSSLESS_ROUNDTRIP_SMOKE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-core-block-lossless-roundtrip-smoke.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_EDITORIAL_PARITY_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_EDITORIAL_PARITY_SMOKE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-core-block-editorial-parity-smoke.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_BLOCK_MIGRATION_READINESS_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_BLOCK_MIGRATION_READINESS_SMOKE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-block-migration-readiness-smoke.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_STORAGE_LOCK_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_STORAGE_LOCK_SMOKE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-block-migration-storage-lock-smoke.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_AUTHORIZATION_PACK_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_AUTHORIZATION_PACK_SMOKE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-block-migration-authorization-pack-smoke.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_T099C_CANARY_BUILD' ) && BDC_KB_SPEC004_G245_T099C_CANARY_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-block-migration-canary-t099c.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_T100A_BATCH_AUTHORIZATION_PACK_BUILD' ) && BDC_KB_SPEC004_G245_T100A_BATCH_AUTHORIZATION_PACK_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-block-migration-batch-authorization-pack-smoke.php';
}
if ( defined( 'BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD' ) && BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-post-core-blocks-executor-t100d.php';
}
if ( defined( 'BDC_KB_SPEC004_T100E_E6_REGRESSION_BUILD' ) && BDC_KB_SPEC004_T100E_E6_REGRESSION_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-workspace-regression-matrix-t100e.php';
}
if ( defined( 'BDC_KB_SPEC004_G250_LIFECYCLE_BUILD' ) && BDC_KB_SPEC004_G250_LIFECYCLE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-lifecycle-rc-smoke-g250.php';
}
if ( defined( 'BDC_KB_SPEC005_R500_DIAGNOSTIC_BUILD' ) && BDC_KB_SPEC005_R500_DIAGNOSTIC_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-search-baseline-diagnostic.php';
}
if ( defined( 'BDC_KB_SPEC005_R510_LEGACY_GOLDEN_BUILD' ) && BDC_KB_SPEC005_R510_LEGACY_GOLDEN_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-legacy-golden-discovery.php';
}
if ( defined( 'BDC_KB_SPEC005_R510_GOLDEN_BASELINE_BUILD' ) && BDC_KB_SPEC005_R510_GOLDEN_BASELINE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-golden-candidate-seed.php';
	require_once BDC_KB_DIR . 'includes/class-golden-baseline-runner.php';
}
if ( defined( 'BDC_KB_SPEC005_R510_GOLDEN_AUTO_VALIDATOR_BUILD' ) && BDC_KB_SPEC005_R510_GOLDEN_AUTO_VALIDATOR_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-golden-candidate-seed.php';
	require_once BDC_KB_DIR . 'includes/class-golden-candidate-validator.php';
	require_once BDC_KB_DIR . 'includes/class-golden-diversity-validator.php';
	require_once BDC_KB_DIR . 'includes/class-golden-challenge-discovery.php';
	require_once BDC_KB_DIR . 'includes/class-golden-auto-validation-runner.php';
}
if ( defined( 'BDC_KB_SPEC005_G530_SEARCH_ENGINE_BUILD' ) && BDC_KB_SPEC005_G530_SEARCH_ENGINE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-search-query-normalizer.php';
	require_once BDC_KB_DIR . 'includes/class-search-section-projector.php';
	require_once BDC_KB_DIR . 'includes/class-search-document-builder.php';
	require_once BDC_KB_DIR . 'includes/class-search-projection-repository.php';
	require_once BDC_KB_DIR . 'includes/class-search-rebuild-service.php';
	require_once BDC_KB_DIR . 'includes/class-search-lifecycle.php';
	require_once BDC_KB_DIR . 'includes/class-lexical-ranker.php';
	require_once BDC_KB_DIR . 'includes/class-search-section-ranker.php';
	require_once BDC_KB_DIR . 'includes/class-search-section-service.php';
	require_once BDC_KB_DIR . 'includes/class-search-anchor-manager.php';
	require_once BDC_KB_DIR . 'includes/class-search-service.php';
}
if ( defined( 'BDC_KB_SPEC005_G540_CORPUS_RUNNER_BUILD' ) && BDC_KB_SPEC005_G540_CORPUS_RUNNER_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-search-corpus-runner-g540.php';
}
if ( defined( 'BDC_KB_SPEC005_G550_GOLDEN_RUNNER_BUILD' ) && BDC_KB_SPEC005_G550_GOLDEN_RUNNER_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-golden-suite-loader.php';
	require_once BDC_KB_DIR . 'includes/class-golden-gate-runner-g550.php';
}
if ( defined( 'BDC_KB_SPEC005_G560_SEARCH_UX_RUNNER_BUILD' ) && BDC_KB_SPEC005_G560_SEARCH_UX_RUNNER_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-search-ux-runner-g560.php';
}
if ( defined( 'BDC_KB_SPEC005_G570_SECURITY_PERFORMANCE_BUILD' ) && BDC_KB_SPEC005_G570_SECURITY_PERFORMANCE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-search-security-performance-runner-g570.php';
}
if ( defined( 'BDC_KB_SPEC005_G580_LIFECYCLE_BUILD' ) && BDC_KB_SPEC005_G580_LIFECYCLE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-search-lifecycle-runner-g580.php';
}
if ( defined( 'BDC_KB_SPEC005_G585_ASI_INDEPENDENCE_BUILD' ) && BDC_KB_SPEC005_G585_ASI_INDEPENDENCE_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-golden-suite-loader.php';
	require_once BDC_KB_DIR . 'includes/class-golden-gate-runner-g550.php';
	require_once BDC_KB_DIR . 'includes/class-search-independence-runner-g585.php';
}
if ( defined( 'BDC_KB_SPEC005_G590_SECTION_BUILD' ) && BDC_KB_SPEC005_G590_SECTION_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-golden-suite-loader.php';
	require_once BDC_KB_DIR . 'includes/class-golden-gate-runner-g550.php';
	require_once BDC_KB_DIR . 'includes/class-r260-hierarchy-profiler.php';
	require_once BDC_KB_DIR . 'includes/class-r260-structural-shadow-projector.php';
	require_once BDC_KB_DIR . 'includes/class-r260-anchor-feasibility-profiler.php';
	require_once BDC_KB_DIR . 'includes/class-search-section-runner-g590.php';
}
if ( defined( 'BDC_KB_UX004_H030_TECHNICAL_BUILD' ) && BDC_KB_UX004_H030_TECHNICAL_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-golden-suite-loader.php';
	require_once BDC_KB_DIR . 'includes/class-golden-gate-runner-g550.php';
	require_once BDC_KB_DIR . 'includes/class-public-home-technical-runner-h030.php';
}
if ( defined( 'BDC_KB_P580_PUBLIC_INVENTORY_BUILD' ) && BDC_KB_P580_PUBLIC_INVENTORY_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-public-experience-inventory-runner-p580.php';
}
if ( defined( 'BDC_KB_PUBLIC_EXPERIENCE_PREVIEW_BUILD' ) && BDC_KB_PUBLIC_EXPERIENCE_PREVIEW_BUILD ) {
	require_once BDC_KB_DIR . 'includes/class-public-search-facade.php';
	require_once BDC_KB_DIR . 'includes/class-helpful-tips-store.php';
	require_once BDC_KB_DIR . 'includes/class-public-navigation.php';
	require_once BDC_KB_DIR . 'includes/class-public-auth-bridge.php';
	require_once BDC_KB_DIR . 'includes/class-public-home-read-model.php';
	require_once BDC_KB_DIR . 'includes/class-public-article-read-model.php';
	require_once BDC_KB_DIR . 'includes/class-public-article-content.php';
	require_once BDC_KB_DIR . 'includes/class-public-experience.php';
}

\BDC\KnowledgeBase\Plugin::register();
\BDC\KnowledgeBase\Visual_Foundation::register();
if ( defined( 'BDC_KB_WORD_CLOUD_BUILD' ) && BDC_KB_WORD_CLOUD_BUILD ) {
	\BDC\KnowledgeBase\Word_Cloud_Service::register();
	\BDC\KnowledgeBase\Word_Cloud_Consultations::register();
	\BDC\KnowledgeBase\Word_Cloud_Admin::register();
}
if ( defined( 'BDC_KB_SPEC005_G530_SEARCH_ENGINE_BUILD' ) && BDC_KB_SPEC005_G530_SEARCH_ENGINE_BUILD ) {
	\BDC\KnowledgeBase\Search_Lifecycle::register();
	\BDC\KnowledgeBase\Search_Anchor_Manager::register();
}
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
if ( defined( 'BDC_KB_SPEC004_FINAL_DIAG_BUILD' ) && BDC_KB_SPEC004_FINAL_DIAG_BUILD ) {
	\BDC\KnowledgeBase\Final_Structure_Diagnostic::register();
}
if ( defined( 'BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD' ) && BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD ) {
	\BDC\KnowledgeBase\Real_Content_Acceptance_V2::register();
}
if ( defined( 'BDC_KB_SPEC004_PIPELINE_DIAG_BUILD' ) && BDC_KB_SPEC004_PIPELINE_DIAG_BUILD ) {
	\BDC\KnowledgeBase\Pipeline_Structure_Diagnostic::register();
}
if ( defined( 'BDC_KB_SPEC004_G245_PREFLIGHT_BUILD' ) && BDC_KB_SPEC004_G245_PREFLIGHT_BUILD ) {
	\BDC\KnowledgeBase\Production_Preflight::register();
}
if ( defined( 'BDC_KB_SPEC004_G245_PROJECTION_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_PROJECTION_SMOKE_BUILD ) {
	\BDC\KnowledgeBase\Elementor_Projection_Plan_Smoke::register();
}
if ( defined( 'BDC_KB_SPEC004_G245_JOURNAL_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_JOURNAL_SMOKE_BUILD ) {
	\BDC\KnowledgeBase\Elementor_Migration_Journal_Smoke::register();
}
if ( defined( 'BDC_KB_SPEC004_G245_BLOCK_PROJECTION_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_BLOCK_PROJECTION_SMOKE_BUILD ) {
	\BDC\KnowledgeBase\Block_Projection_Plan_Smoke::register();
}
if ( defined( 'BDC_KB_SPEC004_G245_EDITORIAL_FIDELITY_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_EDITORIAL_FIDELITY_SMOKE_BUILD ) {
	\BDC\KnowledgeBase\Editorial_Fidelity_Inventory_Smoke::register();
}
if ( defined( 'BDC_KB_SPEC004_G245_LOSSLESS_ROUNDTRIP_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_LOSSLESS_ROUNDTRIP_SMOKE_BUILD ) {
	\BDC\KnowledgeBase\Core_Block_Lossless_Roundtrip_Smoke::register();
}
if ( defined( 'BDC_KB_SPEC004_G245_EDITORIAL_PARITY_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_EDITORIAL_PARITY_SMOKE_BUILD ) {
	\BDC\KnowledgeBase\Core_Block_Editorial_Parity_Smoke::register();
}
if ( defined( 'BDC_KB_SPEC004_G245_BLOCK_MIGRATION_READINESS_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_BLOCK_MIGRATION_READINESS_SMOKE_BUILD ) {
	\BDC\KnowledgeBase\Block_Migration_Readiness_Smoke::register();
}
if ( defined( 'BDC_KB_SPEC004_G245_STORAGE_LOCK_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_STORAGE_LOCK_SMOKE_BUILD ) {
	\BDC\KnowledgeBase\Block_Migration_Storage_Lock_Smoke::register();
}
if ( defined( 'BDC_KB_SPEC004_G245_AUTHORIZATION_PACK_SMOKE_BUILD' ) && BDC_KB_SPEC004_G245_AUTHORIZATION_PACK_SMOKE_BUILD ) {
	\BDC\KnowledgeBase\Block_Migration_Authorization_Pack_Smoke::register();
}
if ( defined( 'BDC_KB_SPEC004_G245_T099C_CANARY_BUILD' ) && BDC_KB_SPEC004_G245_T099C_CANARY_BUILD ) {
	\BDC\KnowledgeBase\Block_Migration_Canary_T099C::register();
}
if ( defined( 'BDC_KB_SPEC004_G245_T100A_BATCH_AUTHORIZATION_PACK_BUILD' ) && BDC_KB_SPEC004_G245_T100A_BATCH_AUTHORIZATION_PACK_BUILD ) {
	\BDC\KnowledgeBase\Block_Migration_Batch_Authorization_Pack_Smoke::register();
}
if ( defined( 'BDC_KB_SPEC004_T100E_E6_REGRESSION_BUILD' ) && BDC_KB_SPEC004_T100E_E6_REGRESSION_BUILD ) {
	\BDC\KnowledgeBase\Workspace_Regression_Matrix_T100E::register();
}
if ( defined( 'BDC_KB_SPEC004_G250_LIFECYCLE_BUILD' ) && BDC_KB_SPEC004_G250_LIFECYCLE_BUILD ) {
	\BDC\KnowledgeBase\Lifecycle_RC_Smoke_G250::register();
}
if ( defined( 'BDC_KB_SPEC005_R500_DIAGNOSTIC_BUILD' ) && BDC_KB_SPEC005_R500_DIAGNOSTIC_BUILD ) {
	\BDC\KnowledgeBase\Search_Baseline_Diagnostic::register();
}
if ( defined( 'BDC_KB_SPEC005_R510_LEGACY_GOLDEN_BUILD' ) && BDC_KB_SPEC005_R510_LEGACY_GOLDEN_BUILD ) {
	\BDC\KnowledgeBase\Legacy_Golden_Discovery::register();
}
if ( defined( 'BDC_KB_SPEC005_R510_GOLDEN_BASELINE_BUILD' ) && BDC_KB_SPEC005_R510_GOLDEN_BASELINE_BUILD ) {
	\BDC\KnowledgeBase\Golden_Baseline_Runner::register();
}
if ( defined( 'BDC_KB_SPEC005_R510_GOLDEN_AUTO_VALIDATOR_BUILD' ) && BDC_KB_SPEC005_R510_GOLDEN_AUTO_VALIDATOR_BUILD ) {
	\BDC\KnowledgeBase\Golden_Auto_Validation_Runner::register();
}
if ( defined( 'BDC_KB_SPEC005_G540_CORPUS_RUNNER_BUILD' ) && BDC_KB_SPEC005_G540_CORPUS_RUNNER_BUILD ) {
	\BDC\KnowledgeBase\Search_Corpus_Runner_G540::register();
}
if ( defined( 'BDC_KB_SPEC005_G550_GOLDEN_RUNNER_BUILD' ) && BDC_KB_SPEC005_G550_GOLDEN_RUNNER_BUILD ) {
	\BDC\KnowledgeBase\Golden_Gate_Runner_G550::register();
}
if ( defined( 'BDC_KB_SPEC005_G560_SEARCH_UX_RUNNER_BUILD' ) && BDC_KB_SPEC005_G560_SEARCH_UX_RUNNER_BUILD ) {
	\BDC\KnowledgeBase\Search_UX_Runner_G560::register();
}
if ( defined( 'BDC_KB_SPEC005_G570_SECURITY_PERFORMANCE_BUILD' ) && BDC_KB_SPEC005_G570_SECURITY_PERFORMANCE_BUILD ) {
	\BDC\KnowledgeBase\Search_Security_Performance_Runner_G570::register();
}
if ( defined( 'BDC_KB_SPEC005_G580_LIFECYCLE_BUILD' ) && BDC_KB_SPEC005_G580_LIFECYCLE_BUILD ) {
	\BDC\KnowledgeBase\Search_Lifecycle_Runner_G580::register();
}
if ( defined( 'BDC_KB_SPEC005_G585_ASI_INDEPENDENCE_BUILD' ) && BDC_KB_SPEC005_G585_ASI_INDEPENDENCE_BUILD ) {
	\BDC\KnowledgeBase\Search_Independence_Runner_G585::register();
}
if ( defined( 'BDC_KB_SPEC005_G590_SECTION_BUILD' ) && BDC_KB_SPEC005_G590_SECTION_BUILD ) {
	\BDC\KnowledgeBase\Search_Section_Runner_G590::register();
}
if ( defined( 'BDC_KB_UX004_H030_TECHNICAL_BUILD' ) && BDC_KB_UX004_H030_TECHNICAL_BUILD ) {
	\BDC\KnowledgeBase\Public_Home_Technical_Runner_H030::register();
}
if ( defined( 'BDC_KB_P580_PUBLIC_INVENTORY_BUILD' ) && BDC_KB_P580_PUBLIC_INVENTORY_BUILD ) {
	\BDC\KnowledgeBase\Public_Experience_Inventory_Runner_P580::register();
}
if ( defined( 'BDC_KB_PUBLIC_EXPERIENCE_PREVIEW_BUILD' ) && BDC_KB_PUBLIC_EXPERIENCE_PREVIEW_BUILD ) {
	\BDC\KnowledgeBase\Public_Search_Facade::register();
	\BDC\KnowledgeBase\Public_Experience::register();
}