<?php
namespace {
define('ABSPATH', __DIR__ . '/');
class WP_Error {
    private string $code;
    private string $message;
    private mixed $data;
    public function __construct($code='',$message='',$data=null){$this->code=(string)$code;$this->message=(string)$message;$this->data=$data;}
    public function get_error_code(){return $this->code;}
    public function get_error_message(){return $this->message;}
    public function get_error_data(){return $this->data;}
}
function is_wp_error($v){return $v instanceof WP_Error;}
$GLOBALS['FAKE_POSTS']=[358=>(object)['ID'=>358]];
$GLOBALS['FAKE_META']=[];
$GLOBALS['FAKE_NEXT_META']=1000;
$GLOBALS['FAKE_USER_ID']=7;

function get_post($id){return $GLOBALS['FAKE_POSTS'][$id]??null;}
function get_current_user_id(){return $GLOBALS['FAKE_USER_ID'];}
function current_user_can($cap){return $cap==='manage_options';}
function wp_json_encode($value,$flags=0){return json_encode($value,$flags);}
function add_post_meta($post_id,$key,$value,$unique=false){
    if($unique){
        foreach($GLOBALS['FAKE_META'] as $m){if($m['post_id']===$post_id&&$m['meta_key']===$key)return false;}
    }
    $id=$GLOBALS['FAKE_NEXT_META']++;
    $GLOBALS['FAKE_META'][$id]=['meta_id'=>$id,'post_id'=>$post_id,'meta_key'=>$key,'meta_value'=>$value];
    return $id;
}
function get_metadata_by_mid($type,$id){
    if($type!=='post'||!isset($GLOBALS['FAKE_META'][$id]))return false;
    return (object)$GLOBALS['FAKE_META'][$id];
}
function delete_metadata_by_mid($type,$id){
    if($type!=='post'||!isset($GLOBALS['FAKE_META'][$id]))return false;
    unset($GLOBALS['FAKE_META'][$id]); return true;
}
class FakeWPDB {
    public string $postmeta='fake_postmeta';
    public function prepare($sql,...$args){return ['sql'=>$sql,'args'=>$args];}
    public function get_var($prepared){
        $args=$prepared['args']; $post_id=(int)$args[0]; $key=(string)$args[1]; $ids=[];
        foreach($GLOBALS['FAKE_META'] as $id=>$m){if($m['post_id']===$post_id&&$m['meta_key']===$key)$ids[]=$id;}
        rsort($ids,SORT_NUMERIC); return $ids[0]??0;
    }
}
$GLOBALS['wpdb']=new FakeWPDB();
}
namespace BDC\KnowledgeBase {
require __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-canonical-json.php';
require __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-block-migration-journal.php';
require __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-block-migration-journal-store.php';

$N=0;
function ok($cond,$msg){global $N;if(!$cond){echo "FAIL: $msg\n";exit(1);}++$N;}
function sha($x){return hash('sha256',$x);}

$input=[
 'run_id'=>'t100e-hardening-001',
 'post_id'=>358,
 'fidelity_hash_before'=>sha('fidelity'),
 'serialization_hash'=>sha('serialization'),
 'recorded_at'=>'2026-09-18T11:00:00Z',
 'source_kind'=>'legacy_html',
 'before'=>['post_content'=>'<p>before</p>','elementor_data'=>'[]'],
];

$j=Block_Migration_Journal::prepare($input);
ok(!($j instanceof \WP_Error),'prepare valid');
ok(Block_Migration_Journal::validate_record($j,false)===true,'prepared validates');

$persisted=Block_Migration_Journal_Store::persist_prepared($j);
ok(!($persisted instanceof \WP_Error),'persist prepared');
ok(($persisted['record']['state']??'')===Block_Migration_Journal::STATE_PREPARED,'prepared state durable');
$prepared_id=(int)$persisted['event_id'];

$illegal=Block_Migration_Journal::mark_rolled_back($persisted['record'],sha('<p>before</p>'),sha('[]'));
ok($illegal instanceof \WP_Error && $illegal->get_error_code()==='bdc_kb_block_journal_rollback_state','direct prepared rollback rejected');

$applied=Block_Migration_Journal::mark_applied($persisted['record'],sha('<!-- wp:html --><p>before</p><!-- /wp:html -->'),sha('[]'));
ok(!($applied instanceof \WP_Error),'mark applied');

$applied_event=Block_Migration_Journal_Store::persist_transition($applied,$prepared_id);
ok(!($applied_event instanceof \WP_Error),'persist applied');

$rollback=Block_Migration_Journal::mark_rolled_back($applied_event['record'],sha('<p>before</p>'),sha('[]'));
ok(!($rollback instanceof \WP_Error),'mark rollback after applied');

$rollback_event=Block_Migration_Journal_Store::persist_transition($rollback,(int)$applied_event['event_id']);
ok(!($rollback_event instanceof \WP_Error),'persist rollback');
ok(($rollback_event['record']['state']??'')===Block_Migration_Journal::STATE_ROLLED_BACK,'rolled back durable');

$tamper=$rollback_event['record']; $tamper['state']='evil';
ok(Block_Migration_Journal::validate_record($tamper,true) instanceof \WP_Error,'invalid state rejected');

$tamper=$rollback_event['record']; $tamper['run_id']='x';
ok(Block_Migration_Journal::validate_record($tamper,true) instanceof \WP_Error,'invalid run id rejected');

$tamper=$rollback_event['record']; $tamper['post_id']=0;
ok(Block_Migration_Journal::validate_record($tamper,true) instanceof \WP_Error,'invalid post id rejected');

$tamper=$rollback_event['record']; $tamper['journal_id']=sha('different');
ok(Block_Migration_Journal::validate_record($tamper,true) instanceof \WP_Error,'journal identity mismatch rejected');

$rid=(int)$rollback_event['event_id'];
$payload=json_decode($GLOBALS['FAKE_META'][$rid]['meta_value'],true);
$payload['state']='applied';
$GLOBALS['FAKE_META'][$rid]['meta_value']=json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
$bad=Block_Migration_Journal_Store::read_event($rid);
ok($bad instanceof \WP_Error && $bad->get_error_code()==='bdc_kb_block_store_state','outer state mismatch rejected');

echo "ALL PASS $N\n";
}
