<?php
namespace BDC\KnowledgeBase;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Block_Migration_Batch_Plan {
	public const SCHEMA_VERSION = '1.0.0';
	public const CURSOR_VERSION = '1.0.0';
	public const DEFAULT_BATCH_SIZE = 25;
	public const MAX_BATCH_SIZE = 100;

	public static function plan( array $dry_runs, int $batch_size = self::DEFAULT_BATCH_SIZE, ?array $cursor = null ): array|\WP_Error {
		if ( $batch_size < 1 || $batch_size > self::MAX_BATCH_SIZE ) return new \WP_Error( 'bdc_kb_block_batch_size', 'batch_size inválido.' );
		$n = self::normalize( $dry_runs ); if ( $n instanceof \WP_Error ) return $n;
		$candidates = $n['eligible'];
		try { $cohort_hash = Canonical_JSON::hash( array( 'schema_version'=>self::SCHEMA_VERSION, 'candidates'=>$candidates ) ); }
		catch ( \JsonException $e ) { return new \WP_Error( 'bdc_kb_block_batch_cohort_hash', 'Falha cohort hash.' ); }
		$offset=0;
		if ( null !== $cursor ) { $v=self::validate_cursor($cursor,$cohort_hash,count($candidates)); if($v instanceof \WP_Error)return $v; $offset=$v; }
		$items=array_slice($candidates,$offset,$batch_size); $next=$offset+count($items); $done=$next>=count($candidates);
		$next_cursor=$done?null:self::cursor($cohort_hash,$next); if($next_cursor instanceof \WP_Error)return $next_cursor;
		try { $batch_hash=Canonical_JSON::hash(array('cohort_hash'=>$cohort_hash,'offset'=>$offset,'batch_size'=>$batch_size,'items'=>$items)); }
		catch(\JsonException $e){return new \WP_Error('bdc_kb_block_batch_hash','Falha batch hash.');}
		return array(
			'schema_version'=>self::SCHEMA_VERSION,'cohort_hash'=>$cohort_hash,'batch_hash'=>$batch_hash,'batch_size'=>$batch_size,
			'offset'=>$offset,'next_offset'=>$next,'eligible_total'=>count($candidates),'excluded_total'=>$n['excluded_total'],
			'items'=>$items,'item_count'=>count($items),'done'=>$done,'next_cursor'=>$next_cursor,
			'execution_allowed'=>false,'writer_allowed'=>false,'migration_execution_allowed'=>false,'persists_checkpoint'=>false,
			'safety'=>array('persists_state'=>false,'writes_post_content'=>false,'writes_elementor_data'=>false,'calls_external_network'=>false,'executes_shortcodes'=>false),
		);
	}
	private static function normalize(array $runs): array|\WP_Error {
		$by=array();$excluded=0;
		foreach($runs as $d){
			if(!is_array($d))return new \WP_Error('bdc_kb_block_batch_candidate','Candidato inválido.');
			if('ready'!==(string)($d['dry_run_status']??'')){++$excluded;continue;}
			$id=(int)($d['post_id']??0);$f=strtolower((string)($d['fidelity_hash_before']??''));$s=strtolower((string)($d['serialization_hash']??''));$h=strtolower((string)($d['dry_run_hash']??''));
			if($id<=0||!self::sha($f)||!self::sha($s)||!self::sha($h))return new \WP_Error('bdc_kb_block_batch_identity','Candidato ready inválido.');
			if(true===($d['execution_allowed']??false)||true===($d['writer_allowed']??false)||true===($d['migration_execution_allowed']??false))return new \WP_Error('bdc_kb_block_batch_safety','Candidato viola zero-write.');
			$c=array('post_id'=>$id,'fidelity_hash_before'=>$f,'serialization_hash'=>$s,'dry_run_hash'=>$h);
			if(isset($by[$id])&&$by[$id]!==$c)return new \WP_Error('bdc_kb_block_batch_duplicate','Duplicidade conflitante.');
			$by[$id]=$c;
		}
		ksort($by,SORT_NUMERIC); return array('eligible'=>array_values($by),'excluded_total'=>$excluded);
	}
	private static function cursor(string $cohort,int $next): array|\WP_Error {
		$p=array('cursor_version'=>self::CURSOR_VERSION,'cohort_hash'=>$cohort,'next_offset'=>$next);
		try{$p['cursor_hash']=Canonical_JSON::hash($p);}catch(\JsonException $e){return new \WP_Error('bdc_kb_block_batch_cursor_hash','Falha cursor hash.');}return $p;
	}
	private static function validate_cursor(array $c,string $cohort,int $total): int|\WP_Error {
		$v=(string)($c['cursor_version']??'');$ch=strtolower((string)($c['cohort_hash']??''));$o=isset($c['next_offset'])?(int)$c['next_offset']:-1;$h=strtolower((string)($c['cursor_hash']??''));
		if(self::CURSOR_VERSION!==$v||!self::sha($ch)||!self::sha($h)||$o<0||$o>$total)return new \WP_Error('bdc_kb_block_batch_cursor','Cursor inválido.');
		try{$expected=Canonical_JSON::hash(array('cursor_version'=>$v,'cohort_hash'=>$ch,'next_offset'=>$o));}catch(\JsonException $e){return new \WP_Error('bdc_kb_block_batch_cursor_validate','Falha cursor validate.');}
		if(!hash_equals($expected,$h))return new \WP_Error('bdc_kb_block_batch_cursor_tampered','Cursor adulterado.');
		if(!hash_equals($cohort,$ch))return new \WP_Error('bdc_kb_block_batch_cursor_stale','Cursor stale.');
		return $o;
	}
	private static function sha(string $v):bool{return 1===preg_match('/^[a-f0-9]{64}$/',$v);}
}
