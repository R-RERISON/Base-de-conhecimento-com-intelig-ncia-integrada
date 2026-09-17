<?php
/**
 * T100A — read-only authorization pack for the first controlled batch.
 * Selects five deterministic low-risk legacy_html posts.
 */
namespace BDC\KnowledgeBase;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Block_Migration_Batch_Authorization_Pack_Smoke {
	public const ACTION = 'bdc_kb_spec004_g245_t100a_batch_authpack';
	public const PAGE_SLUG = 'bdc-kb-spec004-g245-t100a-batch-authpack';
	public const SCHEMA_VERSION = '1.0.0';
	public const BATCH_SIZE = 5;
	private const NONCE_ACTION = 'bdc_kb_spec004_g245_t100a_batch_authpack_run';
	private const NONCE_FIELD = 'bdc_kb_spec004_g245_t100a_batch_authpack_nonce';
	private const MAX_CONTENT_BYTES = 30000;

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 42 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}
	public static function register_page(): void {
		add_submenu_page( Admin_Page::PAGE_SLUG, 'T100A Batch Authorization G-245', 'T100A Batch Authorization G-245', 'manage_options', self::PAGE_SLUG, array( self::class, 'render_page' ) );
	}
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ) ); }
		echo '<div class="wrap"><h1>' . esc_html__( 'SPEC-004 — T100A Batch Authorization Pack', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-info inline"><p><strong>Read-only.</strong> Seleciona 5 artigos low-risk sem resíduos de journal/lock e gera uma autorização coletiva. Nenhum conteúdo é alterado.</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Gerar T100A Batch Authorization Pack e baixar JSON', 'bdc-knowledge-base' ) );
		echo '</form></div>';
	}
	public static function handle_run(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) { wp_die( 'Método não permitido.', '', array( 'response' => 405 ) ); }
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Permissão insuficiente.', '', array( 'response' => 403 ) ); }
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] ) ? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) { wp_die( 'Nonce inválido.', '', array( 'response' => 403 ) ); }
		$report=self::run();
		$json=wp_json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		if(!is_string($json)){wp_die('Falha JSON.','',array('response'=>500));}
		nocache_headers(); header('Content-Type: application/json; charset=utf-8');
		header('Content-Disposition: attachment; filename="bdc-kb-spec004-t100a-batch-authpack-'.gmdate('Ymd-His').'.json"');
		echo $json; exit;
	}

	private static function run(): array {
		$started=microtime(true);
		$ids=get_posts(array('post_type'=>'post','post_status'=>'any','numberposts'=>-1,'fields'=>'ids','orderby'=>'ID','order'=>'ASC','suppress_filters'=>false));
		$ids=array_values(array_map('intval',is_array($ids)?$ids:array()));
		$eligible=array(); $rejection=array(); $errors=0;
		foreach($ids as $post_id){
			$c=self::candidate($post_id);
			if($c instanceof \WP_Error){++$errors;$code=$c->get_error_code();$rejection[$code]=1+($rejection[$code]??0);continue;}
			if(null===$c){continue;}
			$eligible[]=$c;
		}
		usort($eligible,static function(array $a,array $b):int{
			$s=(int)$a['risk_profile']['risk_score']<=>(int)$b['risk_profile']['risk_score'];
			return 0!==$s?$s:(int)$a['post_id']<=>(int)$b['post_id'];
		});
		$selected=array_slice($eligible,0,self::BATCH_SIZE);
		if(count($selected)!==self::BATCH_SIZE){
			return self::failure('INSUFFICIENT_LOW_RISK_CANDIDATES',$started,array('eligible_count'=>count($eligible),'errors'=>$errors));
		}
		$items=array(); $auth_material=array();
		foreach($selected as $c){
			$dry=$c['dry'];$post=$c['post'];
			$item_identity=array(
				'gate'=>'T100B',
				'post_id'=>(int)$c['post_id'],
				'fidelity_hash_before'=>(string)$dry['fidelity_hash_before'],
				'serialization_hash'=>(string)$dry['serialization_hash'],
				'dry_run_hash'=>(string)$dry['dry_run_hash'],
				'serialized_post_content_sha256'=>(string)$dry['serialized_post_content_sha256'],
			);
			try{$item_auth=Canonical_JSON::hash($item_identity);}
			catch(\JsonException $e){return self::failure('ITEM_AUTH_HASH_FAILED',$started);}
			$auth_material[]=array('post_id'=>(int)$c['post_id'],'item_authorization_id'=>$item_auth);
			$items[]=array(
				'sequence'=>count($items)+1,
				'post_id'=>(int)$c['post_id'],
				'post_title'=>sanitize_text_field((string)($post->post_title??'')),
				'post_status'=>(string)($post->post_status??''),
				'source_kind'=>'legacy_html',
				'risk_tier'=>'low',
				'risk_profile'=>$c['risk_profile'],
				'identity'=>array(
					'fidelity_hash_before'=>(string)$dry['fidelity_hash_before'],
					'serialization_hash'=>(string)$dry['serialization_hash'],
					'dry_run_hash'=>(string)$dry['dry_run_hash'],
					'post_content_sha256_before'=>(string)($dry['source_material']['post_content_sha256']??''),
					'elementor_data_sha256_before'=>(string)($dry['source_material']['elementor_data_sha256']??''),
					'serialized_post_content_sha256_expected'=>(string)$dry['serialized_post_content_sha256'],
					'expected_block_name'=>'core/freeform',
					'item_authorization_id'=>$item_auth,
				),
			);
		}
		$batch_identity=array('gate'=>'T100B','batch_size'=>self::BATCH_SIZE,'execution_mode'=>'sequential_apply_verify_immediate_rollback_stop_on_first_failure','items'=>$auth_material);
		try{$batch_auth=Canonical_JSON::hash($batch_identity);}
		catch(\JsonException $e){return self::failure('BATCH_AUTH_HASH_FAILED',$started);}

		$rejection['errors_total']=$errors;ksort($rejection);
		return array(
			'schema_version'=>self::SCHEMA_VERSION,'gate'=>'T100A','mode'=>'batch_authorization_pack_read_only','generated_at'=>gmdate('c'),
			'environment'=>array('wordpress'=>get_bloginfo('version'),'php'=>PHP_VERSION,'plugin'=>defined('BDC_KB_VERSION')?BDC_KB_VERSION:'','gutenberg_plugin_dependency'=>false),
			'selection'=>array(
				'policy'=>'legacy_html + dry_run ready + zero Block Migration journal/lock residue + empty Elementor payload + conservative static low-risk profile',
				'evaluated_posts'=>count($ids),'eligible_low_risk'=>count($eligible),'batch_size'=>self::BATCH_SIZE,
				'excludes_prior_canary_with_persistent_journal'=>true,'rejections_or_errors'=>$rejection,
			),
			'batch'=>array(
				'batch_size'=>self::BATCH_SIZE,'execution_order'=>'sequence ASC','concurrency'=>1,'stop_on_first_failure'=>true,
				'rollback_policy'=>'immediate rollback after verification for every item, including the successful path',
				'items'=>$items,
				'batch_authorization_id'=>$batch_auth,
			),
			'planned_operation'=>array(
				'gate'=>'T100B','scope'=>'exactly the five listed post_ids and frozen identities only',
				'write_target'=>'WP_Post.post_content','elementor_data_action'=>'preserve unchanged',
				'journal_required_before_each_write'=>true,'exclusive_lock_per_item'=>true,
				'stale_source_recheck_immediately_before_each_write'=>true,'verify_after_each_write'=>true,
				'immediate_rollback_each_item'=>true,'verify_after_each_rollback'=>true,'no_concurrency'=>true,
			),
			'authorization'=>array(
				'authorized'=>false,'batch_authorization_id'=>$batch_auth,
				'required_exact_scope'=>'T100B sequential apply + verify + immediate rollback for exactly 5 frozen items; stop on first failure',
				'suggested_approval_text'=>'Autorizo o T100B batch_authorization_id='.$batch_auth.', para os 5 itens congelados, sequencial, stop-on-first-failure, com apply, verificação e rollback imediato por item.',
			),
			'safety'=>array('read_only_design'=>true,'persists_journal'=>false,'acquires_lock'=>false,'writes_post_content'=>false,'writes_elementor_data'=>false,
				'writer_allowed'=>false,'migration_execution_allowed'=>false,'executes_shortcodes'=>false,'renders_blocks'=>false,'calls_external_network'=>false,
				'exports_editorial_body'=>false,'exports_urls'=>false),
			'gate_result'=>array('t100a_batch_authorization_pack_pass'=>true),
			'duration_ms'=>(int)round((microtime(true)-$started)*1000),
		);
	}

	/** @return array<string,mixed>|\WP_Error|null */
	private static function candidate(int $post_id){
		$post=get_post($post_id);
		if(!is_object($post)||'post'!==(string)($post->post_type??'')){return null;}
		$dry=Block_Migration_Dry_Run::build($post_id);
		if($dry instanceof \WP_Error){return $dry;}
		if('ready'!==(string)($dry['dry_run_status']??'')||'legacy_html'!==(string)($dry['source_kind']??'')){return null;}
		if(0!==count(get_post_meta($post_id,Block_Migration_Journal_Store::META_KEY,false))){return null;}
		$lock=Block_Migration_Lock::inspect($post_id);
		if($lock instanceof \WP_Error){return $lock;}
		if('free'!==(string)($lock['status']??'')){return null;}
		$content=(string)($post->post_content??'');
		$elementor=get_post_meta($post_id,'_elementor_data',true);
		if(!is_string($elementor)){$j=wp_json_encode($elementor,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);$elementor=is_string($j)?$j:'';}
		$risk=self::risk_profile($content,$elementor);
		if(true!==($risk['low_risk']??false)){return null;}
		return array('post_id'=>$post_id,'post'=>$post,'dry'=>$dry,'risk_profile'=>$risk);
	}

	public static function risk_profile(string $content,string $elementor_raw=''):array{
		$count=static function(string $pattern,string $subject):int{$m=array();$r=preg_match_all($pattern,$subject,$m);return false===$r?0:(int)$r;};
		$bytes=strlen($content);$links=$count('/<a\b/i',$content);$images=$count('/<img\b/i',$content);$tables=$count('/<table\b/i',$content);
		$dangerous=$count('/<(?:script|iframe|form|object|embed|style)\b/i',$content);$block_comments=$count('/<!--\s*wp:/i',$content);$shortcodes=0;
		if(function_exists('get_shortcode_regex')){global $shortcode_tags;if(is_array($shortcode_tags)&&!empty($shortcode_tags)){$regex=get_shortcode_regex(array_keys($shortcode_tags));$shortcodes=$count('/'.$regex.'/s',$content);}}
		$elementor_bytes=strlen($elementor_raw);
		$low=$bytes>0&&$bytes<=self::MAX_CONTENT_BYTES&&0===$dangerous&&0===$block_comments&&0===$shortcodes&&0===$elementor_bytes&&$links<=10&&$images<=1&&$tables<=1;
		$score=(int)ceil($bytes/100)+($links*20)+($images*200)+($tables*300)+($shortcodes*1000)+($dangerous*5000)+($elementor_bytes>0?10000:0);
		return array('low_risk'=>$low,'risk_score'=>$score,'content_bytes'=>$bytes,'links'=>$links,'images'=>$images,'tables'=>$tables,
			'registered_shortcode_occurrences'=>$shortcodes,'dangerous_embed_or_script_tags'=>$dangerous,'core_block_comments'=>$block_comments,'elementor_data_bytes'=>$elementor_bytes);
	}
	private static function failure(string $code,float $started,array $extra=array()):array{
		return array_merge(array('schema_version'=>self::SCHEMA_VERSION,'gate'=>'T100A','mode'=>'batch_authorization_pack_read_only','generated_at'=>gmdate('c'),
			'failure_code'=>$code,'safety'=>array('read_only_design'=>true,'writes_post_content'=>false,'writes_elementor_data'=>false),
			'gate_result'=>array('t100a_batch_authorization_pack_pass'=>false),'duration_ms'=>(int)round((microtime(true)-$started)*1000)),$extra);
	}
}
