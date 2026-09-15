<?php
/** Fixture temporária do Browser Acceptance G-110. */
namespace BDC\KnowledgeBase;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class G110_Fixture {
	private const MARKER = '_bdc_g110_acceptance_fixture';
	private const TOKEN  = '_bdc_g110_acceptance_token';
	private const TERMS  = '_bdc_g110_acceptance_terms';
	private const PREFIX = '__bdc_g110_acceptance_';

	public static function create(): array|\WP_Error {
		$token = strtolower( wp_generate_password( 12, false, false ) );
		$post_id = wp_insert_post( array(
			'post_type' => Meta_Contract::POST_TYPE,
			'post_status' => 'draft',
			'post_title' => self::PREFIX . $token,
			'post_content' => 'G110 acceptance editorial sentinel ' . $token,
			'post_author' => get_current_user_id(),
		), true );
		if ( is_wp_error( $post_id ) || (int) $post_id <= 0 ) { return new \WP_Error( 'g110_fixture_post' ); }
		$post_id = (int) $post_id;
		update_post_meta( $post_id, self::MARKER, '1' );
		update_post_meta( $post_id, self::TOKEN, $token );
		update_post_meta( $post_id, '_elementor_data', '[{"g110_acceptance":"' . $token . '"}]' );
		$term_ids = array();
		foreach ( Classification_Contract::fields() as $field => $definition ) {
			$created = wp_insert_term( self::PREFIX . $token . '_' . $field, $definition['taxonomy'] );
			if ( is_wp_error( $created ) || empty( $created['term_id'] ) ) {
				self::cleanup( $post_id );
				return new \WP_Error( 'g110_fixture_term' );
			}
			$term_ids[ $field ] = (int) $created['term_id'];
		}
		update_post_meta( $post_id, self::TERMS, $term_ids );
		return array( 'token' => $token, 'post_id' => $post_id, 'term_ids' => $term_ids );
	}

	public static function expected( string $token ): array {
		return array(
			'objective' => 'G110 objective ' . $token,
			'escalation' => 'G110 escalation ' . $token,
			'important' => 'G110 important ' . $token,
			'needsNote' => 'G110 needs changes ' . $token,
		);
	}

	public static function is_valid( int $post_id, string $token ): bool {
		return $post_id > 0 && '' !== $token && '1' === (string) get_post_meta( $post_id, self::MARKER, true ) && hash_equals( (string) get_post_meta( $post_id, self::TOKEN, true ), $token );
	}

	public static function terms( int $post_id ): array {
		$terms = get_post_meta( $post_id, self::TERMS, true );
		return is_array( $terms ) ? array_map( 'intval', $terms ) : array();
	}

	public static function cleanup_stale(): void {
		$ids = get_posts( array( 'post_type' => Meta_Contract::POST_TYPE, 'post_status' => 'any', 'posts_per_page' => 20, 'fields' => 'ids', 'meta_key' => self::MARKER, 'meta_value' => '1' ) );
		foreach ( $ids as $id ) { self::cleanup( (int) $id ); }
	}

	public static function cleanup( int $post_id ): array {
		$terms = self::terms( $post_id );
		$history = Review_Store::history( $post_id, 100, 0 );
		if ( is_array( $history ) ) {
			foreach ( $history as $event ) {
				if ( ! empty( $event['event_id'] ) ) { wp_delete_comment( (int) $event['event_id'], true ); }
			}
		}
		wp_delete_post( $post_id, true );
		foreach ( $terms as $field => $term_id ) {
			$definition = Classification_Contract::fields()[ $field ] ?? null;
			if ( is_array( $definition ) && $term_id > 0 ) { wp_delete_term( $term_id, $definition['taxonomy'] ); }
		}
		$residual_terms = 0;
		foreach ( $terms as $field => $term_id ) {
			$definition = Classification_Contract::fields()[ $field ] ?? null;
			if ( is_array( $definition ) && $term_id > 0 && term_exists( $term_id, $definition['taxonomy'] ) ) { ++$residual_terms; }
		}
		return array(
			'residual_posts' => get_post( $post_id ) ? 1 : 0,
			'residual_terms' => $residual_terms,
			'residual_review_events' => (int) get_comments( array( 'post_id' => $post_id, 'type' => Review_Contract::COMMENT_TYPE, 'status' => 'all', 'count' => true ) ),
		);
	}
}
