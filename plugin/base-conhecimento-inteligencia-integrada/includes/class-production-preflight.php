<?php
/**
 * Production Preflight read-only da SPEC-004.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compara o runtime atual com um perfil alvo fornecido pelo ambiente.
 */
final class Production_Preflight {

	public const PAGE_SLUG = 'bdc-kb-spec004-preflight';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 34 );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'G-245 Production Preflight',
			'G-245 Preflight',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ) );
		}

		$report = self::run();
		$json   = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( esc_html__( 'Falha ao serializar o preflight.', 'bdc-knowledge-base' ), '', array( 'response' => 500 ) );
		}

		$status = (string) ( $report['decision']['status'] ?? 'NOT_CONFIGURED' );
		$notice = 'compatible' === $status ? 'notice-success' : ( 'blocking' === $status ? 'notice-error' : 'notice-warning' );
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'SPEC-004 — G-245 Production Preflight', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice ' . esc_attr( $notice ) . ' inline"><p><strong>' . esc_html( strtoupper( $status ) ) . '</strong> — ' . esc_html__( 'diagnóstico read-only; não autoriza migration editorial.', 'bdc-knowledge-base' ) . '</p></div>';
		echo '<p>' . esc_html__( 'O alvo só é comparado quando as variáveis BDC_KB_PREFLIGHT_TARGET_* estão configuradas. Ausência de alvo não é PASS.', 'bdc-knowledge-base' ) . '</p>';
		echo '<pre style="max-height:70vh;overflow:auto;background:#fff;border:1px solid #c3c4c7;padding:16px;white-space:pre-wrap;">' . esc_html( $json ) . '</pre>';
		echo '</div>';
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		global $wpdb;
		$current = array(
			'wordpress' => (string) get_bloginfo( 'version' ),
			'php' => PHP_VERSION,
			'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
			'elementor' => defined( 'ELEMENTOR_VERSION' ) ? (string) ELEMENTOR_VERSION : null,
			'multisite' => is_multisite(),
			'memory_limit' => (string) ini_get( 'memory_limit' ),
			'max_execution_time' => (int) ini_get( 'max_execution_time' ),
			'db_engine' => self::database_engine( $wpdb ),
			'cron_disabled' => defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON,
			'loopback' => self::loopback_status(),
			'active_plugins' => self::active_plugins(),
		);
		$target = array(
			'wordpress' => self::env_value( 'BDC_KB_PREFLIGHT_TARGET_WORDPRESS' ),
			'php' => self::env_value( 'BDC_KB_PREFLIGHT_TARGET_PHP' ),
			'elementor' => self::env_value( 'BDC_KB_PREFLIGHT_TARGET_ELEMENTOR' ),
			'backup' => self::env_value( 'BDC_KB_PREFLIGHT_TARGET_BACKUP' ),
		);
		$checks = self::checks( $current, $target );
		return array(
			'schema_version' => '1.0.0',
			'mode' => 'temporary_spec004_g245_production_preflight_read_only',
			'generated_at' => gmdate( 'c' ),
			'current' => $current,
			'target' => $target,
			'checks' => $checks,
			'decision' => self::decision( $checks, $target ),
			'safety' => array(
				'read_only_design' => true,
				'editorial_writes' => false,
				'elementor_writes' => false,
				'network_calls' => false,
				'persistent_storage' => false,
			),
		);
	}

	/** @param array<string,mixed> $current @param array<string,mixed> $target @return array<string,array<string,string>> */
	private static function checks( array $current, array $target ): array {
		$checks = array();
		foreach ( array( 'wordpress', 'php', 'elementor' ) as $key ) {
			$expected = (string) ( $target[ $key ] ?? '' );
			$actual   = (string) ( $current[ $key ] ?? '' );
			$checks[ $key ] = array(
				'actual' => '' === $actual ? 'unknown' : $actual,
				'expected' => '' === $expected ? 'not_configured' : $expected,
				'status' => '' === $expected ? 'review_required' : ( $actual === $expected ? 'compatible' : 'blocking' ),
			);
		}
		$checks['backup'] = array(
			'actual' => 'not_verified',
			'expected' => '' === (string) $target['backup'] ? 'not_configured' : (string) $target['backup'],
			'status' => '' === (string) $target['backup'] ? 'review_required' : ( 'confirmed' === strtolower( (string) $target['backup'] ) ? 'compatible' : 'blocking' ),
		);
		return $checks;
	}

	/** @param array<string,array<string,string>> $checks @param array<string,mixed> $target @return array<string,mixed> */
	private static function decision( array $checks, array $target ): array {
		$statuses = array_column( $checks, 'status' );
		if ( in_array( 'blocking', $statuses, true ) ) {
			return array( 'status' => 'blocking', 'reason' => 'Uma ou mais verificações obrigatórias divergem.' );
		}
		if ( '' === (string) $target['wordpress'] || '' === (string) $target['php'] || '' === (string) $target['elementor'] || '' === (string) $target['backup'] ) {
			return array( 'status' => 'NOT_CONFIGURED', 'reason' => 'Perfil alvo incompleto; nenhuma promoção pode ser autorizada.' );
		}
		return array( 'status' => 'compatible', 'reason' => 'Perfil alvo completo e verificações compatíveis; migration continua separada.' );
	}

	private static function env_value( string $name ): string {
		$value = getenv( $name );
		return false === $value ? '' : trim( (string) $value );
	}

	private static function database_engine( object $wpdb ): string {
		$version = method_exists( $wpdb, 'db_version' ) ? (string) $wpdb->db_version() : '';
		$server_info = isset( $wpdb->dbh ) && function_exists( 'mysqli_get_server_info' ) ? (string) mysqli_get_server_info( $wpdb->dbh ) : '';
		$reported    = '' !== $server_info ? $server_info : $version;
		if ( '' === $reported ) {
			return 'unknown';
		}
		return str_contains( strtolower( $reported ), 'mariadb' ) ? 'MariaDB ' . $reported : 'MySQL ' . $reported;
	}

	private static function loopback_status(): string {
		return function_exists( 'wp_remote_get' ) ? 'available_via_wp_http_api' : 'not_available';
	}

	/** @return array<int,string> */
	private static function active_plugins(): array {
		$plugins = get_option( 'active_plugins', array() );
		return is_array( $plugins ) ? array_values( array_map( 'strval', $plugins ) ) : array();
	}
}