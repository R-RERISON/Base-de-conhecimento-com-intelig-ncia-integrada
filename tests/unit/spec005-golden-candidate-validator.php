<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}

	function wp_strip_all_tags( string $value ): string {
		return strip_tags( $value );
	}

	function remove_accents( string $value ): string {
		$map = array(
			'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a','Á'=>'A','À'=>'A','Ã'=>'A','Â'=>'A','Ä'=>'A',
			'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E',
			'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i','Í'=>'I','Ì'=>'I','Î'=>'I','Ï'=>'I',
			'ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o','Ó'=>'O','Ò'=>'O','Õ'=>'O','Ô'=>'O','Ö'=>'O',
			'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','Ú'=>'U','Ù'=>'U','Û'=>'U','Ü'=>'U',
			'ç'=>'c','Ç'=>'C',
		);
		return strtr( $value, $map );
	}
}

namespace BDC\KnowledgeBase {
	$root = __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/';
	require_once $root . 'class-golden-candidate-validator.php';
	require_once $root . 'class-golden-diversity-validator.php';
	require_once $root . 'class-golden-challenge-discovery.php';

	function assert_true_spec005( bool $condition, string $message ): void {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
	}

	function assert_same_spec005( mixed $expected, mixed $actual, string $message ): void {
		if ( $expected !== $actual ) {
			throw new \RuntimeException(
				$message . "\nEsperado: " . var_export( $expected, true ) . "\nAtual: " . var_export( $actual, true )
			);
		}
	}

	$tests = array();

	$tests['normalize_is_deterministic'] = static function (): void {
		assert_same_spec005(
			'termo de assinatura',
			Golden_Candidate_Validator::normalize( '  Térmo   de ASSINATURA  ' ),
			'Normalizer deve remover acento, caixa e whitespace.'
		);
	};

	$tests['coverage_full'] = static function (): void {
		assert_same_spec005(
			100.0,
			Golden_Candidate_Validator::coverage_percent( 'Windows 11', 'Guia oficial Windows 11 atualização' ),
			'Cobertura integral esperada.'
		);
	};

	$tests['exact_phrase_respects_token_boundary'] = static function (): void {
		assert_true_spec005(
			Golden_Candidate_Validator::exact_phrase( 'Estrutura', 'Estrutura CTC no BACEN' ),
			'Estrutura isolada deve casar.'
		);
		assert_true_spec005(
			! Golden_Candidate_Validator::exact_phrase( 'Estrutura', 'Materiais e Infraestrutura' ),
			'Estrutura não pode casar dentro de infraestrutura.'
		);
	};

	$tests['auto_pass_strong_evidence'] = static function (): void {
		$signals = array(
			'title_coverage_percent' => 100.0,
			'summary_coverage_percent' => 0.0,
			'semantic_coverage_percent' => 100.0,
			'native_coverage_percent' => 100.0,
			'exact_title_phrase' => true,
		);
		$result = Golden_Candidate_Validator::assess(
			array(
				'expected_post_id' => 583,
				'expected_exists' => true,
				'expected_published' => true,
				'expected_rank' => 1,
				'max_rank' => 3,
				'semantic_coverage_percent' => 100.0,
				'title_coverage_percent' => 100.0,
				'summary_coverage_percent' => 0.0,
				'native_coverage_percent' => 100.0,
				'exact_title_phrase' => true,
				'query_token_count' => 2,
				'expected_validation_score' => Golden_Candidate_Validator::validation_score( $signals ),
				'strongest_competitor' => array(),
				'extractor_error' => false,
			)
		);
		assert_same_spec005( 'AUTO_PASS', $result['status'], 'Caso inequívoco deve auto-pass.' );
		assert_true_spec005( true === $result['active_for_blocking'], 'AUTO_PASS deve ficar ativo para blocking.' );
	};

	$tests['missing_expected_auto_fail'] = static function (): void {
		$result = Golden_Candidate_Validator::assess(
			array(
				'expected_post_id' => 999,
				'expected_exists' => false,
				'expected_published' => false,
				'expected_rank' => 0,
				'max_rank' => 3,
			)
		);
		assert_same_spec005( 'AUTO_FAIL', $result['status'], 'Expected ausente deve falhar.' );
	};

	$tests['outside_max_rank_auto_fail'] = static function (): void {
		$result = Golden_Candidate_Validator::assess(
			array(
				'expected_post_id' => 527,
				'expected_exists' => true,
				'expected_published' => true,
				'expected_rank' => 4,
				'max_rank' => 3,
				'semantic_coverage_percent' => 100.0,
				'title_coverage_percent' => 100.0,
				'native_coverage_percent' => 100.0,
			)
		);
		assert_same_spec005( 'AUTO_FAIL', $result['status'], 'Expected fora de max_rank deve falhar.' );
	};

	$tests['material_ambiguity_is_quarantined'] = static function (): void {
		$signals = array(
			'title_coverage_percent' => 100.0,
			'summary_coverage_percent' => 0.0,
			'semantic_coverage_percent' => 100.0,
			'native_coverage_percent' => 100.0,
			'exact_title_phrase' => true,
		);
		$score = Golden_Candidate_Validator::validation_score( $signals );
		$result = Golden_Candidate_Validator::assess(
			array(
				'expected_post_id' => 36620,
				'expected_exists' => true,
				'expected_published' => true,
				'expected_rank' => 2,
				'max_rank' => 3,
				'semantic_coverage_percent' => 100.0,
				'title_coverage_percent' => 100.0,
				'summary_coverage_percent' => 0.0,
				'native_coverage_percent' => 100.0,
				'exact_title_phrase' => true,
				'query_token_count' => 1,
				'expected_validation_score' => $score,
				'strongest_competitor' => array(
					'ahead_of_expected' => true,
					'validation_score' => $score,
					'semantic_coverage_percent' => 100.0,
					'title_coverage_percent' => 100.0,
				),
				'extractor_error' => false,
			)
		);
		assert_same_spec005( 'AMBIGUOUS_QUARANTINED', $result['status'], 'Ambiguidade não deve pedir escolha manual nem autoaceitar.' );
		assert_true_spec005( false === $result['active_for_blocking'], 'Quarentena não pode bloquear release.' );
		assert_true_spec005( in_array( 'STRONG_COMPETITOR_AHEAD', $result['reasons'], true ), 'Razão de concorrência deve permanecer.' );
	};

	$tests['incomplete_semantic_evidence_is_quarantined'] = static function (): void {
		$result = Golden_Candidate_Validator::assess(
			array(
				'expected_post_id' => 527,
				'expected_exists' => true,
				'expected_published' => true,
				'expected_rank' => 1,
				'max_rank' => 3,
				'semantic_coverage_percent' => 50.0,
				'title_coverage_percent' => 100.0,
				'summary_coverage_percent' => 0.0,
				'native_coverage_percent' => 100.0,
				'exact_title_phrase' => true,
				'query_token_count' => 1,
				'expected_validation_score' => 75.0,
				'strongest_competitor' => array(),
				'extractor_error' => false,
			)
		);
		assert_same_spec005( 'AMBIGUOUS_QUARANTINED', $result['status'], 'Evidência incompleta deve fail-safe para quarentena.' );
	};

	$tests['product_token_classifier_is_bounded'] = static function (): void {
		assert_true_spec005(
			in_array( 'product_token', Golden_Diversity_Validator::classify_query( 'MSTeams' ), true ),
			'MSTeams deve ser product_token.'
		);
		assert_true_spec005(
			! in_array( 'product_token', Golden_Diversity_Validator::classify_query( 'Estrutura' ), true ),
			'Palavra capitalizada comum não pode virar product_token.'
		);
	};

	$tests['historical_seed_covers_core_classes'] = static function (): void {
		$cases = array(
			array( 'id'=>'1', 'query'=>'pendrive', 'origin'=>'legacy_validated' ),
			array( 'id'=>'2', 'query'=>'MSTeams', 'origin'=>'legacy_validated' ),
			array( 'id'=>'3', 'query'=>'Windows 11', 'origin'=>'legacy_validated' ),
			array( 'id'=>'4', 'query'=>'Termo de assinatura', 'origin'=>'legacy_validated' ),
			array( 'id'=>'5', 'query'=>'Estrutura', 'origin'=>'legacy_validated' ),
			array( 'id'=>'6', 'query'=>'SCCM', 'origin'=>'legacy_validated' ),
		);
		$result = Golden_Diversity_Validator::assess( $cases );
		assert_same_spec005( array(), $result['missing_golden_required'], 'Core Golden diversity deve estar coberta.' );
		assert_same_spec005( 'INCOMPLETE', $result['status'], 'Sem Technical Challenge, T514 ainda fica incompleto.' );
		assert_same_spec005( 'PENDING_TELEMETRY', $result['real_world_enrichment_status'], 'Typo/alias reais ficam para telemetria.' );
	};

	$tests['technical_challenges_complete_t514_without_faking_real_queries'] = static function (): void {
		$cases = array(
			array( 'id'=>'1', 'query'=>'pendrive', 'origin'=>'legacy_validated' ),
			array( 'id'=>'2', 'query'=>'MSTeams', 'origin'=>'legacy_validated' ),
			array( 'id'=>'3', 'query'=>'Windows 11', 'origin'=>'legacy_validated' ),
			array( 'id'=>'4', 'query'=>'Termo de assinatura', 'origin'=>'legacy_validated' ),
			array( 'id'=>'5', 'query'=>'SCCM', 'origin'=>'legacy_validated' ),
			array(
				'id'=>'CH-NL-001',
				'query'=>'Como configurar acesso remoto no Windows?',
				'origin'=>'corpus_derived_challenge',
				'declared_classes'=>array('natural_language'),
			),
			array(
				'id'=>'CH-SUM-001',
				'query'=>'tokenunicosummary',
				'origin'=>'corpus_derived_challenge',
				'summary_dependent'=>true,
			),
			array(
				'id'=>'CH-ELM-001',
				'query'=>'tokenunicoelementor',
				'origin'=>'corpus_derived_challenge',
				'elementor_semantic_gap'=>true,
			),
		);
		$result = Golden_Diversity_Validator::assess( $cases );
		assert_same_spec005( 'PASS', $result['status'], 'Golden core + Technical Challenge devem fechar cobertura técnica.' );
		assert_same_spec005( 'PENDING_TELEMETRY', $result['real_world_enrichment_status'], 'Challenge não pode fingir typo/alias real.' );
	};

	$tests['challenge_token_selector_requires_unique_gap'] = static function (): void {
		$token = Golden_Challenge_Discovery::best_unique_gap_token(
			array( 'comum', 'tokenrarissimo', 'outro' ),
			array( 'comum', 'outro' ),
			array( 'comum'=>10, 'tokenrarissimo'=>1, 'outro'=>2 )
		);
		assert_same_spec005( 'tokenrarissimo', $token, 'Discovery deve escolher apenas token gap único.' );
	};

	$tests['challenge_candidate_tokens_filter_noise'] = static function (): void {
		$tokens = Golden_Challenge_Discovery::candidate_tokens( 'Como acesso ao BACEN tokenEspecial 123 sistema' );
		assert_true_spec005( in_array( 'tokenespecial', $tokens, true ), 'Token material deve sobreviver.' );
		assert_true_spec005( ! in_array( 'como', $tokens, true ), 'Stopword deve sair.' );
		assert_true_spec005( ! in_array( 'bacen', $tokens, true ), 'Termo institucional genérico deve sair.' );
	};

	$tests['synthetic_variants_are_explicit'] = static function (): void {
		$variants = Golden_Diversity_Validator::synthetic_variants( 'Windows 11' );
		assert_true_spec005( count( $variants ) >= 2, 'Deve produzir variantes de robustez.' );
		foreach ( $variants as $variant ) {
			assert_same_spec005( 'synthetic', $variant['origin'], 'Variante automática deve ser rotulada synthetic.' );
		}
	};

	$passed = 0;
	$failed = 0;
	$failures = array();

	foreach ( $tests as $name => $fn ) {
		try {
			$fn();
			++$passed;
			echo "PASS {$name}\n";
		} catch ( \Throwable $error ) {
			++$failed;
			$failures[] = array( 'test' => $name, 'error' => $error->getMessage() );
			echo "FAIL {$name}: {$error->getMessage()}\n";
		}
	}

	echo "\nRESULT passed={$passed} failed={$failed}\n";
	if ( $failed > 0 ) {
		echo json_encode( $failures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . "\n";
		exit( 1 );
	}
}
