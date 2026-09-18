<?php
/**
 * G-250 — Lifecycle / RC read-only validation.
 *
 * Hidden engineering surface. No product menu entry.
 *
 * @package BDC_Knowledge_Base
 */
namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Lifecycle_RC_Smoke_G250 {
	public const SCHEMA_VERSION = '1.0.0';
	public const PAGE_SLUG = 'bdc-kb-g250-lifecycle';
	public const ACTION = 'bdc_kb_g250_lifecycle_smoke';
	public const NONCE_FIELD = 'bdc_kb_g250_lifecycle_nonce';
	public const TARGET_POST_ID = 358;

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_hidden_page' ), 99 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_hidden_page(): void {
		add_submenu_page(
			null,
			'G-250',
			'G-250',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Permissão insuficiente.', '', array( 'response' => 403 ) );
		}

		echo '<div class="wrap">';
		echo '<h1>G-250 — validação do ciclo de vida</h1>';
		echo '<p>Esta página é temporária, oculta e somente para homologação. Nenhum conteúdo será alterado.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::ACTION, self::NONCE_FIELD );
		echo '<p><label><input type="checkbox" name="lifecycle_reactivated" value="1" required> Confirme que este RC foi desativado e reativado com sucesso nesta homologação.</label></p>';
		echo '<p><label><input type="checkbox" name="rollback_cycle_validated" value="1" required> Confirme que o rollback/downgrade para o build anterior validado foi testado e que este RC foi reinstalado em seguida.</label></p>';
		submit_button( 'Executar validação e baixar relatório JSON', 'primary', 'submit', false );
		echo '</form>';
		echo '</div>';
	}

	public static function handle_run(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( 'Método não permitido.', '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Permissão insuficiente.', '', array( 'response' => 403 ) );
		}
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] )
			? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] ) : '';
		if ( ! wp_verify_nonce( $nonce, self::ACTION ) ) {
			wp_die( 'Nonce inválido.', '', array( 'response' => 403 ) );
		}

		$lifecycle = isset( $_POST['lifecycle_reactivated'] ) && '1' === (string) wp_unslash( $_POST['lifecycle_reactivated'] );
		$rollback = isset( $_POST['rollback_cycle_validated'] ) && '1' === (string) wp_unslash( $_POST['rollback_cycle_validated'] );
		$report = self::run( $lifecycle, $rollback );

		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( 'Falha ao gerar relatório JSON.', '', array( 'response' => 500 ) );
		}
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-g250-lifecycle-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json;
		exit;
	}

	/** @return array<string,mixed> */
	private static function run( bool $lifecycle_reactivated, bool $rollback_cycle_validated ): array {
		$started = microtime( true );
		$post_id = self::TARGET_POST_ID;
		$before = self::fingerprint( $post_id );
		$checks = array();

		$checks['plugin_version'] = array(
			'pass' => defined( 'BDC_KB_VERSION' ) && str_starts_with( (string) BDC_KB_VERSION, '0.4.0-spec004-rc1' ),
			'observed' => defined( 'BDC_KB_VERSION' ) ? (string) BDC_KB_VERSION : '',
		);
		$checks['t100d_off'] = array(
			'pass' => defined( 'BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD' ) && false === BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD,
		);
		$checks['elementor_writer_off'] = array(
			'pass' => defined( 'BDC_KB_ELEMENTOR_WRITER_ENABLED' ) && false === BDC_KB_ELEMENTOR_WRITER_ENABLED,
		);
		$checks['e6_runner_off'] = array(
			'pass' => defined( 'BDC_KB_SPEC004_T100E_E6_REGRESSION_BUILD' ) && false === BDC_KB_SPEC004_T100E_E6_REGRESSION_BUILD,
		);
		$checks['g250_runner_on'] = array(
			'pass' => defined( 'BDC_KB_SPEC004_G250_LIFECYCLE_BUILD' ) && true === BDC_KB_SPEC004_G250_LIFECYCLE_BUILD,
		);

		$summary = Summary_Store::read( $post_id );
		$checks['spec001_summary_read'] = array(
			'pass' => ! ( $summary instanceof \WP_Error ),
			'error_code' => $summary instanceof \WP_Error ? $summary->get_error_code() : '',
		);

		$classification = Classification_Store::read( $post_id );
		$checks['spec002_classification_read'] = array(
			'pass' => ! ( $classification instanceof \WP_Error ),
			'error_code' => $classification instanceof \WP_Error ? $classification->get_error_code() : '',
		);

		$review = Review_Store::read( $post_id );
		$checks['spec003_review_read'] = array(
			'pass' => ! ( $review instanceof \WP_Error ),
			'error_code' => $review instanceof \WP_Error ? $review->get_error_code() : '',
		);

		$extraction = Content_Extractor::extract( $post_id );
		$checks['spec004_extractor_read'] = array(
			'pass' => is_array( $extraction ) && 'gutenberg' === (string) ( $extraction['source_kind'] ?? '' ),
			'source_kind' => is_array( $extraction ) ? (string) ( $extraction['source_kind'] ?? '' ) : '',
			'error_code' => $extraction instanceof \WP_Error ? $extraction->get_error_code() : '',
		);

		$document = Knowledge_Document::build( $post_id );
		$checks['spec004_knowledge_document_read'] = array(
			'pass' => is_array( $document )
				&& Knowledge_Document::SCHEMA_VERSION === (string) ( $document['schema_version'] ?? '' )
				&& 'gutenberg' === (string) ( $document['source_kind'] ?? '' )
				&& self::is_sha256( (string) ( $document['source_hash'] ?? '' ) )
				&& self::is_sha256( (string) ( $document['document_hash'] ?? '' ) ),
			'schema_version' => is_array( $document ) ? (string) ( $document['schema_version'] ?? '' ) : '',
			'source_kind' => is_array( $document ) ? (string) ( $document['source_kind'] ?? '' ) : '',
			'error_code' => $document instanceof \WP_Error ? $document->get_error_code() : '',
		);

		$context = Post_Management_Context::build( $post_id );
		$checks['workspace_context'] = array(
			'pass' => is_array( $context )
				&& 'gutenberg' === (string) ( $context['source']['kind'] ?? '' )
				&& 'no_action_required' === (string) ( $context['core_blocks']['operational_status'] ?? '' )
				&& Block_Migration_Journal::STATE_APPLIED === (string) ( $context['core_blocks']['latest_journal_state'] ?? '' )
				&& 'free' === (string) ( $context['core_blocks']['lock_status'] ?? '' ),
			'source_kind' => is_array( $context ) ? (string) ( $context['source']['kind'] ?? '' ) : '',
			'core_status' => is_array( $context ) ? (string) ( $context['core_blocks']['operational_status'] ?? '' ) : '',
			'journal_state' => is_array( $context ) ? (string) ( $context['core_blocks']['latest_journal_state'] ?? '' ) : '',
			'lock_status' => is_array( $context ) ? (string) ( $context['core_blocks']['lock_status'] ?? '' ) : '',
			'error_code' => $context instanceof \WP_Error ? $context->get_error_code() : '',
		);

		$definitions = Post_Activity_Registry::definitions();
		$required_activities = array( 'overview', 'content', 'summary', 'classification', 'intelligence', 'core_blocks', 'review', 'history' );
		$checks['workspace_activity_registry'] = array(
			'pass' => empty( array_diff( $required_activities, array_keys( $definitions ) ) ),
			'activity_count' => count( $definitions ),
		);

		$checks['preflight_not_visible_in_product_menu'] = array(
			'pass' => ! self::visible_menu_contains( 'bdc-kb-g245-preflight' ) && ! self::visible_menu_contains( 'Preflight G-245' ),
		);

		$checks['lifecycle_reactivated_confirmation'] = array( 'pass' => $lifecycle_reactivated );
		$checks['rollback_cycle_confirmation'] = array( 'pass' => $rollback_cycle_validated );

		$after = self::fingerprint( $post_id );
		$checks['target_integrity_unchanged'] = array(
			'pass' => '' !== $before && hash_equals( $before, $after ),
			'fingerprint_before' => $before,
			'fingerprint_after' => $after,
		);

		$pass = true;
		foreach ( $checks as $check ) {
			if ( true !== ( $check['pass'] ?? false ) ) {
				$pass = false;
				break;
			}
		}

		return array(
			'schema_version' => self::SCHEMA_VERSION,
			'gate' => 'G-250',
			'mode' => 'lifecycle_rc_read_only_validation',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
			),
			'target' => array(
				'post_id' => $post_id,
				'expected_source_kind' => 'gutenberg',
			),
			'checks' => $checks,
			'safety' => array(
				'read_only_design' => true,
				'persists_state' => false,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'writes_summary' => false,
				'writes_classification' => false,
				'writes_review' => false,
				'writes_journal' => false,
				'acquires_lock' => false,
				'calls_external_network' => false,
				'executes_shortcodes' => false,
				'renders_blocks' => false,
				'exports_editorial_body' => false,
				'exports_urls' => false,
			),
			'gate_result' => array(
				'g250_lifecycle_rc_pass' => $pass,
			),
			'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
		);
	}

	private static function fingerprint( int $post_id ): string {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) {
			return '';
		}

		$summary = array();
		foreach ( Meta_Contract::fields() as $definition ) {
			$summary[] = get_post_meta( $post_id, (string) $definition['key'], true );
		}

		$classification = array();
		foreach ( Classification_Contract::fields() as $definition ) {
			$terms = wp_get_object_terms( $post_id, (string) $definition['taxonomy'], array( 'fields' => 'ids' ) );
			$classification[] = is_wp_error( $terms ) ? array( 'error' => $terms->get_error_code() ) : array_values( array_map( 'intval', $terms ) );
		}

		$payload = array(
			'post_content_sha256' => hash( 'sha256', (string) ( $post->post_content ?? '' ) ),
			'elementor_data_sha256' => hash( 'sha256', self::stable( get_post_meta( $post_id, '_elementor_data', true ) ) ),
			'summary_sha256' => hash( 'sha256', self::stable( $summary ) ),
			'classification_sha256' => hash( 'sha256', self::stable( $classification ) ),
			'review_sha256' => hash( 'sha256', self::stable( Review_Store::read( $post_id ) ) ),
			'journal_sha256' => hash( 'sha256', self::stable( get_post_meta( $post_id, Block_Migration_Journal_Store::META_KEY, false ) ) ),
			'lock_sha256' => hash( 'sha256', self::stable( get_post_meta( $post_id, Block_Migration_Lock::META_KEY, true ) ) ),
		);
		return hash( 'sha256', self::stable( $payload ) );
	}

	private static function stable( mixed $value ): string {
		if ( is_string( $value ) ) {
			return $value;
		}
		if ( $value instanceof \WP_Error ) {
			return 'WP_ERROR:' . $value->get_error_code();
		}
		$json = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $json ) ? $json : '';
	}

	private static function visible_menu_contains( string $needle ): bool {
		global $menu, $submenu;
		foreach ( array( $menu, $submenu ) as $collection ) {
			$json = wp_json_encode( $collection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			if ( is_string( $json ) && false !== stripos( $json, $needle ) ) {
				return true;
			}
		}
		return false;
	}

	private static function is_sha256( string $value ): bool {
		return 1 === preg_match( '/^[a-f0-9]{64}$/', strtolower( $value ) );
	}
}
