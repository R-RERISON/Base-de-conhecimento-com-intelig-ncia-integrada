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
	 * Retorna as definições explícitas dos módulos de engenharia.
	 *
	 * @return array<string,array{files:array<int,string>,register:array<int,string>}>
	 */
	private static function definitions(): array {
		$definitions = array();

		self::add( $definitions, 'BDC_KB_SPEC004_PROFILE_BUILD', array( 'includes/class-content-profile.php' ), array( Content_Profile::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G220_SMOKE_BUILD', array( 'includes/class-content-extractor-smoke.php' ), array( Content_Extractor_Smoke::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G230_SMOKE_BUILD', array( 'includes/class-knowledge-document-smoke.php' ), array( Knowledge_Document_Smoke::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_KD_V2_SMOKE_BUILD', array( 'includes/class-knowledge-document-v2-smoke.php' ), array( Knowledge_Document_V2_Smoke::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_FINAL_DIAG_BUILD', array( 'includes/class-final-structure-diagnostic.php' ), array( Final_Structure_Diagnostic::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD', array( 'includes/class-real-content-acceptance-v2.php' ), array( Real_Content_Acceptance_V2::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_PIPELINE_DIAG_BUILD', array( 'includes/class-pipeline-structure-diagnostic.php' ), array( Pipeline_Structure_Diagnostic::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G245_PREFLIGHT_BUILD', array( 'includes/class-production-preflight.php' ), array( Production_Preflight::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G245_PROJECTION_SMOKE_BUILD', array( 'includes/class-elementor-projection-plan-smoke.php' ), array( Elementor_Projection_Plan_Smoke::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G245_JOURNAL_SMOKE_BUILD', array( 'includes/class-elementor-migration-journal-smoke.php' ), array( Elementor_Migration_Journal_Smoke::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G245_BLOCK_PROJECTION_SMOKE_BUILD', array( 'includes/class-block-projection-plan-smoke.php' ), array( Block_Projection_Plan_Smoke::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G245_EDITORIAL_FIDELITY_SMOKE_BUILD', array( 'includes/class-editorial-fidelity-inventory-smoke.php' ), array( Editorial_Fidelity_Inventory_Smoke::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G245_LOSSLESS_ROUNDTRIP_SMOKE_BUILD', array( 'includes/class-core-block-lossless-roundtrip-smoke.php' ), array( Core_Block_Lossless_Roundtrip_Smoke::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G245_EDITORIAL_PARITY_SMOKE_BUILD', array( 'includes/class-core-block-editorial-parity-smoke.php' ), array( Core_Block_Editorial_Parity_Smoke::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G245_BLOCK_MIGRATION_READINESS_SMOKE_BUILD', array( 'includes/class-block-migration-readiness-smoke.php' ), array( Block_Migration_Readiness_Smoke::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G245_STORAGE_LOCK_SMOKE_BUILD', array( 'includes/class-block-migration-storage-lock-smoke.php' ), array( Block_Migration_Storage_Lock_Smoke::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G245_AUTHORIZATION_PACK_SMOKE_BUILD', array( 'includes/class-block-migration-authorization-pack-smoke.php' ), array( Block_Migration_Authorization_Pack_Smoke::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G245_T099C_CANARY_BUILD', array( 'includes/class-block-migration-canary-t099c.php' ), array( Block_Migration_Canary_T099C::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G245_T100A_BATCH_AUTHORIZATION_PACK_BUILD', array( 'includes/class-block-migration-batch-authorization-pack-smoke.php' ), array( Block_Migration_Batch_Authorization_Pack_Smoke::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD', array( 'includes/class-post-core-blocks-executor-t100d.php' ), array() );
		self::add( $definitions, 'BDC_KB_SPEC004_T100E_E6_REGRESSION_BUILD', array( 'includes/class-workspace-regression-matrix-t100e.php' ), array( Workspace_Regression_Matrix_T100E::class ) );
		self::add( $definitions, 'BDC_KB_SPEC004_G250_LIFECYCLE_BUILD', array( 'includes/class-lifecycle-rc-smoke-g250.php' ), array( Lifecycle_RC_Smoke_G250::class ) );
		self::add( $definitions, 'BDC_KB_SPEC005_R500_DIAGNOSTIC_BUILD', array( 'includes/class-search-baseline-diagnostic.php' ), array( Search_Baseline_Diagnostic::class ) );
		self::add( $definitions, 'BDC_KB_SPEC005_R510_LEGACY_GOLDEN_BUILD', array( 'includes/class-legacy-golden-discovery.php' ), array( Legacy_Golden_Discovery::class ) );
		self::add(
			$definitions,
			'BDC_KB_SPEC005_R510_GOLDEN_BASELINE_BUILD',
			array( 'includes/class-golden-candidate-seed.php', 'includes/class-golden-baseline-runner.php' ),
			array( Golden_Baseline_Runner::class )
		);
		self::add(
			$definitions,
			'BDC_KB_SPEC005_R510_GOLDEN_AUTO_VALIDATOR_BUILD',
			array(
				'includes/class-golden-candidate-seed.php',
				'includes/class-golden-candidate-validator.php',
				'includes/class-golden-diversity-validator.php',
				'includes/class-golden-challenge-discovery.php',
				'includes/class-golden-auto-validation-runner.php',
			),
			array( Golden_Auto_Validation_Runner::class )
		);
		self::add( $definitions, 'BDC_KB_SPEC005_G540_CORPUS_RUNNER_BUILD', array( 'includes/class-search-corpus-runner-g540.php' ), array( Search_Corpus_Runner_G540::class ) );
		self::add(
			$definitions,
			'BDC_KB_SPEC005_G550_GOLDEN_RUNNER_BUILD',
			array( 'includes/class-golden-suite-loader.php', 'includes/class-golden-gate-runner-g550.php' ),
			array( Golden_Gate_Runner_G550::class )
		);
		self::add( $definitions, 'BDC_KB_SPEC005_G560_SEARCH_UX_RUNNER_BUILD', array( 'includes/class-search-ux-runner-g560.php' ), array( Search_UX_Runner_G560::class ) );
		self::add( $definitions, 'BDC_KB_SPEC005_G570_SECURITY_PERFORMANCE_BUILD', array( 'includes/class-search-security-performance-runner-g570.php' ), array( Search_Security_Performance_Runner_G570::class ) );
		self::add( $definitions, 'BDC_KB_SPEC005_G580_LIFECYCLE_BUILD', array( 'includes/class-search-lifecycle-runner-g580.php' ), array( Search_Lifecycle_Runner_G580::class ) );
		self::add(
			$definitions,
			'BDC_KB_SPEC005_G585_ASI_INDEPENDENCE_BUILD',
			array( 'includes/class-golden-suite-loader.php', 'includes/class-golden-gate-runner-g550.php', 'includes/class-search-independence-runner-g585.php' ),
			array( Search_Independence_Runner_G585::class )
		);
		self::add(
			$definitions,
			'BDC_KB_SPEC005_G590_SECTION_BUILD',
			array(
				'includes/class-golden-suite-loader.php',
				'includes/class-golden-gate-runner-g550.php',
				'includes/class-r260-hierarchy-profiler.php',
				'includes/class-r260-structural-shadow-projector.php',
				'includes/class-r260-anchor-feasibility-profiler.php',
				'includes/class-r260-contextual-anchor-feasibility-profiler.php',
				'includes/class-search-section-runner-g590.php',
			),
			array( Search_Section_Runner_G590::class )
		);
		self::add(
			$definitions,
			'BDC_KB_UX004_H030_TECHNICAL_BUILD',
			array( 'includes/class-golden-suite-loader.php', 'includes/class-golden-gate-runner-g550.php', 'includes/class-public-home-technical-runner-h030.php' ),
			array( Public_Home_Technical_Runner_H030::class )
		);
		self::add( $definitions, 'BDC_KB_P580_PUBLIC_INVENTORY_BUILD', array( 'includes/class-public-experience-inventory-runner-p580.php' ), array( Public_Experience_Inventory_Runner_P580::class ) );
		self::add( $definitions, 'BDC_KB_SPEC006_P630_ENVIRONMENTAL_BUILD', array( 'includes/class-domain-closure-runner-p630.php' ), array( Domain_Closure_Runner_P630::class ) );
		self::add( $definitions, 'BDC_KB_SPEC006_P640_ENVIRONMENTAL_BUILD', array( 'includes/class-modular-runtime-runner-p640.php' ), array( Modular_Runtime_Runner_P640::class ) );

		return $definitions;
	}

	/**
	 * Carrega todos os módulos de engenharia habilitados.
	 *
	 * @throws \RuntimeException Quando um arquivo obrigatório não existe.
	 */
	public static function load_enabled(): void {
		foreach ( self::definitions() as $flag => $definition ) {
			if ( ! self::enabled( $flag ) ) {
				continue;
			}

			foreach ( $definition['files'] as $relative_path ) {
				$path = BDC_KB_DIR . $relative_path;
				if ( ! is_file( $path ) ) {
					throw new \RuntimeException(
						sprintf(
							'Arquivo obrigatório de engenharia ausente: %s',
							esc_html( $relative_path )
						)
					);
				}

				require_once $path;
			}
		}
	}

	/**
	 * Registra todos os entrypoints de engenharia habilitados.
	 *
	 * @throws \RuntimeException Quando um entrypoint não é registrável.
	 */
	public static function register_enabled(): void {
		foreach ( self::definitions() as $flag => $definition ) {
			if ( ! self::enabled( $flag ) ) {
				continue;
			}

			foreach ( $definition['register'] as $class_name ) {
				if ( ! class_exists( $class_name ) || ! is_callable( array( $class_name, 'register' ) ) ) {
					throw new \RuntimeException(
						sprintf(
							'Entrypoint inválido de engenharia: %s',
							esc_html( $class_name )
						)
					);
				}

				$class_name::register();
			}
		}
	}

	/**
	 * Avalia a flag de um módulo de engenharia.
	 *
	 * @param string $flag Nome da constante.
	 * @return bool
	 */
	private static function enabled( string $flag ): bool {
		return defined( $flag ) && true === constant( $flag );
	}

	/**
	 * Adiciona uma definição explícita.
	 *
	 * @param array<string,array{files:array<int,string>,register:array<int,string>}> $definitions Definições acumuladas.
	 * @param string                                                                 $flag        Flag canônica.
	 * @param array<int,string>                                                      $files       Arquivos do módulo.
	 * @param array<int,string>                                                      $register    Entrypoints.
	 */
	private static function add( array &$definitions, string $flag, array $files, array $register ): void {
		$definitions[ $flag ] = array(
			'files'    => $files,
			'register' => $register,
		);
	}
}
