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

		$assertions = array();
		self::put( $assertions, 'core_classes_loaded', $core_pass );
		self::put( $assertions, 'known_product_modules_exact', $registry_shape_pass );
		self::put( $assertions, 'enabled_product_modules_loaded', $module_pass );
		self::put( $assertions, 'search_enabled', Runtime_Module_Registry::is_enabled( 'search' ) );
		self::put( $assertions, 'public_preview_enabled', Runtime_Module_Registry::is_enabled( 'public_experience_preview' ) );
		self::put( $assertions, 'word_cloud_enabled', Runtime_Module_Registry::is_enabled( 'word_cloud' ) );
		self::put( $assertions, 'engineering_gate_enabled', defined( 'BDC_KB_SPEC006_P640_ENVIRONMENTAL_BUILD' ) && BDC_KB_SPEC006_P640_ENVIRONMENTAL_BUILD );
		self::put( $assertions, 'legacy_elementor_reader_available', class_exists( Elementor_Adapter::class ) );
		self::put( $assertions, 'editorial_writer_unchanged', ! defined( 'BDC_KB_ELEMENTOR_WRITER_ENABLED' ) || false === BDC_KB_ELEMENTOR_WRITER_ENABLED );

		$status = ! in_array( false, $assertions, true ) ? 'PASS' : 'FAIL';

		$environment = array(
			'wordpress' => get_bloginfo( 'version' ),
			'php'       => PHP_VERSION,
			'plugin'    => BDC_KB_VERSION,
		);

		$bootstrap = array(
			'composition_root'     => 'Core_Runtime_Loader + Runtime_Module_Registry + Engineering_Module_Loader + Plugin',
			'product_module_count' => count( $definitions ),
		);

		$report = array();
		self::put( $report, 'schema_version', '1.0.0' );
		self::put( $report, 'gate', 'P-640' );
		self::put( $report, 'status', $status );
		self::put( $report, 'generated_at', gmdate( 'c' ) );
		self::put( $report, 'environment', $environment );
		self::put( $report, 'bootstrap', $bootstrap );
		self::put( $report, 'core', $core );
		self::put( $report, 'modules', $modules );
		self::put( $report, 'assertions', $assertions );
		self::put( $report, 'content_mutation', false );
		self::put( $report, 'data_migration', false );
		self::put( $report, 'cutover_authorized', false );
		self::put( $report, 'retirement_authorized', false );
		self::put( $report, 'next_gate_on_pass', 'P640_PACKAGE_RUNTIME_INVENTORY' );

		return $report;
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

	/**
	 * Adiciona um valor a um payload sem exigir alinhamento artificial.
	 *
	 * @param array<string,mixed> $target Payload em construção.
	 * @param string              $key    Chave.
	 * @param mixed               $value  Valor.
	 */
	private static function put( array &$target, string $key, mixed $value ): void {
		$target[ $key ] = $value;
	}
}
