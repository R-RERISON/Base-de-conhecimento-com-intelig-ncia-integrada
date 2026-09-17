<?php
/**
 * Exclusive per-post migration lock for SPEC-004 / G-245 / T087B.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Elementor_Migration_Lock {

	public const META_KEY = '_bdc_kb_migration_lock';
	public const SCHEMA_VERSION = 1;
	public const DEFAULT_TTL_SECONDS = 300;
	public const MAX_TTL_SECONDS = 900;

	/** @return array<string,mixed>|\WP_Error */
	public static function acquire( int $post_id, string $run_id, int $ttl_seconds = self::DEFAULT_TTL_SECONDS ) {
		$run_id = trim( $run_id );
		if ( $post_id <= 0 || ! get_post( $post_id ) ) {
			return new \WP_Error( 'bdc_kb_migration_lock_invalid_post', 'Post inválido para lock.' );
		}
		if ( 1 !== preg_match( '/^[A-Za-z0-9._:-]{8,128}$/', $run_id ) ) {
			return new \WP_Error( 'bdc_kb_migration_lock_invalid_run_id', 'run_id inválido para lock.' );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'bdc_kb_migration_lock_forbidden', 'Lock de migração exige manage_options.' );
		}
		$actor_id = (int) get_current_user_id();
		if ( $actor_id <= 0 ) {
			return new \WP_Error( 'bdc_kb_migration_lock_missing_actor', 'Usuário autenticado obrigatório para lock.' );
		}
		$ttl_seconds = max( 30, min( self::MAX_TTL_SECONDS, $ttl_seconds ) );
		$now = time();
		$token = wp_generate_uuid4();
		$payload = array(
			'schema_version' => self::SCHEMA_VERSION,
			'run_id' => $run_id,
			'token' => $token,
			'actor_id' => $actor_id,
			'acquired_at_gmt' => gmdate( 'c', $now ),
			'expires_at_gmt' => gmdate( 'c', $now + $ttl_seconds ),
			'ttl_seconds' => $ttl_seconds,
		);
		$json = wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) || '' === $json ) {
			return new \WP_Error( 'bdc_kb_migration_lock_encode_failed', 'Falha ao serializar lock.' );
		}
		$meta_id = add_post_meta( $post_id, self::META_KEY, $json, true );
		if ( false === $meta_id ) {
			$existing = self::inspect( $post_id );
			return new \WP_Error( 'bdc_kb_migration_lock_already_held', 'Artigo já possui lock de migração.', array( 'existing' => $existing ) );
		}
		$readback = self::inspect( $post_id );
		if ( is_wp_error( $readback ) || 'held' !== (string) ( $readback['status'] ?? '' ) || ! hash_equals( $token, (string) ( $readback['token'] ?? '' ) ) ) {
			delete_post_meta( $post_id, self::META_KEY, $json );
			return new \WP_Error( 'bdc_kb_migration_lock_readback_failed', 'Lock divergiu na releitura e foi removido.' );
		}
		return $readback;
	}

	/** @return array<string,mixed>|\WP_Error */
	public static function inspect( int $post_id ) {
		if ( $post_id <= 0 ) {
			return new \WP_Error( 'bdc_kb_migration_lock_invalid_post', 'Post inválido para lock.' );
		}
		$raw = get_post_meta( $post_id, self::META_KEY, true );
		if ( '' === $raw || null === $raw || false === $raw ) {
			return array(
				'status' => 'free',
				'post_id' => $post_id,
				'writer_allowed' => false,
				'migration_execution_allowed' => false,
			);
		}
		if ( ! is_string( $raw ) ) {
			return new \WP_Error( 'bdc_kb_migration_lock_invalid_payload', 'Payload do lock inválido.' );
		}
		$payload = json_decode( $raw, true );
		if ( ! is_array( $payload ) || self::SCHEMA_VERSION !== (int) ( $payload['schema_version'] ?? 0 ) ) {
			return new \WP_Error( 'bdc_kb_migration_lock_invalid_payload', 'Payload do lock malformado.' );
		}
		$token = (string) ( $payload['token'] ?? '' );
		$run_id = (string) ( $payload['run_id'] ?? '' );
		$expires = strtotime( (string) ( $payload['expires_at_gmt'] ?? '' ) );
		if ( '' === $token || false === $expires || 1 !== preg_match( '/^[A-Za-z0-9._:-]{8,128}$/', $run_id ) ) {
			return new \WP_Error( 'bdc_kb_migration_lock_invalid_payload', 'Payload do lock incompleto.' );
		}
		return array(
			'status' => $expires <= time() ? 'expired' : 'held',
			'post_id' => $post_id,
			'run_id' => $run_id,
			'token' => $token,
			'actor_id' => (int) ( $payload['actor_id'] ?? 0 ),
			'acquired_at_gmt' => (string) ( $payload['acquired_at_gmt'] ?? '' ),
			'expires_at_gmt' => (string) ( $payload['expires_at_gmt'] ?? '' ),
			'ttl_seconds' => (int) ( $payload['ttl_seconds'] ?? 0 ),
			'writer_allowed' => false,
			'migration_execution_allowed' => false,
		);
	}

	/** @return array<string,mixed>|\WP_Error */
	public static function release( int $post_id, string $token ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'bdc_kb_migration_lock_forbidden', 'Liberação de lock exige manage_options.' );
		}
		$raw = get_post_meta( $post_id, self::META_KEY, true );
		if ( '' === $raw || null === $raw || false === $raw ) {
			return array( 'status' => 'already_free', 'released' => false, 'idempotent_noop' => true );
		}
		if ( ! is_string( $raw ) ) {
			return new \WP_Error( 'bdc_kb_migration_lock_invalid_payload', 'Payload do lock inválido.' );
		}
		$payload = json_decode( $raw, true );
		$actual_token = is_array( $payload ) ? (string) ( $payload['token'] ?? '' ) : '';
		if ( '' === $actual_token || '' === $token || ! hash_equals( $actual_token, $token ) ) {
			return new \WP_Error( 'bdc_kb_migration_lock_token_mismatch', 'Token não corresponde ao lock atual.' );
		}
		$deleted = delete_post_meta( $post_id, self::META_KEY, $raw );
		if ( ! $deleted ) {
			return new \WP_Error( 'bdc_kb_migration_lock_release_failed', 'Falha ao liberar lock.' );
		}
		$after = self::inspect( $post_id );
		if ( is_wp_error( $after ) || 'free' !== (string) ( $after['status'] ?? '' ) ) {
			return new \WP_Error( 'bdc_kb_migration_lock_release_readback_failed', 'Lock continuou presente após liberação.' );
		}
		return array( 'status' => 'released', 'released' => true, 'idempotent_noop' => false );
	}
}
