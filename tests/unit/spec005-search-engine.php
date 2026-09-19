<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
	if ( ! defined( 'ARRAY_A' ) ) {
		define( 'ARRAY_A', 'ARRAY_A' );
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

	final class WP_Query {
		public array $posts = array();
		public function __construct( array $args = array() ) {
			$this->posts = $GLOBALS['spec005_fallback_ids'] ?? array();
			$GLOBALS['spec005_last_wp_query_args'] = $args;
		}
	}

	final class Spec005_Fake_WPDB {
		public string $prefix = 'wp_';
		public string $last_error = '';
		public array $queries = array();
		public array $result_batches = array();
		public function esc_like( string $value ): string { return addcslashes( $value, '_%\\' ); }
		public function prepare( string $sql, mixed ...$args ): string {
			$this->queries[] = array( 'sql' => $sql, 'args' => $args );
			return $sql;
		}
		public function get_results( string $sql, string $format ): array {
			unset( $sql, $format );
			return array_shift( $this->result_batches ) ?? array();
		}
		public function replace( string $table, array $data, array $format ): int|false {
			$GLOBALS['spec005_last_replace'] = compact( 'table', 'data', 'format' );
			return 1;
		}
		public function get_charset_collate(): string { return 'DEFAULT CHARACTER SET utf8mb4'; }
	}

	$GLOBALS['wpdb'] = new Spec005_Fake_WPDB();
	$GLOBALS['spec005_projection_state'] = array();
	$GLOBALS['spec005_posts'] = array();
	$GLOBALS['spec005_denied_post_ids'] = array();
	$GLOBALS['spec005_fallback_ids'] = array();

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
		$value = strtolower( $value );
		return preg_replace( '/[^a-z0-9_\-]/', '', $value ) ?? '';
	}
	function current_time( string $type, bool $gmt = false ): string {
		unset( $type, $gmt );
		return '2026-09-19 09:30:00';
	}
	function get_option( string $key, mixed $default = false ): mixed {
		return 'bdc_kb_search_projection_state' === $key ? ( $GLOBALS['spec005_projection_state'] ?? $default ) : $default;
	}
	function update_option( string $key, mixed $value, mixed $autoload = null ): bool {
		unset( $autoload );
		if ( 'bdc_kb_search_projection_state' === $key ) {
			$GLOBALS['spec005_projection_state'] = $value;
		}
		return true;
	}
	function current_user_can( string $capability, mixed ...$args ): bool {
		if ( 'edit_posts' === $capability ) {
			return true;
		}
		if ( 'edit_post' === $capability ) {
			$post_id = (int) ( $args[0] ?? 0 );
			return ! in_array( $post_id, $GLOBALS['spec005_denied_post_ids'] ?? array(), true );
		}
		return true;
	}
	function get_post( int $post_id ): ?object {
		return $GLOBALS['spec005_posts'][ $post_id ] ?? null;
	}
	function get_permalink( int $post_id ): string { return 'https://example.test/?p=' . $post_id; }
	function get_edit_post_link( int $post_id, string $context = 'display' ): string {
		unset( $context );
		return 'https://example.test/wp-admin/post.php?post=' . $post_id . '&action=edit';
	}
	function wp_reset_postdata(): void {}
	function is_wp_error( mixed $value ): bool { return $value instanceof WP_Error; }
}

namespace BDC\KnowledgeBase {
	final class Meta_Contract {
		public const POST_TYPE = 'post';
		public static function fields(): array {
			return array(
				'objective'=>array('key'=>'_bdc_es_objective'),
				'escalation'=>array('key'=>'_bdc_es_escalation'),
				'important'=>array('key'=>'_bdc_es_important'),
			);
		}
	}
	final class Classification_Contract {
		public static function fields(): array {
			return array(
				'audience'=>array('taxonomy'=>'bdc_kb_audience'),
				'responsible_team'=>array('taxonomy'=>'bdc_kb_responsible_team'),
				'knowledge_type'=>array('taxonomy'=>'bdc_kb_knowledge_type'),
				'catalog_item'=>array('taxonomy'=>'bdc_kb_catalog_item'),
			);
		}
	}
	final class Canonical_JSON {
		public static function hash( mixed $value ): string {
			return hash( 'sha256', self::encode( $value ) );
		}
		private static function encode( mixed $value ): string {
			return json_encode(
				self::normalize( $value ),
				JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR
			);
		}
		private static function normalize( mixed $value ): mixed {
			if ( is_object( $value ) ) {
				$value = get_object_vars( $value );
			}
			if ( ! is_array( $value ) ) {
				return $value;
			}
			if ( array_is_list( $value ) ) {
				return array_map( array( self::class, 'normalize' ), $value );
			}
			ksort( $value, SORT_STRING );
			foreach ( $value as $key => $item ) {
				$value[ $key ] = self::normalize( $item );
			}
			return $value;
		}
	}

	$root = __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/';
	require_once $root . 'class-search-query-normalizer.php';
	require_once $root . 'class-search-document-builder.php';
	require_once $root . 'class-search-projection-repository.php';
	require_once $root . 'class-lexical-ranker.php';
	require_once $root . 'class-search-service.php';

	function assert_true_search( bool $condition, string $message ): void {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
	}
	function assert_same_search( mixed $expected, mixed $actual, string $message ): void {
		if ( $expected !== $actual ) {
			throw new \RuntimeException(
				$message . "\nEsperado: " . var_export( $expected, true ) . "\nAtual: " . var_export( $actual, true )
			);
		}
	}

	$tests = array();

	$tests['normalizer_html_accent_whitespace'] = static function (): void {
		$result = Search_Query_Normalizer::normalize( "  <b>Térmo</b>&nbsp;   de\tASSINATURA  " );
		assert_true_search( ! $result instanceof \WP_Error, 'Query válida não pode falhar.' );
		assert_same_search( 'termo de assinatura', $result['normalized'], 'Normalização lexical divergente.' );
		assert_same_search( array( 'termo', 'de', 'assinatura' ), $result['tokens'], 'Tokens devem preservar ordem.' );
	};

	$tests['normalizer_rejects_oversized_query'] = static function (): void {
		$result = Search_Query_Normalizer::normalize( str_repeat( 'a', 257 ) );
		assert_true_search( $result instanceof \WP_Error, 'Query >256 chars deve falhar.' );
		assert_same_search( 'search_query_too_long', $result->get_error_code(), 'Código de erro incorreto.' );
	};

	$tests['normalizer_rejects_more_than_16_unique_tokens'] = static function (): void {
		$result = Search_Query_Normalizer::normalize(
			implode( ' ', array_map( static fn ( int $i ): string => 't' . $i, range( 1, 17 ) ) )
		);
		assert_true_search( $result instanceof \WP_Error, '>16 tokens deve falhar.' );
		assert_same_search( 'search_too_many_tokens', $result->get_error_code(), 'Código de token bound incorreto.' );
	};

	$tests['normalizer_deduplicates_tokens'] = static function (): void {
		$result = Search_Query_Normalizer::normalize( 'SCCM sccm Windows Windows' );
		assert_true_search( is_array( $result ), 'Query válida esperada.' );
		assert_same_search( array( 'sccm', 'windows' ), $result['tokens'], 'Tokens duplicados devem ser removidos.' );
	};

	$tests['exact_phrase_respects_boundaries'] = static function (): void {
		assert_true_search( Lexical_Ranker::exact_phrase( 'estrutura', 'estrutura ctc no bacen' ), 'Exact phrase válida.' );
		assert_true_search( ! Lexical_Ranker::exact_phrase( 'estrutura', 'materiais e infraestrutura' ), 'Substring não pode ser exact phrase.' );
	};

	$tests['ranker_prefers_exact_title'] = static function (): void {
		$query = Search_Query_Normalizer::normalize( 'Windows 11' );
		assert_true_search( is_array( $query ), 'Query válida esperada.' );

		$ranked = Lexical_Ranker::rank(
			$query,
			array(
				array(
					'post_id'=>20,'document_state'=>'ready','source_kind'=>'legacy_html',
					'title_norm'=>'guia operacional','summary_norm'=>'','headings_norm'=>'',
					'taxonomy_norm'=>'','body_norm'=>'windows 11',
				),
				array(
					'post_id'=>10,'document_state'=>'ready','source_kind'=>'gutenberg',
					'title_norm'=>'windows 11','summary_norm'=>'','headings_norm'=>'',
					'taxonomy_norm'=>'','body_norm'=>'',
				),
			),
			20
		);
		assert_same_search( 10, $ranked[0]['post_id'], 'Exact title deve superar body match.' );
		assert_true_search( in_array( 'exact_title_phrase', $ranked[0]['matched_signals'], true ), 'Sinal exact title deve ser explicável.' );
	};

	$tests['ranker_uses_deterministic_post_id_tie_break'] = static function (): void {
		$query = Search_Query_Normalizer::normalize( 'sccm' );
		$base = array(
			'document_state'=>'ready','source_kind'=>'legacy_html','title_norm'=>'',
			'summary_norm'=>'','headings_norm'=>'','taxonomy_norm'=>'','body_norm'=>'sccm',
		);
		$ranked = Lexical_Ranker::rank(
			$query,
			array(
				array_merge( $base, array( 'post_id'=>9 ) ),
				array_merge( $base, array( 'post_id'=>3 ) ),
			)
		);
		assert_same_search( 3, $ranked[0]['post_id'], 'Tie-break final deve usar post_id ASC.' );
	};

	$tests['document_compose_maps_fields_and_hashes_deterministically'] = static function (): void {
		$components = array(
			'post_id'=>42,
			'post_title'=>'Guia Windows 11',
			'post_modified_gmt'=>'2026-09-18 10:00:00',
			'summary_parts'=>array(
				'objective'=>'Atualizar estação',
				'escalation'=>'Equipe Desktop',
				'important'=>'Reiniciar ao final',
			),
			'taxonomy_rows'=>array(
				array( 'taxonomy'=>'bdc_kb_audience','term_id'=>2,'name'=>'Usuários','slug'=>'usuarios' ),
			),
			'fragments'=>array(
				array( 'kind'=>'heading','text'=>'Pré-requisitos' ),
				array( 'kind'=>'paragraph','text'=>'Instale o pacote.' ),
				array( 'kind'=>'list_item','text'=>'Reinicie o computador.' ),
			),
			'source_kind'=>'gutenberg',
			'source_material'=>array(
				'post_content_sha256'=>str_repeat( 'a', 64 ),
				'elementor_data_sha256'=>str_repeat( 'b', 64 ),
			),
			'extractor_error'=>false,
			'taxonomy_error'=>false,
		);

		$a = Search_Document_Builder::compose( $components );
		$b = Search_Document_Builder::compose( $components );

		assert_same_search( 'guia windows 11', $a['title_norm'], 'Título normalizado incorreto.' );
		assert_same_search( 'pre requisitos', $a['headings_norm'], 'Heading deve ir ao campo próprio.' );
		assert_true_search( ! str_contains( $a['body_norm'], 'pre requisitos' ), 'Heading não pode ser duplicado no body.' );
		assert_true_search( str_contains( $a['summary_norm'], 'equipe desktop' ), 'Summary deve incluir escalation.' );
		assert_true_search( str_contains( $a['taxonomy_norm'], 'usuarios' ), 'Taxonomia deve ser materializada.' );
		assert_same_search( $a['source_hash'], $b['source_hash'], 'Source hash deve ser determinístico.' );
		assert_same_search( $a['document_hash'], $b['document_hash'], 'Document hash deve ser determinístico.' );
	};

	$tests['document_compose_degrades_on_source_error'] = static function (): void {
		$document = Search_Document_Builder::compose(
			array(
				'post_id'=>7,'post_title'=>'Título','post_modified_gmt'=>'',
				'summary_parts'=>array(),'taxonomy_rows'=>array(),'fragments'=>array(),
				'source_kind'=>'unknown',
				'source_material'=>array(
					'post_content_sha256'=>str_repeat( 'a', 64 ),
					'elementor_data_sha256'=>str_repeat( 'b', 64 ),
				),
				'extractor_error'=>true,'taxonomy_error'=>false,
			)
		);
		assert_same_search( 'degraded', $document['document_state'], 'Erro de source deve degradar documento.' );
	};

	$tests['repository_ready_requires_current_versions'] = static function (): void {
		$GLOBALS['spec005_projection_state'] = array(
			'schema_version'=>Search_Projection_Repository::SCHEMA_VERSION,
			'status'=>'ready',
			'document_version'=>Search_Document_Builder::VERSION,
			'normalizer_version'=>Search_Query_Normalizer::VERSION,
		);
		assert_true_search( Search_Projection_Repository::is_ready(), 'State current deve ser ready.' );

		$GLOBALS['spec005_projection_state']['document_version'] = 'old';
		assert_true_search( ! Search_Projection_Repository::is_ready(), 'Version mismatch deve invalidar Projection.' );
	};

	$tests['service_projection_revalidates_permissions_before_ranking'] = static function (): void {
		$GLOBALS['spec005_projection_state'] = array(
			'schema_version'=>Search_Projection_Repository::SCHEMA_VERSION,
			'status'=>'ready',
			'document_version'=>Search_Document_Builder::VERSION,
			'normalizer_version'=>Search_Query_Normalizer::VERSION,
		);
		$GLOBALS['spec005_posts'] = array(
			1 => (object) array( 'ID'=>1,'post_type'=>'post','post_status'=>'publish','post_title'=>'Windows 11 oficial' ),
			2 => (object) array( 'ID'=>2,'post_type'=>'post','post_status'=>'publish','post_title'=>'Windows 11 privado ao usuário' ),
		);
		$GLOBALS['spec005_denied_post_ids'] = array( 2 );
		$GLOBALS['wpdb']->last_error = '';
		$GLOBALS['wpdb']->queries = array();
		$GLOBALS['wpdb']->result_batches = array(
			array(
				array(
					'post_id'=>2,'document_state'=>'ready','source_kind'=>'legacy_html',
					'title_norm'=>'windows 11','summary_norm'=>'','headings_norm'=>'','taxonomy_norm'=>'','body_norm'=>'',
				),
				array(
					'post_id'=>1,'document_state'=>'ready','source_kind'=>'legacy_html',
					'title_norm'=>'windows 11 oficial','summary_norm'=>'','headings_norm'=>'','taxonomy_norm'=>'','body_norm'=>'',
				),
			),
			array(),
		);

		$response = Search_Service::search( 'Windows 11' );
		assert_same_search( 'success', $response['state'], 'Projection ready deve responder success.' );
		assert_same_search( 'projection_like', $response['retrieval_mode'], 'Modo incorreto.' );
		assert_same_search( 1, $response['count'], 'Post sem permissão deve ser eliminado antes do ranking final.' );
		assert_same_search( 1, $response['results'][0]['post_id'], 'Somente post autorizado pode retornar.' );
		assert_true_search( true === $response['results'][0]['visibility_revalidated'], 'Revalidação deve ser explícita.' );
	};

	$tests['service_fallback_is_degraded_and_scoreless'] = static function (): void {
		$GLOBALS['spec005_projection_state'] = array( 'status'=>'not_built' );
		$GLOBALS['spec005_posts'] = array(
			3 => (object) array( 'ID'=>3,'post_type'=>'post','post_status'=>'publish','post_title'=>'SCCM Guia' ),
			4 => (object) array( 'ID'=>4,'post_type'=>'post','post_status'=>'private','post_title'=>'SCCM Restrito' ),
		);
		$GLOBALS['spec005_denied_post_ids'] = array( 4 );
		$GLOBALS['spec005_fallback_ids'] = array( 3, 4 );

		$response = Search_Service::search( 'SCCM' );
		assert_same_search( 'degraded', $response['state'], 'Fallback deve ser degraded.' );
		assert_same_search( 'wordpress_fallback', $response['retrieval_mode'], 'Fallback mode incorreto.' );
		assert_same_search( 1, $response['count'], 'Fallback também deve revalidar permissão.' );
		assert_same_search( null, $response['results'][0]['score'], 'Fallback não possui score próprio.' );
		assert_same_search( array( 'wordpress_native_relevance' ), $response['results'][0]['matched_signals'], 'Sinal do fallback incorreto.' );
		assert_true_search( ! array_key_exists( 'orderby', $GLOBALS['spec005_last_wp_query_args'] ), 'Fallback não pode forçar modified DESC.' );
	};

	$tests['repository_candidate_sql_is_bounded'] = static function (): void {
		$GLOBALS['spec005_projection_state'] = array(
			'schema_version'=>Search_Projection_Repository::SCHEMA_VERSION,
			'status'=>'ready',
			'document_version'=>Search_Document_Builder::VERSION,
			'normalizer_version'=>Search_Query_Normalizer::VERSION,
		);
		$GLOBALS['wpdb']->last_error = '';
		$GLOBALS['wpdb']->queries = array();
		$GLOBALS['wpdb']->result_batches = array( array(), array() );

		Search_Projection_Repository::retrieve_candidates( array( 'windows', '11' ), 999 );
		assert_true_search( count( $GLOBALS['wpdb']->queries ) >= 1, 'Repository deve executar query preparada.' );
		$first = $GLOBALS['wpdb']->queries[0];
		$last_arg = $first['args'][ count( $first['args'] ) - 1 ];
		assert_same_search( 200, $last_arg, 'Candidate cap deve ser 200.' );
		assert_true_search( str_contains( $first['sql'], 'ORDER BY post_id ASC' ), 'Candidate SQL deve ordenar determinísticamente.' );
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
