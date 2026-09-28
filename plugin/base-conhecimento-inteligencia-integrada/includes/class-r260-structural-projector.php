<?php
/**
 * R-260D2 — canonical derived structural projection.
 *
 * @package BDC_Knowledge_Base
 */
namespace BDC\KnowledgeBase;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class R260_Structural_Projector {
	public const VERSION = 'r260-structural-projection-v1.0.1';
	private const BODY_KINDS = array('paragraph','list_item','table_caption','table_row','quote','code','image');

	/** @param array<int,array<string,mixed>> $fragments @return array<string,mixed> */
	public static function project(int $post_id,string $source_kind,array $fragments,string $rendered_html=''):array {
		$fragments=array_values($fragments);
		$hierarchy=Numbered_Hierarchy_Resolver::resolve($fragments);
		$boundaries=R260_Contextual_Anchor_Resolver::boundary_ordinals($fragments,$hierarchy);

		$heading_labels=array();
		foreach($fragments as $fragment){
			if(!is_array($fragment)||'heading'!==(string)($fragment['kind']??'')){continue;}
			$label=Search_Query_Normalizer::normalize_document_text((string)($fragment['text']??''));
			if(''!==$label){$heading_labels[$label]=(int)($heading_labels[$label]??0)+1;}
		}

		/*
		 * R-260A semantics: occurrence evidence is collected from every hierarchy
		 * node first, and only then is the runtime candidate subset filtered.
		 *
		 * This is intentional. An early deterministic paragraph can be TOC-like
		 * because the same label appears later in a node that is not itself a
		 * runtime candidate (for example, because it already has heading context
		 * or its hierarchy confidence differs). Restricting occurrences to the
		 * candidate subset silently changes the R-260A TOC/uncertain partition.
		 */
		$label_occurrences=array(); $token_occurrences=array();
		foreach((array)($hierarchy['nodes']??array()) as $node){
			if(!is_array($node)){continue;}
			$ordinal=(int)($node['ordinal']??-1);
			if($ordinal<0||!isset($fragments[$ordinal])||!is_array($fragments[$ordinal])){continue;}
			$fragment=$fragments[$ordinal];
			$title_norm=Search_Query_Normalizer::normalize_document_text((string)($fragment['text']??''));
			$token=(string)($node['token']??'');
			if(''!==$title_norm){$label_occurrences[$title_norm][]=$ordinal;}
			if(''!==$token){$token_occurrences[$token][]=$ordinal;}
		}

		$candidates=array();
		foreach((array)($hierarchy['nodes']??array()) as $node){
			if(!is_array($node)){continue;}
			$ordinal=(int)($node['ordinal']??-1);
			if($ordinal<0||!isset($fragments[$ordinal])||!is_array($fragments[$ordinal])){continue;}
			$fragment=$fragments[$ordinal];
			if(
				'numbering_inferred'!==(string)($node['hierarchy_source']??'')
				||'deterministic'!==(string)($node['hierarchy_confidence']??'')
				||(int)($node['depth']??0)<=1
				||'paragraph'!==(string)($fragment['kind']??'')
				||self::has_heading_context($fragments,$ordinal)
			){continue;}
			$title_norm=Search_Query_Normalizer::normalize_document_text((string)($fragment['text']??''));
			if(''===$title_norm){continue;}
			$candidates[]=array(
				'ordinal'=>$ordinal,
				'token'=>(string)($node['token']??''),
				'depth'=>(int)($node['depth']??0),
				'title_norm'=>$title_norm,
				'body_span'=>self::body_span($fragments,$boundaries,$ordinal),
			);
		}

		$candidate_label_counts=array();
		foreach($candidates as $candidate){
			$label=(string)$candidate['title_norm'];
			$candidate_label_counts[$label]=(int)($candidate_label_counts[$label]??0)+1;
		}

		$states=array('toc_suppressed'=>0,'body_projected'=>0,'existing_heading_redundant'=>0,'uncertain'=>0);
		$classified=array(); $nodes=array(); $identity_occurrences=array();
		$generated=0; $unresolved=0; $duplicate_body=0;
		$count=count($fragments);

		foreach($candidates as $candidate){
			$ordinal=(int)$candidate['ordinal']; $title_norm=(string)$candidate['title_norm'];
			$token=(string)$candidate['token']; $depth=(int)$candidate['depth']; $body_span=(int)$candidate['body_span'];
			$ratio=$count>1?$ordinal/max(1,$count-1):0.0;
			$bucket=$ratio<=0.25?'early':($ratio<=0.75?'middle':'late');
			$later_label=self::has_later((array)($label_occurrences[$title_norm]??array()),$ordinal);
			$later_token=self::has_later((array)($token_occurrences[$token]??array()),$ordinal);
			$toc='early'===$bucket&&$later_label&&$body_span<=1;
			$body=$body_span>=2;
			$heading_collision=(int)($heading_labels[$title_norm]??0);
			$label_count=(int)($candidate_label_counts[$title_norm]??0);

			if($toc){$state='toc_suppressed';}
			elseif(!$body){$state='uncertain';}
			elseif($heading_collision>0){$state='existing_heading_redundant';}
			else{$state='body_projected';}
			++$states[$state];

			$classified[]=array(
				'ordinal'=>$ordinal,'token'=>$token,'depth'=>$depth,'title_norm'=>$title_norm,'state'=>$state,
				'body_span'=>$body_span,'position_bucket'=>$bucket,'later_same_label'=>$later_label,'later_same_token'=>$later_token,
				'toc_signal'=>$toc,'body_signal'=>$body,'heading_collision_count'=>$heading_collision,'candidate_label_count'=>$label_count,
			);
			if('body_projected'!==$state){continue;}
			if($label_count>1){++$duplicate_body;}

			$fragment=$fragments[$ordinal];
			$title=trim(wp_strip_all_tags(html_entity_decode((string)($fragment['text']??''),ENT_QUOTES|ENT_HTML5,'UTF-8')));
			if(''===$title){continue;}
			$identity_base=$depth.'|'.$token.'|'.$title_norm;
			$occ=(int)($identity_occurrences[$identity_base]??0)+1; $identity_occurrences[$identity_base]=$occ;
			$key=hash('sha256',$post_id.'|numbered_paragraph|'.$identity_base.'|'.$occ);
			$context=R260_Contextual_Anchor_Resolver::source_context_parts($fragments,$boundaries,$ordinal);
			$resolution=''!==$rendered_html
				?R260_Contextual_Anchor_Resolver::resolve_target($rendered_html,$title_norm,$context)
				:array('state'=>'not_evaluated','title_match_count'=>0,'context_part_count'=>count($context),'contextual_match_count'=>0,'target'=>null);
			$target_state=(string)($resolution['state']??'not_evaluated');
			$anchor_generated=in_array($target_state,array('title_unique','context_unique'),true);
			$anchor_generated?++$generated:++$unresolved;

			$nodes[]=array(
				'section_key'=>$key,
				'source_kind'=>$source_kind,
				'structural_source'=>'numbered_paragraph',
				'source_ordinal'=>max(0,(int)($fragment['ordinal']??$ordinal)),
				'fragment_index'=>$ordinal,
				'level'=>max(2,min(6,$depth)),
				'hierarchy_token'=>$token,
				'hierarchy_depth'=>$depth,
				'title'=>$title,
				'title_norm'=>$title_norm,
				'path_norm'=>$title_norm,
				'text_norm'=>self::section_text($fragments,$boundaries,$ordinal),
				'identity_occurrence'=>$occ,
				'duplicate_label'=>$label_count>1,
				'target_state'=>$target_state,
				'anchor_strategy'=>$anchor_generated?$target_state:'none',
				'anchor_context_parts'=>'context_unique'===$target_state
					?array_slice($context,0,R260_Contextual_Anchor_Resolver::CONTEXT_PARTS_REQUIRED):array(),
				'anchor_generated'=>$anchor_generated,
				'title_match_count'=>(int)($resolution['title_match_count']??0),
				'contextual_match_count'=>(int)($resolution['contextual_match_count']??0),
			);
		}

		$keys=array(); $collisions=0;
		foreach($nodes as $node){
			$key=(string)($node['section_key']??'');
			if(''===$key||isset($keys[$key])){++$collisions;}
			if(''!==$key){$keys[$key]=true;}
		}

		return array(
			'version'=>self::VERSION,'post_id'=>$post_id,'source_kind'=>$source_kind,'candidate_count'=>count($candidates),
			'states'=>$states,'body_bearing_count'=>(int)$states['body_projected']+(int)$states['existing_heading_redundant'],
			'projected_count'=>count($nodes),'duplicate_body_count'=>$duplicate_body,
			'generated_anchor_count'=>$generated,'unresolved_anchor_count'=>$unresolved,'identity_collision_count'=>$collisions,
			'classified_candidates'=>$classified,'nodes'=>$nodes,'interpretation'=>'runtime_read_only_structural_projection'
		);
	}

	/** @param array<int,array<string,mixed>> $fragments */
	private static function has_heading_context(array $fragments,int $ordinal):bool{
		$fragment=is_array($fragments[$ordinal]??null)?$fragments[$ordinal]:array();
		$path=is_array($fragment['heading_path']??null)?$fragment['heading_path']:array();
		if(!empty($path)){return true;}
		for($i=$ordinal-1;$i>=0;--$i){
			$prev=is_array($fragments[$i]??null)?$fragments[$i]:array();
			if('heading'===(string)($prev['kind']??'')){return true;}
		}
		return false;
	}

	/** @param array<int,int> $occurrences */
	private static function has_later(array $occurrences,int $ordinal):bool{
		foreach($occurrences as $value){if((int)$value>$ordinal){return true;}}
		return false;
	}

	/** @param array<int,array<string,mixed>> $fragments @param array<int,int> $boundaries */
	private static function body_span(array $fragments,array $boundaries,int $ordinal):int{
		return count(self::body_parts($fragments,$boundaries,$ordinal,false));
	}

	/** @param array<int,array<string,mixed>> $fragments @param array<int,int> $boundaries */
	private static function section_text(array $fragments,array $boundaries,int $ordinal):string{
		return trim(implode(' ',self::body_parts($fragments,$boundaries,$ordinal,true)));
	}

	/** @param array<int,array<string,mixed>> $fragments @param array<int,int> $boundaries @return array<int,string> */
	private static function body_parts(array $fragments,array $boundaries,int $ordinal,bool $normalized):array{
		$next=count($fragments);
		foreach($boundaries as $boundary){if($boundary>$ordinal){$next=$boundary;break;}}
		$parts=array();
		for($i=$ordinal+1;$i<$next;++$i){
			$fragment=is_array($fragments[$i]??null)?$fragments[$i]:array();
			$kind=(string)($fragment['kind']??''); $text=trim((string)($fragment['text']??''));
			if(''===$text||!in_array($kind,self::BODY_KINDS,true)){continue;}
			$value=$normalized?Search_Query_Normalizer::normalize_document_text($text):$text;
			if(''!==$value){$parts[]=$value;}
		}
		return $parts;
	}
}
