<?php
/**
 * Runner temporário de integração da SPEC-003.
 *
 * IMPORTANTE: cria somente fixtures temporárias e deve ser removido antes do RC.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Review_Diagnostics {

	public const ACTION = 'bdc_kb_run_review_diagnostics';
	private const NONCE_ACTION = 'bdc_kb_review_diagnostics';
	private const NONCE_FIELD  = 'bdc_kb_review_diagnostics_nonce';
	private const SCHEMA       = '1.0.0';

	public static function register(): void {
		add_action( 'admin_notices', array( self::class, 'render_panel' ) );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
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
		echo '<p><strong>Homologação temporária SPEC-003 — Review &amp; Governança</strong></p>';
		echo '<p>Cria somente fixtures temporárias para validar a WordPress Comments API, máquina de estados, preservação de Summary/Classificação e cleanup. Não usa posts reais.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( 'Executar diagnóstico Review/Governança e gerar JSON', 'secondary', 'submit', false );
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
		$stamp  = gmdate( 'Ymd-His' );
		$name   = 'bdc-kb-review-diagnostics-' . $stamp . '.json';

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $name . '"' );
		echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$token   = strtolower( wp_generate_password( 10, false, false ) );
		$prefix  = '__bdc_spec003_' . $token;
		$tests   = array();
		$post_id = 0;
		$page_id = 0;
		$term_ids = array();
		$malformed_event_id = 0;
		$setup_ok = false;

		$report = array(
			'schema_version' => self::SCHEMA,
			'mode'           => 'temporary_review_governance_integration_fixture_only',
			'generated_at'   => gmdate( 'c' ),
			'environment'    => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php'       => PHP_VERSION,
				'plugin'    => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'multisite' => is_multisite(),
			),
			'safety' => array(
				'fixture_only'          => true,
				'reads_real_editorial'  => false,
				'modifies_real_content' => false,
				'creates_temp_post'     => true,
				'creates_temp_page'     => true,
				'creates_temp_terms'    => true,
				'creates_review_events' => true,
				'cleanup_required'      => true,
			),
		);

		try {
			$post_id = wp_insert_post(
				array(
					'post_type'    => Review_Contract::POST_TYPE,
					'post_status'  => 'draft',
					'post_title'   => $prefix . '_post',
					'post_content' => 'fixture-only-content',
				),
				true
			);
			if ( is_wp_error( $post_id ) || (int) $post_id <= 0 ) {
				throw new \RuntimeException( 'Falha ao criar post fixture.' );
			}
			$post_id = (int) $post_id;

			$page_id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'draft',
					'post_title'   => $prefix . '_page',
					'post_content' => 'fixture-only-page',
				),
				true
			);
			if ( is_wp_error( $page_id ) || (int) $page_id <= 0 ) {
				throw new \RuntimeException( 'Falha ao criar page fixture.' );
			}
			$page_id = (int) $page_id;

			update_post_meta( $post_id, '_elementor_data', '[{"fixture":true}]' );
			foreach ( Meta_Contract::fields() as $field => $definition ) {
				update_post_meta( $post_id, $definition['key'], 'fixture-summary-' . $field );
			}
			update_post_meta( $post_id, '_kb2ops_review_state', 'legacy-marker' );
			update_post_meta( $post_id, '_kb2ops_include_ai', 'legacy-marker' );

			foreach ( Classification_Contract::fields() as $field => $definition ) {
				$created = wp_insert_term( $prefix . '_' . $field, $definition['taxonomy'] );
				if ( is_wp_error( $created ) || empty( $created['term_id'] ) ) {
					throw new \RuntimeException( 'Falha ao criar termo fixture: ' . $field );
				}
				$term_id = (int) $created['term_id'];
				$term_ids[ $definition['taxonomy'] ] = $term_id;
				$assigned = wp_set_object_terms( $post_id, array( $term_id ), $definition['taxonomy'], false );
				if ( is_wp_error( $assigned ) ) {
					throw new \RuntimeException( 'Falha ao atribuir termo fixture: ' . $field );
				}
			}

			$baseline = self::snapshot_non_review_state( $post_id );
			$setup_ok = true;
			self::case_result( $tests, 'R030-I01', true, 'Fixtures temporárias criadas sem usar artigos reais.' );

			$initial = Review_Store::read( $post_id );
			self::case_result( $tests, 'R030-I02', ! is_wp_error( $initial ) && Review_Contract::STATE_UNREVIEWED === (string) ( $initial['state'] ?? '' ) && 0 === (int) ( $initial['last_event_id'] ?? -1 ) && 0 === self::event_count( $post_id ), 'Leitura inicial deriva unreviewed sem criar evento.' );

			$r1 = Review_Store::transition( $post_id, Review_Contract::STATE_IN_REVIEW );
			self::case_result( $tests, 'R030-I03', self::is_success( $r1 ) && 1 === self::event_count( $post_id ), 'unreviewed -> in_review persiste um único evento canônico.' );

			$before_noop = self::event_count( $post_id );
			$noop = Review_Store::transition( $post_id, Review_Contract::STATE_IN_REVIEW );
			self::case_result( $tests, 'R030-I04', is_array( $noop ) && Review_Store::STATUS_NO_CHANGE === (string) ( $noop['status'] ?? '' ) && $before_noop === self::event_count( $post_id ), 'Mesmo estado retorna NO_CHANGE e zero write.' );

			$before_required = self::event_count( $post_id );
			$required = Review_Store::transition( $post_id, Review_Contract::STATE_NEEDS_CHANGES, '' );
			self::case_result( $tests, 'R030-I05', is_wp_error( $required ) && 'bdc_review_note_required' === $required->get_error_code() && $before_required === self::event_count( $post_id ), 'needs_changes exige justificativa antes do write.' );

			$r2 = Review_Store::transition( $post_id, Review_Contract::STATE_NEEDS_CHANGES, 'Ajustar instruções da fixture.' );
			self::case_result( $tests, 'R030-I06', self::is_success( $r2 ), 'in_review -> needs_changes confirmado por releitura.' );

			$r3 = Review_Store::transition( $post_id, Review_Contract::STATE_APPROVED, 'Aprovação da fixture.' );
			self::case_result( $tests, 'R030-I07', self::is_success( $r3 ), 'needs_changes -> approved confirmado por releitura.' );

			$r4 = Review_Store::transition( $post_id, Review_Contract::STATE_IN_REVIEW );
			self::case_result( $tests, 'R030-I08', self::is_success( $r4 ), 'approved -> in_review reabre a revisão.' );

			$r5 = Review_Store::transition( $post_id, Review_Contract::STATE_EXCLUDED, 'Fixture excluída para validar contrato.' );
			self::case_result( $tests, 'R030-I09', self::is_success( $r5 ), 'in_review -> excluded exige reviewer e nota.' );

			$r6 = Review_Store::transition( $post_id, Review_Contract::STATE_IN_REVIEW );
			self::case_result( $tests, 'R030-I10', self::is_success( $r6 ), 'excluded -> in_review reabre a revisão.' );

			$history = Review_Store::history( $post_id, 20, 0 );
			$history_ok = is_array( $history ) && 6 === count( $history );
			if ( $history_ok ) {
				$expected = array(
					array( Review_Contract::STATE_EXCLUDED, Review_Contract::STATE_IN_REVIEW ),
					array( Review_Contract::STATE_IN_REVIEW, Review_Contract::STATE_EXCLUDED ),
					array( Review_Contract::STATE_APPROVED, Review_Contract::STATE_IN_REVIEW ),
					array( Review_Contract::STATE_NEEDS_CHANGES, Review_Contract::STATE_APPROVED ),
					array( Review_Contract::STATE_IN_REVIEW, Review_Contract::STATE_NEEDS_CHANGES ),
					array( Review_Contract::STATE_UNREVIEWED, Review_Contract::STATE_IN_REVIEW ),
				);
				foreach ( $expected as $index => $pair ) {
					$row = $history[ $index ] ?? array();
					if ( $pair[0] !== (string) ( $row['from'] ?? '' ) || $pair[1] !== (string) ( $row['to'] ?? '' ) || (int) ( $row['actor_id'] ?? 0 ) <= 0 || '' === (string) ( $row['decision_at'] ?? '' ) ) {
						$history_ok = false;
						break;
					}
				}
			}
			self::case_result( $tests, 'R030-I11', $history_ok, 'Histórico real retorna seis eventos em ordem reversa, com ator e timestamp.' );

			$before_invalid = self::event_count( $post_id );
			$invalid_target = Review_Store::transition( $post_id, Review_Contract::STATE_UNREVIEWED );
			self::case_result( $tests, 'R030-I12', is_wp_error( $invalid_target ) && 'bdc_review_invalid_target' === $invalid_target->get_error_code() && $before_invalid === self::event_count( $post_id ), 'unreviewed não pode ser destino explícito.' );

			$too_large = str_repeat( 'x', Review_Contract::MAX_NOTE_BYTES + 1 );
			$before_large = self::event_count( $post_id );
			$large_result = Review_Store::transition( $post_id, Review_Contract::STATE_NEEDS_CHANGES, $too_large );
			self::case_result( $tests, 'R030-I13', is_wp_error( $large_result ) && 'bdc_review_note_too_large' === $large_result->get_error_code() && $before_large === self::event_count( $post_id ), 'Nota acima do limite falha antes da mutação.' );

			$invalid_page_read = Review_Store::read( $page_id );
			$invalid_page_write = Review_Store::transition( $page_id, Review_Contract::STATE_IN_REVIEW );
			self::case_result( $tests, 'R030-I14', is_wp_error( $invalid_page_read ) && 'bdc_review_invalid_post' === $invalid_page_read->get_error_code() && is_wp_error( $invalid_page_write ) && 'bdc_review_invalid_post' === $invalid_page_write->get_error_code(), 'Post type fora do escopo é rejeitado na leitura e na transição.' );

			self::case_result( $tests, 'R030-I15', self::snapshots_equal( $baseline, self::snapshot_non_review_state( $post_id ) ), 'Review não altera editorial, Summary, Classificação ou stores legados da fixture.' );

			$malformed_event_id = (int) wp_insert_comment( array( 'comment_post_ID' => $post_id, 'comment_content' => '{invalid-json', 'comment_type' => Review_Contract::COMMENT_TYPE, 'comment_approved' => 1, 'user_id' => get_current_user_id() ) );
			$integrity = $malformed_event_id > 0 ? Review_Store::read( $post_id ) : null;
			self::case_result( $tests, 'R030-I16', $malformed_event_id > 0 && is_wp_error( $integrity ) && 'bdc_review_integrity_error' === $integrity->get_error_code(), 'Evento mais recente malformado gera erro explícito de integridade; não recua silenciosamente.' );

			if ( $malformed_event_id > 0 ) {
				wp_delete_comment( $malformed_event_id, true );
				$malformed_event_id = 0;
			}
			$recovered = Review_Store::read( $post_id );
			self::case_result( $tests, 'R030-I17', ! is_wp_error( $recovered ) && Review_Contract::STATE_IN_REVIEW === (string) ( $recovered['state'] ?? '' ) && 6 === self::event_count( $post_id ), 'Remoção do evento inválido de teste restaura a leitura do histórico anterior.' );
		} catch ( \Throwable $throwable ) {
			self::case_result( $tests, 'R030-I00', false, 'Falha de setup/execução do runner.', array( 'exception' => get_class( $throwable ) ) );
		} finally {
			if ( $malformed_event_id > 0 ) {
				wp_delete_comment( $malformed_event_id, true );
			}
			if ( $page_id > 0 ) {
				wp_delete_post( $page_id, true );
			}
			if ( $post_id > 0 ) {
				wp_delete_post( $post_id, true );
			}
			foreach ( $term_ids as $taxonomy => $term_id ) {
				wp_delete_term( (int) $term_id, (string) $taxonomy );
			}
		}

		$residual_posts = 0;
		if ( $post_id > 0 && get_post( $post_id ) ) {
			++$residual_posts;
		}
		if ( $page_id > 0 && get_post( $page_id ) ) {
			++$residual_posts;
		}

		$residual_terms = 0;
		foreach ( $term_ids as $taxonomy => $term_id ) {
			if ( term_exists( (int) $term_id, (string) $taxonomy ) ) {
				++$residual_terms;
			}
		}

		$residual_events = $post_id > 0 ? self::event_count( $post_id ) : 0;
		$pass = 0;
		$fail = 0;
		foreach ( $tests as $test ) {
			if ( 'PASS' === ( $test['status'] ?? '' ) ) {
				++$pass;
			} else {
				++$fail;
			}
		}

		$cleanup_ok = 0 === $residual_posts && 0 === $residual_terms && 0 === $residual_events;
		$report['tests'] = $tests;
		$report['cleanup'] = array(
			'setup_completed' => $setup_ok,
			'post_fixture_deleted' => $post_id > 0 && ! get_post( $post_id ),
			'page_fixture_deleted' => $page_id > 0 && ! get_post( $page_id ),
			'terms_created_count' => count( $term_ids ),
			'residual_posts' => $residual_posts,
			'residual_terms' => $residual_terms,
			'residual_review_events' => $residual_events,
		);
		$report['summary'] = array(
			'pass' => $pass,
			'fail' => $fail,
			'overall' => 0 === $fail && $cleanup_ok ? 'PASS' : 'FAIL',
			'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
		);
		$report['limitations'] = array(
			'Este runner valida integração real da Comments API e do Review_Store com fixtures temporárias.',
			'FAIL_SAFE/PARTIAL_FAILURE_CRITICAL continuam cobertos pelos unitários determinísticos; não há fault injection no banco real nesta execução.',
			'Segurança HTTP do writer final ainda pertence ao Gate G-070 e não é validada por este runner.',
		);

		return $report;
	}

	private static function event_count( int $post_id ): int {
		if ( $post_id <= 0 ) {
			return 0;
		}
		return (int) get_comments( array( 'post_id' => $post_id, 'type' => Review_Contract::COMMENT_TYPE, 'status' => 'approve', 'count' => true ) );
	}

	/** @param mixed $result */
	private static function is_success( $result ): bool {
		return is_array( $result ) && Review_Store::STATUS_SUCCESS === (string) ( $result['status'] ?? '' ) && (int) ( $result['event_id'] ?? 0 ) > 0;
	}

	/** @return array<string,mixed> */
	private static function snapshot_non_review_state( int $post_id ): array {
		$post = get_post( $post_id );
		$summary = array();
		foreach ( Meta_Contract::fields() as $field => $definition ) {
			$summary[ $field ] = (string) get_post_meta( $post_id, $definition['key'], true );
		}
		$classification = array();
		foreach ( Classification_Contract::fields() as $field => $definition ) {
			$ids = wp_get_object_terms( $post_id, $definition['taxonomy'], array( 'fields' => 'ids' ) );
			$ids = is_wp_error( $ids ) ? array() : array_map( 'intval', $ids );
			sort( $ids, SORT_NUMERIC );
			$classification[ $field ] = $ids;
		}
		return array(
			'post_title' => is_object( $post ) ? (string) $post->post_title : '',
			'post_content' => is_object( $post ) ? (string) $post->post_content : '',
			'post_status' => is_object( $post ) ? (string) $post->post_status : '',
			'elementor_data' => (string) get_post_meta( $post_id, '_elementor_data', true ),
			'summary' => $summary,
			'classification' => $classification,
			'legacy_review_state' => (string) get_post_meta( $post_id, '_kb2ops_review_state', true ),
			'legacy_include_ai' => (string) get_post_meta( $post_id, '_kb2ops_include_ai', true ),
		);
	}

	/** @param array<string,mixed> $a @param array<string,mixed> $b */
	private static function snapshots_equal( array $a, array $b ): bool {
		return wp_json_encode( $a ) === wp_json_encode( $b );
	}

	/** @param array<int,array<string,mixed>> $tests @param array<string,mixed> $details */
	private static function case_result( array &$tests, string $id, bool $pass, string $description, array $details = array() ): void {
		$row = array( 'id' => $id, 'status' => $pass ? 'PASS' : 'FAIL', 'description' => $description );
		if ( ! empty( $details ) ) {
			$row['details'] = $details;
		}
		$tests[] = $row;
	}
}
