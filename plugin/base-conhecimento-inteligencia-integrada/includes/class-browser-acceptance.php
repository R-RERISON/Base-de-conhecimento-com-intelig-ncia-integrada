<?php
/**
 * Coleta manual e temporária da evidência de browser acceptance G-110.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gera JSON de aceite manual sem persistir estado no WordPress.
 *
 * IMPORTANTE: classe temporária. Deve ser removida antes do package/release da SPEC-001.
 */
final class Browser_Acceptance {

	public const ACTION = 'bdc_kb_browser_acceptance';

	private const NONCE_ACTION   = 'bdc_kb_browser_acceptance_v1';
	private const NONCE_FIELD    = 'bdc_kb_browser_nonce';
	private const SCHEMA_VERSION = '1.0.0';

	public static function register(): void {
		if ( ! Diagnostics_Runner::is_enabled() ) {
			return;
		}

		add_action( 'admin_notices', array( self::class, 'render_panel' ) );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle' ) );
	}

	public static function render_panel(): void {
		if ( ! Diagnostics_Runner::is_enabled() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page = isset( $_GET['page'] ) && is_scalar( $_GET['page'] )
			? sanitize_key( wp_unslash( (string) $_GET['page'] ) )
			: '';

		if ( Admin_Page::PAGE_SLUG !== $page ) {
			return;
		}

		echo '<div class="notice notice-info">';
		echo '<p><strong>' . esc_html__( 'Browser acceptance temporário — G-110', 'bdc-knowledge-base' ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Após executar manualmente o fluxo no navegador, marque somente os itens realmente observados. O formulário gera JSON e não grava as respostas no WordPress.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		echo '<p><label for="bdc-kb-browser-label"><strong>' . esc_html__( 'Navegador/versão', 'bdc-knowledge-base' ) . '</strong></label><br>';
		echo '<input id="bdc-kb-browser-label" name="browser_label" type="text" class="regular-text" required maxlength="120" placeholder="Ex.: Chrome 152 / Windows 11"></p>';

		echo '<p><label for="bdc-kb-viewport"><strong>' . esc_html__( 'Viewport estreito validado', 'bdc-knowledge-base' ) . '</strong></label><br>';
		echo '<input id="bdc-kb-viewport" name="viewport" type="text" class="regular-text" required maxlength="80" placeholder="Ex.: 1024x768 e 768x1024"></p>';

		echo '<fieldset><legend><strong>' . esc_html__( 'Checklist observado', 'bdc-knowledge-base' ) . '</strong></legend>';
		foreach ( self::checks() as $id => $label ) {
			echo '<p><label><input type="checkbox" name="checks[]" value="' . esc_attr( $id ) . '"> ' . esc_html( $label ) . '</label></p>';
		}
		echo '</fieldset>';

		echo '<p><label for="bdc-kb-browser-notes"><strong>' . esc_html__( 'Observações', 'bdc-knowledge-base' ) . '</strong></label><br>';
		echo '<textarea id="bdc-kb-browser-notes" name="notes" class="large-text" rows="4" maxlength="2000"></textarea></p>';
		echo '<p><button type="submit" class="button button-secondary">' . esc_html__( 'Gerar JSON do browser acceptance', 'bdc-knowledge-base' ) . '</button></p>';
		echo '</form>';
		echo '</div>';
	}

	public static function handle(): never {
		if ( ! Diagnostics_Runner::is_enabled() ) {
			wp_die( esc_html__( 'Diagnóstico desabilitado.', 'bdc-knowledge-base' ), '', array( 'response' => 404 ) );
		}

		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( esc_html__( 'Método HTTP não permitido.', 'bdc-knowledge-base' ), '', array( 'response' => 405 ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Você não tem permissão para registrar esta evidência.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$browser_label = isset( $_POST['browser_label'] ) && is_scalar( $_POST['browser_label'] )
			? sanitize_text_field( wp_unslash( (string) $_POST['browser_label'] ) )
			: '';
		$viewport = isset( $_POST['viewport'] ) && is_scalar( $_POST['viewport'] )
			? sanitize_text_field( wp_unslash( (string) $_POST['viewport'] ) )
			: '';
		$notes = isset( $_POST['notes'] ) && is_scalar( $_POST['notes'] )
			? sanitize_textarea_field( wp_unslash( (string) $_POST['notes'] ) )
			: '';
		$selected = isset( $_POST['checks'] ) && is_array( $_POST['checks'] )
			? array_map( 'sanitize_key', wp_unslash( $_POST['checks'] ) )
			: array();

		$checks = array();
		$pass   = 0;
		$fail   = 0;
		foreach ( self::checks() as $id => $label ) {
			$passed = in_array( $id, $selected, true );
			$checks[] = array(
				'id'      => $id,
				'gate'    => 'G-110',
				'status'  => $passed ? 'PASS' : 'FAIL',
				'message' => $label,
				'source'  => 'operator_assertion',
			);
			$passed ? ++$pass : ++$fail;
		}

		$report = array(
			'schema_version' => self::SCHEMA_VERSION,
			'mode'           => 'temporary_browser_acceptance',
			'generated_at'   => gmdate( 'c' ),
			'environment'    => array(
				'wordpress'     => (string) get_bloginfo( 'version' ),
				'php'           => PHP_VERSION,
				'plugin'        => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : 'unknown',
				'browser_label' => $browser_label,
				'viewport'      => $viewport,
				'user_agent'    => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( (string) $_SERVER['HTTP_USER_AGENT'] ) : '',
			),
			'checks' => $checks,
			'summary' => array(
				'pass'    => $pass,
				'fail'    => $fail,
				'overall' => 0 === $fail && '' !== $browser_label && '' !== $viewport ? 'PASS' : 'FAIL',
			),
			'notes' => $notes,
			'safety' => array(
				'persistent_report'   => false,
				'database_writes'     => false,
				'requires_capability' => 'manage_options',
				'source'              => 'manual_browser_observation',
			),
			'limitations' => array(
				'Este JSON registra observação humana guiada; não é automação E2E.',
				'G-110 só pode ser promovido após revisão do JSON e do contexto de execução.',
				'Antes de package/release, esta classe temporária deve ser removida.',
			),
		);

		$filename = 'bdc-kb-browser-acceptance-' . gmdate( 'Ymd-His' ) . '.json';
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/** @return array<string,string> */
	private static function checks(): array {
		return array(
			'G110-01' => 'A tela usa o shell do wp-admin e não cria segunda sidebar.',
			'G110-02' => 'A listagem está legível, paginada e permite selecionar um artigo.',
			'G110-03' => 'Título/contexto são read-only e os três textareas possuem labels associados.',
			'G110-04' => 'Salvar executa POST-Redirect-GET sem alerta de reenvio ao atualizar a página.',
			'G110-05' => 'Sucesso e erro são comunicados por texto; cor não é o único indicador.',
			'G110-06' => 'Navegação por teclado e ordem de foco são utilizáveis.',
			'G110-07' => 'Viewport administrativo estreito permanece utilizável sem perda funcional.',
			'G110-08' => 'Valores salvos reaparecem após redirect/reload e o estado visual corresponde ao persistido.',
		);
	}
}
