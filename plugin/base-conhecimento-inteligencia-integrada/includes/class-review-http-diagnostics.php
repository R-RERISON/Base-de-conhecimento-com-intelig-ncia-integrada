<?php
/**
 * Runner HTTP temporário do Gate G-070 da SPEC-003.
 *
 * Deve ser removido antes do RC.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Review_HTTP_Diagnostics {

	public const ACTION = 'bdc_kb_run_review_http_diagnostics';
	private const NONCE_ACTION = 'bdc_kb_review_http_diagnostics';
	private const NONCE_FIELD  = 'bdc_kb_review_http_diagnostics_nonce';
	private const SCHEMA       = '1.0.0';
	private const TEST_MODE    = '__bdc_review_test_mode';
	private const TEST_POST    = '__bdc_review_test_post';
	private const TEST_NONCE   = '__bdc_review_test_nonce';

	public static function register(): void {
		add_action( 'admin_notices', array( self::class, 'render_panel' ) );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
		add_filter( 'map_meta_cap', array( self::class, 'maybe_force_edit_post_denial' ), 999, 4 );
		add_filter( 'user_has_cap', array( self::class, 'maybe_force_reviewer_denial' ), 999, 4 );
	}

	public static function render_panel(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page = isset( $_GET['page'] ) && is_scalar( $_GET['page'] )
			? sanitize_key( wp_unslash( (string) $_GET['page'] ) )
			: '';
		if ( Admin_Page::PAGE_SLUG !== $page ) {
			return;
		}

		echo '<div class="notice notice-warning">';
		echo '<p><strong>Homologação temporária SPEC-003 — Segurança HTTP Review</strong></p>';
		echo '<p>Executa requests reais contra admin-post.php usando apenas fixtures temporárias. Não usa artigos reais.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( 'Executar segurança HTTP Review e gerar JSON', 'secondary', 'submit', false );
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
			wp_die( esc_html__( 'Nonce inválido.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		$report = self::run();
		$name   = 'bdc-kb-review-http-security-' . gmdate( 'Ymd-His' ) . '.json';
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $name . '"' );
		echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		exit;
	}

	public static function maybe_force_edit_post_denial( array $caps, string $cap, int $user_id, array $args ): array {
		unset( $user_id );
		if ( 'edit_post' !== $cap || 'deny_edit_post' !== self::test_mode() ) {
			return $caps;
		}
		$post_id = isset( $args[0] ) ? absint( $args[0] ) : 0;
		if ( $post_id <= 0 || ! self::valid_test_signature( 'deny_edit_post', $post_id ) ) {
			return $caps;
		}
		return array( 'do_not_allow' );
	}

	public static function maybe_force_reviewer_denial( array $allcaps, array $caps, array $args, $user ): array {
		unset( $caps, $user );
		$requested = isset( $args[0] ) && is_string( $args[0] ) ? $args[0] : '';
		if ( 'edit_others_posts' !== $requested || 'deny_reviewer' !== self::test_mode() ) {
			return $allcaps;
		}
		$post_id = isset( $_POST['post_id'] ) && is_scalar( $_POST['post_id'] ) ? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
		if ( $post_id <= 0 || ! self::valid_test_signature( 'deny_reviewer', $post_id ) ) {
			return $allcaps;
		}
		$allcaps['edit_others_posts'] = false;
		return $allcaps;
	}

	private static function run(): array {
		$started = microtime( true );
		$token   = strtolower( wp_generate_password( 10, false, false ) );
		$prefix  = '__bdc_spec003_http_' . $token;
		$tests   = array();
		$post_a  = 0;
		$post_b  = 0;
		$page_id = 0;
		$term_ids = array();
		$baseline = array();
		$setup_ok = false;
		$observations = array();

		$report = array(
			'schema_version' => self::SCHEMA,
			'mode'           => 'temporary_review_http_security_fixture_only',
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
				'uses_real_admin_post'  => true,
				'uses_signed_test_deny' => true,
				'cleanup_required'      => true,
			),
		);

		try {
			$post_a = self::create_post( $prefix . '_a' );
			$post_b = self::create_post( $prefix . '_b' );
			$page_id = self::create_page( $prefix . '_page' );

			update_post_meta( $post_a, '_elementor_data', '[{"fixture":true}]' );
			foreach ( Meta_Contract::fields() as $field => $definition ) {
				update_post_meta( $post_a, $definition['key'], 'fixture-summary-' . $field );
			}
			update_post_meta( $post_a, '_kb2ops_review_state', 'legacy-marker' );
			update_post_meta( $post_a, '_kb2ops_include_ai', 'legacy-marker' );

			foreach ( Classification_Contract::fields() as $field => $definition ) {
				$created = wp_insert_term( $prefix . '_' . $field, $definition['taxonomy'] );
				if ( is_wp_error( $created ) || empty( $created['term_id'] ) ) {
					throw new \RuntimeException( 'Falha ao criar termo fixture: ' . $field );
				}
				$term_id = (int) $created['term_id'];
				$term_ids[ $definition['taxonomy'] ] = $term_id;
				$assigned = wp_set_object_terms( $post_a, array( $term_id ), $definition['taxonomy'], false );
				if ( is_wp_error( $assigned ) ) {
					throw new \RuntimeException( 'Falha ao atribuir termo fixture.' );
				}
			}

			$baseline = self::snapshot_non_review_state( $post_a );
			$setup_ok = true;
			self::case_result( $tests, 'R070-H01', true, 'Fixtures temporárias preparadas sem usar artigos reais.' );

			$get = self::request( 'GET', $post_a, array() );
			self::case_result( $tests, 'R070-H02', 405 === $get['code'], 'GET no writer Review retorna HTTP 405.' );

			$missing_nonce = self::request( 'POST', $post_a, array( 'review' => array( 'target_state' => Review_Contract::STATE_IN_REVIEW ) ), false );
			self::case_result( $tests, 'R070-H03', self::redirect_is( $missing_nonce, 'invalid_nonce', $post_a ) && 0 === self::event_count( $post_a ), 'POST sem nonce é rejeitado antes de qualquer evento.' );

			$invalid_nonce = self::request( 'POST', $post_a, array( Review_Admin::NONCE_FIELD => 'invalid', 'review' => array( 'target_state' => Review_Contract::STATE_IN_REVIEW ) ), false );
			self::case_result( $tests, 'R070-H04', self::redirect_is( $invalid_nonce, 'invalid_nonce', $post_a ) && 0 === self::event_count( $post_a ), 'Nonce inválido é rejeitado com zero write.' );

			$nonce_a = wp_create_nonce( Review_Admin::nonce_action( $post_a ) );
			$reused = self::request( 'POST', $post_b, array( Review_Admin::NONCE_FIELD => $nonce_a, 'review' => array( 'target_state' => Review_Contract::STATE_IN_REVIEW ) ), false );
			self::case_result( $tests, 'R070-H05', self::redirect_is( $reused, 'invalid_nonce', $post_b ) && 0 === self::event_count( $post_b ), 'Nonce é vinculado ao post e não pode ser reutilizado em outro artigo.' );

			$missing_payload = self::request( 'POST', $post_a, array() );
			self::case_result( $tests, 'R070-H06', self::redirect_is( $missing_payload, 'invalid_payload', $post_a ) && 0 === self::event_count( $post_a ), 'Payload Review ausente é rejeitado.' );

			$scalar_payload = self::request( 'POST', $post_a, array( 'review' => 'invalid' ) );
			self::case_result( $tests, 'R070-H07', self::redirect_is( $scalar_payload, 'invalid_payload', $post_a ) && 0 === self::event_count( $post_a ), 'Payload Review escalar é rejeitado.' );

			$mass = self::request( 'POST', $post_a, array( 'review' => array( 'target_state' => Review_Contract::STATE_IN_REVIEW, 'unexpected' => '1' ) ) );
			self::case_result( $tests, 'R070-H08', self::redirect_is( $mass, 'invalid_payload', $post_a ) && 0 === self::event_count( $post_a ), 'Mass assignment/campo Review não autorizado é rejeitado.' );

			$invalid_target = self::request( 'POST', $post_a, array( 'review' => array( 'target_state' => Review_Contract::STATE_UNREVIEWED ) ) );
			self::case_result( $tests, 'R070-H09', self::redirect_is( $invalid_target, 'validation_error', $post_a ) && 0 === self::event_count( $post_a ), 'Destino unreviewed explícito é rejeitado sem write.' );

			$xss_target = self::request( 'POST', $post_a, array( 'review' => array( 'target_state' => '<script>approved</script>' ) ) );
			self::case_result( $tests, 'R070-H10', self::redirect_is( $xss_target, 'validation_error', $post_a ) && 0 === self::event_count( $post_a ), 'Payload de estado malformado/XSS não é aceito como transição.' );

			$page = self::request( 'POST', $page_id, array( 'review' => array( 'target_state' => Review_Contract::STATE_IN_REVIEW ) ) );
			self::case_result( $tests, 'R070-H11', self::redirect_is( $page, 'invalid_post', $page_id ), 'Post type page é rejeitado no handler real.' );

			$missing_post_id = 2147483000;
			$missing_post = self::request( 'POST', $missing_post_id, array( 'review' => array( 'target_state' => Review_Contract::STATE_IN_REVIEW ) ) );
			self::case_result( $tests, 'R070-H12', self::redirect_is( $missing_post, 'invalid_post', $missing_post_id ), 'ID de post inexistente é rejeitado.' );

			$deny_edit = self::request( 'POST', $post_a, array(
				'review' => array( 'target_state' => Review_Contract::STATE_IN_REVIEW ),
				self::TEST_MODE => 'deny_edit_post',
				self::TEST_POST => $post_a,
				self::TEST_NONCE => wp_create_nonce( self::test_nonce_action( 'deny_edit_post', $post_a ) ),
			) );
			$observations['object_capability'] = self::observation( $deny_edit );
			self::case_result( $tests, 'R070-H13', self::redirect_is( $deny_edit, 'forbidden', $post_a ) && 0 === self::event_count( $post_a ), 'Negação assinada de edit_post na fixture atravessa admin-post e retorna forbidden sem write.' );

			$valid = self::request( 'POST', $post_a, array( 'review' => array( 'target_state' => Review_Contract::STATE_IN_REVIEW ) ) );
			$state = Review_Store::read( $post_a );
			$observations['valid_submit'] = self::observation( $valid );
			self::case_result( $tests, 'R070-H14', self::redirect_is( $valid, 'saved', $post_a ) && ! is_wp_error( $state ) && Review_Contract::STATE_IN_REVIEW === (string) ( $state['state'] ?? '' ) && 1 === self::event_count( $post_a ), 'POST válido persiste in_review, confirma releitura e executa PRG.' );

			$noop = self::request( 'POST', $post_a, array( 'review' => array( 'target_state' => Review_Contract::STATE_IN_REVIEW ) ) );
			self::case_result( $tests, 'R070-H15', self::redirect_is( $noop, 'no_change', $post_a ) && 1 === self::event_count( $post_a ), 'Mesmo estado retorna no_change via PRG e não cria evento.' );

			$note_required = self::request( 'POST', $post_a, array( 'review' => array( 'target_state' => Review_Contract::STATE_NEEDS_CHANGES, 'note' => '' ) ) );
			self::case_result( $tests, 'R070-H16', self::redirect_is( $note_required, 'validation_error', $post_a ) && 1 === self::event_count( $post_a ), 'needs_changes sem justificativa falha antes da mutação.' );

			$large_note = self::request( 'POST', $post_a, array( 'review' => array( 'target_state' => Review_Contract::STATE_NEEDS_CHANGES, 'note' => str_repeat( 'x', Review_Contract::MAX_NOTE_BYTES + 1 ) ) ) );
			self::case_result( $tests, 'R070-H17', self::redirect_is( $large_note, 'validation_error', $post_a ) && 1 === self::event_count( $post_a ), 'Nota acima do limite é rejeitada via handler com zero write.' );

			$deny_reviewer = self::request( 'POST', $post_a, array(
				'review' => array( 'target_state' => Review_Contract::STATE_APPROVED, 'note' => 'fixture' ),
				self::TEST_MODE => 'deny_reviewer',
				self::TEST_POST => $post_a,
				self::TEST_NONCE => wp_create_nonce( self::test_nonce_action( 'deny_reviewer', $post_a ) ),
			) );
			$observations['reviewer_capability'] = self::observation( $deny_reviewer );
			self::case_result( $tests, 'R070-H18', self::redirect_is( $deny_reviewer, 'forbidden', $post_a ) && 1 === self::event_count( $post_a ), 'Decisão de reviewer exige edit_others_posts no handler/store real.' );

			$needs_changes = self::request( 'POST', $post_a, array( 'review' => array( 'target_state' => Review_Contract::STATE_NEEDS_CHANGES, 'note' => 'Ajustar a fixture.' ) ) );
			$state_nc = Review_Store::read( $post_a );
			self::case_result( $tests, 'R070-H19', self::redirect_is( $needs_changes, 'saved', $post_a ) && ! is_wp_error( $state_nc ) && Review_Contract::STATE_NEEDS_CHANGES === (string) ( $state_nc['state'] ?? '' ) && 2 === self::event_count( $post_a ), 'Transição válida para needs_changes é persistida via HTTP real.' );

			$approved = self::request( 'POST', $post_a, array( 'review' => array( 'target_state' => Review_Contract::STATE_APPROVED, 'note' => 'Fixture aprovada.' ) ) );
			$state_approved = Review_Store::read( $post_a );
			self::case_result( $tests, 'R070-H20', self::redirect_is( $approved, 'saved', $post_a ) && ! is_wp_error( $state_approved ) && Review_Contract::STATE_APPROVED === (string) ( $state_approved['state'] ?? '' ) && 3 === self::event_count( $post_a ), 'Decisão approved válida persiste evento, ator e estado final via HTTP real.' );

			self::case_result( $tests, 'R070-H21', self::snapshots_equal( $baseline, self::snapshot_non_review_state( $post_a ) ), 'Writer HTTP Review preserva editorial, Summary, Classificação e stores legados da fixture.' );
		} catch ( \Throwable $e ) {
			self::case_result( $tests, 'R070-SETUP', false, 'Falha de setup/execução do runner: ' . get_class( $e ) );
		} finally {
			foreach ( array( $post_a, $post_b, $page_id ) as $fixture_id ) {
				if ( $fixture_id > 0 ) {
					$comments = get_comments( array( 'post_id' => $fixture_id, 'type' => Review_Contract::COMMENT_TYPE, 'status' => 'all', 'number' => 0 ) );
					if ( is_array( $comments ) ) {
						foreach ( $comments as $comment ) {
							wp_delete_comment( (int) $comment->comment_ID, true );
						}
					}
					wp_delete_post( $fixture_id, true );
				}
			}
			foreach ( $term_ids as $taxonomy => $term_id ) {
				wp_delete_term( (int) $term_id, (string) $taxonomy );
			}
		}

		$residual_posts = 0;
		foreach ( array( $post_a, $post_b, $page_id ) as $fixture_id ) {
			if ( $fixture_id > 0 && get_post( $fixture_id ) ) {
				++$residual_posts;
			}
		}
		$residual_terms = 0;
		foreach ( $term_ids as $taxonomy => $term_id ) {
			if ( term_exists( (int) $term_id, (string) $taxonomy ) ) {
				++$residual_terms;
			}
		}
		$residual_events = 0;
		foreach ( array( $post_a, $post_b ) as $fixture_id ) {
			if ( $fixture_id > 0 ) {
				$residual_events += self::event_count( $fixture_id );
			}
		}
		self::case_result( $tests, 'R070-H22', $setup_ok && 0 === $residual_posts && 0 === $residual_terms && 0 === $residual_events, 'Cleanup remove posts, termos e eventos de Review sem resíduos.' );

		$pass = count( array_filter( $tests, static fn( array $row ): bool => 'PASS' === $row['status'] ) );
		$fail = count( $tests ) - $pass;
		$report['tests'] = $tests;
		$report['observations'] = $observations;
		$report['cleanup'] = array(
			'setup_completed'        => $setup_ok,
			'residual_posts'         => $residual_posts,
			'residual_terms'         => $residual_terms,
			'residual_review_events' => $residual_events,
		);
		$report['summary'] = array(
			'pass'        => $pass,
			'fail'        => $fail,
			'overall'     => 0 === $fail ? 'PASS' : 'FAIL',
			'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
		);
		$report['limitations'] = array(
			'Runner temporário usa sslverify=false apenas no loopback local para evitar falso negativo de CA interna; não testa TLS.',
			'FAIL_SAFE/PARTIAL_FAILURE_CRITICAL permanecem cobertos pelos unitários determinísticos da store.',
			'UI/Browser Acceptance pertence ao Gate G-110 e não é validada aqui.',
		);
		return $report;
	}

	private static function create_post( string $title ): int {
		$id = wp_insert_post( array( 'post_type' => Review_Contract::POST_TYPE, 'post_status' => 'draft', 'post_title' => $title, 'post_content' => 'fixture-only-content', 'post_author' => get_current_user_id() ), true );
		if ( is_wp_error( $id ) || (int) $id <= 0 ) {
			throw new \RuntimeException( 'Falha ao criar post fixture.' );
		}
		return (int) $id;
	}

	private static function create_page( string $title ): int {
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => $title, 'post_content' => 'fixture-only-page', 'post_author' => get_current_user_id() ), true );
		if ( is_wp_error( $id ) || (int) $id <= 0 ) {
			throw new \RuntimeException( 'Falha ao criar page fixture.' );
		}
		return (int) $id;
	}

	private static function request( string $method, int $post_id, array $extra, bool $auto_nonce = true ): array {
		$body = array_merge( array( 'action' => Review_Admin::ACTION, 'post_id' => $post_id ), $extra );
		if ( $auto_nonce && ! array_key_exists( Review_Admin::NONCE_FIELD, $body ) ) {
			$body[ Review_Admin::NONCE_FIELD ] = wp_create_nonce( Review_Admin::nonce_action( $post_id ) );
		}

		$url = admin_url( 'admin-post.php' );
		$args = array(
			'timeout'     => 20,
			'redirection' => 0,
			'sslverify'   => false,
			'headers'     => array( 'Cookie' => self::cookie_header() ),
		);
		if ( 'GET' === strtoupper( $method ) ) {
			$url = add_query_arg( array( 'action' => Review_Admin::ACTION, 'post_id' => $post_id ), $url );
			$response = wp_remote_get( $url, $args );
		} else {
			$args['body'] = $body;
			$response = wp_remote_post( $url, $args );
		}

		if ( is_wp_error( $response ) ) {
			return array( 'code' => 0, 'location' => '', 'status' => 'transport_error', 'post_id' => 0 );
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		$location = (string) wp_remote_retrieve_header( $response, 'location' );
		$status = '';
		$redirect_post_id = 0;
		if ( '' !== $location ) {
			$query = (string) wp_parse_url( $location, PHP_URL_QUERY );
			$params = array();
			parse_str( $query, $params );
			$status = isset( $params['bdc_review_status'] ) && is_scalar( $params['bdc_review_status'] ) ? sanitize_key( (string) $params['bdc_review_status'] ) : '';
			$redirect_post_id = isset( $params['post_id'] ) && is_scalar( $params['post_id'] ) ? absint( (string) $params['post_id'] ) : 0;
		}
		return array( 'code' => $code, 'location' => $location, 'status' => $status, 'post_id' => $redirect_post_id );
	}

	private static function redirect_is( array $response, string $status, int $post_id ): bool {
		return 302 === (int) ( $response['code'] ?? 0 )
			&& $status === (string) ( $response['status'] ?? '' )
			&& $post_id === (int) ( $response['post_id'] ?? 0 );
	}

	private static function observation( array $response ): array {
		return array(
			'http_code'       => (int) ( $response['code'] ?? 0 ),
			'redirect_status' => (string) ( $response['status'] ?? '' ),
			'redirect_post_id_matches_fixture' => (int) ( $response['post_id'] ?? 0 ) > 0,
		);
	}

	private static function cookie_header(): string {
		$user_id = get_current_user_id();
		$token   = wp_get_session_token();
		$expiry  = time() + 600;
		$cookies = array(
			AUTH_COOKIE        => wp_generate_auth_cookie( $user_id, $expiry, 'auth', $token ),
			SECURE_AUTH_COOKIE => wp_generate_auth_cookie( $user_id, $expiry, 'secure_auth', $token ),
			LOGGED_IN_COOKIE   => wp_generate_auth_cookie( $user_id, $expiry, 'logged_in', $token ),
		);
		$parts = array();
		foreach ( $cookies as $name => $value ) {
			$parts[] = $name . '=' . $value;
		}
		return implode( '; ', $parts );
	}

	private static function test_mode(): string {
		return isset( $_POST[ self::TEST_MODE ] ) && is_scalar( $_POST[ self::TEST_MODE ] )
			? sanitize_key( wp_unslash( (string) $_POST[ self::TEST_MODE ] ) )
			: '';
	}

	private static function test_nonce_action( string $mode, int $post_id ): string {
		return 'bdc_kb_review_http_' . sanitize_key( $mode ) . '_' . $post_id;
	}

	private static function valid_test_signature( string $mode, int $post_id ): bool {
		$posted_id = isset( $_POST[ self::TEST_POST ] ) && is_scalar( $_POST[ self::TEST_POST ] ) ? absint( wp_unslash( (string) $_POST[ self::TEST_POST ] ) ) : 0;
		$nonce = isset( $_POST[ self::TEST_NONCE ] ) && is_scalar( $_POST[ self::TEST_NONCE ] ) ? wp_unslash( (string) $_POST[ self::TEST_NONCE ] ) : '';
		return $posted_id === $post_id && wp_verify_nonce( $nonce, self::test_nonce_action( $mode, $post_id ) );
	}

	private static function event_count( int $post_id ): int {
		$count = get_comments( array( 'post_id' => $post_id, 'type' => Review_Contract::COMMENT_TYPE, 'status' => 'approve', 'count' => true ) );
		return (int) $count;
	}

	private static function snapshot_non_review_state( int $post_id ): array {
		$post = get_post( $post_id );
		$summary = Summary_Store::read( $post_id );
		$classification = Classification_Store::read( $post_id );
		return array(
			'post_title' => is_object( $post ) ? (string) $post->post_title : '',
			'post_content' => is_object( $post ) ? (string) $post->post_content : '',
			'elementor' => get_post_meta( $post_id, '_elementor_data', true ),
			'summary' => is_wp_error( $summary ) ? 'ERROR' : $summary,
			'classification' => is_wp_error( $classification ) ? 'ERROR' : $classification,
			'legacy_state' => get_post_meta( $post_id, '_kb2ops_review_state', true ),
			'legacy_include_ai' => get_post_meta( $post_id, '_kb2ops_include_ai', true ),
		);
	}

	private static function snapshots_equal( array $a, array $b ): bool {
		return wp_json_encode( $a ) === wp_json_encode( $b );
	}

	private static function case_result( array &$tests, string $id, bool $ok, string $description ): void {
		$tests[] = array( 'id' => $id, 'status' => $ok ? 'PASS' : 'FAIL', 'description' => $description );
	}
}
