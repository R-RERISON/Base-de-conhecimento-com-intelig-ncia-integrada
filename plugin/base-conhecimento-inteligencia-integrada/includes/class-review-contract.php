<?php
/**
 * Contrato canônico de Review & Governança da SPEC-003.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define estados, transições e regras de autorização sem executar writes.
 */
final class Review_Contract {

	public const POST_TYPE        = 'post';
	public const COMMENT_TYPE     = 'bdc_kb_review_event';
	public const EVENT_SCHEMA     = 1;
	public const MAX_NOTE_BYTES   = 2000;
	public const STATE_UNREVIEWED = 'unreviewed';
	public const STATE_IN_REVIEW  = 'in_review';
	public const STATE_NEEDS_CHANGES = 'needs_changes';
	public const STATE_APPROVED   = 'approved';
	public const STATE_EXCLUDED   = 'excluded';

	/** @return array<string,string> */
	public static function states(): array {
		return array(
			self::STATE_UNREVIEWED    => 'Não revisado',
			self::STATE_IN_REVIEW     => 'Em revisão',
			self::STATE_NEEDS_CHANGES => 'Requer ajustes',
			self::STATE_APPROVED      => 'Aprovado',
			self::STATE_EXCLUDED      => 'Excluído da base governada',
		);
	}

	public static function is_state( string $state ): bool {
		return isset( self::states()[ $state ] );
	}

	/** @return array<int,string> */
	public static function allowed_targets( string $from ): array {
		$map = array(
			self::STATE_UNREVIEWED => array(
				self::STATE_IN_REVIEW,
				self::STATE_APPROVED,
				self::STATE_EXCLUDED,
			),
			self::STATE_IN_REVIEW => array(
				self::STATE_APPROVED,
				self::STATE_NEEDS_CHANGES,
				self::STATE_EXCLUDED,
			),
			self::STATE_NEEDS_CHANGES => array(
				self::STATE_IN_REVIEW,
				self::STATE_APPROVED,
				self::STATE_EXCLUDED,
			),
			self::STATE_APPROVED => array(
				self::STATE_IN_REVIEW,
				self::STATE_NEEDS_CHANGES,
				self::STATE_EXCLUDED,
			),
			self::STATE_EXCLUDED => array(
				self::STATE_IN_REVIEW,
			),
		);

		return $map[ $from ] ?? array();
	}

	public static function is_transition_allowed( string $from, string $to ): bool {
		if ( $from === $to && self::is_state( $from ) ) {
			return true;
		}

		return in_array( $to, self::allowed_targets( $from ), true );
	}

	public static function note_required( string $to ): bool {
		return in_array( $to, array( self::STATE_NEEDS_CHANGES, self::STATE_EXCLUDED ), true );
	}

	public static function requires_reviewer_capability( string $from, string $to ): bool {
		if ( in_array( $to, array( self::STATE_APPROVED, self::STATE_NEEDS_CHANGES, self::STATE_EXCLUDED ), true ) ) {
			return true;
		}

		return self::STATE_IN_REVIEW === $to
			&& in_array( $from, array( self::STATE_APPROVED, self::STATE_EXCLUDED ), true );
	}
}
