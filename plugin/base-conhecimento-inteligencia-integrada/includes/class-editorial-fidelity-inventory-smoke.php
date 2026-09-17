<?php
/**
 * Full-corpus, read-only Editorial Fidelity inventory for SPEC-004 / G-245 / T094.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Editorial_Fidelity_Inventory_Smoke {

	public const ACTION = 'bdc_kb_spec004_g245_editorial_fidelity_smoke';
	public const PAGE_SLUG = 'bdc-kb-spec004-g245-editorial-fidelity-smoke';

	private const NONCE_ACTION = 'bdc_kb_spec004_g245_editorial_fidelity_smoke_run';
	private const NONCE_FIELD = 'bdc_kb_spec004_g245_editorial_fidelity_smoke_nonce';

	/** @var array<int,string> */
	private const INLINE_TAGS = array(
		'strong', 'b', 'em', 'i', 'u', 'mark', 's', 'del', 'sup', 'sub', 'small', 'code',
	);

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 37 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Editorial Fidelity G-245',
			'Editorial Fidelity G-245',
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
		echo '<h1>' . esc_html__( 'SPEC-004 — T094 Editorial Fidelity Inventory', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Read-only.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'Inventaria links, mídia, rich text, shortcodes e dependências de source sem exportar conteúdo, URLs ou IDs de posts.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar T094 e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
		echo '</form></div>';
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

		$report = self::run();
		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( esc_html__( 'Falha ao serializar relatório.', 'bdc-knowledge-base' ), '', array( 'response' => 500 ) );
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-t094-editorial-fidelity-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$ids_before = self::post_ids();
		$fingerprint_before = self::editorial_fingerprint( $ids_before );

		$summary = array(
			'posts_scanned' => 0,
			'errors' => 0,
			'throwables' => 0,
			'source_kind' => array(),
			'fidelity_class' => array(),
			'source_fidelity_matrix' => array(),
			'post_content_features' => self::empty_features(),
			'elementor_features' => array(
				'text_editor_widgets' => 0,
				'image_widgets' => 0,
				'shortcode_widgets' => 0,
				'html_widgets' => 0,
				'heading_widgets' => 0,
				'other_widgets' => 0,
				'editor_html_links' => 0,
				'editor_html_images' => 0,
				'editor_html_inline_formatting' => 0,
				'image_refs_with_id' => 0,
				'image_refs_with_url' => 0,
			),
			'posts_with' => array(
				'links' => 0,
				'images' => 0,
				'inline_formatting' => 0,
				'shortcodes' => 0,
				'elementor_meta' => 0,
			),
		);

		foreach ( $ids_before as $post_id ) {
			try {
				$source = Content_Source::inspect( $post_id );
				$document = Knowledge_Document::build( $post_id );
				if ( is_wp_error( $source ) || is_wp_error( $document ) ) {
					++$summary['errors'];
					continue;
				}

				$kind = (string) ( $document['source_kind'] ?? 'unknown' );
				self::inc( $summary['source_kind'], $kind );

				$content = (string) ( $source['post_content'] ?? '' );
				$content_features = self::inspect_html( $content );
				self::merge_features( $summary['post_content_features'], $content_features );

				$shortcodes = (int) ( $source['shortcodes']['total'] ?? 0 );
				if ( $shortcodes > 0 ) {
					++$summary['posts_with']['shortcodes'];
				}
				if ( (int) $content_features['links'] > 0 ) {
					++$summary['posts_with']['links'];
				}
				if ( (int) $content_features['images'] > 0 ) {
					++$summary['posts_with']['images'];
				}
				if ( (int) $content_features['inline_formatting'] > 0 ) {
					++$summary['posts_with']['inline_formatting'];
				}

				$has_elementor = true === ( $source['flags']['has_elementor_meta'] ?? false );
				if ( $has_elementor ) {
					++$summary['posts_with']['elementor_meta'];
					self::inspect_elementor(
						is_array( $source['elementor_data'] ?? null ) ? $source['elementor_data'] : array(),
						$summary['elementor_features']
					);
				}

				$fidelity_class = self::classify_fidelity( $kind, $content_features, $shortcodes, $has_elementor );
				self::inc( $summary['fidelity_class'], $fidelity_class );
				if ( ! isset( $summary['source_fidelity_matrix'][ $kind ] ) ) {
					$summary['source_fidelity_matrix'][ $kind ] = array();
				}
				self::inc( $summary['source_fidelity_matrix'][ $kind ], $fidelity_class );
				++$summary['posts_scanned'];
			} catch ( \Throwable $error ) {
				++$summary['throwables'];
			}
		}

		foreach ( $summary['source_fidelity_matrix'] as &$row ) {
			ksort( $row );
		}
		unset( $row );
		ksort( $summary['source_kind'] );
		ksort( $summary['fidelity_class'] );
		ksort( $summary['source_fidelity_matrix'] );

		$ids_after = self::post_ids();
		$fingerprint_after = self::editorial_fingerprint( $ids_after );
		$corpus_unchanged = $ids_before === $ids_after;
		$fingerprint_equal = hash_equals( $fingerprint_before, $fingerprint_after );

		$gate = $corpus_unchanged
			&& $fingerprint_equal
			&& count( $ids_before ) === (int) $summary['posts_scanned']
			&& 0 === (int) $summary['errors']
			&& 0 === (int) $summary['throwables'];

		return array(
			'schema_version' => '1.0.0',
			'gate' => 'T094',
			'mode' => 'editorial_fidelity_inventory_read_only',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'knowledge_document_schema' => Knowledge_Document::SCHEMA_VERSION,
				'block_projection_schema' => Block_Projection_Plan::SCHEMA_VERSION,
				'domdocument' => class_exists( '\DOMDocument' ),
				'gutenberg_plugin_dependency' => false,
			),
			'corpus' => array(
				'total_posts' => count( $ids_before ),
				'posts_scanned' => $summary['posts_scanned'],
				'errors' => $summary['errors'],
				'throwables' => $summary['throwables'],
			),
			'distribution' => array(
				'source_kind' => $summary['source_kind'],
				'fidelity_class' => $summary['fidelity_class'],
				'source_fidelity_matrix' => $summary['source_fidelity_matrix'],
				'posts_with' => $summary['posts_with'],
				'post_content_features' => $summary['post_content_features'],
				'elementor_features' => $summary['elementor_features'],
			),
			'safety' => array(
				'read_only_design' => true,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'writer_allowed' => false,
				'migration_execution_allowed' => false,
				'depends_on_gutenberg_plugin' => false,
				'exports_editorial_content' => false,
				'exports_urls' => false,
				'exports_post_ids' => false,
				'calls_external_network' => false,
				'renders_shortcodes' => false,
				'corpus_unchanged' => $corpus_unchanged,
				'editorial_fingerprint_before' => $fingerprint_before,
				'editorial_fingerprint_after' => $fingerprint_after,
				'editorial_fingerprint_equal' => $fingerprint_equal,
			),
			'gate_result' => array( 't094_editorial_fidelity_pass' => $gate ),
			'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
		);
	}

	/** @return array<string,int> */
	private static function empty_features(): array {
		return array(
			'links' => 0,
			'images' => 0,
			'inline_formatting' => 0,
			'styled_spans' => 0,
			'line_breaks' => 0,
			'figures' => 0,
			'figcaptions' => 0,
			'tables' => 0,
			'rowspan_cells' => 0,
			'colspan_cells' => 0,
			'attachment_urls_resolved' => 0,
			'attachment_urls_unresolved' => 0,
		);
	}

	/** @return array<string,int> */
	private static function inspect_html( string $html ): array {
		$out = self::empty_features();
		if ( '' === trim( $html ) || ! class_exists( '\DOMDocument' ) ) {
			return $out;
		}

		$previous = libxml_use_internal_errors( true );
		libxml_clear_errors();
		$dom = new \DOMDocument( '1.0', 'UTF-8' );
		$loaded = $dom->loadHTML( '<?xml encoding="utf-8" ?><!DOCTYPE html><html><body><div id="bdc-t094-root">' . $html . '</div></body></html>', LIBXML_NONET | LIBXML_COMPACT );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
		if ( ! $loaded ) {
			return $out;
		}

		$xpath = new \DOMXPath( $dom );
		$root = $dom->getElementById( 'bdc-t094-root' );
		if ( ! $root instanceof \DOMElement ) {
			$roots = $xpath->query( '//*[@id="bdc-t094-root"]' );
			$root = ( $roots && $roots->length > 0 ) ? $roots->item( 0 ) : null;
		}
		if ( ! $root instanceof \DOMElement ) {
			return $out;
		}

		$out['links'] = self::query_count( $xpath, './/a[@href]', $root );
		$out['images'] = self::query_count( $xpath, './/img[@src]', $root );
		$out['styled_spans'] = self::query_count( $xpath, './/span[@style or @class]', $root );
		$out['line_breaks'] = self::query_count( $xpath, './/br', $root );
		$out['figures'] = self::query_count( $xpath, './/figure', $root );
		$out['figcaptions'] = self::query_count( $xpath, './/figcaption', $root );
		$out['tables'] = self::query_count( $xpath, './/table', $root );
		$out['rowspan_cells'] = self::query_count( $xpath, './/td[@rowspan]|.//th[@rowspan]', $root );
		$out['colspan_cells'] = self::query_count( $xpath, './/td[@colspan]|.//th[@colspan]', $root );

		$inline = 0;
		foreach ( self::INLINE_TAGS as $tag ) {
			$inline += self::query_count( $xpath, './/' . $tag, $root );
		}
		$out['inline_formatting'] = $inline;

		$images = $xpath->query( './/img[@src]', $root );
		if ( $images ) {
			foreach ( $images as $image ) {
				if ( ! $image instanceof \DOMElement ) {
					continue;
				}
				$src = trim( $image->getAttribute( 'src' ) );
				if ( '' === $src ) {
					continue;
				}
				$attachment_id = function_exists( 'attachment_url_to_postid' ) ? (int) attachment_url_to_postid( $src ) : 0;
				if ( $attachment_id > 0 ) {
					++$out['attachment_urls_resolved'];
				} else {
					++$out['attachment_urls_unresolved'];
				}
			}
		}
		return $out;
	}

	/** @param array<string,int> $target @param array<string,int> $source */
	private static function merge_features( array &$target, array $source ): void {
		foreach ( $target as $key => $value ) {
			$target[ $key ] = (int) $value + (int) ( $source[ $key ] ?? 0 );
		}
	}

	/** @param array<int|string,mixed> $nodes @param array<string,int> $out */
	private static function inspect_elementor( array $nodes, array &$out ): void {
		foreach ( $nodes as $node ) {
			if ( ! is_array( $node ) ) {
				continue;
			}
			$widget = isset( $node['widgetType'] ) && is_string( $node['widgetType'] ) ? $node['widgetType'] : '';
			$settings = is_array( $node['settings'] ?? null ) ? $node['settings'] : array();
			if ( '' !== $widget ) {
				switch ( $widget ) {
					case 'text-editor':
						++$out['text_editor_widgets'];
						$editor = isset( $settings['editor'] ) && is_scalar( $settings['editor'] ) ? (string) $settings['editor'] : '';
						$f = self::inspect_html( $editor );
						$out['editor_html_links'] += (int) $f['links'];
						$out['editor_html_images'] += (int) $f['images'];
						$out['editor_html_inline_formatting'] += (int) $f['inline_formatting'];
						break;
					case 'image':
						++$out['image_widgets'];
						$image = is_array( $settings['image'] ?? null ) ? $settings['image'] : array();
						if ( (int) ( $image['id'] ?? 0 ) > 0 ) {
							++$out['image_refs_with_id'];
						}
						if ( isset( $image['url'] ) && is_string( $image['url'] ) && '' !== trim( $image['url'] ) ) {
							++$out['image_refs_with_url'];
						}
						break;
					case 'shortcode': ++$out['shortcode_widgets']; break;
					case 'html': ++$out['html_widgets']; break;
					case 'heading': ++$out['heading_widgets']; break;
					default: ++$out['other_widgets']; break;
				}
			}
			if ( isset( $node['elements'] ) && is_array( $node['elements'] ) ) {
				self::inspect_elementor( $node['elements'], $out );
			}
		}
	}

	/** @param array<string,int> $features */
	private static function classify_fidelity( string $source_kind, array $features, int $shortcodes, bool $has_elementor ): string {
		if ( 'empty' === $source_kind ) { return 'not_applicable'; }
		if ( 'gutenberg' === $source_kind ) { return 'native_core_blocks'; }
		if ( $has_elementor || in_array( $source_kind, array( 'elementor', 'mixed' ), true ) ) { return 'elementor_source_adapter_required'; }
		if ( $shortcodes > 0 ) { return 'shortcode_resolution_required'; }
		if ( (int) ( $features['links'] ?? 0 ) > 0 || (int) ( $features['images'] ?? 0 ) > 0 || (int) ( $features['inline_formatting'] ?? 0 ) > 0 || (int) ( $features['styled_spans'] ?? 0 ) > 0 || (int) ( $features['figures'] ?? 0 ) > 0 ) { return 'rich_html_source_required'; }
		if ( (int) ( $features['rowspan_cells'] ?? 0 ) > 0 || (int) ( $features['colspan_cells'] ?? 0 ) > 0 ) { return 'complex_table_source_required'; }
		return 'kd_structure_sufficient_candidate';
	}

	private static function query_count( \DOMXPath $xpath, string $query, \DOMNode $context ): int {
		$nodes = $xpath->query( $query, $context );
		return $nodes ? $nodes->length : 0;
	}

	/** @return array<int,int> */
	private static function post_ids(): array {
		$ids = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC', 'suppress_filters' => false ) );
		$ids = is_array( $ids ) ? array_map( 'intval', $ids ) : array();
		sort( $ids, SORT_NUMERIC );
		return array_values( array_filter( $ids, static fn( int $id ): bool => $id > 0 ) );
	}

	/** @param array<int,int> $post_ids */
	private static function editorial_fingerprint( array $post_ids ): string {
		$rows = array();
		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) { continue; }
			$rows[] = array(
				'id' => $post_id,
				'content_sha256' => hash( 'sha256', (string) ( $post->post_content ?? '' ) ),
				'elementor_sha256' => hash( 'sha256', (string) get_post_meta( $post_id, '_elementor_data', true ) ),
				'status' => (string) ( $post->post_status ?? '' ),
				'modified_gmt' => (string) ( $post->post_modified_gmt ?? '' ),
			);
		}
		return Canonical_JSON::hash( $rows );
	}

	/** @param array<string,int> $bucket */
	private static function inc( array &$bucket, string $key ): void {
		$key = '' !== $key ? $key : 'unknown';
		$bucket[ $key ] = (int) ( $bucket[ $key ] ?? 0 ) + 1;
	}
}
