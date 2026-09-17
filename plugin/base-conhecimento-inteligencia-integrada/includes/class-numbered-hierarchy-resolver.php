<?php
/**
 * Conservative numbered hierarchy resolver for Knowledge Document 2.1.0.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Numbered_Hierarchy_Resolver {

	/**
	 * @param array<int,array<string,mixed>> $sections
	 * @return array{sections:array<int,array<string,mixed>>,status:string,strong_signal_count:int,resolved_edges:int,nodes:array<int,array<string,mixed>>,edges:array<int,array<string,mixed>>,warnings:array<int,string>}
	 */
	public static function resolve( array $sections ): array {
		$out = array_values( $sections );
		$contexts = array();
		$warnings = array();
		$nodes = array();
		$edges = array();
		$resolved_edges = 0;
		$strong_signal_count = 0;

		foreach ( $out as $ordinal => &$section ) {
			$meta = is_array( $section['meta'] ?? null ) ? $section['meta'] : array();
			$kind = (string) ( $section['kind'] ?? 'paragraph' );
			if ( 'list_item' === $kind && '' !== (string) ( $meta['list_id'] ?? '' ) ) {
				$meta['hierarchy_source'] = 'explicit_dom';
				$meta['hierarchy_confidence'] = 'authoritative';
			} elseif ( 'heading' === $kind ) {
				$meta['hierarchy_source'] = 'heading_inferred';
				$meta['hierarchy_confidence'] = 'deterministic';
			} else {
				$meta['hierarchy_source'] = 'flat';
				$meta['hierarchy_confidence'] = 'deterministic';
			}
			$section['meta'] = $meta;

			if ( ! in_array( $kind, array( 'paragraph', 'quote', 'list_item' ), true ) ) {
				continue;
			}
			$parsed = self::parse_token( (string) ( $section['text'] ?? '' ) );
			if ( null === $parsed ) {
				continue;
			}
			$context = self::context_key( $section );
			$contexts[ $context ][] = array(
				'ordinal'        => $ordinal,
				'kind'           => $kind,
				'token'          => $parsed['token'],
				'parts'          => $parsed['parts'],
				'depth'          => count( $parsed['parts'] ),
				'parent_token'   => count( $parsed['parts'] ) > 1 ? implode( '.', array_slice( $parsed['parts'], 0, -1 ) ) : '',
				'item_id'        => (string) ( $meta['item_id'] ?? '' ),
				'parent_item_id' => (string) ( $meta['parent_item_id'] ?? '' ),
				'explicit'       => 'list_item' === $kind && '' !== (string) ( $meta['list_id'] ?? '' ),
			);
		}
		unset( $section );

		foreach ( $contexts as $context_candidates ) {
			if ( count( $context_candidates ) < 2 ) {
				continue;
			}
			$seen = array();
			$proposals = array();
			$context_nested = 0;
			foreach ( $context_candidates as $candidate ) {
				$token = (string) $candidate['token'];
				if ( (int) $candidate['depth'] <= 1 ) {
					$seen[ $token ] = $candidate;
					continue;
				}
				++$context_nested;
				$parent_token = (string) $candidate['parent_token'];
				if ( isset( $seen[ $parent_token ] ) ) {
					$proposals[] = array( 'parent' => $seen[ $parent_token ], 'child' => $candidate );
				} else {
					$proposals[] = array( 'parent' => null, 'child' => $candidate );
				}
				$seen[ $token ] = $candidate;
			}
			if ( 0 === $context_nested ) {
				continue;
			}

			foreach ( $proposals as $proposal ) {
				$child = $proposal['child'];
				$parent = $proposal['parent'];
				if ( ! is_array( $parent ) ) {
					++$strong_signal_count;
					$warnings[] = 'HIERARCHY_AMBIGUOUS:ordinal:' . (int) $child['ordinal'] . ':token:' . (string) $child['token'];
					self::mark_ambiguous( $out, (int) $child['ordinal'], (string) $child['token'], (int) $child['depth'] );
					continue;
				}

				++$strong_signal_count;
				$parent_ordinal = (int) $parent['ordinal'];
				$child_ordinal  = (int) $child['ordinal'];
				if ( true === (bool) $child['explicit'] ) {
					$matches_explicit = '' !== (string) $child['parent_item_id']
						&& '' !== (string) $parent['item_id']
						&& hash_equals( (string) $child['parent_item_id'], (string) $parent['item_id'] );
					if ( ! $matches_explicit ) {
						$warnings[] = 'HIERARCHY_NUMBERING_CONFLICT:ordinal:' . $child_ordinal . ':token:' . (string) $child['token'];
						self::mark_conflict( $out, $child_ordinal, (string) $child['token'], (int) $child['depth'] );
						continue;
					}
					$edges[] = array(
						'parent_ordinal'       => $parent_ordinal,
						'child_ordinal'        => $child_ordinal,
						'hierarchy_source'     => 'explicit_dom',
						'hierarchy_confidence' => 'authoritative',
					);
					continue;
				}

				self::mark_inferred( $out, $parent_ordinal, (string) $parent['token'], (int) $parent['depth'], null );
				self::mark_inferred( $out, $child_ordinal, (string) $child['token'], (int) $child['depth'], $parent_ordinal );
				$edges[] = array(
					'parent_ordinal'       => $parent_ordinal,
					'child_ordinal'        => $child_ordinal,
					'hierarchy_source'     => 'numbering_inferred',
					'hierarchy_confidence' => 'deterministic',
				);
				++$resolved_edges;
			}
		}

		foreach ( $out as $ordinal => $section ) {
			$meta = is_array( $section['meta'] ?? null ) ? $section['meta'] : array();
			if ( ! isset( $meta['hierarchy_token'] ) && 'flat' === (string) ( $meta['hierarchy_source'] ?? '' ) ) {
				continue;
			}
			$node = array(
				'ordinal'              => $ordinal,
				'hierarchy_source'     => (string) ( $meta['hierarchy_source'] ?? 'flat' ),
				'hierarchy_confidence' => (string) ( $meta['hierarchy_confidence'] ?? 'deterministic' ),
			);
			if ( isset( $meta['hierarchy_token'] ) ) {
				$node['token'] = (string) $meta['hierarchy_token'];
				$node['depth'] = (int) ( $meta['hierarchy_depth'] ?? 0 );
				$node['parent_ordinal'] = isset( $meta['hierarchy_parent_ordinal'] ) ? (int) $meta['hierarchy_parent_ordinal'] : null;
			}
			$nodes[] = $node;
		}

		$warnings = array_values( array_unique( $warnings ) );
		$status = 'none';
		if ( ! empty( $warnings ) ) {
			$status = 'ambiguous';
		} elseif ( $resolved_edges > 0 ) {
			$status = 'resolved';
		}

		return array(
			'sections'            => $out,
			'status'              => $status,
			'strong_signal_count' => $strong_signal_count,
			'resolved_edges'      => $resolved_edges,
			'nodes'               => $nodes,
			'edges'               => $edges,
			'warnings'            => $warnings,
		);
	}

	/** @return array{token:string,parts:array<int,string>}|null */
	private static function parse_token( string $text ): ?array {
		if ( ! preg_match( '/^\s*(\d+(?:\.\d+){0,2})([.)]?)(?:\s+|\x{00A0}+)(\S.*)$/u', $text, $match ) ) {
			return null;
		}
		$token = (string) $match[1];
		$parts = explode( '.', $token );
		foreach ( $parts as $part ) {
			if ( '' === $part || ( strlen( $part ) > 1 && '0' === $part[0] ) || (int) $part > 999 ) {
				return null;
			}
		}
		return array( 'token' => $token, 'parts' => $parts );
	}

	/** @param array<string,mixed> $section */
	private static function context_key( array $section ): string {
		$source = (string) ( $section['source'] ?? '' );
		$path = is_array( $section['heading_path'] ?? null ) ? $section['heading_path'] : array();
		$projection = array();
		foreach ( $path as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$projection[] = array(
				'level' => (int) ( $item['level'] ?? 0 ),
				'text_hash' => hash( 'sha256', (string) ( $item['text'] ?? '' ) ),
			);
		}
		return hash( 'sha256', $source . "\n" . ( json_encode( $projection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ?: '' ) );
	}

	/** @param array<int,array<string,mixed>> $sections */
	private static function mark_inferred( array &$sections, int $ordinal, string $token, int $depth, ?int $parent_ordinal ): void {
		if ( ! isset( $sections[ $ordinal ] ) ) {
			return;
		}
		$meta = is_array( $sections[ $ordinal ]['meta'] ?? null ) ? $sections[ $ordinal ]['meta'] : array();
		if ( 'explicit_dom' === (string) ( $meta['hierarchy_source'] ?? '' ) ) {
			return;
		}
		$meta['hierarchy_source'] = 'numbering_inferred';
		$meta['hierarchy_confidence'] = 'deterministic';
		$meta['hierarchy_token'] = $token;
		$meta['hierarchy_depth'] = $depth;
		if ( null !== $parent_ordinal ) {
			$meta['hierarchy_parent_ordinal'] = $parent_ordinal;
		}
		$sections[ $ordinal ]['meta'] = $meta;
	}

	/** @param array<int,array<string,mixed>> $sections */
	private static function mark_ambiguous( array &$sections, int $ordinal, string $token, int $depth ): void {
		if ( ! isset( $sections[ $ordinal ] ) ) {
			return;
		}
		$meta = is_array( $sections[ $ordinal ]['meta'] ?? null ) ? $sections[ $ordinal ]['meta'] : array();
		if ( 'explicit_dom' !== (string) ( $meta['hierarchy_source'] ?? '' ) ) {
			$meta['hierarchy_source'] = 'numbering_inferred';
		}
		$meta['hierarchy_confidence'] = 'ambiguous';
		$meta['hierarchy_token'] = $token;
		$meta['hierarchy_depth'] = $depth;
		$sections[ $ordinal ]['meta'] = $meta;
	}

	/** @param array<int,array<string,mixed>> $sections */
	private static function mark_conflict( array &$sections, int $ordinal, string $token, int $depth ): void {
		if ( ! isset( $sections[ $ordinal ] ) ) {
			return;
		}
		$meta = is_array( $sections[ $ordinal ]['meta'] ?? null ) ? $sections[ $ordinal ]['meta'] : array();
		$meta['hierarchy_confidence'] = 'ambiguous';
		$meta['hierarchy_token'] = $token;
		$meta['hierarchy_depth'] = $depth;
		$sections[ $ordinal ]['meta'] = $meta;
	}
}
