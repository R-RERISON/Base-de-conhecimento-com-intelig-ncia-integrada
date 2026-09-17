<?php
namespace BDC\KnowledgeBase;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Block_Migration_Lock {
	public const META_KEY = '_bdc_kb_block_migration_lock';
	public const SCHEMA_VERSION = 1;
	public const DEFAULT_TTL_SECONDS = 300;
	public const MAX_TTL_SECONDS = 900;

	public static function acquire( int $post_id, string $run_id, int $ttl_seconds = self::DEFAULT_TTL_SECONDS ): array|\WP_Error {
		$run_id=trim($run_id);
		if($post_id<=0||!get_post($post_id))return new \WP_Error('bdc_kb_block_lock_post','Post inválido.');
		if(1!==preg_match('/^[A-Za-z0-9._:-]{8,128}$/',$run_id))return new \WP_Error('bdc_kb_block_lock_run','run_id inválido.');
		if(!current_user_can('manage_options'))return new \WP_Error('bdc_kb_block_lock_forbidden','Lock exige manage_options.');
		$actor=(int)get_current_user_id(); if($actor<=0)return new \WP_Error('bdc_kb_block_lock_actor','Usuário autenticado obrigatório.');
		$ttl=max(30,min(self::MAX_TTL_SECONDS,$ttl_seconds));$now=time();$token=wp_generate_uuid4();
		$p=array('schema_version'=>self::SCHEMA_VERSION,'family'=>'core_blocks','run_id'=>$run_id,'token'=>$token,'actor_id'=>$actor,'acquired_at_gmt'=>gmdate('c',$now),'expires_at_gmt'=>gmdate('c',$now+$ttl),'ttl_seconds'=>$ttl);
		$j=wp_json_encode($p,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);if(!is_string($j)||''===$j)return new \WP_Error('bdc_kb_block_lock_encode','Falha lock encode.');
		$mid=add_post_meta($post_id,self::META_KEY,$j,true);
		if(false===$mid)return new \WP_Error('bdc_kb_block_lock_held','Lock já existente.',array('existing'=>self::inspect($post_id)));
		$rb=self::inspect($post_id);
		if($rb instanceof \WP_Error||'held'!==(string)($rb['status']??'')||!hash_equals($token,(string)($rb['token']??''))){delete_post_meta($post_id,self::META_KEY,$j);return new \WP_Error('bdc_kb_block_lock_readback','Lock divergente removido.');}
		return $rb;
	}
	public static function inspect(int $post_id):array|\WP_Error{
		if($post_id<=0)return new \WP_Error('bdc_kb_block_lock_post','Post inválido.');
		$raw=get_post_meta($post_id,self::META_KEY,true);
		if(''===$raw||null===$raw||false===$raw)return array('status'=>'free','post_id'=>$post_id,'writer_allowed'=>false,'migration_execution_allowed'=>false);
		if(!is_string($raw))return new \WP_Error('bdc_kb_block_lock_payload','Payload inválido.');
		$p=json_decode($raw,true);if(!is_array($p)||self::SCHEMA_VERSION!==(int)($p['schema_version']??0)||'core_blocks'!==(string)($p['family']??''))return new \WP_Error('bdc_kb_block_lock_payload','Payload malformado.');
		$exp=strtotime((string)($p['expires_at_gmt']??''));$token=(string)($p['token']??'');$run=(string)($p['run_id']??'');
		if(false===$exp||''===$token||1!==preg_match('/^[A-Za-z0-9._:-]{8,128}$/',$run))return new \WP_Error('bdc_kb_block_lock_payload','Payload incompleto.');
		return array('status'=>$exp<=time()?'expired':'held','post_id'=>$post_id,'run_id'=>$run,'token'=>$token,'actor_id'=>(int)($p['actor_id']??0),'acquired_at_gmt'=>(string)($p['acquired_at_gmt']??''),'expires_at_gmt'=>(string)($p['expires_at_gmt']??''),'ttl_seconds'=>(int)($p['ttl_seconds']??0),'writer_allowed'=>false,'migration_execution_allowed'=>false);
	}
	public static function release(int $post_id,string $token):array|\WP_Error{
		if(!current_user_can('manage_options'))return new \WP_Error('bdc_kb_block_lock_forbidden','Release exige manage_options.');
		$raw=get_post_meta($post_id,self::META_KEY,true);if(''===$raw||null===$raw||false===$raw)return array('status'=>'already_free','released'=>false,'idempotent_noop'=>true);
		if(!is_string($raw))return new \WP_Error('bdc_kb_block_lock_payload','Payload inválido.');
		$p=json_decode($raw,true);$actual=is_array($p)?(string)($p['token']??''):'';
		if(''===$actual||''===$token||!hash_equals($actual,$token))return new \WP_Error('bdc_kb_block_lock_token','Token divergente.');
		if(!delete_post_meta($post_id,self::META_KEY,$raw))return new \WP_Error('bdc_kb_block_lock_release','Falha release.');
		$after=self::inspect($post_id);if($after instanceof \WP_Error||'free'!==(string)($after['status']??''))return new \WP_Error('bdc_kb_block_lock_release_readback','Lock persiste.');
		return array('status'=>'released','released'=>true,'idempotent_noop'=>false);
	}
}
