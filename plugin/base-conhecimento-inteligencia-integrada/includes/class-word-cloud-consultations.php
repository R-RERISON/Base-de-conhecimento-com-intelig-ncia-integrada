<?php
/**
 * Privacy-minimal aggregate consultation counter for BDC Word Cloud terms.
 *
 * This is intentionally not an event log. It stores bounded aggregate counters
 * only for terms that are already public in the current Word Cloud snapshot.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Word_Cloud_Consultations {

	public const VERSION = 'consultation-aggregate-v1.1.0';
	public const OPTION = 'bdc_kb_word_cloud_consultations';
	public const AJAX_ACTION = 'bdc_kb_word_cloud_consult_preview';
	public const NONCE_ACTION = 'bdc_kb_word_cloud_consult_preview';
	private const MAX_TERMS = 500;
	private const EVENT_DEDUPE_TTL = 5 * MINUTE_IN_SECONDS;
	private const EVENT_ID_MAX_LENGTH = 96;

	public static function register(): void {
		add_action( 'init', array( self::class, 'ensure_default' ), 20 );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( self::class, 'ajax_record' ) );
	}

	public static function ensure_default(): void {
		if ( false === get_option( self::OPTION, false ) ) {
			add_option( self::OPTION, array( 'version' => self::VERSION, 'terms' => array(), 'updated_at' => '' ), '', false );
		}
	}

	/** @return array<string,mixed> */
	public static function payload(): array {
		$value = get_option( self::OPTION, array() );
		if ( ! is_array( $value ) ) {
			return array( 'version' => self::VERSION, 'terms' => array(), 'updated_at' => '' );
		}
		$version = (string) ( $value['version'] ?? '' );
		if ( ! in_array( $version, array( self::VERSION, 'consultation-aggregate-v1.0.0' ), true ) ) {
			return array( 'version' => self::VERSION, 'terms' => array(), 'updated_at' => '' );
		}
		$value['version'] = self::VERSION;
		$value['terms'] = is_array( $value['terms'] ?? null ) ? $value['terms'] : array();
		return $value;
	}

	/** @return array<string,int> */
	public static function counts(): array {
		$out = array();
		foreach ( (array) ( self::payload()['terms'] ?? array() ) as $canonical => $row ) {
			if ( is_array( $row ) ) {
				$out[ (string) $canonical ] = max( 0, absint( $row['count'] ?? 0 ) );
			}
		}
		return $out;
	}

	public static function total_count(): int {
		return array_sum( self::counts() );
	}

	public static function reset(): void {
		update_option( self::OPTION, array( 'version' => self::VERSION, 'terms' => array(), 'updated_at' => gmdate( 'c' ) ), false );
	}

	/**
	 * @param array<int,array<string,mixed>> $terms
	 * @return array<int,array<string,mixed>>
	 */
	public static function decorate_terms( array $terms ): array {
		$counts = self::counts();
		foreach ( $terms as &$row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$canonical = (string) ( $row['canonical'] ?? Word_Cloud_Quality::canonical( (string) ( $row['term'] ?? '' ) ) );
			$count = isset( $counts[ $canonical ] ) ? max( 0, (int) $counts[ $canonical ] ) : 0;
			$semantic_score = (float) ( $row['score'] ?? 0 );
			$usage_boost = min( 60.0, 12.0 * log( 1.0 + $count ) );
			$row['consultation_count'] = $count;
			$row['consultation_boost'] = round( $usage_boost, 2 );
			$row['display_score'] = round( $semantic_score + $usage_boost, 2 );
		}
		unset( $row );

		usort(
			$terms,
			static function ( array $a, array $b ): int {
				$display = (float) ( $b['display_score'] ?? 0 ) <=> (float) ( $a['display_score'] ?? 0 );
				if ( 0 !== $display ) {
					return $display;
				}
				$consultations = (int) ( $b['consultation_count'] ?? 0 ) <=> (int) ( $a['consultation_count'] ?? 0 );
				if ( 0 !== $consultations ) {
					return $consultations;
				}
				return strcmp( (string) ( $a['term'] ?? '' ), (string) ( $b['term'] ?? '' ) );
			}
		);
		return $terms;
	}

	public static function record( string $term, string $source ): bool {
		$canonical = Word_Cloud_Quality::canonical( $term );
		if ( '' === $canonical || ! isset( self::current_public_term_map()[ $canonical ] ) ) {
			return false;
		}
		$source = sanitize_key( $source );
		if ( ! in_array( $source, array( 'topic_click', 'result_click' ), true ) ) {
			$source = 'result_click';
		}

		$payload = self::payload();
		$terms = is_array( $payload['terms'] ?? null ) ? $payload['terms'] : array();
		$row = isset( $terms[ $canonical ] ) && is_array( $terms[ $canonical ] ) ? $terms[ $canonical ] : array();
		$row['count'] = min( PHP_INT_MAX, max( 0, absint( $row['count'] ?? 0 ) ) + 1 );
		$row['updated_at'] = gmdate( 'c' );
		$row['sources'] = is_array( $row['sources'] ?? null ) ? $row['sources'] : array();
		$row['sources'][ $source ] = max( 0, absint( $row['sources'][ $source ] ?? 0 ) ) + 1;
		$terms[ $canonical ] = $row;

		if ( count( $terms ) > self::MAX_TERMS ) {
			uasort( $terms, static fn ( array $a, array $b ): int => strcmp( (string) ( $b['updated_at'] ?? '' ), (string) ( $a['updated_at'] ?? '' ) ) );
			$terms = array_slice( $terms, 0, self::MAX_TERMS, true );
		}

		$payload['version'] = self::VERSION;
		$payload['terms'] = $terms;
		$payload['updated_at'] = gmdate( 'c' );
		update_option( self::OPTION, $payload, false );
		return true;
	}

	public static function ajax_record(): void {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permissão insuficiente.' ), 403 );
		}
		$term = isset( $_POST['term'] ) && is_scalar( $_POST['term'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['term'] ) ) : '';
		$source = isset( $_POST['source'] ) && is_scalar( $_POST['source'] ) ? sanitize_key( wp_unslash( (string) $_POST['source'] ) ) : 'result_click';
		$event_id = isset( $_POST['event_id'] ) && is_scalar( $_POST['event_id'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['event_id'] ) ) : '';
		$event_id = substr( trim( $event_id ), 0, self::EVENT_ID_MAX_LENGTH );

		if ( '' === $event_id ) {
			wp_send_json_error( array( 'message' => 'event_id ausente.' ), 400 );
		}

		$canonical = Word_Cloud_Quality::canonical( $term );
		$dedupe_key = 'bdc_kb_wc_evt_' . sha1( $event_id . '|' . $canonical . '|' . sanitize_key( $source ) );
		if ( get_transient( $dedupe_key ) ) {
			wp_send_json_success( array( 'recorded' => false, 'duplicate' => true ) );
		}

		set_transient( $dedupe_key, 1, self::EVENT_DEDUPE_TTL );
		$recorded = self::record( $term, $source );
		if ( ! $recorded ) {
			delete_transient( $dedupe_key );
		}
		wp_send_json_success( array( 'recorded' => $recorded, 'duplicate' => false ) );
	}

	/** @return array<string,string> */
	private static function current_public_term_map(): array {
		$snapshot = Word_Cloud_Service::snapshot();
		if ( Word_Cloud_Contract::SNAPSHOT_VERSION !== (string) ( $snapshot['version'] ?? '' )
			|| Word_Cloud_Contract::QUALITY_PROFILE !== (string) ( $snapshot['quality_profile'] ?? '' ) ) {
			return array();
		}
		$out = array();
		foreach ( (array) ( $snapshot['terms'] ?? array() ) as $row ) {
			if ( ! is_array( $row ) || empty( $row['public_allowed'] ) ) {
				continue;
			}
			$canonical = (string) ( $row['canonical'] ?? '' );
			if ( '' !== $canonical ) {
				$out[ $canonical ] = (string) ( $row['term'] ?? $canonical );
			}
		}
		return $out;
	}
}