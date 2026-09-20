<?php
/**
 * BDC-owned Word Cloud generator, snapshot, health and scheduling.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Word_Cloud_Service {

	public static function register(): void {
		register_deactivation_hook( BDC_KB_FILE, array( self::class, 'deactivate' ) );
		add_action( 'init', array( self::class, 'ensure_defaults' ), 19 );
		add_action( 'init', array( self::class, 'ensure_schedule' ), 31 );
		add_action( Word_Cloud_Contract::CRON_HOOK, array( self::class, 'cron_generate' ) );
	}

	public static function deactivate(): void {
		wp_clear_scheduled_hook( Word_Cloud_Contract::CRON_HOOK );
	}

	public static function ensure_defaults(): void {
		self::add_option_if_missing( Word_Cloud_Contract::SETTINGS_OPTION, Word_Cloud_Contract::default_settings() );
		self::add_option_if_missing( Word_Cloud_Contract::ALLOWLIST_OPTION, Word_Cloud_Contract::default_allowlist() );
		self::add_option_if_missing( Word_Cloud_Contract::BLOCKLIST_OPTION, Word_Cloud_Contract::default_blocklist() );
		self::add_option_if_missing( Word_Cloud_Contract::STATE_OPTION, self::default_state() );
		self::add_option_if_missing( Word_Cloud_Contract::HISTORY_OPTION, array() );
	}

	public static function ensure_schedule(): void {
		$settings = self::settings();
		if ( empty( $settings['enabled'] ) ) {
			wp_clear_scheduled_hook( Word_Cloud_Contract::CRON_HOOK );
			return;
		}
		if ( ! wp_next_scheduled( Word_Cloud_Contract::CRON_HOOK ) ) {
			wp_schedule_event( time() + 5 * MINUTE_IN_SECONDS, 'hourly', Word_Cloud_Contract::CRON_HOOK );
		}
	}

	public static function cron_generate(): void {
		$health = self::health();
		if ( 'ready' === (string) ( $health['status'] ?? '' ) && ! empty( $health['fresh'] ) ) {
			return;
		}
		self::generate( 'cron_hourly' );
	}

	/** @return array<string,mixed> */
	public static function settings(): array {
		$raw = get_option( Word_Cloud_Contract::SETTINGS_OPTION, array() );
		$settings = wp_parse_args( is_array( $raw ) ? $raw : array(), Word_Cloud_Contract::default_settings() );
		$settings['enabled'] = ! empty( $settings['enabled'] );
		$settings['max_posts_scan'] = max( 25, min( 1000, absint( $settings['max_posts_scan'] ) ) );
		$settings['include_body_terms'] = ! empty( $settings['include_body_terms'] );
		$settings['max_public_terms'] = max( 6, min( 60, absint( $settings['max_public_terms'] ) ) );
		$settings['stale_after_seconds'] = max( HOUR_IN_SECONDS, min( DAY_IN_SECONDS, absint( $settings['stale_after_seconds'] ) ) );
		return $settings;
	}

	/** @return array<int,string> */
	public static function allowlist(): array {
		return self::normalized_list_option( Word_Cloud_Contract::ALLOWLIST_OPTION );
	}

	/** @return array<int,string> */
	public static function blocklist(): array {
		return self::normalized_list_option( Word_Cloud_Contract::BLOCKLIST_OPTION );
	}

	/** @return array<string,mixed> */
	public static function snapshot(): array {
		$value = get_option( Word_Cloud_Contract::SNAPSHOT_OPTION, array() );
		return is_array( $value ) ? $value : array();
	}

	/** @return array<int,array<string,mixed>> */
	public static function public_terms( int $limit = 12 ): array {
		$snapshot = self::snapshot();
		$terms = is_array( $snapshot['terms'] ?? null ) ? $snapshot['terms'] : array();
		$terms = array_values( array_filter( $terms, static fn ( $row ): bool => is_array( $row ) && ! empty( $row['public_allowed'] ) ) );
		return array_slice( $terms, 0, max( 1, min( 60, $limit ) ) );
	}

	/** @return array<string,mixed> */
	public static function state(): array {
		$value = get_option( Word_Cloud_Contract::STATE_OPTION, array() );
		return is_array( $value ) ? wp_parse_args( $value, self::default_state() ) : self::default_state();
	}

	/** @return array<string,mixed> */
	public static function health(): array {
		$state = self::state();
		$settings = self::settings();
		$generated_ts = absint( $state['generated_ts'] ?? 0 );
		$age = $generated_ts > 0 ? max( 0, time() - $generated_ts ) : null;
		$status = (string) ( $state['status'] ?? 'not_built' );
		$fresh = 'ready' === $status && null !== $age && $age <= (int) $settings['stale_after_seconds'];
		return array(
			'status' => $status,
			'fresh' => $fresh,
			'age_seconds' => $age,
			'generated_at' => (string) ( $state['generated_at'] ?? '' ),
			'term_count' => absint( $state['term_count'] ?? 0 ),
			'public_term_count' => absint( $state['public_term_count'] ?? 0 ),
			'partial' => ! empty( $state['partial'] ),
			'locked' => (bool) get_transient( Word_Cloud_Contract::LOCK_TRANSIENT ),
			'next_scheduled' => wp_next_scheduled( Word_Cloud_Contract::CRON_HOOK ) ?: null,
			'sources' => Word_Cloud_Contract::source_availability(),
		);
	}

	/** @return array<string,mixed> */
	public static function generate( string $trigger = 'manual' ): array {
		self::ensure_defaults();
		$trigger = sanitize_key( $trigger );
		if ( get_transient( Word_Cloud_Contract::LOCK_TRANSIENT ) ) {
			return array( 'status' => 'skipped_locked', 'trigger' => $trigger, 'snapshot' => self::snapshot() );
		}

		set_transient( Word_Cloud_Contract::LOCK_TRANSIENT, 1, 10 * MINUTE_IN_SECONDS );
		$started = microtime( true );
		$report = array(
			'trigger' => $trigger,
			'started_at' => gmdate( 'c' ),
			'status' => 'building',
			'posts_candidates' => 0,
			'posts_evaluated' => 0,
			'taxonomy_terms_evaluated' => 0,
			'extractor_errors' => 0,
			'partial' => false,
			'sources' => Word_Cloud_Contract::source_availability(),
		);

		try {
			$settings = self::settings();
			$terms = array();
			$allow_map = self::map_from_list( self::allowlist() );
			$block_map = self::map_from_list( self::blocklist() );

			$ids = get_posts(
				array(
					'post_type' => 'post',
					'post_status' => 'publish',
					'posts_per_page' => (int) $settings['max_posts_scan'],
					'fields' => 'ids',
					'orderby' => 'modified',
					'order' => 'DESC',
					'no_found_rows' => true,
					'suppress_filters' => false,
				)
			);
			$report['posts_candidates'] = count( (array) $ids );

			foreach ( (array) $ids as $raw_id ) {
				if ( microtime( true ) - $started >= Word_Cloud_Contract::SOFT_TIME_LIMIT_SECONDS ) {
					$report['partial'] = true;
					$report['stopped_reason'] = 'soft_time_limit';
					break;
				}
				$post_id = absint( $raw_id );
				$post = get_post( $post_id );
				if ( ! is_object( $post ) ) {
					continue;
				}

				self::add_tokens( $terms, (string) $post->post_title, 8.0, 'title', $block_map, 60 );
				$extracted = Content_Extractor::extract( $post_id );
				if ( is_wp_error( $extracted ) ) {
					++$report['extractor_errors'];
					continue;
				}
				foreach ( (array) ( $extracted['fragments'] ?? array() ) as $fragment ) {
					if ( ! is_array( $fragment ) ) {
						continue;
					}
					$kind = (string) ( $fragment['kind'] ?? '' );
					$text = (string) ( $fragment['text'] ?? '' );
					if ( '' === trim( $text ) ) {
						continue;
					}
					if ( str_contains( $kind, 'heading' ) || 'title' === $kind ) {
						self::add_tokens( $terms, $text, 6.0, 'heading', $block_map, 50 );
					} elseif ( $settings['include_body_terms'] ) {
						self::add_tokens( $terms, $text, 1.0, 'content', $block_map, 80 );
					}
				}
				++$report['posts_evaluated'];
			}

			$taxonomies = array( 'category', 'post_tag' );
			foreach ( Classification_Contract::fields() as $definition ) {
				$taxonomies[] = (string) $definition['taxonomy'];
			}
			$taxonomies = array_values( array_unique( $taxonomies ) );
			$tax_terms = get_terms( array( 'taxonomy' => $taxonomies, 'hide_empty' => true, 'number' => 300 ) );
			if ( ! is_wp_error( $tax_terms ) ) {
				$report['taxonomy_terms_evaluated'] = count( (array) $tax_terms );
				foreach ( (array) $tax_terms as $term ) {
					if ( ! is_object( $term ) || ! isset( $term->name ) ) {
						continue;
					}
					$count = max( 1, absint( $term->count ?? 1 ) );
					self::add_term( $terms, (string) $term->name, 8 + min( 20, $count ), 'taxonomy', $block_map, $count );
				}
			}

			foreach ( $allow_map as $canonical => $label ) {
				self::add_term( $terms, $label, 20, 'allowlist', $block_map, 1 );
			}

			$list = array();
			$excluded = 0;
			foreach ( $terms as $canonical => $row ) {
				$row = Word_Cloud_Quality::classify( $row, isset( $allow_map[ $canonical ] ) );
				if ( empty( $row['public_allowed'] ) ) {
					++$excluded;
				}
				$row['sources'] = array_values( array_keys( (array) $row['sources'] ) );
				$list[] = $row;
			}
			usort( $list, static function ( array $a, array $b ): int {
				$score = (float) ( $b['score'] ?? 0 ) <=> (float) ( $a['score'] ?? 0 );
				return 0 !== $score ? $score : strcmp( (string) ( $a['term'] ?? '' ), (string) ( $b['term'] ?? '' ) );
			} );
			$list = array_slice( $list, 0, 300 );
			$max_score = 0.0;
			foreach ( $list as $row ) {
				$max_score = max( $max_score, (float) ( $row['score'] ?? 0 ) );
			}
			foreach ( $list as &$row ) {
				$score = (float) ( $row['score'] ?? 0 );
				$row['score'] = round( $score, 2 );
				$row['weight'] = $max_score > 0 ? max( 1, min( 6, (int) ceil( 6 * $score / $max_score ) ) ) : 1;
			}
			unset( $row );

			$public_count = count( array_filter( $list, static fn ( array $row ): bool => ! empty( $row['public_allowed'] ) ) );
			$report['status'] = $report['partial'] ? 'partial' : 'ready';
			$report['finished_at'] = gmdate( 'c' );
			$report['runtime_ms'] = round( ( microtime( true ) - $started ) * 1000, 3 );
			$report['terms_total'] = count( $list );
			$report['terms_public'] = $public_count;
			$report['terms_excluded'] = $excluded;
			$report['review_required'] = count( $terms ) > 0 && ( $excluded / count( $terms ) ) > 0.35;
			$report['memory_peak_bytes'] = memory_get_peak_usage( true );

			$snapshot = array(
				'version' => Word_Cloud_Contract::SNAPSHOT_VERSION,
				'generated_at' => $report['finished_at'],
				'generated_ts' => time(),
				'terms' => $list,
				'quality' => array(
					'public_count' => $public_count,
					'excluded_count' => $excluded,
					'review_required' => $report['review_required'],
				),
				'sources' => Word_Cloud_Contract::source_availability(),
			);
			update_option( Word_Cloud_Contract::SNAPSHOT_OPTION, $snapshot, false );
			update_option(
				Word_Cloud_Contract::STATE_OPTION,
				array(
					'status' => $report['partial'] ? 'partial' : 'ready',
					'generated_at' => $report['finished_at'],
					'generated_ts' => $snapshot['generated_ts'],
					'term_count' => count( $list ),
					'public_term_count' => $public_count,
					'partial' => $report['partial'],
					'last_trigger' => $trigger,
					'last_report' => $report,
				),
				false
			);
			self::append_history( $report );
			return array( 'status' => $report['status'], 'snapshot' => $snapshot, 'report' => $report );
		} catch ( \Throwable $error ) {
			$report['status'] = 'failed';
			$report['finished_at'] = gmdate( 'c' );
			$report['error_class'] = get_class( $error );
			$report['error_code'] = (string) $error->getCode();
			update_option(
				Word_Cloud_Contract::STATE_OPTION,
				array(
					'status' => 'failed',
					'generated_at' => '',
					'generated_ts' => 0,
					'term_count' => 0,
					'public_term_count' => 0,
					'partial' => false,
					'last_trigger' => $trigger,
					'last_report' => $report,
				),
				false
			);
			self::append_history( $report );
			return array( 'status' => 'failed', 'report' => $report );
		} finally {
			delete_transient( Word_Cloud_Contract::LOCK_TRANSIENT );
		}
	}

	/** @param array<string,array<string,mixed>> $terms @param array<string,string> $block_map */
	private static function add_tokens( array &$terms, string $text, float $weight, string $source, array $block_map, int $limit ): void {
		$tokens = Word_Cloud_Quality::tokens( $text, $limit );
		foreach ( array_count_values( $tokens ) as $token => $count ) {
			self::add_term( $terms, (string) $token, $weight * (int) $count, $source, $block_map, (int) $count );
		}
	}

	/** @param array<string,array<string,mixed>> $terms @param array<string,string> $block_map */
	private static function add_term( array &$terms, string $label, float $score, string $source, array $block_map, int $count = 1 ): void {
		$canonical = Word_Cloud_Quality::canonical( $label );
		if ( '' === $canonical || strlen( $canonical ) < 3 || isset( $block_map[ $canonical ] ) ) {
			return;
		}
		if ( ! isset( $terms[ $canonical ] ) ) {
			$terms[ $canonical ] = array(
				'term' => trim( $label ) ?: $canonical,
				'canonical' => $canonical,
				'score' => 0.0,
				'count' => 0,
				'sources' => array(),
			);
		}
		$terms[ $canonical ]['score'] += $score;
		$terms[ $canonical ]['count'] += max( 1, $count );
		$terms[ $canonical ]['sources'][ sanitize_key( $source ) ] = true;
	}

	/** @return array<string,string> */
	private static function map_from_list( array $list ): array {
		$out = array();
		foreach ( $list as $label ) {
			$canonical = Word_Cloud_Quality::canonical( (string) $label );
			if ( '' !== $canonical ) {
				$out[ $canonical ] = (string) $label;
			}
		}
		return $out;
	}

	/** @return array<int,string> */
	private static function normalized_list_option( string $option ): array {
		$value = get_option( $option, array() );
		if ( is_string( $value ) ) {
			$value = preg_split( '/[\r\n,]+/', $value ) ?: array();
		}
		$out = array();
		$seen = array();
		foreach ( (array) $value as $item ) {
			$label = trim( sanitize_text_field( (string) $item ) );
			$key = Word_Cloud_Quality::canonical( $label );
			if ( '' === $key || isset( $seen[ $key ] ) ) {
				continue;
			}
			$seen[ $key ] = true;
			$out[] = $label;
		}
		return $out;
	}

	/** @param array<string,mixed> $report */
	private static function append_history( array $report ): void {
		$history = get_option( Word_Cloud_Contract::HISTORY_OPTION, array() );
		$history = is_array( $history ) ? $history : array();
		array_unshift( $history, $report );
		$history = array_slice( $history, 0, Word_Cloud_Contract::HISTORY_LIMIT );
		update_option( Word_Cloud_Contract::HISTORY_OPTION, $history, false );
	}

	private static function add_option_if_missing( string $name, mixed $value ): void {
		if ( false === get_option( $name, false ) ) {
			add_option( $name, $value, '', false );
		}
	}

	/** @return array<string,mixed> */
	private static function default_state(): array {
		return array(
			'status' => 'not_built',
			'generated_at' => '',
			'generated_ts' => 0,
			'term_count' => 0,
			'public_term_count' => 0,
			'partial' => false,
			'last_trigger' => '',
			'last_report' => array(),
		);
	}
}