<?php
/**
 * T098 Block Migration defensive readiness smoke.
 * Full-corpus, read-only. No journal persistence, no locks, no writers.
 */
namespace BDC\KnowledgeBase;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Block_Migration_Readiness_Smoke {
	public const ACTION = 'bdc_kb_spec004_g245_block_migration_readiness';
	public const PAGE_SLUG = 'bdc-kb-spec004-g245-block-migration-readiness';
	private const NONCE_ACTION = 'bdc_kb_spec004_g245_block_migration_readiness_run';
	private const NONCE_FIELD = 'bdc_kb_spec004_g245_block_migration_readiness_nonce';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 39 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}
	public static function register_page(): void {
		add_submenu_page( Admin_Page::PAGE_SLUG, 'Block Migration Readiness G-245', 'Block Migration Readiness G-245', 'manage_options', self::PAGE_SLUG, array( self::class, 'render_page' ) );
	}
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ) ); }
		echo '<div class="wrap"><h1>' . esc_html__( 'SPEC-004 — T098 Block Migration Readiness', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>Read-only.</strong> Dry-run, journal em memória, stale guard e batch planner. Não persiste journal, não adquire lock e não escreve conteúdo.</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar T098 e baixar JSON', 'bdc-knowledge-base' ) );
		echo '</form></div>';
	}
	public static function handle_run(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) { wp_die( 'Método não permitido.', '', array( 'response' => 405 ) ); }
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Permissão insuficiente.', '', array( 'response' => 403 ) ); }
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] ) ? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) { wp_die( 'Nonce inválido.', '', array( 'response' => 403 ) ); }
		$report = self::run();
		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) { wp_die( 'Falha JSON.', '', array( 'response' => 500 ) ); }
		nocache_headers(); header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-t098-block-migration-readiness-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; exit;
	}

	private static function run(): array {
		$started=microtime(true);
		$ids=self::post_ids(); $finger_before=self::fingerprint($ids);
		$a=self::pass($ids); $b=self::pass($ids);
		$dry_mismatch=0;$journal_mismatch=0;
		foreach($ids as $id){
			if(isset($a['dry_hash'][$id],$b['dry_hash'][$id])&&!hash_equals($a['dry_hash'][$id],$b['dry_hash'][$id]))++$dry_mismatch;
			if(isset($a['journal_hash'][$id],$b['journal_hash'][$id])&&!hash_equals($a['journal_hash'][$id],$b['journal_hash'][$id]))++$journal_mismatch;
		}
		$batch=self::batch_walk($a['dry_runs']);
		$ids_after=self::post_ids();$finger_after=self::fingerprint($ids_after);
		$unchanged=$ids===$ids_after;$finger_equal=hash_equals($finger_before,$finger_after);
		$gate=$unchanged&&$finger_equal&&0===$a['errors']&&0===$b['errors']&&0===$a['throwables']&&0===$b['throwables']
			&&0===$a['safety_violations']&&0===$b['safety_violations']&&0===$dry_mismatch&&0===$journal_mismatch
			&&true===$batch['pass']&&$a['ready']===$a['journals_prepared']&&$b['ready']===$b['journals_prepared'];

		return array(
			'schema_version'=>'1.0.1','gate'=>'T098','mode'=>'block_migration_defensive_readiness_read_only','generated_at'=>gmdate('c'),
			'environment'=>array('wordpress'=>get_bloginfo('version'),'php'=>PHP_VERSION,'plugin'=>defined('BDC_KB_VERSION')?BDC_KB_VERSION:'',
				'journal_schema'=>Block_Migration_Journal::SCHEMA_VERSION,'dry_run_schema'=>Block_Migration_Dry_Run::SCHEMA_VERSION,
				'batch_schema'=>Block_Migration_Batch_Plan::SCHEMA_VERSION,'stale_guard_schema'=>Block_Migration_Stale_Source_Guard::SCHEMA_VERSION,
				'gutenberg_plugin_dependency'=>false),
			'corpus'=>array('total_posts'=>count($ids),'first_pass'=>count($a['dry_runs']),'second_pass'=>count($b['dry_runs']),
				'first_errors'=>$a['errors'],'second_errors'=>$b['errors'],'first_throwables'=>$a['throwables'],'second_throwables'=>$b['throwables'],
				'first_safety_violations'=>$a['safety_violations'],'second_safety_violations'=>$b['safety_violations'],
				'first_throwable_signatures'=>$a['throwable_signatures'],'second_throwable_signatures'=>$b['throwable_signatures'],
				'dry_run_hash_mismatches'=>$dry_mismatch,'journal_hash_mismatches'=>$journal_mismatch),
			'distribution'=>array('dry_run_status'=>$a['status'],'source_kind'=>$a['source_kind'],'journals_prepared_in_memory'=>$a['journals_prepared']),
			'batch'=>array('eligible_total'=>$batch['eligible_total'],'excluded_total'=>$batch['excluded_total'],'batch_size'=>25,'batch_count'=>$batch['batch_count'],
				'covered_total'=>$batch['covered_total'],'duplicate_items'=>$batch['duplicate_items'],'cursor_failures'=>$batch['cursor_failures'],'pass'=>$batch['pass']),
			'contracts'=>array('journal_store_meta_key'=>Block_Migration_Journal_Store::META_KEY,'lock_meta_key'=>Block_Migration_Lock::META_KEY,
				'journal_store_invoked'=>false,'lock_acquire_invoked'=>false,'writer_invoked'=>false),
			'safety'=>array('read_only_design'=>true,'persists_journal'=>false,'acquires_lock'=>false,'writes_post_content'=>false,'writes_elementor_data'=>false,
				'writer_allowed'=>false,'migration_execution_allowed'=>false,'executes_shortcodes'=>false,'renders_blocks'=>false,'calls_external_network'=>false,
				'exports_editorial_content'=>false,'exports_urls'=>false,'exports_post_ids'=>false,'corpus_unchanged'=>$unchanged,
				'editorial_fingerprint_before'=>$finger_before,'editorial_fingerprint_after'=>$finger_after,'editorial_fingerprint_equal'=>$finger_equal),
			'gate_result'=>array('t098_block_migration_readiness_pass'=>$gate),
			'duration_ms'=>(int)round((microtime(true)-$started)*1000),
		);
	}

	private static function pass(array $ids): array {
		$out=array('dry_runs'=>array(),'dry_hash'=>array(),'journal_hash'=>array(),'status'=>array(),'source_kind'=>array(),'ready'=>0,'journals_prepared'=>0,'errors'=>0,'throwables'=>0,'throwable_signatures'=>array(),'safety_violations'=>0);
		foreach($ids as $id){
			try{
				$dry=Block_Migration_Dry_Run::build($id);
				if($dry instanceof \WP_Error){++$out['errors'];continue;}
				$out['dry_runs'][]=$dry;$out['dry_hash'][$id]=(string)($dry['dry_run_hash']??'');
				$status=(string)($dry['dry_run_status']??'unknown');$kind=(string)($dry['source_kind']??'unknown');
				$out['status'][$status]=1+($out['status'][$status]??0);$out['source_kind'][$kind]=1+($out['source_kind'][$kind]??0);
				if(true===($dry['execution_allowed']??false)||true===($dry['writer_allowed']??false)||true===($dry['migration_execution_allowed']??false))++$out['safety_violations'];
				if('ready'!==$status)continue;
				++$out['ready'];
				$source=Content_Source::inspect($id); if($source instanceof \WP_Error){++$out['errors'];continue;}
				$raw=$source['elementor_raw']??'';
				if(!is_string($raw)){ $tmp=wp_json_encode($raw,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); $raw=is_string($tmp)?$tmp:''; }
				$j=Block_Migration_Journal::prepare(array('run_id'=>'t098-readonly-0001','post_id'=>$id,'fidelity_hash_before'=>(string)$dry['fidelity_hash_before'],
					'serialization_hash'=>(string)$dry['serialization_hash'],'recorded_at'=>'1970-01-01T00:00:00Z','source_kind'=>$kind,
					'before'=>array('post_content'=>(string)$source['post_content'],'elementor_data'=>$raw)));
				if($j instanceof \WP_Error){++$out['errors'];continue;}
				$v=Block_Migration_Journal::validate_record($j,false);if($v instanceof \WP_Error){++$out['errors'];continue;}
				$out['journal_hash'][$id]=(string)$j['journal_hash'];++$out['journals_prepared'];
			}catch(\Throwable $e){
				++$out['throwables'];
				$signature=get_class($e).'|'.basename($e->getFile()).':'.$e->getLine().'|'.substr(hash('sha256',$e->getMessage()),0,16);
				$out['throwable_signatures'][$signature]=1+($out['throwable_signatures'][$signature]??0);
			}
		}
		ksort($out['status']);ksort($out['source_kind']);ksort($out['throwable_signatures']);return $out;
	}
	private static function batch_walk(array $runs): array {
		$cursor=null;$seen=array();$batches=0;$eligible=0;$excluded=0;$cursor_failures=0;$done=false;
		do{
			$p=Block_Migration_Batch_Plan::plan($runs,25,$cursor);
			if($p instanceof \WP_Error){++$cursor_failures;break;}
			if(0===$batches){$eligible=(int)$p['eligible_total'];$excluded=(int)$p['excluded_total'];}
			foreach((array)$p['items'] as $item){$id=(int)($item['post_id']??0);$seen[]=$id;}
			++$batches;$cursor=$p['next_cursor'];$done=(bool)$p['done'];
		}while(!$done && $batches<1000);
		$duplicates=count($seen)-count(array_unique($seen));$covered=count($seen);
		return array('eligible_total'=>$eligible,'excluded_total'=>$excluded,'batch_count'=>$batches,'covered_total'=>$covered,'duplicate_items'=>$duplicates,'cursor_failures'=>$cursor_failures,
			'pass'=>0===$cursor_failures&&0===$duplicates&&$covered===$eligible);
	}
	private static function post_ids(): array {
		$ids=get_posts(array('post_type'=>'post','post_status'=>'any','numberposts'=>-1,'fields'=>'ids','orderby'=>'ID','order'=>'ASC','suppress_filters'=>false));
		return array_values(array_map('intval',is_array($ids)?$ids:array()));
	}
	private static function fingerprint(array $ids): string {
		$ctx=hash_init('sha256');
		foreach($ids as $id){
			$p=get_post($id);$content=is_object($p)?(string)($p->post_content??''):'';
			$raw=get_post_meta($id,'_elementor_data',true);
			if(!is_string($raw)){$j=wp_json_encode($raw,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);$raw=is_string($j)?$j:'';}
			hash_update($ctx,$id."\0".hash('sha256',$content)."\0".hash('sha256',$raw)."\n");
		}
		return hash_final($ctx);
	}
}
