<?php
/**
 * BDC Word Cloud v1 contract and defaults.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Word_Cloud_Contract {

	public const VERSION = 'word-cloud-v1.0.0';
	public const SNAPSHOT_VERSION = 'word-cloud-snapshot-v1.0.0';
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
			'max_posts_scan' => 400,
			'include_body_terms' => true,
			'max_public_terms' => 24,
			'stale_after_seconds' => 3 * HOUR_IN_SECONDS,
		);
	}

	/** @return array<int,string> */
	public static function default_allowlist(): array {
		return array();
	}

	/** @return array<int,string> */
	public static function default_blocklist(): array {
		return array(
			'base','conhecimento','artigo','artigos','pagina','página','clique','aqui','mais','sobre','para','com','sem','uma','umas','uns',
			'das','dos','que','como','por','nos','nas','de','do','da','em','no','na','os','as','ao','aos','e','ou','se','um','the','and','from',
			'http','https','www','html','php','jpg','jpeg','png','gif','svg','wordpress','elementor',
		);
	}

	/** @return array<string,string> */
	public static function source_availability(): array {
		return array(
			'title' => 'available',
			'heading' => 'available',
			'content' => 'available',
			'taxonomy' => 'available',
			'allowlist' => 'available',
			'blocklist' => 'available',
			'search_events' => 'pending_telemetry_spec',
			'interactions' => 'pending_telemetry_spec',
			'vocabulary' => 'pending_governance_spec',
		);
	}
}