<?php
/**
 * Environmental evidence runner for SPEC-006 / P-630.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Produces read-only/no-op environmental evidence for canonical domain ownership.
 */
final class Domain_Closure_Runner_P630 {

	public const PAGE_SLUG = 'bdc-kb-p630-domain-closure';
	public const ACTION    = 'bdc_kb_p630_export';
	private const NONCE_ACTION = 'bdc_kb_p630_export';
	private const NONCE_FIELD  = 'bdc_kb_p630_nonce';
	private const SAMPLE_LIMIT = 20;

	/**
	 * Register the temporary homologation surface.
	 */
	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_menu' ) );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_export' ) );
	}

	/**
	 * Register submenu under the canonical BDC menu.
	 */
	public static function register_menu(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'P-630 Domain Closure',
			'P-630 Domain Closure',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render' )
		);
	}

	/**
	 * Render the temporary evidence surface.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Você não tem permissão para executar este gate.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'P-630 — Domain Closure', 'bdc-knowledge-base' ) . '</h1>';
		echo '<p>' . esc_html__( 'Gera evidência ambiental de ownership, coverage, Public Reader e writers em modo no-op. Nenhum conteúdo editorial é alterado.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar P-630 e baixar JSON', 'bdc-knowledge-base' ) );
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Execute and download the environmental evidence JSON.
	 */
	public static function handle_export(): never {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Você não tem permissão para executar este gate.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$report = self::run();

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec006-p630-domain-closure-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		exit;
	}

	/**
	 * Execute the gate.
	 *
	 * @return array<string,mixed>
	 */
	public static function run(): array {
		$post_ids = self::published_post_ids();

		$counts = array(
			'posts'                          => count( $post_ids ),
			'affected_service_canonical'     => 0,
			'affected_service_legacy_fallback' => 0,
			'systems_involved'               => 0,
			'technologies'                   => 0,
			'keywords'                       => 0,
			'versions'                       => 0,
			'helpful_tips_posts'             => 0,
			'helpful_tips_items'             => 0,
			'coverage_empty'                 => 0,
			'coverage_partial'               => 0,
			'coverage_complete'              => 0,
		);

		$errors = array();
		$samples = array();

		foreach ( $post_ids as $post_id ) {
			$facts = Knowledge_Facts_Store::read( $post_id );
			$tips = Helpful_Tips_Store::read( $post_id );
			$coverage = Coverage_Read_Model::read( $post_id );
			$public = Public_Article_Read_Model::read( $post_id );

			if ( is_wp_error( $facts ) || is_wp_error( $tips ) || is_wp_error( $coverage ) || is_wp_error( $public ) ) {
				$errors[] = array(
					'post_id' => $post_id,
					'facts_error' => is_wp_error( $facts ) ? $facts->get_error_code() : '',
					'tips_error' => is_wp_error( $tips ) ? $tips->get_error_code() : '',
					'coverage_error' => is_wp_error( $coverage ) ? $coverage->get_error_code() : '',
					'public_error' => is_wp_error( $public ) ? $public->get_error_code() : '',
				);
				continue;
			}

			$values = (array) ( $facts['values'] ?? array() );
			$sources = (array) ( $facts['sources'] ?? array() );

			if ( '' !== (string) ( $values['affected_service'] ?? '' ) ) {
				if ( '_kb2ops_service' === (string) ( $sources['affected_service'] ?? '' ) ) {
					++$counts['affected_service_legacy_fallback'];
				} else {
					++$counts['affected_service_canonical'];
				}
			}
			foreach ( array( 'systems_involved', 'technologies', 'keywords', 'versions' ) as $field ) {
				if ( '' !== (string) ( $values[ $field ] ?? '' ) ) {
					++$counts[ $field ];
				}
			}

			if ( ! empty( $tips ) ) {
				++$counts['helpful_tips_posts'];
				$counts['helpful_tips_items'] += count( $tips );
			}

			$coverage_state = (string) ( $coverage['state'] ?? '' );
			if ( Coverage_Read_Model::STATE_EMPTY === $coverage_state ) {
				++$counts['coverage_empty'];
			} elseif ( Coverage_Read_Model::STATE_COMPLETE === $coverage_state ) {
				++$counts['coverage_complete'];
			} else {
				++$counts['coverage_partial'];
			}

			if ( count( $samples ) >= self::SAMPLE_LIMIT || ! current_user_can( 'edit_post', $post_id ) ) {
				continue;
			}

			$before_hash = self::domain_hash( $post_id );

			$facts_update = Knowledge_Facts_Store::update( $post_id, $values );
			$tips_update = Helpful_Tips_Store::update( $post_id, $tips );

			$after_hash = self::domain_hash( $post_id );
			$public_service = self::public_summary_value( (array) ( $public['summary_items'] ?? array() ), 'Serviço Afetado' );
			$public_systems = self::public_summary_value( (array) ( $public['summary_items'] ?? array() ), 'Sistemas envolvidos' );

			$samples[] = array(
				'post_id' => $post_id,
				'facts_noop_pass' => ! is_wp_error( $facts_update ),
				'tips_noop_pass' => ! is_wp_error( $tips_update ),
				'domain_hash_equal' => hash_equals( $before_hash, $after_hash ),
				'public_service_equal' => $public_service === (string) ( $values['affected_service'] ?? '' ),
				'public_systems_equal' => $public_systems === (string) ( $values['systems_involved'] ?? '' ),
				'coverage_state' => $coverage_state,
				'coverage_filled' => (int) ( $coverage['filled'] ?? 0 ),
				'affected_service_source' => (string) ( $sources['affected_service'] ?? '' ),
			);
		}

		$sample_pass = true;
		foreach ( $samples as $sample ) {
			if (
				empty( $sample['facts_noop_pass'] )
				|| empty( $sample['tips_noop_pass'] )
				|| empty( $sample['domain_hash_equal'] )
				|| empty( $sample['public_service_equal'] )
				|| empty( $sample['public_systems_equal'] )
			) {
				$sample_pass = false;
				break;
			}
		}

		$status = empty( $errors ) && $sample_pass && count( $samples ) > 0 ? 'PASS' : 'FAIL';

		return array(
			'schema_version' => '1.0.0',
			'gate' => 'P-630',
			'status' => $status,
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => BDC_KB_VERSION,
			),
			'counts' => $counts,
			'sample_count' => count( $samples ),
			'samples' => $samples,
			'errors' => $errors,
			'assertions' => array(
				'store_errors_zero' => empty( $errors ),
				'sample_noop_writers_pass' => $sample_pass,
				'samples_present' => count( $samples ) > 0,
				'public_reader_canonical_alignment' => $sample_pass,
				'domain_state_unchanged' => $sample_pass,
			),
			'cutover_authorized' => false,
			'retirement_authorized' => false,
			'next_gate_on_pass' => 'P630_LEDGER_DISPOSITION',
		);
	}

	/**
	 * Read published post IDs.
	 *
	 * @return array<int,int>
	 */
	private static function published_post_ids(): array {
		$query = new \WP_Query(
			array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'fields' => 'ids',
				'orderby' => 'ID',
				'order' => 'ASC',
				'no_found_rows' => true,
				'ignore_sticky_posts' => true,
				'suppress_filters' => false,
			)
		);

		return array_values( array_map( 'intval', (array) $query->posts ) );
	}

	/**
	 * Hash canonical/fallback domain data used by P-630.
	 */
	private static function domain_hash( int $post_id ): string {
		$keys = array(
			'_bdc_es_affected_service',
			'_kb2ops_service',
			'_bdc_es_systems_involved',
			'_kb2ops_technologies',
			'_kb2ops_keywords',
			'_kb2ops_versions',
			'_bdc_es_helpful_tips',
		);

		$data = array();
		foreach ( $keys as $key ) {
			$data[ $key ] = get_post_meta( $post_id, $key, true );
		}

		return hash( 'sha256', (string) wp_json_encode( $data ) );
	}

	/**
	 * Find one label in the public summary read model.
	 *
	 * @param array<int,mixed> $items Public summary items.
	 */
	private static function public_summary_value( array $items, string $label ): string {
		foreach ( $items as $item ) {
			if ( ! is_array( $item ) || $label !== (string) ( $item['label'] ?? '' ) ) {
				continue;
			}
			return (string) ( $item['value'] ?? '' );
		}
		return '';
	}
}
