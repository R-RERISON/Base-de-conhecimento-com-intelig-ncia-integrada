<?php
namespace BDC\KnowledgeBase;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Block_Migration_Journal_Store {
	public const META_KEY = '_bdc_kb_block_migration_journal';
	public const EVENT_SCHEMA = 1;
	public const MAX_EVENT_BYTES = 16777216;

	public static function persist_prepared( array $record ): array|\WP_Error {
		$record=Block_Migration_Journal::mark_persisted($record);if($record instanceof \WP_Error)return $record;
		if(Block_Migration_Journal::STATE_PREPARED!==(string)($record['state']??''))return new \WP_Error('bdc_kb_block_store_state','Somente prepared.');
		return self::append_event($record,0);
	}
	public static function persist_transition(array $record,int $parent_event_id):array|\WP_Error{
		if($parent_event_id<=0)return new \WP_Error('bdc_kb_block_store_parent','parent_event_id inválido.');
		$v=Block_Migration_Journal::validate_record($record,true);if($v instanceof \WP_Error)return $v;
		$state=(string)($record['state']??'');
		if(!in_array($state,array(Block_Migration_Journal::STATE_APPLIED,Block_Migration_Journal::STATE_PARTIAL_FAILURE,Block_Migration_Journal::STATE_ROLLED_BACK),true))return new \WP_Error('bdc_kb_block_store_transition','Estado não persistível.');
		$parent=self::read_event($parent_event_id);if($parent instanceof \WP_Error)return $parent;
		$parent_record=is_array($parent['record']??null)?$parent['record']:array();
		if((string)($parent_record['journal_id']??'')!==(string)($record['journal_id']??'')
			||(int)($parent_record['post_id']??0)!==(int)($record['post_id']??0))return new \WP_Error('bdc_kb_block_store_identity','Journal/post pai divergente.');
		if(!self::transition_allowed((string)($parent_record['state']??''),$state))return new \WP_Error('bdc_kb_block_store_parent_state','Transição incompatível com o estado pai.');
		$latest=self::latest_for_post((int)($record['post_id']??0));
		if($latest instanceof \WP_Error||(int)($latest['event_id']??0)!==$parent_event_id
			||(string)($latest['record']['journal_id']??'')!==(string)($record['journal_id']??''))return new \WP_Error('bdc_kb_block_store_chain','Cadeia não linear.');
		return self::append_event($record,$parent_event_id);
	}
	public static function read_event(int $event_id):array|\WP_Error{
		if($event_id<=0)return new \WP_Error('bdc_kb_block_store_event','event_id inválido.');
		$m=get_metadata_by_mid('post',$event_id);if(!is_object($m)||self::META_KEY!==(string)($m->meta_key??''))return new \WP_Error('bdc_kb_block_store_not_found','Evento não encontrado.');
		$d=json_decode((string)($m->meta_value??''),true);if(!is_array($d)||self::EVENT_SCHEMA!==(int)($d['event_schema']??0)||'core_blocks'!==(string)($d['family']??''))return new \WP_Error('bdc_kb_block_store_malformed','Evento malformado.');
		$r=self::decode_record(is_array($d['record']??null)?$d['record']:array());if($r instanceof \WP_Error)return $r;
		$v=Block_Migration_Journal::validate_record($r,true);if($v instanceof \WP_Error)return $v;
		if((int)($r['post_id']??0)!==(int)($m->post_id??0))return new \WP_Error('bdc_kb_block_store_post','Post divergente.');
		if((string)($d['state']??'')!==(string)($r['state']??''))return new \WP_Error('bdc_kb_block_store_state','Estado externo divergente.');
		if((string)($d['journal_id']??'')!==(string)($r['journal_id']??'')||(string)($d['run_id']??'')!==(string)($r['run_id']??''))return new \WP_Error('bdc_kb_block_store_envelope_identity','Identidade externa divergente.');
		$parent=(int)($d['parent_event_id']??-1);$actor=(int)($d['actor_id']??0);$created=(string)($d['created_at_gmt']??'');
		if($parent<0||$actor<=0||''===$created)return new \WP_Error('bdc_kb_block_store_envelope','Envelope incompleto.');
		return array('event_id'=>(int)($m->meta_id??0),'parent_event_id'=>$parent,'actor_id'=>$actor,'created_at_gmt'=>$created,'record'=>$r);
	}
	public static function latest_for_post(int $post_id):array|\WP_Error{
		if($post_id<=0)return new \WP_Error('bdc_kb_block_store_post','post_id inválido.');
		global $wpdb;if(!is_object($wpdb)||!isset($wpdb->postmeta))return new \WP_Error('bdc_kb_block_store_db','DB indisponível.');
		$sql=$wpdb->prepare("SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s ORDER BY meta_id DESC LIMIT 1",$post_id,self::META_KEY);
		$id=(int)$wpdb->get_var($sql);if($id<=0)return new \WP_Error('bdc_kb_block_store_empty','Sem eventos.');return self::read_event($id);
	}
	private static function append_event(array $record,int $parent):array|\WP_Error{
		$v=Block_Migration_Journal::validate_record($record,true);if($v instanceof \WP_Error)return $v;
		$id=(int)($record['post_id']??0);if($id<=0||!get_post($id))return new \WP_Error('bdc_kb_block_store_post','Post não existe.');
		$actor=(int)get_current_user_id();if($actor<=0||!current_user_can('manage_options'))return new \WP_Error('bdc_kb_block_store_forbidden','manage_options obrigatório.');
		$portable=self::encode_record($record);if($portable instanceof \WP_Error)return $portable;
		$json=wp_json_encode(array('event_schema'=>self::EVENT_SCHEMA,'family'=>'core_blocks','parent_event_id'=>$parent,'journal_id'=>(string)$record['journal_id'],'run_id'=>(string)$record['run_id'],'state'=>(string)$record['state'],'actor_id'=>$actor,'created_at_gmt'=>gmdate('c'),'record'=>$portable),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		if(!is_string($json)||''===$json||strlen($json)>self::MAX_EVENT_BYTES)return new \WP_Error('bdc_kb_block_store_payload','Payload inválido/grande.');
		$mid=(int)add_post_meta($id,self::META_KEY,$json,false);if($mid<=0)return new \WP_Error('bdc_kb_block_store_write','Falha journal write.');
		$rb=self::read_event($mid);if(!($rb instanceof \WP_Error)&&self::readback_matches($rb,$record,$parent,$actor))return $rb;
		if(delete_metadata_by_mid('post',$mid))return new \WP_Error('bdc_kb_block_store_failsafe','Readback divergente; evento removido.');
		error_log(sprintf('[BDC-KB][BLOCK_JOURNAL_PARTIAL_FAILURE_CRITICAL] post_id=%d event_id=%d',$id,$mid));
		return new \WP_Error('bdc_kb_block_store_critical','Evento divergente não pôde ser removido.');
	}
	private static function transition_allowed(string $from,string $to):bool{
		if(Block_Migration_Journal::STATE_PREPARED===$from)return in_array($to,array(Block_Migration_Journal::STATE_APPLIED,Block_Migration_Journal::STATE_PARTIAL_FAILURE),true);
		if(in_array($from,array(Block_Migration_Journal::STATE_APPLIED,Block_Migration_Journal::STATE_PARTIAL_FAILURE),true))return Block_Migration_Journal::STATE_ROLLED_BACK===$to;
		return false;
	}
	private static function readback_matches(array $readback,array $record,int $parent,int $actor):bool{
		$actual=$readback['record']??null;
		return is_array($actual)
			&&(int)($readback['parent_event_id']??-1)===$parent
			&&(int)($readback['actor_id']??0)===$actor
			&&(string)($actual['journal_id']??'')===(string)($record['journal_id']??'')
			&&(string)($actual['journal_hash']??'')===(string)($record['journal_hash']??'')
			&&(string)($actual['rollback_payload_hash']??'')===(string)($record['rollback_payload_hash']??'');
	}
	private static function encode_record(array $r):array|\WP_Error{
		$p=$r['rollback_payload']??null;if(!is_array($p)||!is_string($p['post_content']??null)||!is_string($p['elementor_data']??null))return new \WP_Error('bdc_kb_block_store_capsule','Capsule inválida.');
		$r['rollback_payload']=array('encoding'=>'base64','post_content'=>base64_encode($p['post_content']),'elementor_data'=>base64_encode($p['elementor_data']));return $r;
	}
	private static function decode_record(array $r):array|\WP_Error{
		$p=$r['rollback_payload']??null;if(!is_array($p)||'base64'!==(string)($p['encoding']??''))return new \WP_Error('bdc_kb_block_store_encoding','Encoding inválido.');
		$c=base64_decode((string)($p['post_content']??''),true);$e=base64_decode((string)($p['elementor_data']??''),true);if(false===$c||false===$e)return new \WP_Error('bdc_kb_block_store_decode','Decode inválido.');
		$r['rollback_payload']=array('post_content'=>$c,'elementor_data'=>$e);return $r;
	}
}
