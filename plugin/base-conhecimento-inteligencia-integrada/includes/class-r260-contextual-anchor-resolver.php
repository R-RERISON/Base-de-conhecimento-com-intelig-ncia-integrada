<?php
/**
 * R-260D2 — shared contextual resolver for paragraph-derived structural targets.
 *
 * @package BDC_Knowledge_Base
 */
namespace BDC\KnowledgeBase;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class R260_Contextual_Anchor_Resolver {
	public const VERSION = 'r260-contextual-anchor-resolver-v1.0.0';
	public const CONTEXT_PARTS_REQUIRED = 2;
	private const CONTEXT_SOURCE_KINDS = array( 'paragraph','list_item','table_caption','table_row','quote','code' );
	private const RENDERED_LEAF_TAGS = array( 'p','li','tr','pre','figcaption','caption' );

	/** @param array<int,array<string,mixed>> $fragments @param array<string,mixed> $hierarchy @return array<int,int> */
	public static function boundary_ordinals( array $fragments, array $hierarchy ): array {
		$boundaries = array();
		foreach ( $fragments as $ordinal => $fragment ) {
			if ( is_array( $fragment ) && 'heading' === (string) ( $fragment['kind'] ?? '' ) ) {
				$boundaries[(int)$ordinal] = true;
			}
		}
		foreach ( (array) ( $hierarchy['nodes'] ?? array() ) as $node ) {
			if ( is_array($node) && 'numbering_inferred' === (string)($node['hierarchy_source'] ?? '')
				&& (int)($node['depth'] ?? 0) > 1 ) {
				$ordinal = (int)($node['ordinal'] ?? -1);
				if ( $ordinal >= 0 ) { $boundaries[$ordinal] = true; }
			}
		}
		ksort( $boundaries, SORT_NUMERIC );
		return array_map( 'intval', array_keys( $boundaries ) );
	}

	/** @param array<int,array<string,mixed>> $fragments @param array<int,int> $boundaries @return array<int,string> */
	public static function source_context_parts( array $fragments, array $boundaries, int $ordinal ): array {
		$next = count($fragments);
		foreach ( $boundaries as $boundary ) {
			if ( $boundary > $ordinal ) { $next = $boundary; break; }
		}
		$parts = array();
		for ( $cursor=$ordinal+1; $cursor<$next; ++$cursor ) {
			$fragment = is_array($fragments[$cursor] ?? null) ? $fragments[$cursor] : array();
			$kind = (string)($fragment['kind'] ?? '');
			if ( ! in_array($kind,self::CONTEXT_SOURCE_KINDS,true) ) { continue; }
			$normalized = Search_Query_Normalizer::normalize_document_text((string)($fragment['text'] ?? ''));
			if ( '' === $normalized ) { continue; }
			$parts[] = $normalized;
			if ( count($parts) >= self::CONTEXT_PARTS_REQUIRED ) { break; }
		}
		return $parts;
	}

	/**
	 * @param array<int,string> $context_parts
	 * @return array{state:string,title_match_count:int,context_part_count:int,contextual_match_count:int,target:array<string,mixed>|null}
	 */
	public static function resolve_target( string $html, string $title_norm, array $context_parts ): array {
		$title_norm = trim($title_norm);
		$context_parts = array_values(array_filter(array_map('strval',$context_parts),static fn(string $v):bool=>''!==trim($v)));
		if ( '' === $html || '' === $title_norm ) {
			return array('state'=>'title_not_rendered','title_match_count'=>0,'context_part_count'=>count($context_parts),'contextual_match_count'=>0,'target'=>null);
		}
		$blocks = self::rendered_blocks($html);
		$title_indexes = self::rendered_title_indexes($blocks,$title_norm);
		$title_count = count($title_indexes);
		$contextual = array();
		$target = null;
		if ( 1 === $title_count ) {
			$state = 'title_unique';
			$target = $blocks[$title_indexes[0]] ?? null;
		} elseif ( 0 === $title_count ) {
			$state = 'title_not_rendered';
		} elseif ( count($context_parts) < self::CONTEXT_PARTS_REQUIRED ) {
			$state = 'context_insufficient';
		} else {
			foreach ( $title_indexes as $index ) {
				if ( self::context_matches_after($blocks,$index,$context_parts) ) { $contextual[] = $index; }
			}
			if ( 1 === count($contextual) ) {
				$state = 'context_unique';
				$target = $blocks[$contextual[0]] ?? null;
			} elseif ( count($contextual) > 1 ) {
				$state = 'context_ambiguous';
			} else {
				$state = 'context_not_matched';
			}
		}
		return array(
			'state'=>$state,'title_match_count'=>$title_count,'context_part_count'=>count($context_parts),
			'contextual_match_count'=>count($contextual),'target'=>is_array($target)?$target:null
		);
	}

	/** @return array<int,array<string,mixed>> */
	private static function rendered_blocks( string $html ): array {
		$blocks = array();
		foreach ( self::RENDERED_LEAF_TAGS as $tag ) {
			$pattern = '/<' . preg_quote($tag,'/') . '\\b([^>]*)>(.*?)<\\/' . preg_quote($tag,'/') . '>/is';
			$found = preg_match_all($pattern,$html,$matches,PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
			if ( false === $found || 0 === $found ) { continue; }
			foreach ( $matches as $match ) {
				$whole=(string)($match[0][0]??''); $offset=(int)($match[0][1]??-1);
				$attrs=(string)($match[1][0]??''); $inner=(string)($match[2][0]??'');
				if ( ''===$whole || $offset<0 ) { continue; }
				$norm=Search_Query_Normalizer::normalize_document_text($inner);
				if ( ''===$norm ) { continue; }
				$blocks[]=array('tag'=>$tag,'attrs'=>$attrs,'inner'=>$inner,'whole'=>$whole,'offset'=>$offset,'text_norm'=>$norm);
			}
		}
		usort($blocks,static function(array $a,array $b):int{
			$o=(int)$a['offset']<=>(int)$b['offset']; return 0!==$o?$o:strcmp((string)$a['tag'],(string)$b['tag']);
		});
		return array_values($blocks);
	}

	/** @param array<int,array<string,mixed>> $blocks @return array<int,int> */
	private static function rendered_title_indexes( array $blocks, string $title_norm ): array {
		$out=array();
		foreach($blocks as $i=>$block){
			if('p'===(string)($block['tag']??'') && $title_norm===(string)($block['text_norm']??'')){ $out[]=(int)$i; }
		}
		return $out;
	}

	/** @param array<int,array<string,mixed>> $blocks @param array<int,string> $context_parts */
	private static function context_matches_after( array $blocks, int $title_index, array $context_parts ): bool {
		for($i=0;$i<self::CONTEXT_PARTS_REQUIRED;++$i){
			$idx=$title_index+1+$i;
			if(!isset($blocks[$idx]) || (string)($blocks[$idx]['text_norm']??'') !== (string)($context_parts[$i]??'')){ return false; }
		}
		return true;
	}
}
