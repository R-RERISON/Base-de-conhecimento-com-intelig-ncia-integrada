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
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-golden-candidate-validator.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-golden-diversity-validator.php';

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

	$tests['auto_pass_rank1_full_evidence'] = static function (): void {
		$signals = array(
			'title_coverage_percent' => 100.0,
			'summary_coverage_percent' => 100.0,
			'semantic_coverage_percent' => 100.0,
			'native_coverage_percent' => 100.0,
			'exact_title_phrase' => true,
		);
		$score = Golden_Candidate_Validator::validation_score( $signals );
		$result = Golden_Candidate_Validator::assess(
			array(
				'expected_post_id' => 583,
				'expected_exists' => true,
				'expected_published' => true,
				'expected_rank' => 1,
				'max_rank' => 3,
				'semantic_coverage_percent' => 100.0,
				'title_coverage_percent' => 100.0,
				'summary_coverage_percent' => 100.0,
				'native_coverage_percent' => 100.0,
				'exact_title_phrase' => true,
				'query_token_count' => 2,
				'expected_validation_score' => $score,
				'strongest_competitor' => array(),
				'extractor_error' => false,
			)
		);
		assert_same_spec005( 'AUTO_PASS', $result['status'], 'Caso forte deve ser aprovado automaticamente.' );
		assert_true_spec005( true === $result['auto_accept_existing_expectation'], 'Expectativa existente deve ser autoaceitável.' );
		assert_same_spec005( 'blocking', $result['recommended_severity'], 'Paridade validada deve recomendar blocking.' );
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
		assert_true_spec005( in_array( 'EXPECTED_POST_MISSING', $result['reasons'], true ), 'Razão de ausência deve ser explícita.' );
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
		assert_same_spec005( 'AUTO_FAIL', $result['status'], 'Expected fora do max_rank deve falhar.' );
	};

	$tests['rank2_requires_review'] = static function (): void {
		$expected_score = Golden_Candidate_Validator::validation_score(
			array(
				'title_coverage_percent' => 100.0,
				'summary_coverage_percent' => 0.0,
				'semantic_coverage_percent' => 100.0,
				'native_coverage_percent' => 100.0,
				'exact_title_phrase' => false,
			)
		);
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
				'exact_title_phrase' => false,
				'query_token_count' => 1,
				'expected_validation_score' => $expected_score,
				'strongest_competitor' => array(
					'ahead_of_expected' => true,
					'validation_score' => $expected_score,
					'semantic_coverage_percent' => 100.0,
					'title_coverage_percent' => 100.0,
				),
				'extractor_error' => false,
			)
		);
		assert_same_spec005( 'REVIEW_REQUIRED', $result['status'], 'Ambiguidade material deve exigir review.' );
		assert_true_spec005( in_array( 'STRONG_COMPETITOR_AHEAD', $result['reasons'], true ), 'Concorrente forte deve ser explicitado.' );
	};

	$tests['incomplete_semantic_coverage_requires_review'] = static function (): void {
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
		assert_same_spec005( 'REVIEW_REQUIRED', $result['status'], 'Cobertura semântica incompleta exige review.' );
	};

	$tests['diversity_detects_current_seed_classes'] = static function (): void {
		$cases = array(
			array( 'id'=>'1', 'query'=>'pendrive', 'origin'=>'real' ),
			array( 'id'=>'2', 'query'=>'MSTeams', 'origin'=>'real' ),
			array( 'id'=>'3', 'query'=>'Windows 11', 'origin'=>'real' ),
			array( 'id'=>'4', 'query'=>'Termo de assinatura', 'origin'=>'real' ),
			array( 'id'=>'5', 'query'=>'Estrutura', 'origin'=>'real' ),
			array( 'id'=>'6', 'query'=>'SCCM', 'origin'=>'real' ),
		);
		$result = Golden_Diversity_Validator::assess( $cases );
		assert_true_spec005( $result['coverage']['simple_term'], 'Seed deve cobrir termo simples.' );
		assert_true_spec005( $result['coverage']['product_token'], 'Seed deve cobrir token de produto.' );
		assert_true_spec005( $result['coverage']['compound_or_version'], 'Seed deve cobrir composto/versão.' );
		assert_true_spec005( $result['coverage']['phrase'], 'Seed deve cobrir frase.' );
		assert_true_spec005( $result['coverage']['acronym'], 'Seed deve cobrir sigla.' );
		assert_true_spec005( ! $result['coverage']['natural_language'], 'Seed ainda não cobre linguagem natural.' );
		assert_same_spec005( 'INCOMPLETE', $result['status'], 'Diversidade atual deve continuar incompleta.' );
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
