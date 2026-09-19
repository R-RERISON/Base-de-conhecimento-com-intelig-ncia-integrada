<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
	if ( ! defined( 'BDC_KB_DIR' ) ) {
		define( 'BDC_KB_DIR', dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/' );
	}

	final class WP_Error {
		public function __construct(
			private string $code = '',
			private string $message = '',
			private mixed $data = null
		) {}
		public function get_error_code(): string { return $this->code; }
		public function get_error_message(): string { return $this->message; }
		public function get_error_data(): mixed { return $this->data; }
	}

	function wp_json_encode( mixed $value, int $flags = 0, int $depth = 512 ): string|false {
		return json_encode( $value, $flags, $depth );
	}
	function sanitize_key( string $value ): string {
		$value = strtolower( $value );
		return preg_replace( '/[^a-z0-9_\-]/', '', $value ) ?? '';
	}
}

namespace BDC\KnowledgeBase {
	$root = BDC_KB_DIR . 'includes/';
	require_once $root . 'class-golden-suite-loader.php';
	require_once $root . 'class-golden-gate-runner-g550.php';

	function assert_true_g550( bool $condition, string $message ): void {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
	}
	function assert_same_g550( mixed $expected, mixed $actual, string $message ): void {
		if ( $expected !== $actual ) {
			throw new \RuntimeException(
				$message . "\nEsperado: " . var_export( $expected, true ) . "\nAtual: " . var_export( $actual, true )
			);
		}
	}

	$tests = array();

	$tests['runtime_resources_load_and_hash_match'] = static function (): void {
		$suites = Golden_Suite_Loader::load_all();
		assert_true_g550( ! $suites instanceof \WP_Error, 'Suites runtime devem carregar.' );
		assert_same_g550(
			Golden_Suite_Loader::GOLDEN_HASH,
			$suites['golden']['_runtime']['computed_set_hash'],
			'Golden runtime hash divergente.'
		);
		assert_same_g550(
			Golden_Suite_Loader::CHALLENGE_HASH,
			$suites['challenge']['_runtime']['computed_set_hash'],
			'Challenge runtime hash divergente.'
		);
	};

	$tests['runtime_resource_counts_are_frozen'] = static function (): void {
		$suites = Golden_Suite_Loader::load_all();
		assert_true_g550( is_array( $suites ), 'Suites válidas esperadas.' );
		assert_same_g550( 6, count( $suites['golden']['items'] ), 'Golden count deve permanecer 6.' );
		assert_same_g550( 7, count( $suites['challenge']['items'] ), 'Challenge count deve permanecer 7.' );
	};

	$tests['tampered_golden_content_changes_hash'] = static function (): void {
		$suites = Golden_Suite_Loader::load_all();
		$items = $suites['golden']['items'];
		$items[0]['query'] = 'pendrive adulterado';
		$fields = array(
			'id','query','query_norm','expected_post_id','max_rank','severity',
			'source','active','disposition','quarantined','rationale','contract_version',
		);
		$hash = Golden_Suite_Loader::canonical_items_hash( $items, $fields );
		assert_true_g550( is_string( $hash ), 'Hash deve ser calculável.' );
		assert_true_g550( Golden_Suite_Loader::GOLDEN_HASH !== $hash, 'Alteração material deve mudar set_hash.' );
	};

	$tests['golden_expected_within_rank_passes'] = static function (): void {
		$item = array(
			'id'=>'GQ-X','query'=>'sccm','query_norm'=>'sccm',
			'expected_post_id'=>412,'severity'=>'blocking','active'=>true,'disposition'=>'AUTO_PASS',
		);
		$response = array(
			'state'=>'success','retrieval_mode'=>'projection_like',
			'results'=>array(
				array( 'post_id'=>999,'rank'=>1,'matched_signals'=>array('body:1.0000') ),
				array( 'post_id'=>412,'rank'=>2,'matched_signals'=>array('exact_title_phrase') ),
			),
		);
		$row = Golden_Gate_Runner_G550::evaluate_response( $item, $response, 3, 'golden' );
		assert_true_g550( true === $row['pass'], 'Expected rank 2/max3 deve PASS.' );
		assert_same_g550( 2, $row['actual_rank'], 'Actual rank incorreto.' );
		assert_same_g550( array('exact_title_phrase'), $row['matched_signals'], 'Signals do expected devem ser preservados.' );
	};

	$tests['golden_expected_outside_rank_fails'] = static function (): void {
		$item = array(
			'id'=>'GQ-X','query'=>'x','query_norm'=>'x',
			'expected_post_id'=>10,'severity'=>'blocking','active'=>true,'disposition'=>'AUTO_PASS',
		);
		$response = array(
			'state'=>'success','retrieval_mode'=>'projection_like',
			'results'=>array(
				array('post_id'=>1,'rank'=>1,'matched_signals'=>array()),
				array('post_id'=>2,'rank'=>2,'matched_signals'=>array()),
				array('post_id'=>3,'rank'=>3,'matched_signals'=>array()),
				array('post_id'=>10,'rank'=>4,'matched_signals'=>array()),
			),
		);
		$row = Golden_Gate_Runner_G550::evaluate_response( $item, $response, 3, 'golden' );
		assert_true_g550( false === $row['pass'], 'Rank 4/max3 deve FAIL.' );
		assert_same_g550( 4, $row['actual_rank'], 'Actual rank deve ser reportado.' );
	};

	$tests['zero_results_fails_without_technical_error'] = static function (): void {
		$item = array(
			'id'=>'CH-X','query'=>'x','query_norm'=>'x','expected_post_id'=>10,
		);
		$response = array(
			'state'=>'zero_results','retrieval_mode'=>'projection_like','results'=>array(),
		);
		$row = Golden_Gate_Runner_G550::evaluate_response( $item, $response, 3, 'technical_challenge' );
		assert_true_g550( false === $row['pass'], 'Zero results deve falhar expectativa.' );
		assert_same_g550( '', $row['technical_error'], 'Zero result não é erro técnico.' );
		assert_same_g550( 0, $row['actual_rank'], 'Expected não encontrado deve rank 0.' );
	};

	$tests['wordpress_fallback_can_never_pass_golden'] = static function (): void {
		$item = array(
			'id'=>'GQ-X','query'=>'x','query_norm'=>'x',
			'expected_post_id'=>10,'severity'=>'blocking','active'=>true,'disposition'=>'AUTO_PASS',
		);
		$response = array(
			'state'=>'degraded','retrieval_mode'=>'wordpress_fallback',
			'results'=>array(
				array('post_id'=>10,'rank'=>1,'matched_signals'=>array('wordpress_native_relevance')),
			),
		);
		$row = Golden_Gate_Runner_G550::evaluate_response( $item, $response, 3, 'golden' );
		assert_true_g550( false === $row['pass'], 'Fallback não pode aprovar Golden.' );
		assert_same_g550( 'golden_requires_projection_like', $row['technical_error'], 'Fallback deve ser erro técnico do gate.' );
	};

	$tests['invalid_query_state_is_technical_error'] = static function (): void {
		$item = array(
			'id'=>'CH-X','query'=>'x','query_norm'=>'x','expected_post_id'=>10,
		);
		$response = array(
			'state'=>'invalid_query','retrieval_mode'=>'projection_like','results'=>array(),
		);
		$row = Golden_Gate_Runner_G550::evaluate_response( $item, $response, 3, 'technical_challenge' );
		assert_true_g550( false === $row['pass'], 'Invalid query não pode passar.' );
		assert_same_g550( 'search_state_invalid_query', $row['technical_error'], 'Estado inválido deve ser técnico.' );
	};

	$tests['top_post_ids_are_bounded_to_ten'] = static function (): void {
		$item = array(
			'id'=>'CH-X','query'=>'x','query_norm'=>'x','expected_post_id'=>12,
		);
		$results = array();
		for ( $i = 1; $i <= 12; ++$i ) {
			$results[] = array( 'post_id'=>$i, 'rank'=>$i, 'matched_signals'=>array() );
		}
		$response = array( 'state'=>'success','retrieval_mode'=>'projection_like','results'=>$results );
		$row = Golden_Gate_Runner_G550::evaluate_response( $item, $response, 20, 'technical_challenge' );
		assert_same_g550( 10, count( $row['top_post_ids'] ), 'Top IDs devem ser bounded a 10.' );
		assert_same_g550( range(1,10), $row['top_post_ids'], 'Top IDs devem preservar ranking.' );
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
			$failures[] = array( 'test'=>$name, 'error'=>$error->getMessage() );
			echo "FAIL {$name}: {$error->getMessage()}\n";
		}
	}

	echo "\nRESULT passed={$passed} failed={$failed}\n";
	if ( $failed > 0 ) {
		echo json_encode( $failures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . "\n";
		exit( 1 );
	}
}
