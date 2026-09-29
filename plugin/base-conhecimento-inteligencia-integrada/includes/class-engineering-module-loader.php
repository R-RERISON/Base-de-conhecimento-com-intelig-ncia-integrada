<?php
/**
 * Loader explícito de módulos de engenharia e homologação.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Isola runners, smokes, profilers e acceptance harnesses do runtime de produto.
 */
final class Engineering_Module_Loader {

	/**
	 * @return array<string,array{files:array<int,string>,register:array<int,string>}>
	 */
	private static function definitions(): array {
		return array(
			'BDC_KB_SPEC004_PROFILE_BUILD' => array(
				'files' => array( 'includes/class-content-profile.php' ),
				'register' => array( Content_Profile::class ),
			),
			'BDC_KB_SPEC004_G220_SMOKE_BUILD' => array(
				'files' => array( 'includes/class-content-extractor-smoke.php' ),
				'register' => array( Content_Extractor_Smoke::class ),
			),
			'BDC_KB_SPEC004_G230_SMOKE_BUILD' => array(
				'files' => array( 'includes/class-knowledge-document-smoke.php' ),
				'register' => array( Knowledge_Document_Smoke::class ),
			),
			'BDC_KB_SPEC004_KD_V2_SMOKE_BUILD' => array(
				'files' => array( 'includes/class-knowledge-document-v2-smoke.php' ),
				'register' => array( Knowledge_Document_V2_Smoke::class ),
			),
			'BDC_KB_SPEC004_FINAL_DIAG_BUILD' => array(
				'files' => array( 'includes/class-final-structure-diagnostic.php' ),
				'register' => array( Final_Structure_Diagnostic::class ),
			),
			'BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD' => array(
				'files' => array( 'includes/class-real-content-acceptance-v2.php' ),
				'register' => array( Real_Content_Acceptance_V2::class ),
			),
			'BDC_KB_SPEC004_PIPELINE_DIAG_BUILD' => array(
				'files' => array( 'includes/class-pipeline-structure-diagnostic.php' ),
				'register' => array( Pipeline_Structure_Diagnostic::class ),
			),
			'BDC_KB_SPEC004_G245_PREFLIGHT_BUILD' => array(
				'files' => array( 'includes/class-production-preflight.php' ),
				'register' => array( Production_Preflight::class ),
			),
			'BDC_KB_SPEC004_G245_PROJECTION_SMOKE_BUILD' => array(
				'files' => array( 'includes/class-elementor-projection-plan-smoke.php' ),
				'register' => array( Elementor_Projection_Plan_Smoke::class ),
			),
			'BDC_KB_SPEC004_G245_JOURNAL_SMOKE_BUILD' => array(
				'files' => array( 'includes/class-elementor-migration-journal-smoke.php' ),
				'register' => array( Elementor_Migration_Journal_Smoke::class ),
			),
			'BDC_KB_SPEC004_G245_BLOCK_PROJECTION_SMOKE_BUILD' => array(
				'files' => array( 'includes/class-block-projection-plan-smoke.php' ),
				'register' => array( Block_Projection_Plan_Smoke::class ),
			),
			'BDC_KB_SPEC004_G245_EDITORIAL_FIDELITY_SMOKE_BUILD' => array(
				'files' => array( 'includes/class-editorial-fidelity-inventory-smoke.php' ),
				'register' => array( Editorial_Fidelity_Inventory_Smoke::class ),
			),
			'BDC_KB_SPEC004_G245_LOSSLESS_ROUNDTRIP_SMOKE_BUILD' => array(
				'files' => array( 'includes/class-core-block-lossless-roundtrip-smoke.php' ),
				'register' => array( Core_Block_Lossless_Roundtrip_Smoke::class ),
			),
			'BDC_KB_SPEC004_G245_EDITORIAL_PARITY_SMOKE_BUILD' => array(
				'files' => array( 'includes/class-core-block-editorial-parity-smoke.php' ),
				'register' => array( Core_Block_Editorial_Parity_Smoke::class ),
			),
			'BDC_KB_SPEC004_G245_BLOCK_MIGRATION_READINESS_SMOKE_BUILD' => array(
				'files' => array( 'includes/class-block-migration-readiness-smoke.php' ),
				'register' => array( Block_Migration_Readiness_Smoke::class ),
			),
			'BDC_KB_SPEC004_G245_STORAGE_LOCK_SMOKE_BUILD' => array(
				'files' => array( 'includes/class-block-migration-storage-lock-smoke.php' ),
				'register' => array( Block_Migration_Storage_Lock_Smoke::class ),
			),
			'BDC_KB_SPEC004_G245_AUTHORIZATION_PACK_SMOKE_BUILD' => array(
				'files' => array( 'includes/class-block-migration-authorization-pack-smoke.php' ),
				'register' => array( Block_Migration_Authorization_Pack_Smoke::class ),
			),
			'BDC_KB_SPEC004_G245_T099C_CANARY_BUILD' => array(
				'files' => array( 'includes/class-block-migration-canary-t099c.php' ),
				'register' => array( Block_Migration_Canary_T099C::class ),
			),
			'BDC_KB_SPEC004_G245_T100A_BATCH_AUTHORIZATION_PACK_BUILD' => array(
				'files' => array( 'includes/class-block-migration-batch-authorization-pack-smoke.php' ),
				'register' => array( Block_Migration_Batch_Authorization_Pack_Smoke::class ),
			),
			'BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD' => array(
				'files' => array( 'includes/class-post-core-blocks-executor-t100d.php' ),
				'register' => array(),
			),
			'BDC_KB_SPEC004_T100E_E6_REGRESSION_BUILD' => array(
				'files' => array( 'includes/class-workspace-regression-matrix-t100e.php' ),
				'register' => array( Workspace_Regression_Matrix_T100E::class ),
			),
			'BDC_KB_SPEC004_G250_LIFECYCLE_BUILD' => array(
				'files' => array( 'includes/class-lifecycle-rc-smoke-g250.php' ),
				'register' => array( Lifecycle_RC_Smoke_G250::class ),
			),
			'BDC_KB_SPEC005_R500_DIAGNOSTIC_BUILD' => array(
				'files' => array( 'includes/class-search-baseline-diagnostic.php' ),
				'register' => array( Search_Baseline_Diagnostic::class ),
			),
			'BDC_KB_SPEC005_R510_LEGACY_GOLDEN_BUILD' => array(
				'files' => array( 'includes/class-legacy-golden-discovery.php' ),
				'register' => array( Legacy_Golden_Discovery::class ),
			),
			'BDC_KB_SPEC005_R510_GOLDEN_BASELINE_BUILD' => array(
				'files' => array(
					'includes/class-golden-candidate-seed.php',
					'includes/class-golden-baseline-runner.php',
				),
				'register' => array( Golden_Baseline_Runner::class ),
			),
			'BDC_KB_SPEC005_R510_GOLDEN_AUTO_VALIDATOR_BUILD' => array(
				'files' => array(
					'includes/class-golden-candidate-seed.php',
					'includes/class-golden-candidate-validator.php',
					'includes/class-golden-diversity-validator.php',
					'includes/class-golden-challenge-discovery.php',
					'includes/class-golden-auto-validation-runner.php',
				),
				'register' => array( Golden_Auto_Validation_Runner::class ),
			),
			'BDC_KB_SPEC005_G540_CORPUS_RUNNER_BUILD' => array(
				'files' => array( 'includes/class-search-corpus-runner-g540.php' ),
				'register' => array( Search_Corpus_Runner_G540::class ),
			),
			'BDC_KB_SPEC005_G550_GOLDEN_RUNNER_BUILD' => array(
				'files' => array(
					'includes/class-golden-suite-loader.php',
					'includes/class-golden-gate-runner-g550.php',
				),
				'register' => array( Golden_Gate_Runner_G550::class ),
			),
			'BDC_KB_SPEC005_G560_SEARCH_UX_RUNNER_BUILD' => array(
				'files' => array( 'includes/class-search-ux-runner-g560.php' ),
				'register' => array( Search_UX_Runner_G560::class ),
			),
			'BDC_KB_SPEC005_G570_SECURITY_PERFORMANCE_BUILD' => array(
				'files' => array( 'includes/class-search-security-performance-runner-g570.php' ),
				'register' => array( Search_Security_Performance_Runner_G570::class ),
			),
			'BDC_KB_SPEC005_G580_LIFECYCLE_BUILD' => array(
				'files' => array( 'includes/class-search-lifecycle-runner-g580.php' ),
				'register' => array( Search_Lifecycle_Runner_G580::class ),
			),
			'BDC_KB_SPEC005_G585_ASI_INDEPENDENCE_BUILD' => array(
				'files' => array(
					'includes/class-golden-suite-loader.php',
					'includes/class-golden-gate-runner-g550.php',
					'includes/class-search-independence-runner-g585.php',
				),
				'register' => array( Search_Independence_Runner_G585::class ),
			),
			'BDC_KB_SPEC005_G590_SECTION_BUILD' => array(
				'files' => array(
					'includes/class-golden-suite-loader.php',
					'includes/class-golden-gate-runner-g550.php',
					'includes/class-r260-hierarchy-profiler.php',
					'includes/class-r260-structural-shadow-projector.php',
					'includes/class-r260-anchor-feasibility-profiler.php',
					'includes/class-r260-contextual-anchor-feasibility-profiler.php',
					'includes/class-search-section-runner-g590.php',
				),
				'register' => array( Search_Section_Runner_G590::class ),
			),
			'BDC_KB_UX004_H030_TECHNICAL_BUILD' => array(
				'files' => array(
					'includes/class-golden-suite-loader.php',
					'includes/class-golden-gate-runner-g550.php',
					'includes/class-public-home-technical-runner-h030.php',
				),
				'register' => array( Public_Home_Technical_Runner_H030::class ),
			),
			'BDC_KB_P580_PUBLIC_INVENTORY_BUILD' => array(
				'files' => array( 'includes/class-public-experience-inventory-runner-p580.php' ),
				'register' => array( Public_Experience_Inventory_Runner_P580::class ),
			),
			'BDC_KB_SPEC006_P630_ENVIRONMENTAL_BUILD' => array(
				'files' => array( 'includes/class-domain-closure-runner-p630.php' ),
				'register' => array( Domain_Closure_Runner_P630::class ),
			),
			'BDC_KB_SPEC006_P640_ENVIRONMENTAL_BUILD' => array(
				'files' => array( 'includes/class-modular-runtime-runner-p640.php' ),
				'register' => array( Modular_Runtime_Runner_P640::class ),
			),
		);
	}

	public static function load_enabled(): void {
		foreach ( self::definitions() as $flag => $definition ) {
			if ( ! self::enabled( $flag ) ) {
				continue;
			}

			foreach ( $definition['files'] as $relative_path ) {
				$path = BDC_KB_DIR . $relative_path;
				if ( ! is_file( $path ) ) {
					throw new \RuntimeException( 'Arquivo obrigatório de engenharia ausente: ' . $relative_path );
				}

				require_once $path;
			}
		}
	}

	public static function register_enabled(): void {
		foreach ( self::definitions() as $flag => $definition ) {
			if ( ! self::enabled( $flag ) ) {
				continue;
			}

			foreach ( $definition['register'] as $class_name ) {
				if ( ! class_exists( $class_name ) || ! is_callable( array( $class_name, 'register' ) ) ) {
					throw new \RuntimeException( 'Entrypoint inválido de engenharia: ' . $class_name );
				}

				$class_name::register();
			}
		}
	}

	private static function enabled( string $flag ): bool {
		return defined( $flag ) && true === constant( $flag );
	}
}
