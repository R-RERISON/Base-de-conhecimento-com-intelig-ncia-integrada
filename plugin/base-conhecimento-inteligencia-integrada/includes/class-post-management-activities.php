<?php
/**
 * Atividades contextuais da área de gerenciamento do artigo.
 *
 * @package BDC_Knowledge_Base
 */
namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Post_Management_Activities {

	/** @param array<string,mixed> $context */
	public static function render( string $activity, int $post_id, array $context ): void {
		switch ( $activity ) {
			case 'content':
				self::render_content( $post_id, $context );
				break;
			case 'intelligence':
				self::render_intelligence( $context );
				break;
			case 'core_blocks':
				self::render_core_blocks( $post_id, $context );
				break;
			default:
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Esta área não está disponível.', 'bdc-knowledge-base' ) . '</p></div>';
		}
	}

	/** @param array<string,mixed> $context */
	private static function render_content( int $post_id, array $context ): void {
		$source = is_array( $context['source'] ?? null ) ? $context['source'] : array();
		$bytes  = max( 0, (int) ( $source['bytes'] ?? 0 ) );
		$hash   = (string) ( $source['fidelity_hash'] ?? '' );

		echo '<section class="bdc-kb-domain-panel" aria-labelledby="bdc-kb-content-title">';
		echo '<div class="bdc-kb-domain-heading">';
		echo '<h3 id="bdc-kb-content-title">' . esc_html__( 'Conteúdo e estrutura', 'bdc-knowledge-base' ) . '</h3>';
		echo '<p>' . esc_html__( 'Informações sobre a origem e a preservação do conteúdo deste artigo.', 'bdc-knowledge-base' ) . '</p>';
		echo '</div>';

		self::table(
			array(
				'Artigo'                => '#' . $post_id,
				'Origem do conteúdo'    => (string) ( $source['label'] ?? 'Indisponível' ),
				'Método de preservação' => self::strategy_label( (string) ( $source['strategy'] ?? '' ) ),
				'Partes identificadas'  => (string) max( 0, (int) ( $source['unit_count'] ?? 0 ) ),
				'Tamanho aproximado'    => self::format_bytes( $bytes ),
				'Integridade da leitura'=> self::is_sha256( $hash ) ? 'Verificada' : 'Não disponível',
			)
		);

		echo '<p class="bdc-kb-context-note">' . esc_html__( 'Esta área é somente para consulta e não altera o conteúdo do artigo.', 'bdc-knowledge-base' ) . '</p>';
		echo '</section>';
	}

	/** @param array<string,mixed> $context */
	private static function render_intelligence( array $context ): void {
		$knowledge = is_array( $context['knowledge'] ?? null ) ? $context['knowledge'] : array();

		echo '<section class="bdc-kb-domain-panel" aria-labelledby="bdc-kb-intelligence-title">';
		echo '<div class="bdc-kb-domain-heading">';
		echo '<h3 id="bdc-kb-intelligence-title">' . esc_html__( 'Inteligência', 'bdc-knowledge-base' ) . '</h3>';
		echo '<p>' . esc_html__( 'Área destinada a análises e sugestões assistidas para este artigo.', 'bdc-knowledge-base' ) . '</p>';
		echo '</div>';

		self::table(
			array(
				'Sumário disponível'        => ! empty( $knowledge['summary_available'] ) ? 'Sim' : 'Não',
				'Conceitos classificados'   => (string) max( 0, (int) ( $knowledge['classification_term_count'] ?? 0 ) ),
				'Análise assistida por IA'  => ! empty( $knowledge['intelligence_execution_enabled'] ) ? 'Disponível' : 'Ainda não habilitada',
			)
		);

		if ( empty( $knowledge['intelligence_execution_enabled'] ) ) {
			echo '<div class="notice notice-info inline"><p>' . esc_html__( 'Os recursos de inteligência artificial serão incorporados nesta mesma área após a conclusão das etapas de segurança e validação.', 'bdc-knowledge-base' ) . '</p></div>';
		}

		echo '</section>';
	}

	/** @param array<string,mixed> $context */
	private static function render_core_blocks( int $post_id, array $context ): void {
		$core = is_array( $context['core_blocks'] ?? null ) ? $context['core_blocks'] : array();
		$source = is_array( $context['source'] ?? null ) ? $context['source'] : array();

		$status = (string) ( $core['operational_status'] ?? 'blocked' );
		$reasons = array_values( array_map( 'strval', (array) ( $core['operational_reasons'] ?? array() ) ) );
		$authorization_ready = true === ( $core['authorization_ready'] ?? false );
		$authorization_id = (string) ( $core['authorization_id'] ?? '' );
		$block_names = array_values( array_map( 'strval', (array) ( $core['expected_block_names'] ?? array() ) ) );
		$t100d_enabled = defined( 'BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD' )
			&& BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD
			&& class_exists( Post_Core_Blocks_Executor_T100D::class )
			&& Post_Core_Blocks_Executor_T100D::can_render( $post_id, $authorization_id );

		echo '<section class="bdc-kb-domain-panel" aria-labelledby="bdc-kb-core-blocks-title">';
		echo '<div class="bdc-kb-domain-heading">';
		echo '<h3 id="bdc-kb-core-blocks-title">' . esc_html__( 'Blocos do WordPress', 'bdc-knowledge-base' ) . '</h3>';
		echo '<p>' . esc_html__( 'Situação da estrutura editorial do artigo e disponibilidade de migração, quando necessária.', 'bdc-knowledge-base' ) . '</p>';
		echo '</div>';

		$migration_needed = 'no_action_required' === $status ? 'Não' : ( 'ready_for_authorization' === $status ? 'Sim' : 'Em avaliação' );
		$authorization_label = 'no_action_required' === $status
			? 'Não necessária'
			: ( $authorization_ready ? 'Disponível para geração' : 'Indisponível no momento' );

		self::table(
			array(
				'Artigo'                  => '#' . $post_id,
				'Origem atual'            => (string) ( $source['label'] ?? 'Indisponível' ),
				'Situação'                => self::operational_status_label( $status ),
				'Migração necessária'     => $migration_needed,
				'Formato previsto'        => self::block_names_label( $block_names ),
				'Histórico de migração'   => self::history_count_label( (int) ( $core['journal_event_count'] ?? 0 ) ),
				'Última operação'         => self::journal_state_label( (string) ( $core['latest_journal_state'] ?? '' ) ),
				'Bloqueio operacional'    => self::lock_status_label( (string) ( $core['lock_status'] ?? 'unavailable' ) ),
				'Autorização de migração' => $authorization_label,
			)
		);

		if ( 'no_action_required' === $status ) {
			echo '<div class="notice notice-success inline"><p>' . esc_html__( 'Este artigo já utiliza Blocos do WordPress. Nenhuma ação é necessária.', 'bdc-knowledge-base' ) . '</p></div>';
		} elseif ( ! empty( $reasons ) ) {
			$messages = array_values( array_filter( array_map( array( self::class, 'reason_label' ), $reasons ) ) );
			if ( ! empty( $messages ) ) {
				echo '<div class="notice notice-warning inline"><p>' . esc_html( implode( ' ', $messages ) ) . '</p></div>';
			}
		}

		if ( $authorization_ready && '' !== $authorization_id ) {
			echo '<div class="bdc-kb-domain-actions">';
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
			echo '<input type="hidden" name="action" value="' . esc_attr( Post_Core_Blocks_Activity::ACTION ) . '">';
			echo '<input type="hidden" name="post_id" value="' . esc_attr( (string) $post_id ) . '">';
			wp_nonce_field( Post_Core_Blocks_Activity::nonce_action( $post_id ), Post_Core_Blocks_Activity::NONCE_FIELD );
			submit_button( __( 'Baixar autorização de migração', 'bdc-knowledge-base' ), 'secondary', 'submit', false );
			echo '</form>';

			if ( $t100d_enabled ) {
				echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
				echo '<input type="hidden" name="action" value="' . esc_attr( Post_Core_Blocks_Executor_T100D::ACTION ) . '">';
				echo '<input type="hidden" name="post_id" value="' . esc_attr( (string) $post_id ) . '">';
				echo '<input type="hidden" name="' . esc_attr( Post_Core_Blocks_Executor_T100D::AUTH_FIELD ) . '" value="' . esc_attr( $authorization_id ) . '">';
				wp_nonce_field( Post_Core_Blocks_Executor_T100D::nonce_action(), Post_Core_Blocks_Executor_T100D::NONCE_FIELD );
				submit_button( __( 'Migrar este artigo para Blocos do WordPress', 'bdc-knowledge-base' ), 'primary', 'submit', false );
				echo '</form>';
			}
			echo '</div>';
		}

		if ( $authorization_ready && ! $t100d_enabled ) {
			echo '<p class="bdc-kb-context-note">' . esc_html__( 'A preparação está disponível, mas a migração permanece bloqueada até autorização específica.', 'bdc-knowledge-base' ) . '</p>';
		}

		echo '</section>';
	}

	/** @param array<string,string> $rows */
	private static function table( array $rows ): void {
		echo '<table class="widefat striped bdc-kb-detail-table"><tbody>';
		foreach ( $rows as $label => $value ) {
			echo '<tr><th>' . esc_html( $label ) . '</th><td>' . esc_html( $value ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}

	private static function strategy_label( string $strategy ): string {
		$labels = array(
			'noop_empty'                       => 'Nenhuma ação necessária',
			'native_core_blocks'               => 'Estrutura nativa preservada',
			'preserve_post_content_lossless'   => 'Conteúdo preservado integralmente',
			'extract_elementor_editorial_units'=> 'Elementos editoriais preservados',
			'preserve_dual_source_for_review'  => 'Preservação com revisão humana',
			'preserve_unknown_source_for_review'=> 'Preservação com revisão humana',
		);
		return $labels[ $strategy ] ?? ( '' !== $strategy ? 'Preservação controlada' : 'Não disponível' );
	}

	private static function operational_status_label( string $status ): string {
		$labels = array(
			'ready_for_authorization' => 'Pronto para preparação',
			'human_review_required'   => 'Revisão manual necessária',
			'no_action_required'      => 'Atualizado',
			'blocked'                 => 'Ação indisponível',
		);
		return $labels[ $status ] ?? 'Em avaliação';
	}

	private static function journal_state_label( string $state ): string {
		$labels = array(
			'prepared'        => 'Preparada',
			'applied'         => 'Aplicada',
			'partial_failure' => 'Falha parcial registrada',
			'rolled_back'     => 'Revertida',
		);
		return $labels[ $state ] ?? ( '' === $state ? 'Sem operações registradas' : 'Registrada' );
	}

	private static function lock_status_label( string $status ): string {
		$labels = array(
			'free'        => 'Nenhum',
			'held'        => 'Em uso',
			'expired'     => 'Expirado',
			'unavailable' => 'Indisponível',
		);
		return $labels[ $status ] ?? 'Em avaliação';
	}

	/** @param array<int,string> $block_names */
	private static function block_names_label( array $block_names ): string {
		if ( empty( $block_names ) ) {
			return 'Não se aplica';
		}
		$labels = array(
			'core/freeform'  => 'Conteúdo HTML preservado',
			'core/shortcode' => 'Código incorporado preservado',
		);
		$out = array();
		foreach ( $block_names as $name ) {
			$out[] = $labels[ $name ] ?? 'Blocos do WordPress';
		}
		return implode( ', ', array_values( array_unique( $out ) ) );
	}

	private static function history_count_label( int $count ): string {
		if ( $count <= 0 ) {
			return 'Sem operações registradas';
		}
		return 1 === $count ? '1 registro' : $count . ' registros';
	}

	private static function reason_label( string $reason ): string {
		if ( 'CORE_BLOCKS_NOOP' === $reason ) {
			return 'O artigo já utiliza Blocos do WordPress.';
		}
		if ( 'TERMINAL_ROLLBACK_HISTORY_PRESENT' === $reason ) {
			return 'Há uma reversão anterior registrada no histórico.';
		}
		if ( str_starts_with( $reason, 'LOCK_NOT_FREE:' ) ) {
			return 'O artigo está temporariamente em uso por outra operação.';
		}
		if ( str_starts_with( $reason, 'OPEN_OR_NONTERMINAL_JOURNAL:' ) ) {
			return 'Existe uma operação anterior que precisa ser concluída ou revisada.';
		}
		if ( 'MIXED_SOURCE_REQUIRES_HUMAN' === $reason || 'DRY_RUN_REQUIRES_HUMAN' === $reason ) {
			return 'A origem do conteúdo exige revisão manual antes da migração.';
		}
		if ( str_starts_with( $reason, 'DRY_RUN_NOT_READY:' ) ) {
			return 'A migração ainda não está disponível para este artigo.';
		}
		return '';
	}

	private static function format_bytes( int $bytes ): string {
		if ( $bytes < 1024 ) {
			return $bytes . ' B';
		}
		if ( $bytes < 1048576 ) {
			return number_format_i18n( $bytes / 1024, 1 ) . ' KB';
		}
		return number_format_i18n( $bytes / 1048576, 1 ) . ' MB';
	}

	private static function is_sha256( string $hash ): bool {
		return 1 === preg_match( '/^[a-f0-9]{64}$/', $hash );
	}
}
