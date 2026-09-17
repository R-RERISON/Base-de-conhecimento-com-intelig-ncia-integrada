<?php
/**
 * T099B Authorization Pack generator for one low-risk Block Migration canary.
 * Read-only: no journal persistence, no lock acquisition and no editorial write.
 */
namespace BDC\KnowledgeBase;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Block_Migration_Authorization_Pack_Smoke {
    public const ACTION = 'bdc_kb_spec004_g245_t099b_authorization_pack';
    public const PAGE_SLUG = 'bdc-kb-spec004-g245-t099b-authorization-pack';
    public const SCHEMA_VERSION = '1.0.0';
    private const NONCE_ACTION = 'bdc_kb_spec004_g245_t099b_authorization_pack_run';
    private const NONCE_FIELD = 'bdc_kb_spec004_g245_t099b_authorization_pack_nonce';
    private const PREFERRED_POST_ID = 358;
    private const MAX_CONTENT_BYTES = 30000;

    public static function register(): void {
        add_action( 'admin_menu', array( self::class, 'register_page' ), 40 );
        add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
    }

    public static function register_page(): void {
        add_submenu_page(
            Admin_Page::PAGE_SLUG,
            'T099B Authorization Pack G-245',
            'T099B Authorization Pack G-245',
            'manage_options',
            self::PAGE_SLUG,
            array( self::class, 'render_page' )
        );
    }

    public static function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ) );
        }
        echo '<div class="wrap"><h1>' . esc_html__( 'SPEC-004 — T099B Authorization Pack', 'bdc-knowledge-base' ) . '</h1>';
        echo '<div class="notice notice-info inline"><p><strong>Read-only.</strong> Seleciona um único candidato de baixo risco e gera o pacote de autorização. Não persiste journal, não adquire lock e não altera conteúdo.</p></div>';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
        wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
        submit_button( __( 'Gerar T099B Authorization Pack e baixar JSON', 'bdc-knowledge-base' ) );
        echo '</form></div>';
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
        if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
            wp_die( 'Nonce inválido.', '', array( 'response' => 403 ) );
        }
        $report = self::run();
        $json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        if ( ! is_string( $json ) ) {
            wp_die( 'Falha JSON.', '', array( 'response' => 500 ) );
        }
        nocache_headers();
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-t099b-authorization-pack-' . gmdate( 'Ymd-His' ) . '.json"' );
        echo $json;
        exit;
    }

    private static function run(): array {
        $started = microtime( true );
        $selection = self::select_candidate();
        if ( $selection instanceof \WP_Error ) {
            return self::failure_report( $selection, $started );
        }

        $post_id = (int) $selection['post_id'];
        $post = get_post( $post_id );
        $source = Migration_Fidelity_Source::build( $post_id );
        $serialized = is_array( $source ) ? Core_Block_Lossless_Serializer::serialize_source( $source ) : $source;
        $dry = Block_Migration_Dry_Run::build( $post_id );
        if ( ! is_object( $post ) || $source instanceof \WP_Error || $serialized instanceof \WP_Error || $dry instanceof \WP_Error ) {
            return self::failure_report( new \WP_Error( 'bdc_kb_t099b_target_recheck_failed', 'Candidato falhou no recheck final.' ), $started );
        }

        $lock = Block_Migration_Lock::inspect( $post_id );
        $journal_count = count( get_post_meta( $post_id, Block_Migration_Journal_Store::META_KEY, false ) );
        if ( $lock instanceof \WP_Error || 'free' !== (string) ( $lock['status'] ?? '' ) || 0 !== $journal_count || 'ready' !== (string) ( $dry['dry_run_status'] ?? '' ) ) {
            return self::failure_report( new \WP_Error( 'bdc_kb_t099b_target_not_clean', 'Candidato deixou de estar limpo/ready.' ), $started );
        }

        $source_material = is_array( $dry['source_material'] ?? null ) ? $dry['source_material'] : array();
        $block_names = array();
        foreach ( (array) ( $serialized['blocks'] ?? array() ) as $block ) {
            if ( is_array( $block ) ) {
                $block_names[] = (string) ( $block['blockName'] ?? '' );
            }
        }
        $authorization_identity = array(
            'gate' => 'T099C',
            'post_id' => $post_id,
            'fidelity_hash_before' => (string) ( $dry['fidelity_hash_before'] ?? '' ),
            'serialization_hash' => (string) ( $dry['serialization_hash'] ?? '' ),
            'dry_run_hash' => (string) ( $dry['dry_run_hash'] ?? '' ),
            'serialized_post_content_sha256' => (string) ( $dry['serialized_post_content_sha256'] ?? '' ),
        );
        try {
            $authorization_id = Canonical_JSON::hash( $authorization_identity );
        } catch ( \JsonException $error ) {
            return self::failure_report( new \WP_Error( 'bdc_kb_t099b_authorization_hash_failed', 'Falha ao calcular authorization_id.' ), $started );
        }

        return array(
            'schema_version' => self::SCHEMA_VERSION,
            'gate' => 'T099B',
            'mode' => 'single_canary_authorization_pack_read_only',
            'generated_at' => gmdate( 'c' ),
            'environment' => array(
                'wordpress' => get_bloginfo( 'version' ),
                'php' => PHP_VERSION,
                'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
                'gutenberg_plugin_dependency' => false,
            ),
            'selection' => array(
                'policy' => 'legacy_html + dry_run ready + zero block-migration residue + no active Elementor payload + conservative static risk profile; prefer T099A target if still low-risk',
                'preferred_post_id' => self::PREFERRED_POST_ID,
                'preferred_target_reused' => self::PREFERRED_POST_ID === $post_id,
                'evaluated_candidates' => (int) $selection['evaluated_candidates'],
                'low_risk_candidates' => (int) $selection['low_risk_candidates'],
            ),
            'target' => array(
                'post_id' => $post_id,
                'post_title' => sanitize_text_field( (string) ( $post->post_title ?? '' ) ),
                'post_status' => (string) ( $post->post_status ?? '' ),
                'source_kind' => (string) ( $dry['source_kind'] ?? '' ),
                'risk_tier' => 'low',
                'risk_profile' => $selection['risk_profile'],
            ),
            'identity' => array(
                'fidelity_hash_before' => (string) ( $dry['fidelity_hash_before'] ?? '' ),
                'serialization_hash' => (string) ( $dry['serialization_hash'] ?? '' ),
                'dry_run_hash' => (string) ( $dry['dry_run_hash'] ?? '' ),
                'post_content_sha256_before' => (string) ( $source_material['post_content_sha256'] ?? '' ),
                'elementor_data_sha256_before' => (string) ( $source_material['elementor_data_sha256'] ?? '' ),
                'serialized_post_content_sha256_expected' => (string) ( $dry['serialized_post_content_sha256'] ?? '' ),
                'expected_block_names' => array_values( array_filter( $block_names, 'strlen' ) ),
                'authorization_id' => $authorization_id,
            ),
            'planned_operation' => array(
                'scope' => 'one post only',
                'write_target' => 'WP_Post.post_content',
                'write_action' => 'replace current post_content with deterministic lossless WordPress Core Block serialization',
                'elementor_data_action' => 'preserve unchanged',
                'journal_required_before_write' => true,
                'exclusive_lock_required' => true,
                'stale_source_recheck_immediately_before_write' => true,
                'verify_after_write' => true,
                'immediate_rollback_required' => true,
                'rollback_action' => 'restore exact pre-write post_content snapshot from durable journal',
                'verify_after_rollback' => true,
            ),
            'preconditions' => array(
                'dry_run_status' => (string) ( $dry['dry_run_status'] ?? '' ),
                'journal_count_now' => $journal_count,
                'lock_status_now' => (string) ( $lock['status'] ?? '' ),
                'source_kind_allowed' => 'legacy_html' === (string) ( $dry['source_kind'] ?? '' ),
                'explicit_human_authorization_required' => true,
                'authorization_must_match_id' => $authorization_id,
            ),
            'authorization' => array(
                'authorized' => false,
                'authorization_id' => $authorization_id,
                'required_exact_scope' => 'T099C apply + verify + immediate rollback for post_id=' . $post_id . ' only',
                'suggested_approval_text' => 'Autorizo o T099C para post_id=' . $post_id . ' authorization_id=' . $authorization_id . ', com apply, verificação e rollback imediato.',
            ),
            'safety' => array(
                'read_only_design' => true,
                'persists_journal' => false,
                'acquires_lock' => false,
                'writes_post_content' => false,
                'writes_elementor_data' => false,
                'writer_allowed' => false,
                'migration_execution_allowed' => false,
                'executes_shortcodes' => false,
                'renders_blocks' => false,
                'calls_external_network' => false,
                'exports_editorial_body' => false,
                'exports_urls' => false,
            ),
            'gate_result' => array( 't099b_authorization_pack_pass' => true ),
            'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
        );
    }

    /** @return array<string,mixed>|\WP_Error */
    private static function select_candidate() {
        $ids = get_posts( array(
            'post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1,
            'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC', 'suppress_filters' => false,
        ) );
        $ids = array_values( array_map( 'intval', is_array( $ids ) ? $ids : array() ) );
        $eligible = array();
        $evaluated = 0;
        foreach ( $ids as $post_id ) {
            ++$evaluated;
            $candidate = self::evaluate_candidate( $post_id );
            if ( is_array( $candidate ) ) {
                $eligible[] = $candidate;
            }
        }
        if ( empty( $eligible ) ) {
            return new \WP_Error( 'bdc_kb_t099b_no_low_risk_candidate', 'Nenhum candidato low-risk disponível.' );
        }
        usort( $eligible, static function ( array $a, array $b ): int {
            if ( (int) $a['post_id'] === self::PREFERRED_POST_ID ) { return -1; }
            if ( (int) $b['post_id'] === self::PREFERRED_POST_ID ) { return 1; }
            $score = (int) $a['risk_score'] <=> (int) $b['risk_score'];
            return 0 !== $score ? $score : (int) $a['post_id'] <=> (int) $b['post_id'];
        } );
        $selected = $eligible[0];
        $selected['evaluated_candidates'] = $evaluated;
        $selected['low_risk_candidates'] = count( $eligible );
        return $selected;
    }

    /** @return array<string,mixed>|null */
    private static function evaluate_candidate( int $post_id ): ?array {
        $post = get_post( $post_id );
        if ( ! is_object( $post ) || 'post' !== (string) ( $post->post_type ?? '' ) ) { return null; }
        $dry = Block_Migration_Dry_Run::build( $post_id );
        if ( $dry instanceof \WP_Error || 'ready' !== (string) ( $dry['dry_run_status'] ?? '' ) || 'legacy_html' !== (string) ( $dry['source_kind'] ?? '' ) ) { return null; }
        if ( 0 !== count( get_post_meta( $post_id, Block_Migration_Journal_Store::META_KEY, false ) ) ) { return null; }
        $lock = Block_Migration_Lock::inspect( $post_id );
        if ( $lock instanceof \WP_Error || 'free' !== (string) ( $lock['status'] ?? '' ) ) { return null; }

        $content = (string) ( $post->post_content ?? '' );
        $elementor_raw = get_post_meta( $post_id, '_elementor_data', true );
        if ( ! is_string( $elementor_raw ) ) {
            $json = wp_json_encode( $elementor_raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
            $elementor_raw = is_string( $json ) ? $json : '';
        }
        $risk = self::risk_profile( $content, $elementor_raw );
        if ( true !== ( $risk['low_risk'] ?? false ) ) { return null; }
        return array(
            'post_id' => $post_id,
            'risk_score' => (int) $risk['risk_score'],
            'risk_profile' => $risk,
        );
    }

    /** @return array<string,mixed> */
    public static function risk_profile( string $content, string $elementor_raw = '' ): array {
        $count = static function ( string $pattern, string $subject ): int {
            $matches = array();
            $result = preg_match_all( $pattern, $subject, $matches );
            return false === $result ? 0 : (int) $result;
        };
        $bytes = strlen( $content );
        $links = $count( '/<a\\b/i', $content );
        $images = $count( '/<img\\b/i', $content );
        $tables = $count( '/<table\\b/i', $content );
        $dangerous = $count( '/<(?:script|iframe|form|object|embed|style)\\b/i', $content );
        $block_comments = $count( '/<!--\\s*wp:/i', $content );
        $shortcodes = 0;
        if ( function_exists( 'get_shortcode_regex' ) ) {
            global $shortcode_tags;
            if ( is_array( $shortcode_tags ) && ! empty( $shortcode_tags ) ) {
                $regex = get_shortcode_regex( array_keys( $shortcode_tags ) );
                $shortcodes = $count( '/' . $regex . '/s', $content );
            }
        }
        $elementor_bytes = strlen( $elementor_raw );
        $low = $bytes > 0 && $bytes <= self::MAX_CONTENT_BYTES
            && 0 === $dangerous && 0 === $block_comments && 0 === $shortcodes
            && 0 === $elementor_bytes && $links <= 10 && $images <= 1 && $tables <= 1;
        $score = (int) ceil( $bytes / 100 ) + ( $links * 20 ) + ( $images * 200 ) + ( $tables * 300 )
            + ( $shortcodes * 1000 ) + ( $dangerous * 5000 ) + ( $elementor_bytes > 0 ? 10000 : 0 );
        return array(
            'low_risk' => $low,
            'risk_score' => $score,
            'content_bytes' => $bytes,
            'links' => $links,
            'images' => $images,
            'tables' => $tables,
            'registered_shortcode_occurrences' => $shortcodes,
            'dangerous_embed_or_script_tags' => $dangerous,
            'core_block_comments' => $block_comments,
            'elementor_data_bytes' => $elementor_bytes,
        );
    }

    private static function failure_report( \WP_Error $error, float $started ): array {
        return array(
            'schema_version' => self::SCHEMA_VERSION,
            'gate' => 'T099B',
            'mode' => 'single_canary_authorization_pack_read_only',
            'generated_at' => gmdate( 'c' ),
            'error_code' => $error->get_error_code(),
            'error_message' => $error->get_error_message(),
            'safety' => array(
                'read_only_design' => true, 'persists_journal' => false, 'acquires_lock' => false,
                'writes_post_content' => false, 'writes_elementor_data' => false,
                'writer_allowed' => false, 'migration_execution_allowed' => false,
            ),
            'gate_result' => array( 't099b_authorization_pack_pass' => false ),
            'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
        );
    }
}
