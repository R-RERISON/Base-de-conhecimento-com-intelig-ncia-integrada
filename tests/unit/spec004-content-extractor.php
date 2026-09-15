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

	$GLOBALS['bdc_posts'] = array();
	$GLOBALS['bdc_meta'] = array();
	$GLOBALS['bdc_blocks'] = array();
	$GLOBALS['bdc_write_count'] = 0;
	$GLOBALS['shortcode_tags'] = array();

	function get_post( int $post_id ): ?object {
		return $GLOBALS['bdc_posts'][ $post_id ] ?? null;
	}

	function get_post_meta( int $post_id, string $key, bool $single = false ): mixed {
		unset( $single );
		return $GLOBALS['bdc_meta'][ $post_id ][ $key ] ?? '';
	}

	function update_post_meta( int $post_id, string $key, mixed $value ): bool {
		unset( $post_id, $key, $value );
		++$GLOBALS['bdc_write_count'];
		return true;
	}

	function wp_update_post( array $data ): int {
		unset( $data );
		++$GLOBALS['bdc_write_count'];
		return 1;
	}

	function wp_insert_post( array $data ): int {
		unset( $data );
		++$GLOBALS['bdc_write_count'];
		return 1;
	}

	function has_blocks( string $content ): bool {
		return str_contains( $content, '<!-- wp:' );
	}

	function parse_blocks( string $content ): array {
		return $GLOBALS['bdc_blocks'][ hash( 'sha256', $content ) ] ?? array();
	}

	function wp_strip_all_tags( string $content, bool $remove_breaks = false ): string {
		$value = strip_tags( $content );
		return $remove_breaks ? preg_replace( '/[\r\n\t ]+/', ' ', $value ) ?? '' : $value;
	}

	function wp_json_encode( mixed $value, int $flags = 0 ): string|false {
		return json_encode( $value, $flags );
	}

	function shortcode_exists( string $tag ): bool {
		return isset( $GLOBALS['shortcode_tags'][ $tag ] );
	}

	function get_shortcode_regex( ?array $tagnames = null ): string {
		$tags = $tagnames ?? array_keys( $GLOBALS['shortcode_tags'] );
		$tagregexp = implode( '|', array_map( static fn ( string $tag ): string => preg_quote( $tag, '/' ), $tags ) );
		if ( '' === $tagregexp ) {
			return '(?!)';
		}
		return '\\[(\\[?)(' . $tagregexp . ')(?![\\w-])([^\\]]*?)(?:(\\/)\\]|\\](?:([^\\[]*?)\\[\\/\\2\\])?)(\\]?)';
	}
}

namespace BDC\KnowledgeBase {
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-content-normalizer.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-shortcode-inspector.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-legacy-html-adapter.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-content-source.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-elementor-adapter.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-gutenberg-adapter.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-content-extractor.php';

	function reset_fixture( string $content = '', mixed $elementor = '', string $post_type = 'post' ): void {
		$GLOBALS['bdc_posts'] = array(
			1 => (object) array(
				'ID' => 1,
				'post_type' => $post_type,
				'post_content' => $content,
			),
		);
		$GLOBALS['bdc_meta'] = array( 1 => array( '_elementor_data' => $elementor ) );
		$GLOBALS['bdc_blocks'] = array();
		$GLOBALS['bdc_write_count'] = 0;
		$GLOBALS['shortcode_tags'] = array();
	}

	function set_blocks( string $content, array $blocks ): void {
		$GLOBALS['bdc_blocks'][ hash( 'sha256', $content ) ] = $blocks;
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

	function fragment_texts( array $result ): array {
		return array_map( static fn ( array $fragment ): string => (string) $fragment['text'], $result['fragments'] );
	}

	$tests = array();

	$tests['legacy_html_structural'] = static function (): void {
		reset_fixture( '<h2>Título</h2><p>Texto <a href="https://x.test">com link</a>.</p><ul><li>Item A</li><li>Item B</li></ul><table><tr><th>A</th><td>B</td></tr></table><pre>if (x) {\n  y();\n}</pre>' );
		$result = Content_Extractor::extract( 1 );
		assert_true( is_array( $result ), 'Legacy deve extrair.' );
		assert_same( 'legacy_html', $result['source_kind'], 'Source kind incorreto.' );
		assert_true( in_array( 'Título', fragment_texts( $result ), true ), 'Heading deve ser preservado.' );
		assert_true( in_array( 'Texto com link.', fragment_texts( $result ), true ), 'Texto âncora deve ser preservado.' );
		assert_same( 1, $result['structure']['headings'], 'Heading count incorreto.' );
		assert_same( 1, $result['structure']['lists'], 'List count incorreto.' );
		assert_same( 1, $result['structure']['tables'], 'Table count incorreto.' );
		assert_same( 1, $result['structure']['links'], 'Link count incorreto.' );
		assert_same( 1, $result['structure']['code_blocks'], 'Code count incorreto.' );
	};

	$tests['technical_brackets_not_shortcode'] = static function (): void {
		reset_fixture( '<p>Registro [HKEY_LOCAL_MACHINE\\SYSTEM] e [tipo] devem permanecer.</p>' );
		$result = Content_Extractor::extract( 1 );
		$text = implode( "\n", fragment_texts( $result ) );
		assert_true( str_contains( $text, '[HKEY_LOCAL_MACHINE\\SYSTEM]' ), 'Colchete técnico foi removido indevidamente.' );
		assert_true( ! array_filter( $result['warnings'], static fn ( string $warning ): bool => str_starts_with( $warning, 'SHORTCODE_NOT_EXPANDED:' ) ), 'Texto técnico não pode virar shortcode.' );
	};

	$tests['registered_shortcode_not_executed'] = static function (): void {
		reset_fixture( '<p>Antes</p>[table]conteúdo interno[/table]<p>Depois</p>' );
		$GLOBALS['shortcode_tags']['table'] = static fn (): string => 'NUNCA EXECUTAR';
		$result = Content_Extractor::extract( 1 );
		assert_true( in_array( 'SHORTCODE_NOT_EXPANDED:table', $result['warnings'], true ), 'Warning de shortcode ausente.' );
		assert_true( str_contains( implode( ' ', fragment_texts( $result ) ), 'conteúdo interno' ), 'Conteúdo interno deve sobreviver.' );
		assert_same( 1, $result['structure']['shortcodes'], 'Shortcode count incorreto.' );
	};

	$tests['elementor_valid_text_editor'] = static function (): void {
		$elementor = json_encode( array(
			array(
				'id' => 'abc12345',
				'elType' => 'widget',
				'widgetType' => 'text-editor',
				'settings' => array( 'editor' => '<h3>Elementor</h3><p>Conteúdo principal</p>' ),
				'elements' => array(),
			),
		), JSON_UNESCAPED_UNICODE );
		reset_fixture( '<p>Residual legado</p>', $elementor );
		$result = Content_Extractor::extract( 1 );
		assert_same( 'elementor', $result['source_kind'], 'Elementor válido deve ser fonte efetiva.' );
		assert_same( 'native', $result['elementor_compatibility']['status'], 'Elementor válido deve ser native.' );
		assert_true( in_array( 'Conteúdo principal', fragment_texts( $result ), true ), 'Conteúdo Elementor não extraído.' );
		assert_true( ! in_array( 'Residual legado', fragment_texts( $result ), true ), 'Residual não deve duplicar sem fallback/mixed.' );
	};

	$tests['elementor_invalid_falls_back'] = static function (): void {
		reset_fixture( '<h2>Fallback</h2><p>Legado preservado</p>', '{json-invalido' );
		$result = Content_Extractor::extract( 1 );
		assert_same( 'legacy_html', $result['source_kind'], 'JSON inválido deve cair para legacy.' );
		assert_true( $result['fallback_used'], 'Fallback deve ser marcado.' );
		assert_true( in_array( 'ELEMENTOR_JSON_INVALID', $result['warnings'], true ), 'Warning JSON inválido ausente.' );
		assert_same( 'review_required', $result['elementor_compatibility']['status'], 'Migração futura deve exigir review.' );
	};

	$tests['mixed_elementor_gutenberg'] = static function (): void {
		$content = '<!-- wp:paragraph --><p>Bloco</p><!-- /wp:paragraph -->';
		$elementor = json_encode( array(
			array( 'elType' => 'widget', 'widgetType' => 'text-editor', 'settings' => array( 'editor' => '<p>Elementor</p>' ), 'elements' => array() ),
		) );
		reset_fixture( $content, $elementor );
		set_blocks( $content, array(
			array( 'blockName' => 'core/paragraph', 'innerHTML' => '<p>Bloco</p>', 'innerBlocks' => array() ),
		) );
		$result = Content_Extractor::extract( 1 );
		assert_same( 'mixed', $result['source_kind'], 'Mixed deve preservar as duas proveniências.' );
		assert_true( in_array( 'Elementor', fragment_texts( $result ), true ), 'Parte Elementor ausente.' );
		assert_true( in_array( 'Bloco', fragment_texts( $result ), true ), 'Parte Gutenberg ausente.' );
	};

	$tests['gutenberg_known_blocks'] = static function (): void {
		$content = '<!-- wp:heading --><h2>H</h2><!-- /wp:heading --><!-- wp:list --><ul><li>A</li></ul><!-- /wp:list -->';
		reset_fixture( $content );
		set_blocks( $content, array(
			array( 'blockName' => 'core/heading', 'innerHTML' => '<h2>H</h2>', 'innerBlocks' => array() ),
			array( 'blockName' => 'core/list', 'innerHTML' => '<ul><li>A</li></ul>', 'innerBlocks' => array() ),
		) );
		$result = Content_Extractor::extract( 1 );
		assert_same( 'gutenberg', $result['source_kind'], 'Gutenberg esperado.' );
		assert_same( 1, $result['structure']['headings'], 'Heading Gutenberg incorreto.' );
		assert_same( 1, $result['structure']['lists'], 'Lista Gutenberg incorreta.' );
	};

	$tests['gutenberg_dynamic_not_rendered'] = static function (): void {
		$content = '<!-- wp:acme/dynamic /-->';
		reset_fixture( $content );
		set_blocks( $content, array(
			array( 'blockName' => 'acme/dynamic', 'innerHTML' => '', 'innerBlocks' => array() ),
		) );
		$result = Content_Extractor::extract( 1 );
		assert_true( in_array( 'GUTENBERG_DYNAMIC_NOT_RENDERED:acme/dynamic', $result['warnings'], true ), 'Dynamic block não pode renderizar.' );
		assert_true( in_array( 'RENDER_FALLBACK_CANDIDATE', $result['warnings'], true ), 'Semântico vazio deve virar candidato, não render automático.' );
		assert_same( 'review_required', $result['elementor_compatibility']['status'], 'Dynamic block exige review para migração.' );
	};

	$tests['plain_text'] = static function (): void {
		reset_fixture( "  linha  um\r\nlinha\t dois  " );
		$result = Content_Extractor::extract( 1 );
		assert_same( 'plain_text', $result['source_kind'], 'Plain text esperado.' );
		assert_same( "linha um\nlinha dois", $result['fragments'][0]['text'], 'Normalização textual incorreta.' );
		assert_same( 'projectable', $result['elementor_compatibility']['status'], 'Plain deve ser projetável.' );
	};

	$tests['empty'] = static function (): void {
		reset_fixture( '' );
		$result = Content_Extractor::extract( 1 );
		assert_same( 'empty', $result['source_kind'], 'Empty esperado.' );
		assert_same( array(), $result['fragments'], 'Empty não pode inventar conteúdo.' );
		assert_true( ! in_array( 'SOURCE_EMPTY', $result['warnings'], true ), 'Editorialmente vazio não precisa warning de perda.' );
	};

	$tests['soft_limit_warning'] = static function (): void {
		reset_fixture( '<p>' . str_repeat( 'a', Content_Source::SOFT_LIMIT_BYTES + 32 ) . '</p>' );
		$result = Content_Extractor::extract( 1 );
		assert_true( in_array( 'SOURCE_OVERSIZE_SOFT:post_content', $result['warnings'], true ), 'Soft limit deve ser observável.' );
		assert_true( ! empty( $result['fragments'] ), 'Soft limit ainda deve processar.' );
	};

	$tests['hard_limit_no_silent_truncate'] = static function (): void {
		reset_fixture( str_repeat( 'a', Content_Source::HARD_LIMIT_BYTES + 1 ) );
		$result = Content_Extractor::extract( 1 );
		assert_same( 'empty', $result['source_kind'], 'Hard limit sem fonte alternativa não pode truncar.' );
		assert_true( in_array( 'SOURCE_OVERSIZE_HARD:post_content', $result['warnings'], true ), 'Hard limit warning ausente.' );
		assert_same( 'blocked', $result['elementor_compatibility']['status'], 'Migração deve bloquear source oversized.' );
	};

	$tests['repeatability'] = static function (): void {
		reset_fixture( '<h2>A</h2><p>B</p>' );
		$a = Content_Extractor::extract( 1 );
		$b = Content_Extractor::extract( 1 );
		assert_same( serialize( $a ), serialize( $b ), 'Mesma entrada deve produzir saída intermediária idêntica.' );
	};

	$tests['unsupported_post_type'] = static function (): void {
		reset_fixture( '<p>x</p>', '', 'page' );
		$result = Content_Extractor::extract( 1 );
		assert_true( $result instanceof \WP_Error, 'Post type fora do contrato deve falhar.' );
		assert_same( 'bdc_kb_unsupported_post_type', $result->get_error_code(), 'Código incorreto.' );
	};

	$passed = 0;
	$failed = 0;
	$failures = array();
	foreach ( $tests as $name => $test ) {
		try {
			$test();
			assert_same( 0, $GLOBALS['bdc_write_count'], "{$name}: extractor não pode escrever." );
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
