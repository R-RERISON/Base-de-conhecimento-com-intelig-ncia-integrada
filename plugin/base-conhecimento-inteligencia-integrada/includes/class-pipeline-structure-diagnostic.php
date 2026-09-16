<?php
/**
 * Diagnóstico read-only: DOM bruto -> unwrap -> fragments -> blocks.
 * Exporta somente agregados; nunca IDs, títulos, URLs ou conteúdo editorial.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Pipeline_Structure_Diagnostic {
	public const ACTION = 'bdc_kb_spec004_pipeline_diag';
	public const PAGE_SLUG = 'bdc-kb-spec004-pipeline-diag';
	private const NONCE_ACTION = 'bdc_kb_spec004_pipeline_diag_run';
	private const NONCE_FIELD = 'bdc_kb_spec004_pipeline_diag_nonce';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 34 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page( Admin_Page::PAGE_SLUG, 'Diagnóstico Pipeline Final', 'Diagnóstico Pipeline Final', 'manage_options', self::PAGE_SLUG, array( self::class, 'render_page' ) );
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ) ); }
		echo '<div class="wrap"><h1>' . esc_html__( 'SPEC-004 — Diagnóstico Pipeline Final', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'Read-only. Compara DOM bruto, DOM após unwrap, fragments e blocks somente nos documentos not_ready. Não exporta conteúdo ou identificadores.', 'bdc-knowledge-base' ) . '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar diagnóstico pipeline e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
		echo '</form></div>';
	}

	public static function handle_run(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) { wp_die( 'Método não permitido.', '', array( 'response' => 405 ) ); }
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Permissão insuficiente.', '', array( 'response' => 403 ) ); }
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] ) ? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) { wp_die( 'Nonce inválido.', '', array( 'response' => 403 ) ); }
		$json = wp_json_encode( self::run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) { wp_die( 'Falha ao serializar.', '', array( 'response' => 500 ) ); }
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-pipeline-diag-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$ids = self::post_ids();
		$before = self::snapshot( $ids );
		$out = array( 'not_ready_documents' => 0, 'errors' => 0, 'throwables' => 0, 'by_signature' => array(), 'totals' => self::empty_metrics(), 'shortcode_tags' => array() );
		foreach ( $ids as $post_id ) {
			try {
				$document = Knowledge_Document::build( $post_id );
				if ( is_wp_error( $document ) ) { ++$out['errors']; continue; }
				$readiness = is_array( $document['ai_readiness'] ?? null ) ? $document['ai_readiness'] : array();
				if ( 'not_ready' !== (string) ( $readiness['status'] ?? '' ) ) { continue; }
				++$out['not_ready_documents'];
				$source_kind = (string) ( $document['source_kind'] ?? 'unknown' );
				$signatures = array_values( array_filter( array_map( 'strval', (array) ( $readiness['reasons'] ?? array() ) ), static fn ( string $v ): bool => str_starts_with( $v, 'STRUCTURE_COUNT_MISMATCH:' ) ) );
				$source = Content_Source::inspect( $post_id );
				if ( $source instanceof \WP_Error ) { ++$out['errors']; continue; }
				$metrics = self::empty_metrics();
				$tags = array();
				foreach ( self::html_segments( $source, $source_kind ) as $html ) {
					$segment = self::analyze_segment( $html );
					self::merge_metrics( $metrics, $segment['metrics'] );
					self::merge_map( $tags, $segment['shortcode_tags'] );
				}
				self::merge_metrics( $out['totals'], $metrics );
				self::merge_map( $out['shortcode_tags'], $tags );
				foreach ( $signatures as $signature ) {
					$key = $source_kind . '|' . $signature;
					if ( ! isset( $out['by_signature'][ $key ] ) ) { $out['by_signature'][ $key ] = array( 'documents' => 0, 'metrics' => self::empty_metrics(), 'shortcode_tags' => array() ); }
					++$out['by_signature'][ $key ]['documents'];
					self::merge_metrics( $out['by_signature'][ $key ]['metrics'], $metrics );
					self::merge_map( $out['by_signature'][ $key ]['shortcode_tags'], $tags );
				}
			} catch ( \Throwable $throwable ) { ++$out['throwables']; }
		}
		ksort( $out['by_signature'] );
		$after = self::snapshot( self::post_ids() );
		$before_hash = self::fingerprint( $before );
		$after_hash = self::fingerprint( $after );
		return array(
			'schema_version' => '1.0.0',
			'mode' => 'temporary_spec004_pipeline_structure_diagnostic',
			'generated_at' => gmdate( 'c' ),
			'environment' => array( 'wordpress' => get_bloginfo( 'version' ), 'php' => PHP_VERSION, 'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '', 'elementor' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null, 'knowledge_document_schema' => Knowledge_Document::SCHEMA_VERSION, 'domdocument' => class_exists( '\\DOMDocument' ) ),
			'safety' => array( 'read_only_design' => true, 'exports_editorial_content' => false, 'exports_post_ids' => false, 'exports_titles_or_urls' => false, 'fingerprint_before' => $before_hash, 'fingerprint_after' => $after_hash, 'fingerprint_equal' => hash_equals( $before_hash, $after_hash ), 'changed_posts_during_run' => self::changed_count( $before, $after ) ),
			'diagnostic' => $out,
			'performance' => array( 'runtime_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ), 'peak_memory_bytes' => memory_get_peak_usage( true ) ),
		);
	}

	/** @return array{metrics:array<string,int>,shortcode_tags:array<string,int>} */
	private static function analyze_segment( string $html ): array {
		$m = self::empty_metrics(); $tags = array();
		if ( '' === trim( $html ) ) { return array( 'metrics' => $m, 'shortcode_tags' => $tags ); }
		++$m['segments'];
		$inspection = Shortcode_Inspector::inspect( $html );
		foreach ( (array) ( $inspection['matches'] ?? array() ) as $match ) { $tag = strtolower( (string) ( $match['tag'] ?? '' ) ); if ( '' !== $tag ) { self::inc( $tags, $tag ); } }
		$raw_partial = Legacy_HTML_Adapter::extract( $html, 'diag:raw' );
		$raw_expected = Semantic_DOM_Expectation::apply( $html, $raw_partial );
		$m['raw_expected_lists'] += (int) ( $raw_expected['structure']['lists'] ?? 0 );
		$m['raw_expected_tables'] += (int) ( $raw_expected['structure']['tables'] ?? 0 );
		$unwrapped = Shortcode_Inspector::unwrap_without_execution( $html );
		if ( $unwrapped !== $html ) { ++$m['unwrap_changed_segments']; }
		$partial = Legacy_HTML_Adapter::extract( $unwrapped, 'diag:unwrapped' );
		$expected = Semantic_DOM_Expectation::apply( $unwrapped, $partial );
		$m['unwrapped_expected_lists'] += (int) ( $expected['structure']['lists'] ?? 0 );
		$m['unwrapped_expected_tables'] += (int) ( $expected['structure']['tables'] ?? 0 );
		$list_ids = array(); $table_ids = array();
		foreach ( (array) ( $partial['fragments'] ?? array() ) as $fragment ) {
			if ( ! is_array( $fragment ) ) { continue; }
			$kind = (string) ( $fragment['kind'] ?? '' ); $meta = is_array( $fragment['meta'] ?? null ) ? $fragment['meta'] : array();
			if ( 'list_item' === $kind && '' !== (string) ( $meta['list_id'] ?? '' ) ) { $list_ids[ (string) $meta['list_id'] ] = true; }
			if ( in_array( $kind, array( 'table_row', 'table_caption' ), true ) && '' !== (string) ( $meta['table_id'] ?? '' ) ) { $table_ids[ (string) $meta['table_id'] ] = true; }
		}
		$m['fragment_list_containers'] += count( $list_ids ); $m['fragment_table_containers'] += count( $table_ids );
		$blocks = Semantic_Structure::blocks( Semantic_Structure::sections( (array) ( $partial['fragments'] ?? array() ) ) );
		foreach ( $blocks as $block ) { if ( ! is_array( $block ) ) { continue; } $kind = (string) ( $block['kind'] ?? '' ); if ( 'list' === $kind ) { $m['block_lists'] += self::count_list_blocks( $block ); } elseif ( 'table' === $kind ) { ++$m['block_tables']; } }
		return array( 'metrics' => $m, 'shortcode_tags' => $tags );
	}

	/** @return array<string,int> */
	private static function empty_metrics(): array { return array( 'segments'=>0, 'unwrap_changed_segments'=>0, 'raw_expected_lists'=>0, 'unwrapped_expected_lists'=>0, 'fragment_list_containers'=>0, 'block_lists'=>0, 'raw_expected_tables'=>0, 'unwrapped_expected_tables'=>0, 'fragment_table_containers'=>0, 'block_tables'=>0 ); }
	/** @param array<string,int> $a @param array<string,int> $b */
	private static function merge_metrics( array &$a, array $b ): void { foreach ( $a as $k => $v ) { $a[$k] = (int) $v + (int) ( $b[$k] ?? 0 ); } }
	/** @param array<string,int> $a @param array<string,int> $b */
	private static function merge_map( array &$a, array $b ): void { foreach ( $b as $k => $v ) { $a[(string)$k] = (int) ( $a[(string)$k] ?? 0 ) + (int) $v; } ksort( $a ); }
	/** @param array<string,mixed> $list */
	private static function count_list_blocks( array $list ): int { $n=1; foreach ( (array) ( $list['items'] ?? array() ) as $item ) { if ( ! is_array( $item ) ) { continue; } foreach ( (array) ( $item['children'] ?? array() ) as $child ) { if ( is_array( $child ) ) { $n += self::count_list_blocks( $child ); } } } return $n; }

	/** @param array<string,mixed> $source @return array<int,string> */
	private static function html_segments( array $source, string $source_kind ): array {
		$segments = array();
		if ( in_array( $source_kind, array( 'legacy_html','gutenberg','mixed' ), true ) ) { $c=(string)($source['post_content']??''); if ( ''!==trim($c) ) { $segments[]=$c; } }
		if ( in_array( $source_kind, array( 'elementor','mixed' ), true ) && is_array( $source['elementor_data'] ?? null ) ) { self::collect_elementor_html( $source['elementor_data'], $segments ); }
		return $segments;
	}
	/** @param array<int|string,mixed> $nodes @param array<int,string> $segments */
	private static function collect_elementor_html( array $nodes, array &$segments ): void {
		foreach ( $nodes as $node ) { if ( ! is_array( $node ) ) { continue; } $widget=(string)($node['widgetType']??''); $settings=is_array($node['settings']??null)?$node['settings']:array();
			if ( 'text-editor'===$widget && isset($settings['editor']) && is_scalar($settings['editor']) ) { $h=(string)$settings['editor']; if(''!==trim($h)){$segments[]=$h;} }
			elseif ( 'shortcode'===$widget && isset($settings['shortcode']) && is_scalar($settings['shortcode']) ) { $i=Shortcode_Inspector::inspect((string)$settings['shortcode']); foreach((array)($i['matches']??array()) as $match){$inner=(string)($match['inner']??''); if(''!==trim($inner)){$segments[]=$inner;}} }
			if ( isset($node['elements']) && is_array($node['elements']) ) { self::collect_elementor_html($node['elements'],$segments); }
		}
	}

	/** @return array<int,int> */
	private static function post_ids(): array { $ids=get_posts(array('post_type'=>'post','post_status'=>array('publish','draft','pending','private'),'posts_per_page'=>-1,'fields'=>'ids','orderby'=>'ID','order'=>'ASC','no_found_rows'=>true,'suppress_filters'=>true)); return array_values(array_map('intval',is_array($ids)?$ids:array())); }
	/** @param array<int,int> $ids @return array<int,string> */
	private static function snapshot( array $ids ): array { $o=array(); foreach($ids as $id){$p=get_post($id); if(!$p instanceof \WP_Post){continue;} $e=get_post_meta($id,'_elementor_data',true); $o[$id]=hash('sha256',implode('|',array((string)$p->post_status,(string)$p->post_modified_gmt,hash('sha256',(string)$p->post_content),hash('sha256',serialize($e)))));} return $o; }
	/** @param array<int,string> $s */
	private static function fingerprint( array $s ): string { ksort($s,SORT_NUMERIC); $l=array(); foreach($s as $id=>$h){$l[]=$id.':'.$h;} return hash('sha256',implode("\n",$l)); }
	/** @param array<int,string> $a @param array<int,string> $b */
	private static function changed_count( array $a, array $b ): int { $n=0; foreach(array_unique(array_merge(array_keys($a),array_keys($b))) as $id){if(($a[$id]??null)!==($b[$id]??null)){++$n;}} return $n; }
	/** @param array<string,int> $m */
	private static function inc( array &$m, string $k ): void { $m[$k]=(int)($m[$k]??0)+1; }
}
