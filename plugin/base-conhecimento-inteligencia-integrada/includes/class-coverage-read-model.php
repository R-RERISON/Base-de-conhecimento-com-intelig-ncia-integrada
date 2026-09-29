<?php
/**
 * Structured knowledge coverage read model.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Derives GRE-compatible eight-field coverage without additional persistence.
 */
final class Coverage_Read_Model {

	public const STATE_EMPTY    = 'EMPTY';
	public const STATE_PARTIAL  = 'PARTIAL';
	public const STATE_COMPLETE = 'COMPLETE';

	/**
	 * @return array{post_id:int,filled:int,total:int,state:string,missing:array<int,string>,fields:array<string,bool>}|\WP_Error
	 */
	public static function read( int $post_id ): array|\WP_Error {
		$summary = Summary_Store::read( $post_id );
		if ( is_wp_error( $summary ) ) {
			return $summary;
		}

		$classification = Classification_Store::read( $post_id );
		if ( is_wp_error( $classification ) ) {
			return $classification;
		}

		$facts = Knowledge_Facts_Store::read( $post_id );
		if ( is_wp_error( $facts ) ) {
			return $facts;
		}

		$terms  = (array) ( $classification['terms'] ?? array() );
		$values = (array) ( $facts['values'] ?? array() );

		$fields = array(
			'objective'         => '' !== trim( (string) ( $summary['objective'] ?? '' ) ),
			'responsible_team'  => ! empty( $terms['responsible_team'] ),
			'catalog_item'      => ! empty( $terms['catalog_item'] ),
			'affected_service'  => '' !== trim( (string) ( $values['affected_service'] ?? '' ) ),
			'systems_involved'  => '' !== trim( (string) ( $values['systems_involved'] ?? '' ) ),
			'audience'          => ! empty( $terms['audience'] ),
			'escalation'        => '' !== trim( (string) ( $summary['escalation'] ?? '' ) ),
			'important'         => '' !== trim( (string) ( $summary['important'] ?? '' ) ),
		);

		$filled  = count( array_filter( $fields ) );
		$total   = count( $fields );
		$missing = array_keys( array_filter( $fields, static fn ( bool $present ): bool => ! $present ) );
		$state   = self::STATE_PARTIAL;

		if ( 0 === $filled ) {
			$state = self::STATE_EMPTY;
		} elseif ( $filled === $total ) {
			$state = self::STATE_COMPLETE;
		}

		return array(
			'post_id' => $post_id,
			'filled'  => $filled,
			'total'   => $total,
			'state'   => $state,
			'missing' => $missing,
			'fields'  => $fields,
		);
	}
}
