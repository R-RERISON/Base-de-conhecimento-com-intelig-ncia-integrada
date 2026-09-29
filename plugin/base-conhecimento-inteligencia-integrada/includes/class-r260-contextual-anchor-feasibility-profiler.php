<?php
/**
 * R-260D1 — read-only contextual anchor feasibility profiler.
 *
 * Diagnostic wrapper over the canonical R-260 contextual resolver.
 *
 * @package BDC_Knowledge_Base
 */
namespace BDC\KnowledgeBase;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class R260_Contextual_Anchor_Feasibility_Profiler {
	public const VERSION = 'r260-contextual-anchor-feasibility-v1.0.0';
	private const MAX_SAMPLES_PER_POST = 8;

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 * @param array<string,mixed> $hierarchy
	 * @param array<int,array<string,mixed>> $classified_candidates
	 * @return array<string,mixed>
	 */
	public static function profile(
		int $post_id,
		string $source_kind,
		string $rendered_html,
		array $fragments,
		array $hierarchy,
		array $classified_candidates
	): array {
		$states=array(
			'title_unique'=>0,'context_unique'=>0,'context_ambiguous'=>0,
			'context_insufficient'=>0,'context_not_matched'=>0,'title_not_rendered'=>0
		);
		$samples=array(); $promotable=0;
		$boundaries=R260_Contextual_Anchor_Resolver::boundary_ordinals($fragments,$hierarchy);

		foreach($classified_candidates as $candidate){
			if(!is_array($candidate)||'promotable_shadow'!==(string)($candidate['state']??'')){continue;}
			$ordinal=(int)($candidate['ordinal']??-1); $title_norm=(string)($candidate['title_norm']??'');
			if($ordinal<0||''===$title_norm){continue;}
			++$promotable;
			$context=R260_Contextual_Anchor_Resolver::source_context_parts($fragments,$boundaries,$ordinal);
			$resolution=R260_Contextual_Anchor_Resolver::resolve_target($rendered_html,$title_norm,$context);
			$state=(string)($resolution['state']??'title_not_rendered');
			if(!array_key_exists($state,$states)){$state='title_not_rendered';}
			++$states[$state];

			if(count($samples)<self::MAX_SAMPLES_PER_POST){
				$samples[]=array(
					'ordinal'=>$ordinal,
					'token'=>(string)($candidate['token']??''),
					'text_hash'=>hash('sha256',$title_norm),
					'state'=>$state,
					'title_match_count'=>(int)($resolution['title_match_count']??0),
					'context_part_count'=>count($context),
					'context_hash'=>empty($context)?'':hash('sha256',implode("\n",$context)),
					'contextual_match_count'=>(int)($resolution['contextual_match_count']??0),
				);
			}
		}

		$effective=(int)$states['title_unique']+(int)$states['context_unique'];
		return array(
			'version'=>self::VERSION,
			'post_id'=>$post_id,
			'source_kind'=>$source_kind,
			'promotable_count'=>$promotable,
			'states'=>$states,
			'context_parts_required'=>R260_Contextual_Anchor_Resolver::CONTEXT_PARTS_REQUIRED,
			'effective_unique_count'=>$effective,
			'effective_unique_rate'=>$promotable>0?(float)$effective/$promotable:0.0,
			'context_resolved_count'=>(int)$states['context_unique'],
			'unresolved_remaining_count'=>max(0,$promotable-$effective),
			'samples'=>$samples,
			'interpretation'=>'diagnostic_only_contextual_disambiguation_no_anchor_injection',
		);
	}
}
