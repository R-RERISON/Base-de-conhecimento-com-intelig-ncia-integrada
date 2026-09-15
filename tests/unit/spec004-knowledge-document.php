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
		public function get_error_code(): string { return $this->code; }
		public function get_error_message(): string { return $this->message; }
		public function get_error_data(): mixed { return $this->data; }
	}
}

namespace BDC\KnowledgeBase {
	$GLOBALS['bdc_kd_posts'] = array();
	$GLOBALS['bdc_kd_permalink'] = '';
	$GLOBALS['bdc_kd_extraction'] = array();
	$GLOBALS['bdc_kd_write_count'] = 0;

	function get_post( int $post_id ): ?object {
		return $GLOBALS['bdc_kd_posts'][ $post_id ] ?? null;
	}

	function get_permalink( int $post_id ): string {
		unset( $post_id );
		return (string) $GLOBALS['bdc_kd_permalink'];
	}

	function update_post_meta( int $post_id, string $key, mixed $value ): bool {
		unset( $post_id, $key, $value );
		++$GLOBALS['bdc_kd_write_count'];
		return true;
	}

	final class Content_Extractor {
		public static function extract( int $post_id ): array|\WP_Error {
			unset( $post_id );
			return $GLOBALS['bdc_kd_extraction'];
		}
	}

	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-content-normalizer.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-canonical-json.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-knowledge-document.php';

	function fixture_post( string $title = 'Artigo', string $modified = '2026-09-15 20:00:00' ): object {
		return (object) array(
			'ID' => 42,
			'post_type' => 'post',
			'post_title' => $title,
			'post_modified_gmt' => $modified,
		);
	}

	function fixture_extraction( string $text = 'Conteúdo principal' ): array {
		return array(
			'source_kind' => 'legacy_html',
			'strategies' => array( 'legacy_html' ),
			'fallback_used' => false,
			'warnings' => array(),
			'fragments' => array(
				array( 'kind' => 'heading', 'text' => 'Título interno', 'source' => 'post_content', 'ordinal' => 0, 'meta' => array( 'level' => 2 ) ),
				array( 'kind' => 'paragraph', 'text' => $text, 'source' => 'post_content', 'ordinal' => 1 ),
			),
			'structure' => array(
				'headings' => 1,
				'lists' => 0,
				'tables' => 0,
				'images' => 0,
				'links' => 1,
				'code_blocks' => 0,
				'shortcodes' => 0,
			),
			'source_material' => array(
				'post_content_sha256' => str_repeat( 'a', 64 ),
				'elementor_data_sha256' => str_repeat( 'b', 64 ),
			),
			'source_sizes' => array( 'post_content' => 100, 'elementor_data' => 0 ),
			'elementor_compatibility' => array( 'status' => 'projectable', 'reasons' => array() ),
		);
	}

	function assert_same( mixed $expected, mixed $actual, string $message ): void {
		if ( $expected !== $actual ) {
			throw new \RuntimeException( $message . "\nEsperado: " . var_export( $expected, true ) . "\nAtual: " . var_export( $actual, true ) );
		}
	}

	function assert_true( bool $condition, string $message ): void {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
	}

	function is_wp_error_local( mixed $value ): bool {
		return $value instanceof \WP_Error;
	}

	$tests = array();

	$tests['canonical_json_ordena_mapas_preserva_listas'] = static function (): void {
		$a = array( 'z' => 1, 'a' => array( 'b' => 2, 'a' => 1 ), 'list' => array( array( 'y' => 2, 'x' => 1 ), 'fim' ) );
		$b = array( 'list' => array( array( 'x' => 1, 'y' => 2 ), 'fim' ), 'a' => array( 'a' => 1, 'b' => 2 ), 'z' => 1 );
		assert_same( Canonical_JSON::encode( $a ), Canonical_JSON::encode( $b ), 'Ordem de chaves associativas não pode alterar JSON canônico.' );
	};

	$tests['same_input_same_hashes_and_json'] = static function (): void {
		$post = fixture_post();
		$extraction = fixture_extraction();
		$d1 = Knowledge_Document::from_extraction( $post, $extraction, 'https://exemplo/artigo' );
		$d2 = Knowledge_Document::from_extraction( $post, $extraction, 'https://exemplo/artigo' );
		assert_true( is_array( $d1 ) && is_array( $d2 ), 'Documentos válidos devem ser arrays.' );
		assert_same( $d1['source_hash'], $d2['source_hash'], 'source_hash deve ser estável.' );
		assert_same( $d1['document_hash'], $d2['document_hash'], 'document_hash deve ser estável.' );
		assert_same( Knowledge_Document::canonical_json( $d1 ), Knowledge_Document::canonical_json( $d2 ), 'JSON canônico deve ser byte-a-byte repetível.' );
	};

	$tests['semantic_change_changes_hashes'] = static function (): void {
		$post = fixture_post();
		$d1 = Knowledge_Document::from_extraction( $post, fixture_extraction( 'Versão A' ), 'https://exemplo/artigo' );
		$d2 = Knowledge_Document::from_extraction( $post, fixture_extraction( 'Versão B' ), 'https://exemplo/artigo' );
		assert_true( is_array( $d1 ) && is_array( $d2 ), 'Documentos válidos devem ser arrays.' );
		assert_true( $d1['source_hash'] !== $d2['source_hash'], 'Mudança semântica deve alterar source_hash.' );
		assert_true( $d1['document_hash'] !== $d2['document_hash'], 'Mudança semântica deve alterar document_hash.' );
	};

	$tests['title_change_changes_hashes'] = static function (): void {
		$extraction = fixture_extraction();
		$d1 = Knowledge_Document::from_extraction( fixture_post( 'Título A' ), $extraction, 'https://exemplo/artigo' );
		$d2 = Knowledge_Document::from_extraction( fixture_post( 'Título B' ), $extraction, 'https://exemplo/artigo' );
		assert_true( is_array( $d1 ) && is_array( $d2 ), 'Documentos válidos devem ser arrays.' );
		assert_true( $d1['source_hash'] !== $d2['source_hash'], 'Título editorial faz parte do conhecimento.' );
		assert_true( $d1['document_hash'] !== $d2['document_hash'], 'Título deve alterar document_hash.' );
	};

	$tests['operational_metadata_does_not_change_semantic_hashes'] = static function (): void {
		$extraction = fixture_extraction();
		$d1 = Knowledge_Document::from_extraction( fixture_post( 'Artigo', '2026-09-15 20:00:00' ), $extraction, 'https://host-a/artigo' );
		$d2 = Knowledge_Document::from_extraction( fixture_post( 'Artigo', '2026-09-16 21:00:00' ), $extraction, 'https://host-b/artigo-renomeado' );
		assert_true( is_array( $d1 ) && is_array( $d2 ), 'Documentos válidos devem ser arrays.' );
		assert_same( $d1['source_hash'], $d2['source_hash'], 'URL/data operacional não deve alterar source_hash.' );
		assert_same( $d1['document_hash'], $d2['document_hash'], 'URL/data operacional não deve alterar document_hash sem mudança semântica.' );
		assert_true( $d1['canonical_url'] !== $d2['canonical_url'], 'Proveniência operacional deve continuar visível.' );
	};

	$tests['raw_layout_noise_does_not_contaminate_hash'] = static function (): void {
		$post = fixture_post();
		$e1 = fixture_extraction();
		$e2 = fixture_extraction();
		$e2['source_material']['elementor_data_sha256'] = str_repeat( 'f', 64 );
		$e2['source_sizes']['elementor_data'] = 99999;
		$d1 = Knowledge_Document::from_extraction( $post, $e1, 'https://exemplo/artigo' );
		$d2 = Knowledge_Document::from_extraction( $post, $e2, 'https://exemplo/artigo' );
		assert_true( is_array( $d1 ) && is_array( $d2 ), 'Documentos válidos devem ser arrays.' );
		assert_same( $d1['source_hash'], $d2['source_hash'], 'Hash bruto/layout não pode contaminar source_hash sem alteração semântica.' );
		assert_same( $d1['document_hash'], $d2['document_hash'], 'Metadado bruto ignorado não pode contaminar document_hash.' );
	};

	$tests['section_order_is_semantic'] = static function (): void {
		$post = fixture_post();
		$e1 = fixture_extraction();
		$e2 = fixture_extraction();
		$e2['fragments'] = array_reverse( $e2['fragments'] );
		$d1 = Knowledge_Document::from_extraction( $post, $e1, '' );
		$d2 = Knowledge_Document::from_extraction( $post, $e2, '' );
		assert_true( is_array( $d1 ) && is_array( $d2 ), 'Documentos válidos devem ser arrays.' );
		assert_true( $d1['source_hash'] !== $d2['source_hash'], 'Ordem editorial faz parte da semântica.' );
	};

	$tests['schema_shape_and_heading_projection'] = static function (): void {
		$d = Knowledge_Document::from_extraction( fixture_post(), fixture_extraction(), 'https://exemplo/artigo' );
		assert_true( is_array( $d ), 'Documento deve ser array.' );
		assert_same( '1.0.0', $d['schema_version'], 'Schema version incorreta.' );
		assert_same( 42, $d['post_id'], 'post_id incorreto.' );
		assert_same( 'Título interno', $d['sections'][0]['heading'], 'Heading deve ser explicitamente projetado.' );
		assert_same( '', $d['sections'][1]['heading'], 'Parágrafo não deve inventar heading.' );
		assert_same( 0, $d['sections'][0]['ordinal'], 'Ordinal deve ser reconstruído deterministicamente.' );
		assert_same( 'projectable', $d['extraction']['elementor_compatibility']['status'], 'Readiness Elementor deve ser apenas proveniência.' );
	};

	$tests['build_is_read_only'] = static function (): void {
		$GLOBALS['bdc_kd_posts'] = array( 42 => fixture_post() );
		$GLOBALS['bdc_kd_permalink'] = 'https://exemplo/artigo';
		$GLOBALS['bdc_kd_extraction'] = fixture_extraction();
		$GLOBALS['bdc_kd_write_count'] = 0;
		$d = Knowledge_Document::build( 42 );
		assert_true( is_array( $d ), 'Build válido deve produzir documento.' );
		assert_same( 0, $GLOBALS['bdc_kd_write_count'], 'Knowledge Document não pode escrever.' );
	};

	$tests['invalid_post_fails_closed'] = static function (): void {
		$GLOBALS['bdc_kd_posts'] = array();
		$result = Knowledge_Document::build( 999 );
		assert_true( is_wp_error_local( $result ), 'Post ausente deve falhar fechado.' );
		assert_same( 'bdc_kb_post_not_found', $result->get_error_code(), 'Código de erro incorreto.' );
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
