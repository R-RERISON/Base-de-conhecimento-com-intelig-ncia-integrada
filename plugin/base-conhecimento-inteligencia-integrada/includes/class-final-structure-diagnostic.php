<?php
/**
 * Diagnóstico final read-only dos resíduos estruturais da SPEC-004.
 * Exporta somente agregados; nunca IDs, títulos, URLs ou conteúdo editorial.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Final_Structure_Diagnostic {
	public const ACTION = 'bdc_kb_spec004_final_structure_diag';
	public const PAGE_SLUG = 'bdc-kb-spec004-final-structure-diag';
	private const NONCE_ACTION = 'bdc_kb_spec004_final_structure_diag_run';
	private const NONCE_FIELD = 'bdc_kb_spec004_final_structure_diag_nonce';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 33 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Diagnóstico Estrutural Final',
			'Diagnóstico Estrutural Final',
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
		echo '<h1>' . esc_html__( 'SPEC-004 — Diagnóstico Estrutural Final', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Build temporário de homologação.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'Analisa apenas contexto estrutural agregado dos documentos ainda not_ready. Não exporta IDs, títulos, URLs ou conteúdo.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar diagnóstico final e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
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
			? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Nonce inválido ou expirado.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		$report = self::run();
		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( esc_html__( 'Falha ao serializar o relatório.', 'bdc-knowledge-base' ), '', array( 'response' => 500 ) );
		}
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-final-structure-diag-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$ids = self::post_ids();
		$before = self::snapshot( $ids );
		$before_hash = self::aggregate_fingerprint( $before );
		$out = array(
			'not_ready_documents' => 0,
			'errors' => 0,
			'throwables' => 0,
			'by_source_kind' => array(),
			'by_signature' => array(),
			'contexts_total' => self::empty_contexts(),
			'no_structure_signature' => 0,
		);

		foreach ( $ids as $post_id ) {
			try {
				$document = Knowledge_Document::build( $post_id );
				if ( is_wp_error( $document ) ) {
					++$out['errors'];
					continue;
				}
				$readiness = is_array( $document['ai_readiness'] ?? null ) ? $document['ai_readiness'] : array();
				if ( 'not_ready' !== (string) ( $readiness['status'] ?? '' ) ) {
					continue;
				}
				++$out['not_ready_documents'];
				$source_kind = (string) ( $document['source_kind'] ?? 'unknown' );
				self::increment( $out['by_source_kind'], $source_kind );
				$signatures = array_values( array_filter(
					array_map( 'strval', (array) ( $readiness['reasons'] ?? array() ) ),
					static fn ( string $reason ): bool => str_starts_with( $reason, 'STRUCTURE_COUNT_MISMATCH:' )
				) );
				if ( empty( $signatures ) ) {
					++$out['no_structure_signature'];
				}

				$source = Content_Source::inspect( $post_id );
				if ( $source instanceof \WP_Error ) {
					++$out['errors'];
					continue;
				}
				$contexts = self::empty_contexts();
				foreach ( self::html_segments( $source, $source_kind ) as $html ) {
					self::merge_contexts( $contexts, self::analyze_html( $html ) );
				}
				self::merge_contexts( $out['contexts_total'], $contexts );

				foreach ( $signatures as $signature ) {
					$key = $source_kind . '|' . $signature;
					if ( ! isset( $out['by_signature'][ $key ] ) ) {
						$out['by_signature'][ $key ] = array( 'documents' => 0, 'contexts' => self::empty_contexts() );
					}
					++$out['by_signature'][ $key ]['documents'];
					self::merge_contexts( $out['by_signature'][ $key ]['contexts'], $contexts );
				}
			} catch ( \Throwable $throwable ) {
				++$out['throwables'];
			}
		}

		$after = self::snapshot( self::post_ids() );
		$after_hash = self::aggregate_fingerprint( $after );
		ksort( $out['by_source_kind'] );
		ksort( $out['by_signature'] );

		return array(
			'schema_version' => '1.0.0',
			'mode' => 'temporary_spec004_final_structure_diagnostic',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'elementor' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null,
				'knowledge_document_schema' => Knowledge_Document::SCHEMA_VERSION,
				'domdocument' => class_exists( '\\DOMDocument' ),
			),
			'safety' => array(
				'read_only_design' => true,
				'exports_editorial_content' => false,
				'exports_post_ids' => false,
				'exports_titles_or_urls' => false,
				'fingerprint_before' => $before_hash,
				'fingerprint_after' => $after_hash,
				'fingerprint_equal' => hash_equals( $before_hash, $after_hash ),
				'changed_posts_during_run' => self::changed_snapshot_count( $before, $after ),
			),
			'diagnostic' => $out,
			'performance' => array(
				'runtime_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
		);
	}

	/** @param array<string,mixed> $source @return array<int,string> */
	private static function html_segments( array $source, string $source_kind ): array {
		$segments = array();
		if ( in_array( $source_kind, array( 'legacy_html', 'gutenberg', 'mixed' ), true ) ) {
			$content = (string) ( $source['post_content'] ?? '' );
			if ( '' !== trim( $content ) ) {
				$segments[] = $content;
			}
		}
		if ( in_array( $source_kind, array( 'elementor', 'mixed' ), true ) && is_array( $source['elementor_data'] ?? null ) ) {
			self::collect_elementor_html( $source['elementor_data'], $segments );
		}
		return $segments;
	}

	/** @param array<int|string,mixed> $nodes @param array<int,string> $segments */
	private static function collect_elementor_html( array $nodes, array &$segments ): void {
		foreach ( $nodes as $node ) {
			if ( ! is_array( $node ) ) {
				continue;
			}
			$widget = isset( $node['widgetType'] ) && is_string( $node['widgetType'] ) ? $node['widgetType'] : '';
			$settings = is_array( $node['settings'] ?? null ) ? $node['settings'] : array();
			if ( 'text-editor' === $widget && isset( $settings['editor'] ) && is_scalar( $settings['editor'] ) ) {
				$html = (string) $settings['editor'];
				if ( '' !== trim( $html ) ) {
					$segments[] = $html;
				}
			} elseif ( 'shortcode' === $widget && isset( $settings['shortcode'] ) && is_scalar( $settings['shortcode'] ) ) {
				$inspection = Shortcode_Inspector::inspect( (string) $settings['shortcode'] );
				foreach ( $inspection['matches'] as $match ) {
					$inner = isset( $match['inner'] ) ? (string) $match['inner'] : '';
					if ( '' !== trim( $inner ) ) {
						$segments[] = $inner;
					}
				}
			}
			if ( isset( $node['elements'] ) && is_array( $node['elements'] ) ) {
				self::collect_elementor_html( $node['elements'], $segments );
			}
		}
	}

	/** @return array<string,int> */
	private static function analyze_html( string $html ): array {
		$out = self::empty_contexts();
		if ( '' === trim( $html ) || ! class_exists( '\\DOMDocument' ) ) {
			return $out;
		}
		$previous = libxml_use_internal_errors( true );
		libxml_clear_errors();
		$dom = new \DOMDocument( '1.0', 'UTF-8' );
		$wrapped = '<!DOCTYPE html><html><body><div id="bdc-kb-final-diag-root">' . $html . '</div></body></html>';
		$loaded = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_NONET | LIBXML_COMPACT );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
		if ( ! $loaded ) {
			return $out;
		}
		$xpath = new \DOMXPath( $dom );
		$nodes = $xpath->query( '//*[@id="bdc-kb-final-diag-root"]' );
		$root = ( $nodes && $nodes->length > 0 ) ? $nodes->item( 0 ) : null;
		if ( ! $root instanceof \DOMElement ) {
			return $out;
		}

		$lists = $xpath->query( './/ul|.//ol', $root );
		if ( $lists ) {
			foreach ( $lists as $list ) {
				if ( ! $list instanceof \DOMElement || self::has_ancestor( $list, $root, array( 'table' ) ) || ! self::has_direct_child( $list, 'li' ) ) {
					continue;
				}
				++$out['lists_semantic'];
				if ( self::has_ancestor( $list, $root, array( 'pre', 'code' ) ) ) { ++$out['lists_inside_code']; }
				if ( self::has_ancestor( $list, $root, array( 'h1','h2','h3','h4','h5','h6' ) ) ) { ++$out['lists_inside_heading']; }
				if ( self::has_ancestor( $list, $root, array( 'li', 'ul', 'ol' ) ) ) { ++$out['lists_nested_in_list']; }
				if ( self::parser_unreachable( $list, $root ) ) { ++$out['lists_parser_unreachable']; }
			}
		}

		$tables = $xpath->query( './/table', $root );
		if ( $tables ) {
			foreach ( $tables as $table ) {
				if ( ! $table instanceof \DOMElement || self::has_ancestor( $table, $root, array( 'table' ) ) || 0 === self::direct_row_count( $table ) ) {
					continue;
				}
				++$out['tables_semantic'];
				if ( self::has_ancestor( $table, $root, array( 'pre', 'code' ) ) ) { ++$out['tables_inside_code']; }
				if ( self::has_ancestor( $table, $root, array( 'h1','h2','h3','h4','h5','h6' ) ) ) { ++$out['tables_inside_heading']; }
				if ( self::parser_unreachable( $table, $root ) ) { ++$out['tables_parser_unreachable']; }
				$has_text = false;
				$has_image_alt = false;
				foreach ( self::direct_rows( $table ) as $row ) {
					foreach ( $row->childNodes as $cell ) {
						if ( ! $cell instanceof \DOMElement || ! in_array( strtolower( $cell->tagName ), array( 'th','td' ), true ) ) { continue; }
						if ( '' !== Content_Normalizer::text( self::visible_text_excluding( $cell, array( 'table' ) ) ) ) { $has_text = true; }
						if ( self::has_image_alt( $cell ) ) { $has_image_alt = true; }
					}
				}
				if ( ! $has_text ) { ++$out['tables_without_text']; }
				if ( ! $has_text && $has_image_alt ) { ++$out['tables_image_only']; }
			}
		}
		return $out;
	}

	/** @return array<string,int> */
	private static function empty_contexts(): array {
		return array(
			'lists_semantic' => 0, 'lists_inside_code' => 0, 'lists_inside_heading' => 0,
			'lists_nested_in_list' => 0, 'lists_parser_unreachable' => 0,
			'tables_semantic' => 0, 'tables_inside_code' => 0, 'tables_inside_heading' => 0,
			'tables_parser_unreachable' => 0, 'tables_without_text' => 0, 'tables_image_only' => 0,
		);
	}

	/** @param array<string,int> $target @param array<string,int> $source */
	private static function merge_contexts( array &$target, array $source ): void {
		foreach ( $target as $key => $value ) {
			$target[ $key ] = (int) $value + (int) ( $source[ $key ] ?? 0 );
		}
	}

	/** @param array<int,string> $tags */
	private static function has_ancestor( \DOMNode $node, \DOMElement $root, array $tags ): bool {
		$ancestor = $node->parentNode;
		while ( $ancestor instanceof \DOMElement && $ancestor !== $root ) {
			if ( in_array( strtolower( $ancestor->tagName ), $tags, true ) ) { return true; }
			$ancestor = $ancestor->parentNode;
		}
		return false;
	}

	private static function has_direct_child( \DOMElement $node, string $tag ): bool {
		foreach ( $node->childNodes as $child ) {
			if ( $child instanceof \DOMElement && $tag === strtolower( $child->tagName ) ) { return true; }
		}
		return false;
	}

	private static function parser_unreachable( \DOMElement $node, \DOMElement $root ): bool {
		$ancestor = $node->parentNode;
		while ( $ancestor instanceof \DOMElement && $ancestor !== $root ) {
			$tag = strtolower( $ancestor->tagName );
			if ( 'table' === $tag ) { return false; }
			if ( 'li' === $tag ) { return $node->parentNode !== $ancestor; }
			if ( in_array( $tag, array( 'p','blockquote' ), true ) ) {
				if ( self::has_ancestor( $ancestor, $root, array( 'li','ul','ol' ) ) ) { return true; }
				return $node->parentNode !== $ancestor;
			}
			if ( in_array( $tag, array( 'ul','ol' ), true ) ) { return true; }
			$ancestor = $ancestor->parentNode;
		}
		return false;
	}

	/** @return array<int,\DOMElement> */
	private static function direct_rows( \DOMElement $table ): array {
		$rows = array();
		foreach ( $table->childNodes as $child ) {
			if ( ! $child instanceof \DOMElement ) { continue; }
			$tag = strtolower( $child->tagName );
			if ( 'tr' === $tag ) { $rows[] = $child; continue; }
			if ( in_array( $tag, array( 'thead','tbody','tfoot' ), true ) ) {
				foreach ( $child->childNodes as $row ) {
					if ( $row instanceof \DOMElement && 'tr' === strtolower( $row->tagName ) ) { $rows[] = $row; }
				}
			}
		}
		return $rows;
	}

	private static function direct_row_count( \DOMElement $table ): int { return count( self::direct_rows( $table ) ); }

	/** @param array<int,string> $excluded */
	private static function visible_text_excluding( \DOMNode $node, array $excluded ): string {
		$out = '';
		foreach ( $node->childNodes as $child ) {
			if ( $child instanceof \DOMText ) { $out .= $child->nodeValue ?? ''; continue; }
			if ( ! $child instanceof \DOMElement ) { continue; }
			$tag = strtolower( $child->tagName );
			if ( in_array( $tag, array( 'script','style','noscript' ), true ) || in_array( $tag, $excluded, true ) ) { continue; }
			if ( 'br' === $tag ) { $out .= "\n"; continue; }
			$out .= self::visible_text_excluding( $child, $excluded );
		}
		return html_entity_decode( $out, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	}

	private static function has_image_alt( \DOMElement $node ): bool {
		$stack = array( $node );
		while ( ! empty( $stack ) ) {
			$current = array_pop( $stack );
			if ( 'img' === strtolower( $current->tagName ) && '' !== Content_Normalizer::text( $current->getAttribute( 'alt' ) ) ) { return true; }
			foreach ( $current->childNodes as $child ) { if ( $child instanceof \DOMElement ) { $stack[] = $child; } }
		}
		return false;
	}

	/** @return array<int,int> */
	private static function post_ids(): array {
		$ids = get_posts( array(
			'post_type' => 'post', 'post_status' => array( 'publish','draft','pending','private' ),
			'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC',
			'no_found_rows' => true, 'suppress_filters' => true,
		) );
		return array_values( array_map( 'intval', is_array( $ids ) ? $ids : array() ) );
	}

	/** @param array<int,int> $ids @return array<int,string> */
	private static function snapshot( array $ids ): array {
		$out = array();
		foreach ( $ids as $id ) {
			$post = get_post( $id );
			if ( ! $post instanceof \WP_Post ) { continue; }
			$elementor = get_post_meta( $id, '_elementor_data', true );
			$out[ $id ] = hash( 'sha256', implode( '|', array(
				(string) $post->post_status, (string) $post->post_modified_gmt,
				hash( 'sha256', (string) $post->post_content ), hash( 'sha256', serialize( $elementor ) ),
			) ) );
		}
		return $out;
	}

	/** @param array<int,string> $snapshot */
	private static function aggregate_fingerprint( array $snapshot ): string {
		ksort( $snapshot, SORT_NUMERIC );
		$lines = array();
		foreach ( $snapshot as $id => $hash ) { $lines[] = $id . ':' . $hash; }
		return hash( 'sha256', implode( "\n", $lines ) );
	}

	/** @param array<int,string> $a @param array<int,string> $b */
	private static function changed_snapshot_count( array $a, array $b ): int {
		$count = 0;
		foreach ( array_unique( array_merge( array_keys( $a ), array_keys( $b ) ) ) as $id ) {
			if ( ( $a[ $id ] ?? null ) !== ( $b[ $id ] ?? null ) ) { ++$count; }
		}
		return $count;
	}

	/** @param array<string,int> $map */
	private static function increment( array &$map, string $key ): void { $map[ $key ] = (int) ( $map[ $key ] ?? 0 ) + 1; }
}
