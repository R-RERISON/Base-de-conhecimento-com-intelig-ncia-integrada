<?php
/**
 * Rebuild explícito da Search Projection.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_Rebuild_Service {

	/** @var array<int,string> */
	private const ALLOWED_STATUSES = array( 'publish', 'draft', 'pending', 'private', 'future' );

	/**
	 * @return array<string,mixed>
	 */
	public static function rebuild(): array {
		if ( ! current_user_can( 'manage_options' ) ) {
			return self::failure( 'search_rebuild_forbidden', array(), array() );
		}

		$started = microtime( true );
		$post_ids = self::corpus_ids();
		$errors = array();
		$throwables = array();
		$stale_deleted = null;

		if ( empty( $post_ids ) ) {
			return self::failure( 'search_rebuild_empty_corpus', array(), array() );
		}

		try {
			Search_Projection_Repository::ensure_schema();
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'ensure_schema', 0, $error );
		}

		if ( ! Search_Projection_Repository::schema_exists() ) {
			$errors[] = array( 'code' => 'search_rebuild_schema_unavailable' );
		}

		Search_Projection_Repository::write_state(
			array(
				'status' => 'building',
				'corpus_count' => count( $post_ids ),
				'source_fingerprint' => '',
				'last_success_at_gmt' => '',
				'last_error_code' => '',
			)
		);

		$pass1 = empty( $errors ) && empty( $throwables )
			? self::run_pass( $post_ids, 'pass1' )
			: self::empty_pass( 'pass1' );

		$errors = array_merge( $errors, (array) $pass1['errors'] );
		$throwables = array_merge( $throwables, (array) $pass1['throwables'] );

		if ( empty( $errors ) && empty( $throwables ) && count( $post_ids ) === (int) $pass1['processed'] ) {
			$cleanup = Search_Projection_Repository::delete_stale_rows( $post_ids );
			if ( $cleanup instanceof \WP_Error ) {
				$errors[] = array(
					'code' => $cleanup->get_error_code(),
					'message' => $cleanup->get_error_message(),
				);
			} else {
				$stale_deleted = $cleanup;
			}
		}

		$pass2 = empty( $errors ) && empty( $throwables )
			? self::run_pass( $post_ids, 'pass2' )
			: self::empty_pass( 'pass2' );

		$errors = array_merge( $errors, (array) $pass2['errors'] );
		$throwables = array_merge( $throwables, (array) $pass2['throwables'] );

		$determinism = self::compare_passes( (array) $pass1['documents'], (array) $pass2['documents'] );
		$row_count = Search_Projection_Repository::count_rows();
		if ( $row_count instanceof \WP_Error ) {
			$errors[] = array(
				'code' => $row_count->get_error_code(),
				'message' => $row_count->get_error_message(),
			);
			$row_count = null;
		}

		$source_fingerprint = self::source_fingerprint( (array) $pass2['documents'] );
		$pass = empty( $errors )
			&& empty( $throwables )
			&& count( $post_ids ) === (int) $pass1['processed']
			&& count( $post_ids ) === (int) $pass2['processed']
			&& count( $post_ids ) === (int) $pass2['no_change']
			&& 0 === (int) $pass2['written']
			&& 0 === (int) $determinism['mismatch_count']
			&& count( $post_ids ) === (int) $row_count;

		Search_Projection_Repository::write_state(
			array(
				'status' => $pass ? 'ready' : 'failed',
				'corpus_count' => count( $post_ids ),
				'source_fingerprint' => $source_fingerprint,
				'last_success_at_gmt' => $pass ? gmdate( 'Y-m-d H:i:s' ) : '',
				'last_error_code' => $pass ? '' : self::first_error_code( $errors, $throwables, $determinism ),
			)
		);

		return array(
			'status' => $pass ? 'PASS' : 'FAIL',
			'corpus_count' => count( $post_ids ),
			'row_count' => $row_count,
			'stale_rows_deleted' => $stale_deleted,
			'pass1' => self::public_pass( $pass1 ),
			'pass2' => self::public_pass( $pass2 ),
			'determinism' => $determinism,
			'source_fingerprint' => $source_fingerprint,
			'state_after' => Search_Projection_Repository::state(),
			'errors' => $errors,
			'throwables' => $throwables,
			'runtime_ms' => round( ( microtime( true ) - $started ) * 1000, 4 ),
		);
	}

	/**
	 * @param array<int,int> $post_ids
	 * @return array<string,mixed>
	 */
	private static function run_pass( array $post_ids, string $name ): array {
		$documents = array();
		$errors = array();
		$throwables = array();
		$written = 0;
		$no_change = 0;

		foreach ( $post_ids as $post_id ) {
			try {
				$document = Search_Document_Builder::build( $post_id );
				if ( $document instanceof \WP_Error ) {
					$errors[] = array(
						'phase' => $name,
						'post_id' => $post_id,
						'code' => $document->get_error_code(),
						'message' => $document->get_error_message(),
					);
					continue;
				}

				$result = Search_Projection_Repository::upsert( $document );
				if ( $result instanceof \WP_Error ) {
					$errors[] = array(
						'phase' => $name,
						'post_id' => $post_id,
						'code' => $result->get_error_code(),
						'message' => $result->get_error_message(),
					);
					continue;
				}

				if ( Search_Projection_Repository::UPSERT_NO_CHANGE === $result ) {
					++$no_change;
				} else {
					++$written;
				}

				$documents[ $post_id ] = array(
					'source_hash' => (string) $document['source_hash'],
					'document_hash' => (string) $document['document_hash'],
				);
			} catch ( \Throwable $error ) {
				$throwables[] = self::throwable_row( $name, $post_id, $error );
			}
		}

		return array(
			'name' => $name,
			'processed' => count( $documents ),
			'written' => $written,
			'no_change' => $no_change,
			'documents' => $documents,
			'errors' => $errors,
			'throwables' => $throwables,
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	private static function empty_pass( string $name ): array {
		return array(
			'name' => $name,
			'processed' => 0,
			'written' => 0,
			'no_change' => 0,
			'documents' => array(),
			'errors' => array(),
			'throwables' => array(),
		);
	}

	/**
	 * @param array<string,mixed> $pass
	 * @return array<string,mixed>
	 */
	private static function public_pass( array $pass ): array {
		return array(
			'name' => (string) ( $pass['name'] ?? '' ),
			'processed' => (int) ( $pass['processed'] ?? 0 ),
			'written' => (int) ( $pass['written'] ?? 0 ),
			'no_change' => (int) ( $pass['no_change'] ?? 0 ),
			'error_count' => count( (array) ( $pass['errors'] ?? array() ) ),
			'throwable_count' => count( (array) ( $pass['throwables'] ?? array() ) ),
		);
	}

	/**
	 * @param array<int,array<string,string>> $a
	 * @param array<int,array<string,string>> $b
	 * @return array<string,mixed>
	 */
	private static function compare_passes( array $a, array $b ): array {
		$post_ids = array_values( array_unique( array_merge( array_keys( $a ), array_keys( $b ) ) ) );
		sort( $post_ids, SORT_NUMERIC );
		$mismatches = array();

		foreach ( $post_ids as $post_id ) {
			if ( ! isset( $a[ $post_id ], $b[ $post_id ] )
				|| (string) $a[ $post_id ]['source_hash'] !== (string) $b[ $post_id ]['source_hash']
				|| (string) $a[ $post_id ]['document_hash'] !== (string) $b[ $post_id ]['document_hash'] ) {
				$mismatches[] = (int) $post_id;
			}
		}

		return array(
			'compared' => count( $post_ids ),
			'mismatch_count' => count( $mismatches ),
			'mismatch_post_ids' => array_slice( $mismatches, 0, 50 ),
		);
	}

	/** @return array<int,int> */
	private static function corpus_ids(): array {
		$ids = get_posts(
			array(
				'post_type' => Meta_Contract::POST_TYPE,
				'post_status' => self::ALLOWED_STATUSES,
				'numberposts' => -1,
				'fields' => 'ids',
				'orderby' => 'ID',
				'order' => 'ASC',
				'no_found_rows' => true,
				'suppress_filters' => true,
			)
		);

		return array_values( array_map( 'intval', is_array( $ids ) ? $ids : array() ) );
	}

	/**
	 * @param array<int,array<string,string>> $documents
	 */
	private static function source_fingerprint( array $documents ): string {
		$source = array();
		ksort( $documents, SORT_NUMERIC );
		foreach ( $documents as $post_id => $document ) {
			$source[ (string) $post_id ] = (string) ( $document['source_hash'] ?? '' );
		}

		return empty( $source ) ? '' : Canonical_JSON::hash( $source );
	}

	private static function throwable_row( string $phase, int $post_id, \Throwable $error ): array {
		return array(
			'phase' => $phase,
			'post_id' => $post_id,
			'class' => get_class( $error ),
			'code' => (string) $error->getCode(),
			'message' => $error->getMessage(),
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $errors
	 * @param array<int,array<string,mixed>> $throwables
	 * @param array<string,mixed>            $determinism
	 */
	private static function first_error_code( array $errors, array $throwables, array $determinism ): string {
		if ( ! empty( $errors ) ) {
			return sanitize_key( (string) ( $errors[0]['code'] ?? 'search_rebuild_error' ) );
		}
		if ( ! empty( $throwables ) ) {
			return 'search_rebuild_throwable';
		}
		if ( (int) ( $determinism['mismatch_count'] ?? 0 ) > 0 ) {
			return 'search_rebuild_hash_mismatch';
		}
		return 'search_rebuild_failed';
	}

	/**
	 * @param array<int,array<string,mixed>> $errors
	 * @param array<int,array<string,mixed>> $throwables
	 * @return array<string,mixed>
	 */
	private static function failure( string $code, array $errors, array $throwables ): array {
		if ( empty( $errors ) ) {
			$errors[] = array( 'code' => $code );
		}

		return array(
			'status' => 'FAIL',
			'corpus_count' => 0,
			'row_count' => null,
			'stale_rows_deleted' => null,
			'pass1' => self::public_pass( self::empty_pass( 'pass1' ) ),
			'pass2' => self::public_pass( self::empty_pass( 'pass2' ) ),
			'determinism' => array( 'compared' => 0, 'mismatch_count' => 0, 'mismatch_post_ids' => array() ),
			'source_fingerprint' => '',
			'state_after' => Search_Projection_Repository::state(),
			'errors' => $errors,
			'throwables' => $throwables,
			'runtime_ms' => 0.0,
		);
	}
}
