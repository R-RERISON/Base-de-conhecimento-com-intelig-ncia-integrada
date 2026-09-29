<?php
/**
 * Environmental evidence runner for SPEC-006 / P-640.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Verifica equivalência estrutural do runtime modular no ambiente WordPress.
 */
final class Modular_Runtime_Runner_P640 {

	public const PAGE_SLUG = 'bdc-kb-p640-modular-runtime';
	public const ACTION    = 'bdc_kb_p640_export';

	private const NONCE_ACTION = 'bdc_kb_p640_export';
	private const NONCE_FIELD  = 'bdc_kb_p640_nonce';

	/**
	 * Registra a superfície temporária de homologação.
	 */
	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_menu' ) );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_export' ) );
	}

	/**
	 * Registra o submenu temporário.
	 */
	public static function register_menu(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'P-640 Modular Runtime',
			'P-640 Modular Runtime',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render' )
		);
	}

	/**
	 * Renderiza o gate P-640.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Você não tem permissão para executar este gate.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'P-640 — Modular Runtime', 'bdc-knowledge-base' ) . '</h1>';
		echo '<p>' . esc_html__( 'Valida carregamento modular, entrypoints e invariantes sem alterar conteúdo, dados, ranking ou configuração.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar P-640 e baixar JSON', 'bdc-knowledge-base' ) );
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Executa o gate e baixa o JSON.
	 */
	public static function handle_export(): never {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Você não tem permissão para executar este gate.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( self::NONCE_ACTION, self::NONCE_FIELD );
		$report = self::run();

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec006-p640-modular-runtime-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Download JSON.
		exit;
	}

	/**
	 * Executa as verificações ambientais.
	 *
	 * @return array<string,mixed>
	 */
	public static function run(): array {
		$definitions = Runtime_Module_Registry::definitions();

		$modules = array(
			'search'                    => self::module_state(
				'search',
				array(
					Search_Query_Normalizer::class,
					Search_Projection_Repository::class,
					Search_Lifecycle::class,
					Search_Anchor_Manager::class,
					Search_Service::class,
				)
			),
			'public_experience_preview' => self::module_state(
				'public_experience_preview',
				array(
					Public_Search_Facade::class,
					Public_Home_Read_Model::class,
					Public_Article_Read_Model::class,
					Public_Experience::class,
				)
			),
			'word_cloud'                => self::module_state(
				'word_cloud',
				array(
					Word_Cloud_Contract::class,
					Word_Cloud_Service::class,
					Word_Cloud_Consultations::class,
					Word_Cloud_Admin::class,
				)
			),
		);

		$core_classes = array(
			Meta_Contract::class,
			Summary_Store::class,
			Knowledge_Facts_Store::class,
			Helpful_Tips_Store::class,
			Coverage_Read_Model::class,
			Classification_Store::class,
			Review_Store::class,
			Content_Extractor::class,
			Knowledge_Document::class,
			Elementor_Adapter::class,
			Admin_Page::class,
			Visual_Foundation::class,
			Plugin::class,
		);

		$core = array();
		foreach ( $core_classes as $class_name ) {
			$core[ $class_name ] = class_exists( $class_name );
		}

		$module_pass = true;
		foreach ( $modules as $state ) {
			if ( empty( $state['pass'] ) ) {
				$module_pass = false;
				break;
			}
		}

		$core_pass           = ! in_array( false, $core, true );
		$registry_shape_pass = array_keys( $definitions ) === array( 'search', 'public_experience_preview', 'word_cloud' );

		$assertions = array(
			'core_classes_loaded'                  => $core_pass,
			'known_product_modules_exact'          => $registry_shape_pass,
			'enabled_product_modules_loaded'       => $module_pass,
			'search_enabled'                       => Runtime_Module_Registry::is_enabled( 'search' ),
			'public_preview_enabled'               => Runtime_Module_Registry::is_enabled( 'public_experience_preview' ),
			'word_cloud_enabled'                   => Runtime_Module_Registry::is_enabled( 'word_cloud' ),
			'engineering_gate_enabled'             => defined( 'BDC_KB_SPEC006_P640_ENVIRONMENTAL_BUILD' ) && BDC_KB_SPEC006_P640_ENVIRONMENTAL_BUILD,
			'legacy_elementor_reader_available'    => class_exists( Elementor_Adapter::class ),
			'editorial_writer_unchanged'           => ! defined( 'BDC_KB_ELEMENTOR_WRITER_ENABLED' ) || false === BDC_KB_ELEMENTOR_WRITER_ENABLED,
		);

		$status = ! in_array( false, $assertions, true ) ? 'PASS' : 'FAIL';

		return array(
			'schema_version'       => '1.0.0',
			'gate'                 => 'P-640',
			'status'               => $status,
			'generated_at'         => gmdate( 'c' ),
			'environment'          => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php'       => PHP_VERSION,
				'plugin'    => BDC_KB_VERSION,
			),
			'bootstrap'            => array(
				'composition_root'     => 'Core_Runtime_Loader + Runtime_Module_Registry + Engineering_Module_Loader + Plugin',
				'product_module_count' => count( $definitions ),
			),
			'core'                 => $core,
			'modules'              => $modules,
			'assertions'           => $assertions,
			'content_mutation'     => false,
			'data_migration'       => false,
			'cutover_authorized'   => false,
			'retirement_authorized' => false,
			'next_gate_on_pass'    => 'P640_PACKAGE_RUNTIME_INVENTORY',
		);
	}

	/**
	 * Avalia o estado de um módulo obrigatório neste gate.
	 *
	 * @param string            $module  Nome do módulo.
	 * @param array<int,string> $classes Classes esperadas.
	 * @return array<string,mixed>
	 */
	private static function module_state( string $module, array $classes ): array {
		$enabled = Runtime_Module_Registry::is_enabled( $module );
		$loaded  = array();

		foreach ( $classes as $class_name ) {
			$loaded[ $class_name ] = class_exists( $class_name );
		}

		$classes_pass = ! in_array( false, $loaded, true );

		return array(
			'enabled' => $enabled,
			'classes' => $loaded,
			'pass'    => $enabled && $classes_pass,
		);
	}
}
