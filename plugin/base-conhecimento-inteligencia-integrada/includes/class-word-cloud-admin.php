<?php
/**
 * BDC Word Cloud admin operations.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Word_Cloud_Admin {

	public const PAGE_SLUG = 'bdc-kb-word-cloud';
	private const ACTION_GENERATE = 'bdc_kb_word_cloud_generate';
	private const ACTION_SAVE = 'bdc_kb_word_cloud_save';
	private const ACTION_RESET_CONSULTATIONS = 'bdc_kb_word_cloud_reset_consultations';
	private const NONCE = 'bdc_kb_word_cloud_admin';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 56 );
		add_action( 'admin_post_' . self::ACTION_GENERATE, array( self::class, 'handle_generate' ) );
		add_action( 'admin_post_' . self::ACTION_SAVE, array( self::class, 'handle_save' ) );
		add_action( 'admin_post_' . self::ACTION_RESET_CONSULTATIONS, array( self::class, 'handle_reset_consultations' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Nuvem de Conhecimento',
			'Nuvem de Conhecimento',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render' )
		);
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}
		$health = Word_Cloud_Service::health();
		$settings = Word_Cloud_Service::settings();
		$allow = Word_Cloud_Service::allowlist();
		$block = Word_Cloud_Service::blocklist();
		$snapshot = Word_Cloud_Service::snapshot();
		$terms = Word_Cloud_Service::public_terms( 20 );

		echo '<div class="wrap bdc-kb-admin">';
		echo '<h1>Nuvem de Conhecimento</h1>';
		echo '<p>Geração BDC-owned baseada prioritariamente em títulos, headings, taxonomias e listas governadas. Corpo semântico é opt-in. Consultas agregadas BDC dão peso de uso sem armazenar usuário/IP/sessão; telemetria detalhada/vocabulary permanecem pendentes.</p>';
		if ( empty( $health['snapshot_current'] ) ) {
			echo '<div class="notice notice-warning inline"><p><strong>Snapshot requer regeneração.</strong> O perfil de qualidade foi atualizado e o snapshot anterior não será publicado.</p></div>';
		}
		echo '<div class="bdc-kb-metrics">';
		self::metric( strtoupper( (string) $health['status'] ), 'estado' );
		self::metric( (string) $health['public_term_count'], 'termos públicos' );
		self::metric( ! empty( $health['fresh'] ) ? 'OK' : 'STALE', 'freshness' );
		self::metric( (string) ( $health['quality_profile'] ?? '' ), 'quality profile' );
		self::metric( class_exists( Word_Cloud_Consultations::class ) ? (string) Word_Cloud_Consultations::total_count() : '0', 'consultas agregadas' );
		echo '</div>';

		echo '<section class="bdc-kb-panel" style="padding:20px;margin-bottom:16px">';
		echo '<h2>Operação</h2>';
		echo '<p>Última geração: <strong>' . esc_html( (string) ( $health['generated_at'] ?: 'não executada' ) ) . '</strong></p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION_GENERATE ) . '">';
		wp_nonce_field( self::NONCE, 'bdc_kb_word_cloud_nonce' );
		submit_button( 'Gerar snapshot agora', 'primary', 'submit', false );
		echo '</form>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:10px">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION_RESET_CONSULTATIONS ) . '">';
		wp_nonce_field( self::NONCE, 'bdc_kb_word_cloud_nonce' );
		submit_button( 'Resetar consultas de homologação', 'secondary', 'submit', false, array( 'onclick' => "return confirm('Zerar os contadores agregados da Nuvem de Conhecimento?');" ) );
		echo '</form></section>';

		echo '<section class="bdc-kb-panel" style="padding:20px;margin-bottom:16px"><h2>Configuração</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION_SAVE ) . '">';
		wp_nonce_field( self::NONCE, 'bdc_kb_word_cloud_nonce' );
		echo '<p><label><input type="checkbox" name="enabled" value="1" ' . checked( ! empty( $settings['enabled'] ), true, false ) . '> habilitar geração/scheduling</label></p>';
		echo '<p><label><input type="checkbox" name="include_body_terms" value="1" ' . checked( ! empty( $settings['include_body_terms'] ), true, false ) . '> incluir termos do corpo semântico <em>(avançado; desligado por padrão para reduzir ruído)</em></label></p>';
		echo '<p><label>Máx. posts por geração<br><input type="number" min="25" max="1000" name="max_posts_scan" value="' . esc_attr( (string) $settings['max_posts_scan'] ) . '"></label></p>';
		echo '<p><label>Máx. termos públicos<br><input type="number" min="6" max="60" name="max_public_terms" value="' . esc_attr( (string) $settings['max_public_terms'] ) . '"></label></p>';
		echo '<div class="bdc-kb-field-grid"><div><label>Allowlist</label><textarea name="allowlist" rows="8">' . esc_textarea( implode( "\n", $allow ) ) . '</textarea></div>';
		echo '<div><label>Blocklist / stopwords</label><textarea name="blocklist" rows="8">' . esc_textarea( implode( "\n", $block ) ) . '</textarea></div></div>';
		submit_button( 'Salvar configuração' );
		echo '</form></section>';

		echo '<section class="bdc-kb-panel" style="padding:20px"><h2>Preview do snapshot</h2>';
		if ( empty( $terms ) ) {
			echo '<div class="bdc-kb-empty-state"><div><strong>Nenhum snapshot disponível</strong><p>Execute a geração manual para construir a primeira nuvem.</p></div></div>';
		} else {
			echo '<div style="display:flex;flex-wrap:wrap;gap:8px">';
			foreach ( $terms as $term ) {
				if ( ! is_array( $term ) || empty( $term['public_allowed'] ) ) {
					continue;
				}
				$sources = implode( ', ', array_map( 'sanitize_key', (array) ( $term['sources'] ?? array() ) ) );
				echo '<span class="bdc-kb-badge bdc-kb-badge--info">' . esc_html( (string) $term['term'] ) . ' · ' . esc_html( (string) absint( $term['consultation_count'] ?? 0 ) ) . ' consultas · ' . esc_html( (string) $term['status'] ) . ' · ' . esc_html( (string) ( $term['quality_reason'] ?? '' ) ) . ( '' !== $sources ? ' · ' . esc_html( $sources ) : '' ) . '</span>';
			}
			echo '</div>';
		}
		echo '<h3 style="margin-top:20px">Fontes</h3><ul>';
		foreach ( (array) $health['sources'] as $source => $status ) {
			echo '<li><code>' . esc_html( (string) $source ) . '</code>: ' . esc_html( (string) $status ) . '</li>';
		}
		echo '</ul></section></div>';
	}

	public static function handle_generate(): never {
		self::guard();
		$result = Word_Cloud_Service::generate( 'manual_admin' );
		self::redirect( 'failed' === (string) ( $result['status'] ?? '' ) ? 'failed' : 'generated' );
	}

	public static function handle_reset_consultations(): never {
		self::guard();
		if ( class_exists( Word_Cloud_Consultations::class ) ) {
			Word_Cloud_Consultations::reset();
		}
		self::redirect( 'consultations_reset' );
	}

	public static function handle_save(): never {
		self::guard();
		$defaults = Word_Cloud_Contract::default_settings();
		$settings = array(
			'enabled' => isset( $_POST['enabled'] ),
			'quality_profile' => Word_Cloud_Contract::QUALITY_PROFILE,
			'include_body_terms' => isset( $_POST['include_body_terms'] ),
			'max_posts_scan' => isset( $_POST['max_posts_scan'] ) ? max( 25, min( 1000, absint( wp_unslash( $_POST['max_posts_scan'] ) ) ) ) : $defaults['max_posts_scan'],
			'max_public_terms' => isset( $_POST['max_public_terms'] ) ? max( 6, min( 60, absint( wp_unslash( $_POST['max_public_terms'] ) ) ) ) : $defaults['max_public_terms'],
			'stale_after_seconds' => $defaults['stale_after_seconds'],
		);
		$allow = self::parse_textarea( $_POST['allowlist'] ?? '' );
		$block = self::parse_textarea( $_POST['blocklist'] ?? '' );
		update_option( Word_Cloud_Contract::SETTINGS_OPTION, $settings, false );
		update_option( Word_Cloud_Contract::ALLOWLIST_OPTION, $allow, false );
		update_option( Word_Cloud_Contract::BLOCKLIST_OPTION, $block, false );
		Word_Cloud_Service::ensure_schedule();
		self::redirect( 'saved' );
	}

	private static function guard(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::NONCE, 'bdc_kb_word_cloud_nonce' );
	}

	/** @return array<int,string> */
	private static function parse_textarea( mixed $value ): array {
		$value = is_scalar( $value ) ? wp_unslash( (string) $value ) : '';
		$rows = preg_split( '/[\r\n,]+/', $value ) ?: array();
		$out = array();
		foreach ( $rows as $row ) {
			$row = trim( sanitize_text_field( $row ) );
			if ( '' !== $row ) {
				$out[] = $row;
			}
		}
		return array_values( array_unique( $out ) );
	}

	private static function redirect( string $status ): never {
		wp_safe_redirect( add_query_arg( array( 'page' => self::PAGE_SLUG, 'bdc_wc_status' => sanitize_key( $status ) ), admin_url( 'admin.php' ) ) );
		exit;
	}

	private static function metric( string $value, string $label ): void {
		echo '<div class="bdc-kb-metric"><strong>' . esc_html( $value ) . '</strong><span>' . esc_html( $label ) . '</span></div>';
	}
}