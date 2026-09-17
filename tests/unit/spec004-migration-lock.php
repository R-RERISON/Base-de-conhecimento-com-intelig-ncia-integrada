<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
	if ( ! class_exists( 'WP_Error' ) ) {
		class WP_Error {
			public function __construct(
				public string $code = '',
				public string $message = '',
				public mixed $data = null
			) {}
		}
	}
}

namespace BDC\KnowledgeBase {
	$GLOBALS['spec004_posts'] = array( 321 => (object) array( 'ID' => 321 ) );
	$GLOBALS['spec004_meta'] = array();
	$GLOBALS['spec004_next_meta_id'] = 1;
	$GLOBALS['spec004_user_id'] = 9;
	$GLOBALS['spec004_manage_options'] = true;
	$GLOBALS['spec004_uuid_seq'] = 1;

	function is_wp_error( mixed $value ): bool {
		return $value instanceof \WP_Error;
	}
	function get_post( int $post_id ): mixed {
		return $GLOBALS['spec004_posts'][ $post_id ] ?? null;
	}
	function current_user_can( string $cap ): bool {
		return 'manage_options' === $cap && true === $GLOBALS['spec004_manage_options'];
	}
	function get_current_user_id(): int {
		return (int) $GLOBALS['spec004_user_id'];
	}
	function wp_generate_uuid4(): string {
		return sprintf( '00000000-0000-4000-8000-%012d', $GLOBALS['spec004_uuid_seq']++ );
	}
	function wp_json_encode( mixed $value, int $flags = 0 ): string|false {
		return json_encode( $value, $flags );
	}
	function add_post_meta( int $post_id, string $key, mixed $value, bool $unique = false ): int|false {
		if ( $unique ) {
			foreach ( $GLOBALS['spec004_meta'] as $row ) {
				if ( (int) $row->post_id === $post_id && (string) $row->meta_key === $key ) {
					return false;
				}
			}
		}
		$id = $GLOBALS['spec004_next_meta_id']++;
		$GLOBALS['spec004_meta'][ $id ] = (object) array(
			'meta_id' => $id,
			'post_id' => $post_id,
			'meta_key' => $key,
			'meta_value' => $value,
		);
		return $id;
	}
	function get_post_meta( int $post_id, string $key, bool $single = false ): mixed {
		$values = array();
		foreach ( $GLOBALS['spec004_meta'] as $row ) {
			if ( (int) $row->post_id === $post_id && (string) $row->meta_key === $key ) {
				$values[] = $row->meta_value;
			}
		}
		if ( $single ) {
			return $values[0] ?? '';
		}
		return $values;
	}
	function delete_post_meta( int $post_id, string $key, mixed $value = '' ): bool {
		$deleted = false;
		foreach ( array_keys( $GLOBALS['spec004_meta'] ) as $id ) {
			$row = $GLOBALS['spec004_meta'][ $id ];
			if ( (int) $row->post_id === $post_id && (string) $row->meta_key === $key && ( '' === $value || $row->meta_value === $value ) ) {
				unset( $GLOBALS['spec004_meta'][ $id ] );
				$deleted = true;
			}
		}
		return $deleted;
	}

	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-elementor-migration-lock.php';

	$assertions = 0;
	function assert_lock( bool $condition, string $message ): void {
		global $assertions;
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
		++$assertions;
		echo "PASS {$message}\n";
	}

	$free = Elementor_Migration_Lock::inspect( 321 );
	assert_lock( is_array( $free ) && 'free' === $free['status'], 'initial state free' );
	assert_lock( false === $free['writer_allowed'], 'free state does not authorize writer' );

	$lock = Elementor_Migration_Lock::acquire( 321, 'run-lock-20260917', 120 );
	assert_lock( is_array( $lock ), 'lock acquired' );
	assert_lock( 'held' === $lock['status'], 'lock status held' );
	assert_lock( 120 === $lock['ttl_seconds'], 'requested ttl preserved' );
	assert_lock( '' !== $lock['token'], 'lock token present' );
	assert_lock( false === $lock['migration_execution_allowed'], 'lock alone does not authorize migration' );

	$second = Elementor_Migration_Lock::acquire( 321, 'run-lock-20260918', 120 );
	assert_lock( $second instanceof \WP_Error, 'second concurrent lock blocked' );

	$bad_release = Elementor_Migration_Lock::release( 321, 'wrong-token' );
	assert_lock( $bad_release instanceof \WP_Error, 'wrong token cannot release lock' );
	$still = Elementor_Migration_Lock::inspect( 321 );
	assert_lock( is_array( $still ) && 'held' === $still['status'], 'lock remains after wrong token' );

	$released = Elementor_Migration_Lock::release( 321, $lock['token'] );
	assert_lock( is_array( $released ) && true === $released['released'], 'correct token releases lock' );
	$free2 = Elementor_Migration_Lock::inspect( 321 );
	assert_lock( is_array( $free2 ) && 'free' === $free2['status'], 'state free after release' );
	$release_again = Elementor_Migration_Lock::release( 321, $lock['token'] );
	assert_lock( is_array( $release_again ) && true === $release_again['idempotent_noop'], 'repeated release is idempotent noop' );

	$low_ttl = Elementor_Migration_Lock::acquire( 321, 'run-lock-20260919', 1 );
	assert_lock( is_array( $low_ttl ) && 30 === $low_ttl['ttl_seconds'], 'ttl lower bound enforced' );
	Elementor_Migration_Lock::release( 321, $low_ttl['token'] );
	$high_ttl = Elementor_Migration_Lock::acquire( 321, 'run-lock-20260920', 5000 );
	assert_lock( is_array( $high_ttl ) && 900 === $high_ttl['ttl_seconds'], 'ttl upper bound enforced' );
	Elementor_Migration_Lock::release( 321, $high_ttl['token'] );

	$GLOBALS['spec004_manage_options'] = false;
	$forbidden = Elementor_Migration_Lock::acquire( 321, 'run-lock-20260921', 120 );
	assert_lock( $forbidden instanceof \WP_Error, 'manage_options required to acquire lock' );
	$GLOBALS['spec004_manage_options'] = true;

	$invalid = Elementor_Migration_Lock::acquire( 0, 'run-lock-20260922', 120 );
	assert_lock( $invalid instanceof \WP_Error, 'invalid post fails closed' );
	$invalid_run = Elementor_Migration_Lock::acquire( 321, 'x', 120 );
	assert_lock( $invalid_run instanceof \WP_Error, 'invalid run id fails closed' );

	echo "ALL PASS {$assertions}\n";
}
