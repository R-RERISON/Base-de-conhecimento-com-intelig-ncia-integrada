<?php
/**
 * Unit contract for SPEC-005 / G-590 Section Retrieval & Deep-Link.
 */

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}

	final class WP_Error {
		public function __construct( private string $code, private string $message = '' ) {}
		public function get_error_code(): string { return $this->code; }
		public function get_error_message(): string { return $this->message; }
	}

	function wp_strip_all_tags( string $value ): string { return strip_tags( $value ); }
	function remove_accents( string $value ): string {
		return strtr(
			$value,
			array(
				'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a','Á'=>'A','À'=>'A','Ã'=>'A','Â'=>'A','Ä'=>'A',
				'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E',
				'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i','Í'=>'I','Ì'=>'I','Î'=>'I','Ï'=>'I',
				'ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o','Ó'=>'O','Ò'=>'O','Õ'=>'O','Ô'=>'O','Ö'=>'O',
				'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','Ú'=>'U','Ù'=>'U','Û'=>'U','Ü'=>'U',
				'ç'=>'c','Ç'=>'C',
			)
		);
	}
	function sanitize_key( string $value ): string {
		return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $value ) ) ?? '';
	}
	function esc_attr( string $value ): string {
		return htmlspecialchars( $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
	}
	function get_permalink( int $post_id ): string { return 'https://example.test/?p=' . $post_id; }
}

namespace BDC\KnowledgeBase {
	final class Search_Projection_Repository {
		/** @var array<int,array<int,array<string,mixed>>> */
		public static array $fixture = array();

		/** @return array<int,array<int,array<string,mixed>>> */
		public static function sections_for_posts( array $post_ids ): array {
			$out = array();
			foreach ( $post_ids as $post_id ) {
				$out[ (int) $post_id ] = self::$fixture[ (int) $post_id ] ?? array();
			}
			return $out;
		}
	}

	$root = __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/';
	require_once $root . 'class-search-query-normalizer.php';
	require_once $root . 'class-lexical-ranker.php';
	require_once $root . 'class-search-section-projector.php';
	require_once $root . 'class-search-section-ranker.php';
	require_once $root . 'class-search-section-service.php';
	require_once $root . 'class-search-anchor-manager.php';

	function g590_assert_true( bool $condition, string $message ): void {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
	}

	function g590_assert_same( mixed $expected, mixed $actual, string $message ): void {
		if ( $expected !== $actual ) {
			throw new \RuntimeException(
				$message . "\nEsperado: " . var_export( $expected, true ) . "\nAtual: " . var_export( $actual, true )
			);
		}
	}

	$tests = array();

	$tests['projector_is_deterministic_and_preserves_section_body'] = static function (): void {
		$fragments = array(
			array( 'kind'=>'heading', 'text'=>'Pré-requisitos', 'ordinal'=>0, 'meta'=>array( 'level'=>2 ) ),
			array( 'kind'=>'paragraph', 'text'=>'Instale o pacote oficial.', 'ordinal'=>1 ),
			array( 'kind'=>'list_item', 'text'=>'Reinicie a estação.', 'ordinal'=>2 ),
			array( 'kind'=>'heading', 'text'=>'Validação', 'ordinal'=>3, 'meta'=>array( 'level'=>2 ) ),
			array( 'kind'=>'paragraph', 'text'=>'Confirme o serviço.', 'ordinal'=>4 ),
		);

		$a = Search_Section_Projector::project( 42, $fragments );
		$b = Search_Section_Projector::project( 42, $fragments );

		g590_assert_same( $a, $b, 'Projection deve ser determinística.' );
		g590_assert_same( 2, count( $a ), 'Duas seções esperadas.' );
		g590_assert_same( 'pre requisitos', $a[0]['title_norm'], 'Título normalizado incorreto.' );
		g590_assert_true( str_contains( $a[0]['text_norm'], 'instale o pacote oficial' ), 'Body da seção deve incluir paragraph.' );
		g590_assert_true( str_contains( $a[0]['text_norm'], 'reinicie a estacao' ), 'Body da seção deve incluir list item.' );
		g590_assert_same( 'generated', $a[0]['anchor_state'], 'Heading único deve ser navegável.' );
		g590_assert_true(
			(bool) preg_match( '/^bdc-kb-section-[a-f0-9]{20}$/', (string) $a[0]['anchor_id'] ),
			'Anchor deve cumprir contrato.'
		);
	};

	$tests['projector_marks_duplicate_titles_unresolved'] = static function (): void {
		$sections = Search_Section_Projector::project(
			10,
			array(
				array( 'kind'=>'heading', 'text'=>'Passos', 'ordinal'=>0, 'meta'=>array( 'level'=>2 ) ),
				array( 'kind'=>'paragraph', 'text'=>'Primeiro.', 'ordinal'=>1 ),
				array( 'kind'=>'heading', 'text'=>'Passos', 'ordinal'=>2, 'meta'=>array( 'level'=>2 ) ),
				array( 'kind'=>'paragraph', 'text'=>'Segundo.', 'ordinal'=>3 ),
			)
		);

		g590_assert_same( 2, count( $sections ), 'As duas seções devem continuar projetadas.' );
		g590_assert_same( 'unresolved', $sections[0]['anchor_state'], 'Duplicidade deve falhar fechado.' );
		g590_assert_same( '', $sections[0]['anchor_id'], 'Duplicidade não deve emitir anchor.' );
		g590_assert_same( 'unresolved', $sections[1]['anchor_state'], 'Duplicidade deve falhar fechado.' );
		g590_assert_true( $sections[0]['section_key'] !== $sections[1]['section_key'], 'Identidades devem permanecer distintas.' );
	};

	$tests['projector_bounds_section_count'] = static function (): void {
		$fragments = array();
		for ( $i = 0; $i < 80; ++$i ) {
			$fragments[] = array( 'kind'=>'heading', 'text'=>'Seção ' . $i, 'ordinal'=>$i * 2, 'meta'=>array( 'level'=>2 ) );
			$fragments[] = array( 'kind'=>'paragraph', 'text'=>'Conteúdo ' . $i, 'ordinal'=>$i * 2 + 1 );
		}
		$sections = Search_Section_Projector::project( 1, $fragments );
		g590_assert_same( Search_Section_Projector::MAX_SECTIONS, count( $sections ), 'Projection deve ser bounded.' );
	};

	$tests['projector_bounds_section_text_length'] = static function (): void {
		$sections = Search_Section_Projector::project(
			11,
			array(
				array( 'kind'=>'heading', 'text'=>'Conteúdo extenso', 'ordinal'=>0, 'meta'=>array( 'level'=>2 ) ),
				array( 'kind'=>'paragraph', 'text'=>str_repeat( 'abcdef ', 1000 ), 'ordinal'=>1 ),
			)
		);
		g590_assert_same( 1, count( $sections ), 'Uma seção esperada.' );
		g590_assert_true(
			strlen( (string) $sections[0]['text_norm'] ) <= Search_Section_Projector::MAX_TEXT_CHARS,
			'Texto normalizado deve respeitar o bound de 4.000 caracteres.'
		);
	};

	$tests['ranker_prefers_exact_section_title'] = static function (): void {
		$query = Search_Query_Normalizer::normalize( 'Validar certificado' );
		g590_assert_true( is_array( $query ), 'Query válida esperada.' );

		$base = array(
			'anchor_state'=>'generated',
			'anchor_id'=>'bdc-kb-section-' . str_repeat( 'a', 20 ),
			'section_key'=>str_repeat( 'a', 64 ),
			'path_norm'=>'',
			'projection_version'=>Search_Section_Projector::VERSION,
		);
		$ranked = Search_Section_Ranker::rank(
			$query,
			array(
				array_merge( $base, array(
					'ordinal'=>1, 'title'=>'Procedimento', 'title_norm'=>'procedimento',
					'text_norm'=>'validar certificado antes de continuar',
				) ),
				array_merge( $base, array(
					'ordinal'=>2, 'section_key'=>str_repeat( 'b', 64 ),
					'anchor_id'=>'bdc-kb-section-' . str_repeat( 'b', 20 ),
					'title'=>'Validar certificado', 'title_norm'=>'validar certificado',
					'text_norm'=>'passos operacionais',
				) ),
			),
			1,
			3
		);

		g590_assert_same( 2, $ranked[0]['ordinal'], 'Exact section title deve liderar.' );
		g590_assert_true( in_array( 'exact_section_title', $ranked[0]['matched_signals'], true ), 'Sinal deve ser explicável.' );
	};

	$tests['ranker_does_not_return_parent_only_section'] = static function (): void {
		$query = Search_Query_Normalizer::normalize( 'Windows 11' );
		$ranked = Search_Section_Ranker::rank(
			$query,
			array(
				array(
					'section_key'=>str_repeat( 'c', 64 ),
					'ordinal'=>0,
					'title'=>'Pré-requisitos',
					'title_norm'=>'pre requisitos',
					'text_norm'=>'reinicie o equipamento',
					'anchor_id'=>'bdc-kb-section-' . str_repeat( 'c', 20 ),
					'anchor_state'=>'generated',
				),
			),
			1
		);
		g590_assert_same( array(), $ranked, 'Parent boost não pode criar relevância sem match da seção.' );
	};

	$tests['ranker_requires_half_coverage_for_multi_token_query'] = static function (): void {
		$query = Search_Query_Normalizer::normalize( 'assinatura digital certificado gov' );
		$ranked = Search_Section_Ranker::rank(
			$query,
			array(
				array(
					'section_key'=>str_repeat( 'd', 64 ),
					'ordinal'=>0,
					'title'=>'Assinatura',
					'title_norm'=>'assinatura',
					'text_norm'=>'procedimento local',
					'anchor_id'=>'bdc-kb-section-' . str_repeat( 'd', 20 ),
					'anchor_state'=>'generated',
				),
			),
			1
		);
		g590_assert_same( array(), $ranked, '1/4 tokens não pode superar coverage mínimo.' );
	};

	$tests['anchor_injection_preserves_visible_text_and_existing_heading_id'] = static function (): void {
		$anchor = 'bdc-kb-section-' . str_repeat( 'e', 20 );
		$content = '<h2 id="editorial-id"><em>Pré-requisitos</em></h2><p>Conteúdo.</p>';
		$before = wp_strip_all_tags( $content );
		$after = Search_Anchor_Manager::inject_for_sections(
			$content,
			array(
				array(
					'title_norm'=>'pre requisitos',
					'anchor_id'=>$anchor,
					'anchor_state'=>'generated',
				),
			)
		);

		g590_assert_true( str_contains( $after, 'id="editorial-id"' ), 'ID editorial existente deve ser preservado.' );
		g590_assert_true( str_contains( $after, 'id="' . $anchor . '"' ), 'Anchor BDC deve ser inserido.' );
		g590_assert_same( $before, wp_strip_all_tags( $after ), 'Texto visível não pode mudar.' );
	};

	$tests['anchor_injection_is_idempotent'] = static function (): void {
		$anchor = 'bdc-kb-section-' . str_repeat( 'f', 20 );
		$sections = array(
			array( 'title_norm'=>'validacao', 'anchor_id'=>$anchor, 'anchor_state'=>'generated' ),
		);
		$once = Search_Anchor_Manager::inject_for_sections( '<h2>Validação</h2>', $sections );
		$twice = Search_Anchor_Manager::inject_for_sections( $once, $sections );
		g590_assert_same( $once, $twice, 'Aplicação repetida não pode duplicar anchor.' );
	};

	$tests['anchor_injection_fails_closed_on_duplicate_rendered_heading'] = static function (): void {
		$anchor = 'bdc-kb-section-' . str_repeat( '1', 20 );
		$content = '<h2>Passos</h2><p>A</p><h3>Passos</h3><p>B</p>';
		$after = Search_Anchor_Manager::inject_for_sections(
			$content,
			array(
				array( 'title_norm'=>'passos', 'anchor_id'=>$anchor, 'anchor_state'=>'generated' ),
			)
		);
		g590_assert_same( $content, $after, 'Destino ambíguo não pode receber anchor.' );
	};

	$tests['section_service_keeps_parent_scope_and_builds_deep_link'] = static function (): void {
		$query = Search_Query_Normalizer::normalize( 'certificado' );
		Search_Projection_Repository::$fixture = array(
			9 => array(
				array(
					'section_key'=>str_repeat( '9', 64 ),
					'ordinal'=>0,
					'title'=>'Certificado',
					'title_norm'=>'certificado',
					'text_norm'=>'validar certificado',
					'anchor_id'=>'bdc-kb-section-' . str_repeat( '9', 20 ),
					'anchor_state'=>'generated',
				),
			),
		);

		$result = Search_Section_Service::rank_for_authorized_results(
			$query,
			array( array( 'post_id'=>9, 'rank'=>2 ) )
		);

		g590_assert_same( 'ready', $result['state'], 'Service deve responder ready.' );
		g590_assert_same( 1, $result['count'], 'Uma seção esperada.' );
		g590_assert_true(
			str_contains( $result['items_by_post'][9][0]['url'], '#bdc-kb-section-' ),
			'Deep-link deve usar anchor projetado.'
		);
		g590_assert_same( 2, $result['items_by_post'][9][0]['parent_rank'], 'Parent rank deve ser preservado.' );
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
