<?php
/**
 * Projeção determinística de seções para Search SPEC-005 / G-590.
 *
 * Combina headings autoritativos com R-260 Structural Projection derivada.
 *
 * @package BDC_Knowledge_Base
 */
namespace BDC\KnowledgeBase;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Search_Section_Projector {
	public const VERSION='search-section-projection-v1.1.0';
	public const MAX_SECTIONS=64;
	public const MAX_TEXT_CHARS=4000;
	public const ANCHOR_PREFIX='bdc-kb-section-';

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 * @param array<string,mixed>|null $structural_projection
	 * @return array<int,array<string,mixed>>
	 */
	public static function project(
		int $post_id,
		array $fragments,
		string $source_kind='unknown',
		string $rendered_html='',
		?array $structural_projection=null
	):array{
		$result=self::project_with_diagnostics($post_id,$fragments,$source_kind,$rendered_html,$structural_projection);
		return (array)($result['sections']??array());
	}

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 * @param array<string,mixed>|null $structural_projection
	 * @return array<string,mixed>
	 */
	public static function project_with_diagnostics(
		int $post_id,
		array $fragments,
		string $source_kind='unknown',
		string $rendered_html='',
		?array $structural_projection=null
	):array{
		if($post_id<=0||empty($fragments)){
			return array(
				'sections'=>array(),'heading_count'=>0,'structural_count'=>0,'total_before_bound'=>0,
				'overflow_by'=>0,'structural_projection_version'=>R260_Structural_Projector::VERSION,
			);
		}

		$heading_sections=self::project_headings($post_id,$fragments);
		$structural=is_array($structural_projection)
			?$structural_projection
			:R260_Structural_Projector::project($post_id,$source_kind,$fragments,$rendered_html);

		$structural_sections=array();
		foreach((array)($structural['nodes']??array()) as $node){
			if(!is_array($node)){continue;}
			$key=(string)($node['section_key']??'');
			$title_norm=(string)($node['title_norm']??'');
			if(!preg_match('/^[a-f0-9]{64}$/',$key)||''===$title_norm){continue;}
			$generated=true===($node['anchor_generated']??false);
			$text_norm=self::bound_text((string)($node['text_norm']??''));
			$structural_sections[]=array(
				'section_key'=>$key,
				'ordinal'=>0,
				'source_ordinal'=>max(0,(int)($node['source_ordinal']??0)),
				'level'=>max(1,min(6,(int)($node['level']??2))),
				'title'=>(string)($node['title']??''),
				'title_norm'=>$title_norm,
				'path_norm'=>(string)($node['path_norm']??$title_norm),
				'text_norm'=>$text_norm,
				'anchor_id'=>$generated?self::ANCHOR_PREFIX.substr($key,0,20):'',
				'anchor_state'=>$generated?'generated':'unresolved',
				'projection_version'=>self::VERSION,
				'structural_source'=>'numbered_paragraph',
				'hierarchy_token'=>(string)($node['hierarchy_token']??''),
				'hierarchy_depth'=>(int)($node['hierarchy_depth']??0),
				'target_state'=>(string)($node['target_state']??'not_evaluated'),
				'anchor_strategy'=>$generated?(string)($node['anchor_strategy']??'none'):'none',
				'anchor_context_parts'=>$generated
					?array_values(array_map('strval',(array)($node['anchor_context_parts']??array())))
					:array(),
				'duplicate_label'=>true===($node['duplicate_label']??false),
			);
		}

		$combined=array_merge($heading_sections,$structural_sections);
		usort($combined,static function(array $left,array $right):int{
			$ordinal=(int)($left['source_ordinal']??0)<=>(int)($right['source_ordinal']??0);
			if(0!==$ordinal){return $ordinal;}
			$left_struct=isset($left['structural_source'])?1:0;
			$right_struct=isset($right['structural_source'])?1:0;
			if($left_struct!==$right_struct){return $left_struct<=>$right_struct;}
			return strcmp((string)($left['section_key']??''),(string)($right['section_key']??''));
		});

		$total=count($combined);
		$overflow=max(0,$total-self::MAX_SECTIONS);
		$combined=array_slice($combined,0,self::MAX_SECTIONS);
		foreach($combined as $index=>&$section){
			$section['ordinal']=$index;
			$section['projection_version']=self::VERSION;
		}
		unset($section);

		return array(
			'sections'=>array_values($combined),
			'heading_count'=>count($heading_sections),
			'structural_count'=>count($structural_sections),
			'total_before_bound'=>$total,
			'overflow_by'=>$overflow,
			'structural_projection_version'=>(string)($structural['version']??R260_Structural_Projector::VERSION),
			'structural_projected_count'=>(int)($structural['projected_count']??count($structural_sections)),
			'structural_generated_anchor_count'=>(int)($structural['generated_anchor_count']??0),
			'structural_unresolved_anchor_count'=>(int)($structural['unresolved_anchor_count']??0),
		);
	}

	/** @param array<int,array<string,mixed>> $fragments @return array<int,array<string,mixed>> */
	private static function project_headings(int $post_id,array $fragments):array{
		$sections=array(); $stack=array(); $current=null; $identity_occurrences=array();

		foreach($fragments as $index=>$fragment){
			if(!is_array($fragment)){continue;}
			$kind=sanitize_key((string)($fragment['kind']??''));
			$text=trim((string)($fragment['text']??''));
			$source_ordinal=max(0,(int)($fragment['ordinal']??$index));

			if('heading'===$kind){
				if(is_array($current)){$sections[]=self::finalize($current);}
				$title=trim(wp_strip_all_tags(html_entity_decode($text,ENT_QUOTES|ENT_HTML5,'UTF-8')));
				$title_norm=Search_Query_Normalizer::normalize_document_text($title);
				if(''===$title_norm){$current=null;continue;}
				$meta=is_array($fragment['meta']??null)?$fragment['meta']:array();
				$level=max(1,min(6,(int)($meta['level']??2)));
				foreach(array_keys($stack) as $existing_level){
					if((int)$existing_level>=$level){unset($stack[$existing_level]);}
				}
				$stack[$level]=$title_norm; ksort($stack,SORT_NUMERIC);
				$path=array_values($stack); $path_norm=implode(' > ',$path);
				$base=$level.'|'.$path_norm;
				$occ=(int)($identity_occurrences[$base]??0)+1; $identity_occurrences[$base]=$occ;
				$key=hash('sha256',$post_id.'|'.$level.'|'.$path_norm.'|'.$occ);
				$current=array(
					'section_key'=>$key,'ordinal'=>0,'source_ordinal'=>$source_ordinal,'level'=>$level,
					'title'=>$title,'title_norm'=>$title_norm,'path_norm'=>$path_norm,'text_parts'=>array(),
					'anchor_id'=>self::ANCHOR_PREFIX.substr($key,0,20),'anchor_state'=>'generated',
					'projection_version'=>self::VERSION,
				);
				continue;
			}

			if(!is_array($current)||''===$text){continue;}
			if(in_array($kind,array('paragraph','list_item','table_caption','table_row','quote','code','image'),true)){
				$normalized=Search_Query_Normalizer::normalize_document_text($text);
				if(''!==$normalized){$current['text_parts'][]=$normalized;}
			}
		}
		if(is_array($current)){$sections[]=self::finalize($current);}

		$title_counts=array();
		foreach($sections as $section){
			$title_norm=(string)($section['title_norm']??'');
			if(''!==$title_norm){$title_counts[$title_norm]=(int)($title_counts[$title_norm]??0)+1;}
		}
		foreach($sections as &$section){
			$title_norm=(string)($section['title_norm']??'');
			if(''===$title_norm||1!==(int)($title_counts[$title_norm]??0)){
				$section['anchor_state']='unresolved'; $section['anchor_id']='';
			}
		}
		unset($section);
		return array_values($sections);
	}

	/** @param array<string,mixed> $section @return array<string,mixed> */
	private static function finalize(array $section):array{
		$text=trim(implode(' ',array_map('strval',(array)($section['text_parts']??array()))));
		unset($section['text_parts']);
		$section['text_norm']=self::bound_text($text);
		return $section;
	}

	private static function bound_text(string $value):string{
		if(self::char_length($value)>self::MAX_TEXT_CHARS){
			$value=self::truncate_chars($value,self::MAX_TEXT_CHARS);
			$value=rtrim($value);
		}
		return $value;
	}

	private static function char_length(string $value):int{
		if(function_exists('mb_strlen')){return mb_strlen($value,'UTF-8');}
		$count=preg_match_all('/./us',$value,$unused); return false===$count?strlen($value):$count;
	}
	private static function truncate_chars(string $value,int $limit):string{
		$limit=max(0,$limit);
		if(function_exists('mb_substr')){return mb_substr($value,0,$limit,'UTF-8');}
		if(0===$limit){return '';}
		$matched=preg_match('/^(.{0,'.$limit.'})/us',$value,$matches);
		return 1===$matched?(string)($matches[1]??''):substr($value,0,$limit);
	}
}
