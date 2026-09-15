<?php
/**
 * Profiler read-only temporário da SPEC-004.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mede o corpus editorial sem exportar conteúdo e sem executar writers/renderers.
 *
 * TEMPORÁRIO: remover antes do RC da SPEC-004.
 */
final class Content_Profile {

	public const ACTION    = 'bdc_kb_spec004_content_profile';
	public const PAGE_SLUG = 'bdc-kb-spec004-content-profile';

	private const NONCE_ACTION = 'bdc_kb_spec004_content_profile_run';
	private const NONCE_FIELD  = 'bdc_kb_spec004_content_profile_nonce';

	/** @var array<int,string> */
	private const ELEMENTOR_SEMANTIC_KEYS = array(
		'editor',
		'title',
		'description',
		'text',
		'content',
		'html',
		'caption',
		'alert_title',
		'alert_description',
		'tab_title',
		'tab_content',
		'accordion_title',
		'accordion_content',
		'toggle_title',
		'toggle_content',
		'button_text',
		'shortcode',
	);

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 30 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Profiler SPEC-004',
			'Profiler SPEC-004',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ) );
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Profiler SPEC-004 — Content Extractor', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Build temporário de descoberta.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'O profiler é somente leitura, não executa shortcodes/widgets e não exporta conteúdo textual, títulos, URLs ou IDs de posts.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<p>' . esc_html__( 'O processamento percorre o corpus de posts e gera um JSON com distribuição de Elementor, Gutenberg, HTML legado, widgets, blocos, shortcodes, estrutura, tamanhos e fingerprint editorial before/after.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar profiler read-only e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
		echo '</form>';
		echo '</div>';
	}

	public static function handle_run(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( esc_html__( 'Método HTTP não permitido.', 'bdc-knowledge-base' ), '', array( 'response' => 405 ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] )
			? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] )
			: '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Nonce inválido ou expirado.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		$report   = self::run();
		$filename = 'bdc-kb-spec004-content-profile-' . gmdate( 'Ymd-His' ) . '.json';
		$json     = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

		if ( ! is_string( $json ) ) {
			wp_die( esc_html__( 'Falha ao serializar o relatório.', 'bdc-knowledge-base' ), '', array( 'response' => 500 ) );
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/**
	 * @return array<string,mixed>
	 */
	private static function run(): array {
		$started = microtime( true );

		$ids_before       = self::post_ids();
		$snapshot_before  = self::editorial_snapshot( $ids_before );
		$aggregate_before = self::aggregate_fingerprint( $snapshot_before );

		$status_counts = array();
		$source_kinds  = array(
			'mixed_elementor_blocks' => 0,
			'elementor'              => 0,
			'gutenberg'              => 0,
			'legacy_html'            => 0,
			'shortcode_plain'        => 0,
			'plain_text'             => 0,
			'empty'                  => 0,
		);
		$flags = array(
			'has_elementor'   => 0,
			'has_gutenberg'   => 0,
			'has_html'        => 0,
			'has_shortcode'   => 0,
			'has_plain_text'  => 0,
		);
		$elementor = array(
			'present'                               => 0,
			'valid_json'                            => 0,
			'invalid_json'                          => 0,
			'unexpected_meta_type'                  => 0,
			'posts_without_known_semantic_fields'   => 0,
			'widget_types'                          => array(),
			'semantic_field_hits'                   => array(),
			'unknown_semantic_like_keys'            => array(),
		);
		$gutenberg = array(
			'posts'       => 0,
			'block_names' => array(),
			'structural_blocks' => array(
				'headings'    => 0,
				'lists'       => 0,
				'tables'      => 0,
				'images'      => 0,
				'links'       => 0,
				'code_blocks' => 0,
			),
		);
		$shortcodes = array(
			'tags'  => array(),
			'total' => 0,
		);
		$raw_structure = array(
			'headings'    => 0,
			'lists'       => 0,
			'tables'      => 0,
			'images'      => 0,
			'links'       => 0,
			'code_blocks' => 0,
			'shortcodes'  => 0,
		);
		$post_content_sizes = array();
		$elementor_sizes    = array();

		foreach ( $ids_before as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				continue;
			}

			$status = (string) $post->post_status;
			self::increment( $status_counts, $status );

			$content = (string) $post->post_content;
			$post_content_sizes[] = strlen( $content );

			$raw_elementor = get_post_meta( $post_id, '_elementor_data', true );
			$elementor_string = is_string( $raw_elementor ) ? $raw_elementor : '';
			$elementor_sizes[] = strlen( $elementor_string );

			$has_elementor = '' !== trim( $elementor_string );
			$has_blocks     = self::has_blocks( $content );
			$has_html       = self::has_html( $content );
			$has_plain      = '' !== trim( wp_strip_all_tags( $content ) );
			$content_shortcode_count = self::collect_shortcodes( $content, $shortcodes['tags'] );
			$has_shortcode  = $content_shortcode_count > 0;
			$shortcodes['total'] += $content_shortcode_count;

			if ( $has_elementor ) {
				$flags['has_elementor']++;
			}
			if ( $has_blocks ) {
				$flags['has_gutenberg']++;
			}
			if ( $has_html ) {
				$flags['has_html']++;
			}
			if ( $has_shortcode ) {
				$flags['has_shortcode']++;
			}
			if ( $has_plain ) {
				$flags['has_plain_text']++;
			}

			self::collect_raw_structure( $content, $raw_structure );

			if ( $has_blocks ) {
				$gutenberg['posts']++;
				$blocks = parse_blocks( $content );
				if ( is_array( $blocks ) ) {
					self::collect_blocks( $blocks, $gutenberg['block_names'], $gutenberg['structural_blocks'] );
				}
			}

			if ( $has_elementor ) {
				$elementor['present']++;
				$data = json_decode( $elementor_string, true );
				if ( is_array( $data ) ) {
					$elementor['valid_json']++;
					$semantic_hits_before = array_sum( $elementor['semantic_field_hits'] );
					$shortcodes_before    = array_sum( $shortcodes['tags'] );
					self::collect_elementor(
						$data,
						$elementor['widget_types'],
						$elementor['semantic_field_hits'],
						$elementor['unknown_semantic_like_keys'],
						$shortcodes['tags']
					);
					$semantic_hits_after = array_sum( $elementor['semantic_field_hits'] );
					$shortcodes_after    = array_sum( $shortcodes['tags'] );
					$shortcodes['total'] += max( 0, $shortcodes_after - $shortcodes_before );
					if ( $semantic_hits_after === $semantic_hits_before ) {
						$elementor['posts_without_known_semantic_fields']++;
					}
				} else {
					$elementor['invalid_json']++;
				}
			} elseif ( ! is_string( $raw_elementor ) && ! empty( $raw_elementor ) ) {
				$elementor['unexpected_meta_type']++;
			}

			$source_kind = self::source_kind( $has_elementor, $has_blocks, $has_html, $has_shortcode, $has_plain, $content );
			$source_kinds[ $source_kind ]++;
		}

		self::sort_counts( $status_counts );
		self::sort_counts( $elementor['widget_types'] );
		self::sort_counts( $elementor['semantic_field_hits'] );
		self::sort_counts( $elementor['unknown_semantic_like_keys'] );
		self::sort_counts( $gutenberg['block_names'] );
		self::sort_counts( $shortcodes['tags'] );

		$ids_after       = self::post_ids();
		$snapshot_after  = self::editorial_snapshot( $ids_after );
		$aggregate_after = self::aggregate_fingerprint( $snapshot_after );
		$changed_count   = self::changed_snapshot_count( $snapshot_before, $snapshot_after );

		return array(
			'schema_version' => '1.0.0',
			'mode'           => 'temporary_spec004_read_only_corpus_profile',
			'generated_at'   => gmdate( 'c' ),
			'environment'    => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php'       => PHP_VERSION,
				'plugin'    => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'multisite' => is_multisite(),
			),
			'safety' => array(
				'read_only_design'              => true,
				'exports_editorial_content'      => false,
				'exports_post_ids'               => false,
				'exports_titles_or_urls'         => false,
				'executes_shortcodes'            => false,
				'renders_elementor'              => false,
				'renders_dynamic_blocks'         => false,
				'persists_progress_or_results'   => false,
				'editorial_fingerprint_before'   => $aggregate_before,
				'editorial_fingerprint_after'    => $aggregate_after,
				'editorial_fingerprint_equal'    => hash_equals( $aggregate_before, $aggregate_after ),
				'changed_posts_during_run'       => $changed_count,
				'corpus_count_before'            => count( $ids_before ),
				'corpus_count_after'             => count( $ids_after ),
			),
			'corpus' => array(
				'total_posts'      => count( $ids_before ),
				'by_status'        => $status_counts,
				'source_kinds'     => $source_kinds,
				'source_flags'     => $flags,
			),
			'elementor' => $elementor,
			'gutenberg' => $gutenberg,
			'shortcodes' => $shortcodes,
			'raw_post_content_structure' => $raw_structure,
			'sizes_bytes' => array(
				'post_content'   => self::size_summary( $post_content_sizes ),
				'elementor_data' => self::size_summary( $elementor_sizes ),
			),
			'performance' => array(
				'runtime_ms'        => (int) round( ( microtime( true ) - $started ) * 1000 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'limitations' => array(
				'No shortcode, Elementor widget, dynamic block or theme rendering is executed.',
				'Fallback candidates are heuristic: Elementor JSON present but no known semantic field was found.',
				'A changed fingerprint can also indicate a concurrent editorial change during the profiling window.',
			),
		);
	}

	/** @return array<int,int> */
	private static function post_ids(): array {
		$ids = get_posts(
			array(
				'post_type'        => 'post',
				'post_status'      => 'any',
				'numberposts'      => -1,
				'fields'           => 'ids',
				'orderby'          => 'ID',
				'order'            => 'ASC',
				'no_found_rows'    => true,
				'suppress_filters' => false,
			)
		);

		return array_values( array_map( 'intval', is_array( $ids ) ? $ids : array() ) );
	}

	/**
	 * @param array<int,int> $post_ids IDs.
	 * @return array<int,string>
	 */
	private static function editorial_snapshot( array $post_ids ): array {
		$out = array();
		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				$out[ $post_id ] = 'missing';
				continue;
			}

			$elementor = get_post_meta( $post_id, '_elementor_data', true );
			$elementor_fingerprint_value = self::fingerprint_value( $elementor );
			$payload = implode(
				'|',
				array(
					(string) $post_id,
					(string) $post->post_status,
					(string) $post->post_modified_gmt,
					hash( 'sha256', (string) $post->post_content ),
					hash( 'sha256', $elementor_fingerprint_value ),
				)
			);
			$out[ $post_id ] = hash( 'sha256', $payload );
		}
		ksort( $out, SORT_NUMERIC );
		return $out;
	}

	private static function fingerprint_value( mixed $value ): string {
		if ( is_string( $value ) ) {
			return $value;
		}
		if ( is_scalar( $value ) || null === $value ) {
			return (string) $value;
		}
		$json = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $json ) ? $json : maybe_serialize( $value );
	}

	/** @param array<int,string> $snapshot */
	private static function aggregate_fingerprint( array $snapshot ): string {
		$context = hash_init( 'sha256' );
		foreach ( $snapshot as $post_id => $signature ) {
			hash_update( $context, (string) $post_id . ':' . $signature . "\n" );
		}
		return hash_final( $context );
	}

	/**
	 * @param array<int,string> $before Antes.
	 * @param array<int,string> $after Depois.
	 */
	private static function changed_snapshot_count( array $before, array $after ): int {
		$ids = array_unique( array_merge( array_keys( $before ), array_keys( $after ) ) );
		$count = 0;
		foreach ( $ids as $post_id ) {
			if ( ! isset( $before[ $post_id ], $after[ $post_id ] ) || $before[ $post_id ] !== $after[ $post_id ] ) {
				$count++;
			}
		}
		return $count;
	}

	private static function has_blocks( string $content ): bool {
		return function_exists( 'has_blocks' ) ? has_blocks( $content ) : str_contains( $content, '<!-- wp:' );
	}

	private static function has_html( string $content ): bool {
		return 1 === preg_match( '/<\/?[A-Za-z][^>]*>/u', $content );
	}

	private static function source_kind( bool $elementor, bool $blocks, bool $html, bool $shortcode, bool $plain, string $content ): string {
		if ( $elementor && $blocks ) {
			return 'mixed_elementor_blocks';
		}
		if ( $elementor ) {
			return 'elementor';
		}
		if ( $blocks ) {
			return 'gutenberg';
		}
		if ( $html ) {
			return 'legacy_html';
		}
		if ( $shortcode ) {
			return 'shortcode_plain';
		}
		if ( $plain || '' !== trim( $content ) ) {
			return 'plain_text';
		}
		return 'empty';
	}

	/** @param array<string,int> $counts */
	private static function increment( array &$counts, string $key, int $amount = 1 ): void {
		if ( '' === $key ) {
			$key = '(empty)';
		}
		$counts[ $key ] = (int) ( $counts[ $key ] ?? 0 ) + $amount;
	}

	/** @param array<string,int> $counts */
	private static function sort_counts( array &$counts ): void {
		arsort( $counts, SORT_NUMERIC );
	}

	/**
	 * @param array<string,int> $tags Contagens por tag.
	 */
	private static function collect_shortcodes( string $value, array &$tags ): int {
		if ( '' === $value || ! str_contains( $value, '[' ) ) {
			return 0;
		}
		$count = 0;
		if ( preg_match_all( '/\[(?!\/)([A-Za-z][A-Za-z0-9_-]*)\b[^\]]*\]/u', $value, $matches ) ) {
			foreach ( $matches[1] as $tag ) {
				self::increment( $tags, strtolower( (string) $tag ) );
				$count++;
			}
		}
		return $count;
	}

	/** @param array<string,int> $structure */
	private static function collect_raw_structure( string $content, array &$structure ): void {
		$patterns = array(
			'headings'    => '/<h[1-6]\b/iu',
			'lists'       => '/<(?:ul|ol)\b/iu',
			'tables'      => '/<table\b/iu',
			'images'      => '/<img\b/iu',
			'links'       => '/<a\b/iu',
			'code_blocks' => '/<(?:pre|code)\b/iu',
		);
		foreach ( $patterns as $key => $pattern ) {
			$count = preg_match_all( $pattern, $content, $matches );
			$structure[ $key ] += false === $count ? 0 : $count;
		}
		$tags = array();
		$structure['shortcodes'] += self::collect_shortcodes( $content, $tags );
	}

	/**
	 * @param array<int,array<string,mixed>> $blocks Blocos.
	 * @param array<string,int>              $block_names Contagens.
	 * @param array<string,int>              $structure Estrutura.
	 */
	private static function collect_blocks( array $blocks, array &$block_names, array &$structure ): void {
		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}
			$name = isset( $block['blockName'] ) && is_string( $block['blockName'] ) ? $block['blockName'] : 'core/freeform';
			self::increment( $block_names, $name );

			switch ( $name ) {
				case 'core/heading':
					$structure['headings']++;
					break;
				case 'core/list':
					$structure['lists']++;
					break;
				case 'core/table':
					$structure['tables']++;
					break;
				case 'core/image':
				case 'core/gallery':
					$structure['images']++;
					break;
				case 'core/code':
				case 'core/preformatted':
					$structure['code_blocks']++;
					break;
			}

			$inner_html = isset( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ? $block['innerHTML'] : '';
			$link_count = preg_match_all( '/<a\b/iu', $inner_html, $links );
			$structure['links'] += false === $link_count ? 0 : $link_count;

			$inner = isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ? $block['innerBlocks'] : array();
			if ( $inner ) {
				self::collect_blocks( $inner, $block_names, $structure );
			}
		}
	}

	/**
	 * @param mixed             $node Nó Elementor.
	 * @param array<string,int> $widget_types Widgets.
	 * @param array<string,int> $semantic_hits Campos conhecidos.
	 * @param array<string,int> $unknown_keys Campos semantic-like desconhecidos.
	 * @param array<string,int> $shortcode_tags Tags encontradas.
	 */
	private static function collect_elementor( mixed $node, array &$widget_types, array &$semantic_hits, array &$unknown_keys, array &$shortcode_tags ): void {
		if ( ! is_array( $node ) ) {
			return;
		}

		if ( isset( $node['widgetType'] ) && is_string( $node['widgetType'] ) && '' !== trim( $node['widgetType'] ) ) {
			self::increment( $widget_types, strtolower( trim( $node['widgetType'] ) ) );
		}

		foreach ( $node as $key => $value ) {
			if ( is_array( $value ) ) {
				self::collect_elementor( $value, $widget_types, $semantic_hits, $unknown_keys, $shortcode_tags );
				continue;
			}
			if ( ! is_string( $value ) || '' === trim( $value ) ) {
				continue;
			}

			$key_string = (string) $key;
			if ( in_array( $key_string, self::ELEMENTOR_SEMANTIC_KEYS, true ) ) {
				self::increment( $semantic_hits, $key_string );
			} elseif ( self::looks_semantic_key( $key_string ) ) {
				self::increment( $unknown_keys, $key_string );
			}

			self::collect_shortcodes( $value, $shortcode_tags );
		}
	}

	private static function looks_semantic_key( string $key ): bool {
		$key = strtolower( $key );
		foreach ( array( 'title', 'text', 'content', 'description', 'caption', 'label', 'message', 'html', 'shortcode' ) as $needle ) {
			if ( str_contains( $key, $needle ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param array<int,int> $values Valores em bytes.
	 * @return array{count:int,total:int,p50:int,p95:int,max:int}
	 */
	private static function size_summary( array $values ): array {
		if ( empty( $values ) ) {
			return array( 'count' => 0, 'total' => 0, 'p50' => 0, 'p95' => 0, 'max' => 0 );
		}
		sort( $values, SORT_NUMERIC );
		$count = count( $values );
		return array(
			'count' => $count,
			'total' => array_sum( $values ),
			'p50'   => self::percentile( $values, 50 ),
			'p95'   => self::percentile( $values, 95 ),
			'max'   => (int) $values[ $count - 1 ],
		);
	}

	/** @param array<int,int> $sorted */
	private static function percentile( array $sorted, int $percent ): int {
		$count = count( $sorted );
		if ( 0 === $count ) {
			return 0;
		}
		$index = (int) ceil( ( $percent / 100 ) * $count ) - 1;
		$index = max( 0, min( $count - 1, $index ) );
		return (int) $sorted[ $index ];
	}
}
