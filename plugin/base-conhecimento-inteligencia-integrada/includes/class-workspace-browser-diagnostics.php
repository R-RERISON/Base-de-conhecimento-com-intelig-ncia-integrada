<?php
/**
 * Browser Acceptance temporário do Gate G-110 da SPEC-003.
 *
 * Usa somente fixtures controladas e deve ser removido antes do RC.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Workspace_Browser_Diagnostics {

	public const START_ACTION    = 'bdc_kb_start_workspace_browser_diagnostics';
	public const FINALIZE_ACTION = 'bdc_kb_finalize_workspace_browser_diagnostics';
	private const START_NONCE    = 'bdc_kb_g110_browser_start';
	private const FIXTURE_META   = '_bdc_g110_browser_fixture';
	private const TOKEN_META     = '_bdc_g110_browser_token';
	private const TERMS_META     = '_bdc_g110_browser_term_ids';
	private const SCHEMA         = '1.0.0';
	private const PREFIX         = '__bdc_g110_browser_';

	public static function register(): void {
		add_action( 'admin_notices', array( self::class, 'render_panel' ) );
		add_action( 'admin_post_' . self::START_ACTION, array( self::class, 'handle_start' ) );
		add_action( 'admin_post_' . self::FINALIZE_ACTION, array( self::class, 'handle_finalize' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_runner' ), 99 );
		add_filter( 'user_has_cap', array( self::class, 'maybe_force_reviewer_denial' ), 999, 4 );
	}

	public static function render_panel(): void {
		if ( ! current_user_can( 'manage_options' ) || ! self::is_kb_page() ) {
			return;
		}

		if ( self::request_token() !== '' ) {
			echo '<div class="notice notice-info"><p><strong>G-110 Browser Acceptance em execução</strong></p><p>Não feche esta aba até o download do JSON. O runner usa somente o artigo e os termos de fixture criados para este teste.</p></div>';
			return;
		}

		echo '<div class="notice notice-warning">';
		echo '<p><strong>Homologação temporária SPEC-003 — Browser Acceptance G-110</strong></p>';
		echo '<p>Cria um artigo de fixture e quatro termos temporários, exercita Workspace, Summary, Classificação, Review, Histórico, teclado e reflow; ao final gera JSON e remove as fixtures.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::START_ACTION ) . '">';
		wp_nonce_field( self::START_NONCE, 'bdc_g110_start_nonce' );
		submit_button( 'Executar Browser Acceptance G-110 e gerar JSON', 'secondary', 'submit', false );
		echo '</form>';
		echo '</div>';
	}

	public static function handle_start(): void {
		self::assert_admin_post();
		check_admin_referer( self::START_NONCE, 'bdc_g110_start_nonce' );

		self::cleanup_stale_fixtures();

		$token   = strtolower( wp_generate_password( 12, false, false ) );
		$post_id = wp_insert_post(
			array(
				'post_type'    => Meta_Contract::POST_TYPE,
				'post_status'  => 'draft',
				'post_title'   => self::PREFIX . $token,
				'post_content' => 'G110 browser fixture editorial sentinel ' . $token,
				'post_author'  => get_current_user_id(),
			),
			true
		);

		if ( is_wp_error( $post_id ) || (int) $post_id <= 0 ) {
			wp_die( esc_html__( 'Falha ao criar a fixture do Browser Acceptance.', 'bdc-knowledge-base' ) );
		}
		$post_id = (int) $post_id;

		update_post_meta( $post_id, self::FIXTURE_META, '1' );
		update_post_meta( $post_id, self::TOKEN_META, $token );
		update_post_meta( $post_id, '_elementor_data', '[{"g110_fixture":"' . $token . '"}]' );

		$term_ids = array();
		foreach ( Classification_Contract::fields() as $field => $definition ) {
			$created = wp_insert_term(
				self::PREFIX . $token . '_' . $field,
				$definition['taxonomy'],
				array( 'slug' => 'bdc-g110-' . $token . '-' . str_replace( '_', '-', $field ) )
			);
			if ( is_wp_error( $created ) || empty( $created['term_id'] ) ) {
				self::cleanup_fixture( $post_id );
				wp_die( esc_html__( 'Falha ao criar termos de fixture do Browser Acceptance.', 'bdc-knowledge-base' ) );
			}
			$term_ids[ $field ] = (int) $created['term_id'];
		}
		update_post_meta( $post_id, self::TERMS_META, $term_ids );

		$url = add_query_arg(
			array(
				'page'         => Admin_Page::PAGE_SLUG,
				'post_id'      => $post_id,
				'tab'          => 'overview',
				'bdc_g110_run' => $token,
			),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $url );
		exit;
	}

	public static function enqueue_runner( string $hook_suffix ): void {
		if ( 'toplevel_page_' . Admin_Page::PAGE_SLUG !== $hook_suffix || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$token   = self::request_token();
		$post_id = self::request_post_id();
		if ( '' === $token || $post_id <= 0 || ! self::is_fixture( $post_id, $token ) ) {
			return;
		}

		$term_ids = get_post_meta( $post_id, self::TERMS_META, true );
		if ( ! is_array( $term_ids ) ) {
			$term_ids = array();
		}

		wp_enqueue_script(
			'bdc-kb-browser-acceptance',
			BDC_KB_URL . 'assets/js/browser-acceptance.js',
			array( 'bdc-kb-workspace' ),
			BDC_KB_VERSION,
			true
		);

		wp_localize_script(
			'bdc-kb-browser-acceptance',
			'BDCKBG110',
			array(
				'postId'         => $post_id,
				'token'          => $token,
				'pageSlug'       => Admin_Page::PAGE_SLUG,
				'adminUrl'       => admin_url( 'admin.php' ),
				'adminPostUrl'   => admin_url( 'admin-post.php' ),
				'finalizeAction' => self::FINALIZE_ACTION,
				'finalizeNonce'  => wp_create_nonce( 'bdc_kb_g110_finalize_' . $token ),
				'termIds'        => array_map( 'intval', $term_ids ),
				'capabilitySig'  => self::capability_signature( $token, $post_id ),
				'expected'       => array(
					'objective'  => 'G110 objective ' . $token,
					'escalation' => 'G110 escalation ' . $token,
					'important'  => 'G110 important ' . $token,
					'needsNote'  => 'G110 needs changes ' . $token,
				),
			)
		);
	}

	public static function handle_finalize(): void {
		self::assert_admin_post();

		$token = isset( $_POST['token'] ) && is_scalar( $_POST['token'] )
			? sanitize_key( wp_unslash( (string) $_POST['token'] ) )
			: '';
		$post_id = isset( $_POST['post_id'] ) && is_scalar( $_POST['post_id'] )
			? absint( wp_unslash( (string) $_POST['post_id'] ) )
			: 0;
		$nonce = isset( $_POST['nonce'] ) && is_scalar( $_POST['nonce'] )
			? wp_unslash( (string) $_POST['nonce'] )
			: '';

		if ( '' === $token || $post_id <= 0 || ! wp_verify_nonce( $nonce, 'bdc_kb_g110_finalize_' . $token ) || ! self::is_fixture( $post_id, $token ) ) {
			wp_send_json_error( array( 'message' => 'Fixture/nonce inválido.' ), 403 );
		}

		$browser_raw = isset( $_POST['browser_results'] ) && is_scalar( $_POST['browser_results'] )
			? wp_unslash( (string) $_POST['browser_results'] )
			: '[]';
		if ( strlen( $browser_raw ) > 131072 ) {
			$browser_raw = '[]';
		}
		$browser_tests = json_decode( $browser_raw, true );
		if ( ! is_array( $browser_tests ) ) {
			$browser_tests = array();
		}

		$server_tests = array();
		$term_ids     = get_post_meta( $post_id, self::TERMS_META, true );
		$term_ids     = is_array( $term_ids ) ? array_map( 'intval', $term_ids ) : array();
		$expected     = array(
			'objective'  => 'G110 objective ' . $token,
			'escalation' => 'G110 escalation ' . $token,
			'important'  => 'G110 important ' . $token,
			'needsNote'  => 'G110 needs changes ' . $token,
		);

		$post = get_post( $post_id );
		self::case_result( $server_tests, 'G110-S01', is_object( $post ) && 'draft' === (string) $post->post_status, 'Editorial status da fixture permanece draft.' );
		self::case_result( $server_tests, 'G110-S02', is_object( $post ) && (string) $post->post_content === 'G110 browser fixture editorial sentinel ' . $token, 'post_content da fixture permanece inalterado.' );
		self::case_result( $server_tests, 'G110-S03', (string) get_post_meta( $post_id, '_elementor_data', true ) === '[{"g110_fixture":"' . $token . '"}]', '_elementor_data da fixture permanece inalterado.' );

		$summary = Summary_Store::read( $post_id );
		$summary_ok = ! is_wp_error( $summary );
		foreach ( array( 'objective', 'escalation', 'important' ) as $field ) {
			$summary_ok = $summary_ok && (string) ( $summary[ $field ] ?? '' ) === $expected[ $field ];
		}
		self::case_result( $server_tests, 'G110-S04', $summary_ok, 'Summary persistido pelo formulário real e confirmado pelo store canônico.' );

		$classification = Classification_Store::read( $post_id );
		$class_ok = ! is_wp_error( $classification );
		if ( $class_ok ) {
			foreach ( $term_ids as $field => $term_id ) {
				$actual = $classification['terms'][ $field ] ?? array();
				$class_ok = $class_ok && array( $term_id ) === array_values( array_map( 'intval', is_array( $actual ) ? $actual : array() ) );
			}
		}
		self::case_result( $server_tests, 'G110-S05', $class_ok, 'Classificação persistida pelo formulário real e confirmada pelo store canônico.' );

		$history = Review_Store::history( $post_id, 50, 0 );
		$history_ok = is_array( $history ) && 3 === count( $history );
		if ( $history_ok ) {
			$expected_pairs = array(
				array( Review_Contract::STATE_NEEDS_CHANGES, Review_Contract::STATE_APPROVED ),
				array( Review_Contract::STATE_IN_REVIEW, Review_Contract::STATE_NEEDS_CHANGES ),
				array( Review_Contract::STATE_UNREVIEWED, Review_Contract::STATE_IN_REVIEW ),
			);
			foreach ( $expected_pairs as $index => $pair ) {
				$history_ok = $history_ok
					&& (string) ( $history[ $index ]['from'] ?? '' ) === $pair[0]
					&& (string) ( $history[ $index ]['to'] ?? '' ) === $pair[1];
			}
			$history_ok = $history_ok && (string) ( $history[1]['note'] ?? '' ) === $expected['needsNote'];
		}
		self::case_result( $server_tests, 'G110-S06', $history_ok, 'Event log contém exatamente in_review, needs_changes e approved, sem evento extra de NO_CHANGE/validação.' );

		$state = Review_Store::read( $post_id );
		self::case_result( $server_tests, 'G110-S07', ! is_wp_error( $state ) && Review_Contract::STATE_APPROVED === (string) ( $state['state'] ?? '' ), 'Estado final canônico é approved.' );

		$pre_cleanup_events = is_array( $history ) ? count( $history ) : -1;
		$cleanup = self::cleanup_fixture( $post_id );
		$cleanup['events_before_cleanup'] = $pre_cleanup_events;

		$browser_pass = 0;
		$browser_fail = 0;
		$normalized_browser = array();
		foreach ( $browser_tests as $test ) {
			if ( ! is_array( $test ) ) {
				continue;
			}
			$id = isset( $test['id'] ) && is_scalar( $test['id'] ) ? sanitize_key( (string) $test['id'] ) : '';
			$status = isset( $test['status'] ) && 'PASS' === (string) $test['status'] ? 'PASS' : 'FAIL';
			$description = isset( $test['description'] ) && is_scalar( $test['description'] ) ? sanitize_text_field( (string) $test['description'] ) : '';
			$details = isset( $test['details'] ) && is_scalar( $test['details'] ) ? sanitize_textarea_field( (string) $test['details'] ) : '';
			if ( '' === $id ) {
				continue;
			}
			$normalized_browser[] = array( 'id' => $id, 'status' => $status, 'description' => $description, 'details' => $details );
			if ( 'PASS' === $status ) {
				++$browser_pass;
			} else {
				++$browser_fail;
			}
		}

		$server_pass = 0;
		$server_fail = 0;
		foreach ( $server_tests as $test ) {
			if ( 'PASS' === $test['status'] ) {
				++$server_pass;
			} else {
				++$server_fail;
			}
		}

		$cleanup_ok = 0 === (int) $cleanup['residual_posts']
			&& 0 === (int) $cleanup['residual_terms']
			&& 0 === (int) $cleanup['residual_review_events'];
		$overall = 0 === $browser_fail && 0 === $server_fail && $cleanup_ok && $browser_pass >= 15 ? 'PASS' : 'FAIL';

		$report = array(
			'schema_version' => self::SCHEMA,
			'mode'           => 'temporary_g110_browser_acceptance_fixture_only',
			'generated_at'   => gmdate( 'c' ),
			'environment'    => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php'       => PHP_VERSION,
				'plugin'    => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'multisite' => is_multisite(),
			),
			'safety' => array(
				'fixture_only'          => true,
				'modifies_real_content' => false,
				'cleanup_required'      => true,
			),
			'browser_tests'    => $normalized_browser,
			'server_assertions'=> $server_tests,
			'cleanup'          => $cleanup,
			'summary'          => array(
				'browser_pass' => $browser_pass,
				'browser_fail' => $browser_fail,
				'server_pass'  => $server_pass,
				'server_fail'  => $server_fail,
				'overall'      => $overall,
			),
			'limitations' => array(
				'Reflow é medido no browser real em iframes same-origin com viewport fixa e escopo do plugin; não substitui inspeção visual humana.',
				'Permission denied do writer já foi coberto pelo G-070; G-110 valida que a UI não oferece decisões de reviewer quando a capability é negada por probe assinado.',
			),
		);

		wp_send_json_success( $report );
	}

	public static function maybe_force_reviewer_denial( array $allcaps, array $caps, array $args, $user ): array {
		unset( $caps, $user );
		$requested = isset( $args[0] ) && is_string( $args[0] ) ? $args[0] : '';
		if ( 'edit_others_posts' !== $requested ) {
			return $allcaps;
		}
		if ( ! isset( $_GET['bdc_g110_cap'], $_GET['bdc_g110_token'], $_GET['bdc_g110_sig'], $_GET['post_id'] ) ) {
			return $allcaps;
		}
		$mode    = sanitize_key( wp_unslash( (string) $_GET['bdc_g110_cap'] ) );
		$token   = sanitize_key( wp_unslash( (string) $_GET['bdc_g110_token'] ) );
		$sig     = sanitize_text_field( wp_unslash( (string) $_GET['bdc_g110_sig'] ) );
		$post_id = absint( wp_unslash( (string) $_GET['post_id'] ) );
		if ( 'deny_reviewer' !== $mode || $post_id <= 0 || ! self::is_fixture( $post_id, $token ) ) {
			return $allcaps;
		}
		if ( ! hash_equals( self::capability_signature( $token, $post_id ), $sig ) ) {
			return $allcaps;
		}
		$allcaps['edit_others_posts'] = false;
		return $allcaps;
	}

	private static function cleanup_stale_fixtures(): void {
		$posts = get_posts(
			array(
				'post_type'      => Meta_Contract::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 20,
				'fields'         => 'ids',
				'meta_key'       => self::FIXTURE_META,
				'meta_value'     => '1',
			)
		);
		foreach ( $posts as $post_id ) {
			self::cleanup_fixture( (int) $post_id );
		}
	}

	private static function cleanup_fixture( int $post_id ): array {
		$term_ids = get_post_meta( $post_id, self::TERMS_META, true );
		$term_ids = is_array( $term_ids ) ? array_map( 'intval', $term_ids ) : array();
		$history  = Review_Store::history( $post_id, 100, 0 );
		if ( is_array( $history ) ) {
			foreach ( $history as $event ) {
				$event_id = isset( $event['event_id'] ) ? (int) $event['event_id'] : 0;
				if ( $event_id > 0 ) {
					wp_delete_comment( $event_id, true );
				}
			}
		}
		wp_delete_post( $post_id, true );
		foreach ( $term_ids as $field => $term_id ) {
			$definition = Classification_Contract::fields()[ $field ] ?? null;
			if ( is_array( $definition ) && $term_id > 0 ) {
				wp_delete_term( $term_id, $definition['taxonomy'] );
			}
		}

		$residual_terms = 0;
		foreach ( $term_ids as $field => $term_id ) {
			$definition = Classification_Contract::fields()[ $field ] ?? null;
			if ( is_array( $definition ) && $term_id > 0 && term_exists( $term_id, $definition['taxonomy'] ) ) {
				++$residual_terms;
			}
		}

		$residual_events = get_comments(
			array(
				'post_id' => $post_id,
				'type'    => Review_Contract::COMMENT_TYPE,
				'status'  => 'all',
				'count'   => true,
			)
		);

		return array(
			'cleanup_attempted'      => true,
			'residual_posts'         => get_post( $post_id ) ? 1 : 0,
			'residual_terms'         => $residual_terms,
			'residual_review_events' => (int) $residual_events,
		);
	}

	private static function is_fixture( int $post_id, string $token ): bool {
		return $post_id > 0
			&& '1' === (string) get_post_meta( $post_id, self::FIXTURE_META, true )
			&& '' !== $token
			&& hash_equals( (string) get_post_meta( $post_id, self::TOKEN_META, true ), $token );
	}

	private static function capability_signature( string $token, int $post_id ): string {
		return wp_hash( 'g110|deny_reviewer|' . $token . '|' . $post_id, 'nonce' );
	}

	private static function request_token(): string {
		return isset( $_GET['bdc_g110_run'] ) && is_scalar( $_GET['bdc_g110_run'] )
			? sanitize_key( wp_unslash( (string) $_GET['bdc_g110_run'] ) )
			: '';
	}

	private static function request_post_id(): int {
		return isset( $_GET['post_id'] ) && is_scalar( $_GET['post_id'] )
			? absint( wp_unslash( (string) $_GET['post_id'] ) )
			: 0;
	}

	private static function is_kb_page(): bool {
		$page = isset( $_GET['page'] ) && is_scalar( $_GET['page'] )
			? sanitize_key( wp_unslash( (string) $_GET['page'] ) )
			: '';
		return Admin_Page::PAGE_SLUG === $page;
	}

	private static function assert_admin_post(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( esc_html__( 'Método HTTP não permitido.', 'bdc-knowledge-base' ), '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}
	}

	private static function case_result( array &$tests, string $id, bool $pass, string $description ): void {
		$tests[] = array(
			'id'          => $id,
			'status'      => $pass ? 'PASS' : 'FAIL',
			'description' => $description,
		);
	}
}
