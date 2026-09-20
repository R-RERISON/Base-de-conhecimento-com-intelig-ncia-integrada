<?php
/**
 * BDC Word Cloud v1.1 contract and defaults.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Word_Cloud_Contract {

	public const VERSION = 'word-cloud-v1.2.0';
	public const SNAPSHOT_VERSION = 'word-cloud-snapshot-v1.1.0';
	public const QUALITY_PROFILE = 'semantic-balanced-v2';
	public const CRON_HOOK = 'bdc_kb_word_cloud_hourly_generate';
	public const SETTINGS_OPTION = 'bdc_kb_word_cloud_settings';
	public const ALLOWLIST_OPTION = 'bdc_kb_word_cloud_allowlist';
	public const BLOCKLIST_OPTION = 'bdc_kb_word_cloud_blocklist';
	public const SNAPSHOT_OPTION = 'bdc_kb_word_cloud_snapshot';
	public const STATE_OPTION = 'bdc_kb_word_cloud_state';
	public const HISTORY_OPTION = 'bdc_kb_word_cloud_history';
	public const LOCK_TRANSIENT = 'bdc_kb_word_cloud_generation_lock';
	public const HISTORY_LIMIT = 20;
	public const SOFT_TIME_LIMIT_SECONDS = 8.0;

	/** @return array<string,mixed> */
	public static function default_settings(): array {
		return array(
			'enabled' => true,
			'quality_profile' => self::QUALITY_PROFILE,
			'max_posts_scan' => 400,
			'include_body_terms' => false,
			'max_public_terms' => 24,
			'stale_after_seconds' => 3 * HOUR_IN_SECONDS,
		);
	}

	/** @return array<int,string> */
	public static function default_allowlist(): array {
		return array(
			'VPN','MFA','2FA','AD','TI','BI','RH','Teams','MSTeams','Outlook','Microsoft 365','Office 365','Intune','SCCM',
			'Senha','Certificado','BitLocker','OneDrive','SharePoint','Office','Windows 10','Windows 11','Power BI','Power Apps',
			'Rotulação','E-mail','Python','Autenticação',
		);
	}

	/** @return array<int,string> */
	public static function default_blocklist(): array {
		return array(
			'base','conhecimento','artigo','artigos','pagina','página','clique','clicar','acesse','documento','documentos',
			'sistema','sistemas','arquivo','arquivos','usuario','usuário','usuarios','usuários','informacao','informação',
			'informacoes','informações','ambiente','ambientes','processo','processos','servico','serviço','servicos','serviços',
			'solicitacao','solicitação','solicitacoes','solicitações','realizar','utilizar','acessar','conforme','atraves','através',
			'necessario','necessário','orientacao','orientação','objetivo','objetivos','abrangencia','abrangência','conceitos',
			'definicoes','definições','regras','atividades','abaixo','tela','caso','banco','central','mail',
			'http','https','www','html','php','jpg','jpeg','png','gif','svg','wordpress','elementor','rotu','pytho','auth','ncia',
		);
	}

	/** @return array<string,string> */
	public static function source_availability(): array {
		return array(
			'title' => 'available',
			'title_phrase' => 'available',
			'heading' => 'available',
			'heading_phrase' => 'available',
			'content' => 'available_opt_in',
			'taxonomy' => 'available',
			'allowlist' => 'available',
			'blocklist' => 'available',
			'search_events' => 'pending_telemetry_spec',
			'consultations' => 'available_aggregate_preview',
			'interactions' => 'pending_telemetry_spec',
			'vocabulary' => 'pending_governance_spec',
		);
	}
}