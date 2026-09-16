<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
	final class WP_Error {
		public function __construct( private string $code, private string $message, private mixed $data = null ) {}
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

	function get_post( int $post_id ): ?object { return $GLOBALS['bdc_kd_posts'][ $post_id ] ?? null; }
	function get_permalink( int $post_id ): string { unset( $post_id ); return (string) $GLOBALS['bdc_kd_permalink']; }
	function update_post_meta( int $post_id, string $key, mixed $value ): bool {
		unset( $post_id, $key, $value ); ++$GLOBALS['bdc_kd_write_count']; return true;
	}
	final class Content_Extractor {
		public static function extract( int $post_id ): array|\WP_Error { unset( $post_id ); return $GLOBALS['bdc_kd_extraction']; }
	}

	$root = __DIR__ . '/../base-conhecimento-inteligencia-integrada/includes/';
	require_once $root . 'class-content-normalizer.php';
	require_once $root . 'class-canonical-json.php';
	require_once $root . 'class-semantic-structure.php';
	require_once $root . 'class-knowledge-document.php';

	function fixture_post( string $title = 'Artigo', string $modified = '2026-09-15 20:00:00' ): object {
		return (object) array( 'ID' => 42, 'post_type' => 'post', 'post_title' => $title, 'post_modified_gmt' => $modified );
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
				'headings' => 1, 'paragraphs' => 1, 'lists' => 0, 'list_items' => 0,
				'tables' => 0, 'table_rows' => 0, 'table_cells' => 0, 'images' => 0,
				'links' => 0, 'code_blocks' => 0, 'shortcodes' => 0,
			),
			'source_material' => array( 'post_content_sha256' => str_repeat( 'a', 64 ), 'elementor_data_sha256' => str_repeat( 'b', 64 ) ),
			'source_sizes' => array( 'post_content' => 100, 'elementor_data' => 0 ),
			'elementor_compatibility' => array( 'status' => 'projectable', 'reasons' => array() ),
		);
	}

	function structured_extraction(): array {
		return array(
			'source_kind' => 'legacy_html', 'strategies' => array( 'legacy_html' ), 'fallback_used' => false, 'warnings' => array(),
			'fragments' => array(
				array( 'kind' => 'heading', 'text' => 'Rede', 'source' => 'post_content', 'ordinal' => 0, 'meta' => array( 'level' => 2 ) ),
				array( 'kind' => 'paragraph', 'text' => 'Introdução', 'source' => 'post_content', 'ordinal' => 1 ),
				array( 'kind' => 'list_item', 'text' => 'Item A', 'source' => 'post_content', 'ordinal' => 2, 'meta' => array( 'list_id' => 'list-0', 'list_type' => 'unordered', 'depth' => 0, 'item_index' => 0, 'item_id' => 'list-0-item-0', 'parent_item_id' => '' ) ),
				array( 'kind' => 'list_item', 'text' => 'Sub A1', 'source' => 'post_content', 'ordinal' => 3, 'meta' => array( 'list_id' => 'list-1', 'list_type' => 'ordered', 'depth' => 1, 'item_index' => 0, 'item_id' => 'list-1-item-0', 'parent_item_id' => 'list-0-item-0' ) ),
				array( 'kind' => 'list_item', 'text' => 'Item B', 'source' => 'post_content', 'ordinal' => 4, 'meta' => array( 'list_id' => 'list-0', 'list_type' => 'unordered', 'depth' => 0, 'item_index' => 1, 'item_id' => 'list-0-item-1', 'parent_item_id' => '' ) ),
				array( 'kind' => 'heading', 'text' => 'Tabela', 'source' => 'post_content', 'ordinal' => 5, 'meta' => array( 'level' => 3 ) ),
				array( 'kind' => 'table_row', 'text' => 'Nome | Valor', 'source' => 'post_content', 'ordinal' => 6, 'meta' => array( 'table_id' => 'table-0', 'row_index' => 0, 'cells' => array(
					array( 'cell_index' => 0, 'kind' => 'header', 'text' => 'Nome', 'colspan' => 1, 'rowspan' => 1 ),
					array( 'cell_index' => 1, 'kind' => 'header', 'text' => 'Valor', 'colspan' => 1, 'rowspan' => 1 ),
				) ) ),
				array( 'kind' => 'table_row', 'text' => 'A | 10', 'source' => 'post_content', 'ordinal' => 7, 'meta' => array( 'table_id' => 'table-0', 'row_index' => 1, 'cells' => array(
					array( 'cell_index' => 0, 'kind' => 'data', 'text' => 'A', 'colspan' => 1, 'rowspan' => 1 ),
					array( 'cell_index' => 1, 'kind' => 'data', 'text' => '10', 'colspan' => 1, 'rowspan' => 1 ),
				) ) ),
			),
			'structure' => array( 'headings' => 2, 'paragraphs' => 1, 'lists' => 2, 'list_items' => 3, 'tables' => 1, 'table_rows' => 2, 'table_cells' => 4, 'images' => 0, 'links' => 0, 'code_blocks' => 0, 'shortcodes' => 0 ),
			'elementor_compatibility' => array( 'status' => 'projectable', 'reasons' => array() ),
		);
	}

	function assert_same( mixed $expected, mixed $actual, string $message ): void {
		if ( $expected !== $actual ) { throw new \RuntimeException( $message . "\nEsperado: " . var_export( $expected, true ) . "\nAtual: " . var_export( $actual, true ) ); }
	}
	function assert_true( bool $condition, string $message ): void { if ( ! $condition ) { throw new \RuntimeException( $message ); } }

	$tests = array();
	$tests['canonical_json_deterministic'] = static function (): void {
		$a = array( 'z' => 1, 'a' => array( 'b' => 2, 'a' => 1 ) );
		$b = array( 'a' => array( 'a' => 1, 'b' => 2 ), 'z' => 1 );
		assert_same( Canonical_JSON::encode( $a ), Canonical_JSON::encode( $b ), 'Canonical JSON instável.' );
	};
	$tests['schema_v2_and_heading_path'] = static function (): void {
		$d = Knowledge_Document::from_extraction( fixture_post(), fixture_extraction(), 'https://exemplo/artigo' );
		assert_true( is_array( $d ), 'Documento deve ser array.' );
		assert_same( '2.0.0', $d['schema_version'], 'Schema incorreto.' );
		assert_same( 'Título interno', $d['sections'][1]['heading_path'][0]['text'], 'Parágrafo deve herdar heading.' );
		assert_same( 'candidate_ready', $d['ai_readiness']['status'], 'Documento simples deve ser candidato.' );
	};
	$tests['same_input_same_hashes_and_json'] = static function (): void {
		$d1 = Knowledge_Document::from_extraction( fixture_post(), fixture_extraction(), 'https://exemplo/a' );
		$d2 = Knowledge_Document::from_extraction( fixture_post(), fixture_extraction(), 'https://exemplo/a' );
		assert_same( $d1['source_hash'], $d2['source_hash'], 'source_hash instável.' );
		assert_same( $d1['document_hash'], $d2['document_hash'], 'document_hash instável.' );
		assert_same( Knowledge_Document::canonical_json( $d1 ), Knowledge_Document::canonical_json( $d2 ), 'JSON não repetível.' );
	};
	$tests['semantic_change_changes_hashes'] = static function (): void {
		$d1 = Knowledge_Document::from_extraction( fixture_post(), fixture_extraction( 'A' ), '' );
		$d2 = Knowledge_Document::from_extraction( fixture_post(), fixture_extraction( 'B' ), '' );
		assert_true( $d1['source_hash'] !== $d2['source_hash'], 'Mudança semântica deve alterar source_hash.' );
		assert_true( $d1['document_hash'] !== $d2['document_hash'], 'Mudança semântica deve alterar document_hash.' );
	};
	$tests['title_change_changes_hashes'] = static function (): void {
		$e = fixture_extraction();
		$d1 = Knowledge_Document::from_extraction( fixture_post( 'A' ), $e, '' );
		$d2 = Knowledge_Document::from_extraction( fixture_post( 'B' ), $e, '' );
		assert_true( $d1['source_hash'] !== $d2['source_hash'], 'Título deve alterar source_hash.' );
	};
	$tests['url_and_modified_are_hash_neutral'] = static function (): void {
		$e = fixture_extraction();
		$d1 = Knowledge_Document::from_extraction( fixture_post( 'Artigo', '2026-09-15 20:00:00' ), $e, 'https://a/x' );
		$d2 = Knowledge_Document::from_extraction( fixture_post( 'Artigo', '2026-09-16 21:00:00' ), $e, 'https://b/y' );
		assert_same( $d1['source_hash'], $d2['source_hash'], 'URL/data não deve alterar source_hash.' );
		assert_same( $d1['document_hash'], $d2['document_hash'], 'URL/data não deve alterar document_hash.' );
	};
	$tests['nested_list_preserved'] = static function (): void {
		$d = Knowledge_Document::from_extraction( fixture_post(), structured_extraction(), '' );
		$list = array_values( array_filter( $d['blocks'], static fn ( array $b ): bool => 'list' === $b['kind'] ) )[0];
		assert_same( 2, count( $list['items'] ), 'Lista raiz deve ter 2 itens.' );
		assert_same( 'ordered', $list['items'][0]['children'][0]['list_type'], 'Lista filha deve preservar tipo ordered.' );
	};
	$tests['table_cells_preserved'] = static function (): void {
		$d = Knowledge_Document::from_extraction( fixture_post(), structured_extraction(), '' );
		$table = array_values( array_filter( $d['blocks'], static fn ( array $b ): bool => 'table' === $b['kind'] ) )[0];
		assert_same( 'header', $table['rows'][0]['cells'][0]['kind'], 'TH deve virar header.' );
		assert_same( '10', $table['rows'][1]['cells'][1]['text'], 'Valor da célula perdido.' );
	};
	$tests['structure_change_changes_hash'] = static function (): void {
		$e1 = structured_extraction();
		$e2 = structured_extraction();
		$e2['fragments'][6]['meta']['cells'][0]['colspan'] = 2;
		$d1 = Knowledge_Document::from_extraction( fixture_post(), $e1, '' );
		$d2 = Knowledge_Document::from_extraction( fixture_post(), $e2, '' );
		assert_true( $d1['source_hash'] !== $d2['source_hash'], 'Mudança estrutural deve alterar hash.' );
	};
	$tests['shortcode_warning_requires_review'] = static function (): void {
		$e = fixture_extraction();
		$e['warnings'][] = 'SHORTCODE_NOT_EXPANDED:table';
		$d = Knowledge_Document::from_extraction( fixture_post(), $e, '' );
		assert_same( 'review_required', $d['ai_readiness']['status'], 'Shortcode não expandido deve exigir review.' );
	};
	$tests['elementor_json_invalid_with_good_fallback_is_not_ai_blocker'] = static function (): void {
		$e = fixture_extraction();
		$e['warnings'][] = 'ELEMENTOR_JSON_INVALID';
		$d = Knowledge_Document::from_extraction( fixture_post(), $e, '' );
		assert_same( 'candidate_ready', $d['ai_readiness']['status'], 'Falha de JSON Elementor com fallback completo é risco editorial, não IA.' );
	};
	$tests['build_is_read_only'] = static function (): void {
		$GLOBALS['bdc_kd_posts'] = array( 42 => fixture_post() );
		$GLOBALS['bdc_kd_permalink'] = 'https://exemplo/artigo';
		$GLOBALS['bdc_kd_extraction'] = fixture_extraction();
		$GLOBALS['bdc_kd_write_count'] = 0;
		$d = Knowledge_Document::build( 42 );
		assert_true( is_array( $d ), 'Build deve produzir documento.' );
		assert_same( 0, $GLOBALS['bdc_kd_write_count'], 'Knowledge Document não pode escrever.' );
	};

	$passed = 0; $failed = 0;
	foreach ( $tests as $name => $test ) {
		try { $test(); ++$passed; echo "PASS {$name}\n"; }
		catch ( \Throwable $error ) { ++$failed; echo "FAIL {$name}: {$error->getMessage()}\n"; }
	}
	echo "\nRESULT passed={$passed} failed={$failed}\n";
	if ( $failed > 0 ) { exit( 1 ); }
}
