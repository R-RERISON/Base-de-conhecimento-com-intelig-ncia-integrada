<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}

	final class WP_Error {
		public function __construct(
			private string $code,
			private string $message,
			private mixed $data = null
		) {}

		public function get_error_code(): string {
			return $this->code;
		}

		public function get_error_message(): string {
			return $this->message;
		}

		public function get_error_data(): mixed {
			return $this->data;
		}
	}
}

namespace BDC\KnowledgeBase {
	$GLOBALS['bdc_test_posts']       = array();
	$GLOBALS['bdc_test_meta']        = array();
	$GLOBALS['bdc_test_can_edit']    = true;
	$GLOBALS['bdc_test_write_count'] = 0;
	$GLOBALS['bdc_test_fail_writes'] = array();
	$GLOBALS['bdc_test_registry']    = array();

	function is_wp_error( mixed $thing ): bool {
		return $thing instanceof \WP_Error;
	}

	function get_post( int $post_id ): ?object {
		return $GLOBALS['bdc_test_posts'][ $post_id ] ?? null;
	}

	function current_user_can( string $capability, int $post_id ): bool {
		unset( $capability, $post_id );
		return (bool) $GLOBALS['bdc_test_can_edit'];
	}

	function user_can( int $user_id, string $capability, int $post_id ): bool {
		unset( $user_id, $capability, $post_id );
		return (bool) $GLOBALS['bdc_test_can_edit'];
	}

	function get_post_meta( int $post_id, string $key, bool $single = false ): mixed {
		unset( $single );
		return $GLOBALS['bdc_test_meta'][ $post_id ][ $key ] ?? '';
	}

	function update_post_meta( int $post_id, string $key, mixed $value ): bool {
		++$GLOBALS['bdc_test_write_count'];
		if ( in_array( $GLOBALS['bdc_test_write_count'], $GLOBALS['bdc_test_fail_writes'], true ) ) {
			return false;
		}

		$GLOBALS['bdc_test_meta'][ $post_id ][ $key ] = is_string( $value ) ? stripslashes( $value ) : $value;
		return true;
	}

	function delete_post_meta( int $post_id, string $key ): bool {
		++$GLOBALS['bdc_test_write_count'];
		if ( in_array( $GLOBALS['bdc_test_write_count'], $GLOBALS['bdc_test_fail_writes'], true ) ) {
			return false;
		}

		unset( $GLOBALS['bdc_test_meta'][ $post_id ][ $key ] );
		return true;
	}

	function wp_slash( string $value ): string {
		return addslashes( $value );
	}

	function sanitize_textarea_field( string $value ): string {
		$value = strip_tags( $value );
		$value = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value );
		return is_string( $value ) ? $value : '';
	}

	function register_post_meta( string $post_type, string $key, array $args ): bool {
		$GLOBALS['bdc_test_registry'][ $key ] = array(
			'post_type' => $post_type,
			'args'      => $args,
		);
		return true;
	}

	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-meta-contract.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-summary-store.php';

	function reset_fixture( array $meta = array(), string $post_type = 'post' ): void {
		$GLOBALS['bdc_test_posts'] = array(
			1 => (object) array(
				'ID'         => 1,
				'post_type'  => $post_type,
				'post_title' => 'Artigo de teste',
			),
		);
		$GLOBALS['bdc_test_meta'] = array( 1 => $meta );
		$GLOBALS['bdc_test_can_edit'] = true;
		$GLOBALS['bdc_test_write_count'] = 0;
		$GLOBALS['bdc_test_fail_writes'] = array();
		$GLOBALS['bdc_test_registry'] = array();
	}

	function assert_same( mixed $expected, mixed $actual, string $message ): void {
		if ( $expected !== $actual ) {
			throw new \RuntimeException(
				$message . "\nEsperado: " . var_export( $expected, true ) . "\nAtual: " . var_export( $actual, true )
			);
		}
	}

	function assert_true( bool $condition, string $message ): void {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
	}

	$tests = array();

	$tests['meta_contract_exato'] = static function (): void {
		reset_fixture();
		Meta_Contract::register();
		assert_same(
			array( '_bdc_es_objective', '_bdc_es_escalation', '_bdc_es_important' ),
			array_keys( $GLOBALS['bdc_test_registry'] ),
			'O registry deve conter somente as três metas autorizadas.'
		);
		foreach ( $GLOBALS['bdc_test_registry'] as $registered ) {
			assert_same( 'post', $registered['post_type'], 'O post type deve ser post.' );
			assert_same( false, $registered['args']['show_in_rest'], 'As metas não podem ser expostas via REST nesta SPEC.' );
		}
	};

	$tests['read_side_effect_free'] = static function (): void {
		reset_fixture();
		$result = Summary_Store::read( 1 );
		assert_true( ! is_wp_error( $result ), 'A leitura válida não deve falhar.' );
		assert_same( 0, $GLOBALS['bdc_test_write_count'], 'GET/read não pode escrever.' );
		assert_same( '', $result['objective'], 'Meta ausente deve projetar vazio.' );
	};

	$tests['update_parcial_preserva_omitidos'] = static function (): void {
		reset_fixture(
			array(
				'_bdc_es_objective'  => 'Antes',
				'_bdc_es_escalation' => 'Escalar A',
				'_bdc_es_important'  => 'Atenção',
			)
		);
		$result = Summary_Store::update( 1, array( 'objective' => 'Depois' ) );
		assert_true( ! is_wp_error( $result ), 'Update parcial válido deve passar.' );
		assert_same( 'Depois', $result['state']['objective'], 'Objective deve mudar.' );
		assert_same( 'Escalar A', $result['state']['escalation'], 'Campo omitido deve ser preservado.' );
		assert_same( 1, $GLOBALS['bdc_test_write_count'], 'Apenas o diff deve ser escrito.' );
	};

	$tests['unknown_field_zero_writes'] = static function (): void {
		reset_fixture( array( '_bdc_es_objective' => 'Antes' ) );
		$result = Summary_Store::update( 1, array( 'objective' => 'Depois', 'hacker' => 'x' ) );
		assert_true( is_wp_error( $result ), 'Campo fora da allowlist deve falhar.' );
		assert_same( 'bdc_kb_unknown_field', $result->get_error_code(), 'Código de erro incorreto.' );
		assert_same( 0, $GLOBALS['bdc_test_write_count'], 'Payload inválido deve falhar antes de qualquer write.' );
	};

	$tests['oversized_zero_writes'] = static function (): void {
		reset_fixture();
		$result = Summary_Store::update( 1, array( 'objective' => str_repeat( 'a', 32769 ) ) );
		assert_true( is_wp_error( $result ), 'Valor acima do limite deve falhar.' );
		assert_same( 'bdc_kb_value_too_large', $result->get_error_code(), 'Código de limite incorreto.' );
		assert_same( 0, $GLOBALS['bdc_test_write_count'], 'Excesso de limite não pode escrever.' );
	};

	$tests['empty_delete'] = static function (): void {
		reset_fixture( array( '_bdc_es_objective' => 'Remover' ) );
		$result = Summary_Store::update( 1, array( 'objective' => "  \n " ) );
		assert_true( ! is_wp_error( $result ), 'Vazio sanitizado deve ser operação válida.' );
		assert_same( '', $result['state']['objective'], 'Vazio deve remover a meta.' );
		assert_true( ! isset( $GLOBALS['bdc_test_meta'][1]['_bdc_es_objective'] ), 'A linha de meta deve ser removida.' );
	};

	$tests['noop_zero_writes'] = static function (): void {
		reset_fixture( array( '_bdc_es_objective' => 'Mesmo valor' ) );
		$result = Summary_Store::update( 1, array( 'objective' => 'Mesmo valor' ) );
		assert_true( ! is_wp_error( $result ), 'NO_CHANGE deve ser sucesso.' );
		assert_same( 0, $GLOBALS['bdc_test_write_count'], 'NO_CHANGE não deve chamar write.' );
	};

	$tests['fault_write_1_fail_safe'] = static function (): void {
		reset_fixture( array( '_bdc_es_objective' => 'Antes' ) );
		$GLOBALS['bdc_test_fail_writes'] = array( 1 );
		$result = Summary_Store::update( 1, array( 'objective' => 'Depois' ) );
		assert_true( is_wp_error( $result ), 'Falha injetada deve ser detectada.' );
		assert_same( Summary_Store::STATUS_FAIL_SAFE, $result->get_error_data()['status'], 'Estado deve ser FAIL_SAFE.' );
		assert_same( 'Antes', $GLOBALS['bdc_test_meta'][1]['_bdc_es_objective'], 'Snapshot deve permanecer/restaurar.' );
	};

	$tests['fault_write_2_compensa_write_1'] = static function (): void {
		reset_fixture(
			array(
				'_bdc_es_objective'  => 'O0',
				'_bdc_es_escalation' => 'E0',
			)
		);
		$GLOBALS['bdc_test_fail_writes'] = array( 2 );
		$result = Summary_Store::update( 1, array( 'objective' => 'O1', 'escalation' => 'E1' ) );
		assert_true( is_wp_error( $result ), 'Falha tardia deve ser detectada.' );
		assert_same( Summary_Store::STATUS_FAIL_SAFE, $result->get_error_data()['status'], 'Compensação completa deve resultar FAIL_SAFE.' );
		assert_same( 'O0', $GLOBALS['bdc_test_meta'][1]['_bdc_es_objective'], 'Primeiro write deve ser compensado.' );
		assert_same( 'E0', $GLOBALS['bdc_test_meta'][1]['_bdc_es_escalation'], 'Segundo campo deve terminar no snapshot.' );
	};

	$tests['fault_write_3_compensa_dois_writes'] = static function (): void {
		reset_fixture(
			array(
				'_bdc_es_objective'  => 'O0',
				'_bdc_es_escalation' => 'E0',
				'_bdc_es_important'  => 'I0',
			)
		);
		$GLOBALS['bdc_test_fail_writes'] = array( 3 );
		$result = Summary_Store::update( 1, array( 'objective' => 'O1', 'escalation' => 'E1', 'important' => 'I1' ) );
		assert_true( is_wp_error( $result ), 'Falha no terceiro write deve ser detectada.' );
		assert_same( Summary_Store::STATUS_FAIL_SAFE, $result->get_error_data()['status'], 'Snapshot integral restaurado deve ser FAIL_SAFE.' );
		assert_same( 'O0', $GLOBALS['bdc_test_meta'][1]['_bdc_es_objective'], 'Objective deve ser restaurado.' );
		assert_same( 'E0', $GLOBALS['bdc_test_meta'][1]['_bdc_es_escalation'], 'Escalation deve ser restaurado.' );
		assert_same( 'I0', $GLOBALS['bdc_test_meta'][1]['_bdc_es_important'], 'Important deve permanecer no snapshot.' );
	};

	$tests['fault_delete_fail_safe'] = static function (): void {
		reset_fixture( array( '_bdc_es_objective' => 'Manter' ) );
		$GLOBALS['bdc_test_fail_writes'] = array( 1 );
		$result = Summary_Store::update( 1, array( 'objective' => '' ) );
		assert_true( is_wp_error( $result ), 'Delete falho deve ser detectado.' );
		assert_same( Summary_Store::STATUS_FAIL_SAFE, $result->get_error_data()['status'], 'Delete falho com snapshot íntegro deve ser FAIL_SAFE.' );
		assert_same( 'Manter', $GLOBALS['bdc_test_meta'][1]['_bdc_es_objective'], 'Valor original deve permanecer.' );
	};

	$tests['fault_compensation_critical'] = static function (): void {
		reset_fixture(
			array(
				'_bdc_es_objective'  => 'O0',
				'_bdc_es_escalation' => 'E0',
			)
		);
		$GLOBALS['bdc_test_fail_writes'] = array( 2, 3 );
		$result = Summary_Store::update( 1, array( 'objective' => 'O1', 'escalation' => 'E1' ) );
		assert_true( is_wp_error( $result ), 'Falha de compensação deve ser erro.' );
		assert_same( Summary_Store::STATUS_PARTIAL_FAILURE_CRITICAL, $result->get_error_data()['status'], 'Restauração incompleta deve ser crítica.' );
		assert_same( 'O1', $result->get_error_data()['final_state']['objective'], 'Estado final crítico deve ser relido e reportado.' );
	};

	$tests['mixed_update_delete_success'] = static function (): void {
		reset_fixture(
			array(
				'_bdc_es_objective'  => 'O0',
				'_bdc_es_escalation' => 'E0',
			)
		);
		$result = Summary_Store::update( 1, array( 'objective' => 'O1', 'escalation' => '' ) );
		assert_true( ! is_wp_error( $result ), 'Update+delete válido deve passar.' );
		assert_same( 'O1', $result['state']['objective'], 'Update deve persistir.' );
		assert_same( '', $result['state']['escalation'], 'Delete deve ser confirmado.' );
	};

	$tests['unsupported_post_type'] = static function (): void {
		reset_fixture( array(), 'page' );
		$result = Summary_Store::read( 1 );
		assert_true( is_wp_error( $result ), 'page deve ser rejeitada.' );
		assert_same( 'bdc_kb_unsupported_post_type', $result->get_error_code(), 'Código de post type incorreto.' );
	};

	$tests['capability_required_for_write'] = static function (): void {
		reset_fixture();
		$GLOBALS['bdc_test_can_edit'] = false;
		$result = Summary_Store::update( 1, array( 'objective' => 'x' ) );
		assert_true( is_wp_error( $result ), 'Write sem capability deve falhar.' );
		assert_same( 'bdc_kb_forbidden', $result->get_error_code(), 'Código de capability incorreto.' );
		assert_same( 0, $GLOBALS['bdc_test_write_count'], 'Sem capability não pode haver write.' );
	};

	$passed = 0;
	$failed = 0;
	$failures = array();

	foreach ( $tests as $name => $test ) {
		try {
			$test();
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
