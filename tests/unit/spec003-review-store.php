<?php
// Deterministic unit harness for SPEC-003 Review_Store. No WordPress runtime required.

define( 'ABSPATH', __DIR__ );

class WP_Error {
	private string $code; private string $message; private $data;
	public function __construct( string $code = '', string $message = '', $data = null ) { $this->code=$code; $this->message=$message; $this->data=$data; }
	public function get_error_code(): string { return $this->code; }
	public function get_error_message(): string { return $this->message; }
	public function get_error_data() { return $this->data; }
}
function is_wp_error( $v ): bool { return $v instanceof WP_Error; }
function sanitize_key( $key ): string { $key = strtolower( (string) $key ); return preg_replace('/[^a-z0-9_\-]/','',$key) ?? ''; }
function sanitize_textarea_field( $str ): string { $s=(string)$str; $s=strip_tags($s); $s=str_replace("\r",'', $s); return trim($s); }
function wp_json_encode( $data, $flags=0 ) { return json_encode($data,$flags); }

$GLOBALS['posts'] = array( 10 => (object) array('ID'=>10,'post_type'=>'post'), 11 => (object) array('ID'=>11,'post_type'=>'page') );
$GLOBALS['caps'] = array('edit_post'=>true,'edit_others_posts'=>true);
$GLOBALS['current_user_id'] = 7;
$GLOBALS['comments'] = array();
$GLOBALS['next_comment_id'] = 100;
$GLOBALS['insert_fail'] = false;
$GLOBALS['delete_fail'] = false;
$GLOBALS['tamper_after_insert'] = false;
$GLOBALS['malformed_latest'] = false;

function get_post( $id ) { return $GLOBALS['posts'][$id] ?? null; }
function current_user_can( $cap, ...$args ): bool { return (bool)($GLOBALS['caps'][$cap] ?? false); }
function get_current_user_id(): int { return (int)$GLOBALS['current_user_id']; }
function get_comments( $args ) {
	$post_id=(int)($args['post_id'] ?? 0); $type=(string)($args['type'] ?? '');
	$rows=array_values(array_filter($GLOBALS['comments'], fn($c)=>$c->comment_post_ID===$post_id && $c->comment_type===$type && (string)$c->comment_approved==='1'));
	usort($rows, fn($a,$b)=>$b->comment_ID <=> $a->comment_ID);
	$offset=(int)($args['offset'] ?? 0); $number=(int)($args['number'] ?? count($rows));
	$rows=array_slice($rows,$offset,$number);
	if ($GLOBALS['malformed_latest'] && !empty($rows)) { $rows[0]=clone $rows[0]; $rows[0]->comment_content='{bad-json'; }
	if ($GLOBALS['tamper_after_insert'] && !empty($rows)) { $rows[0]=clone $rows[0]; $d=json_decode($rows[0]->comment_content,true); if(is_array($d)){ $d['to']='excluded'; $rows[0]->comment_content=json_encode($d);} }
	return $rows;
}
function wp_insert_comment( $data ) {
	if ($GLOBALS['insert_fail']) return false;
	$id=$GLOBALS['next_comment_id']++;
	$c=(object)array(
		'comment_ID'=>$id,
		'comment_post_ID'=>(int)$data['comment_post_ID'],
		'comment_content'=>(string)$data['comment_content'],
		'comment_type'=>(string)$data['comment_type'],
		'comment_approved'=>'1',
		'user_id'=>(int)$data['user_id'],
		'comment_date_gmt'=>'2026-09-15 13:00:00',
	);
	$GLOBALS['comments'][$id]=$c; return $id;
}
function wp_delete_comment( $id, $force=false ) {
	if ($GLOBALS['delete_fail']) return false;
	if (!isset($GLOBALS['comments'][$id])) return false;
	unset($GLOBALS['comments'][$id]); return true;
}

require __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-review-contract.php';
require __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-review-store.php';

use BDC\KnowledgeBase\Review_Contract;
use BDC\KnowledgeBase\Review_Store;

function reset_env(): void {
	$GLOBALS['caps']=array('edit_post'=>true,'edit_others_posts'=>true);
	$GLOBALS['current_user_id']=7; $GLOBALS['comments']=array(); $GLOBALS['next_comment_id']=100;
	$GLOBALS['insert_fail']=false; $GLOBALS['delete_fail']=false; $GLOBALS['tamper_after_insert']=false; $GLOBALS['malformed_latest']=false;
}
function seed_event(string $from,string $to,string $note='',int $user=7): int {
	return (int)wp_insert_comment(array('comment_post_ID'=>10,'comment_content'=>json_encode(array('schema_version'=>1,'from'=>$from,'to'=>$to,'note'=>$note)),'comment_type'=>Review_Contract::COMMENT_TYPE,'comment_approved'=>1,'user_id'=>$user));
}
function status_of_error($r): string { $d=$r instanceof WP_Error ? $r->get_error_data() : null; return is_array($d)?(string)($d['status']??''):''; }
$tests=[];
function test_case(string $name, callable $fn): void { global $tests; try { reset_env(); $ok=(bool)$fn(); $tests[] = [$name,$ok,'']; } catch(Throwable $e){ $tests[]=[$name,false,$e->getMessage()]; } }

test_case('contract_states_exact', function(){ return array_keys(Review_Contract::states())===['unreviewed','in_review','needs_changes','approved','excluded']; });
test_case('read_unreviewed_side_effect_free', function(){ $r=Review_Store::read(10); return !is_wp_error($r)&&$r['state']==='unreviewed'&&$r['last_event_id']===0&&count($GLOBALS['comments'])===0; });
test_case('unsupported_post_type', function(){ return is_wp_error(Review_Store::read(11)); });
test_case('submit_in_review_editor_only', function(){ $GLOBALS['caps']['edit_others_posts']=false; $r=Review_Store::transition(10,'in_review'); return !is_wp_error($r)&&$r['status']==='SUCCESS'&&count($GLOBALS['comments'])===1; });
test_case('approve_requires_reviewer', function(){ $GLOBALS['caps']['edit_others_posts']=false; $r=Review_Store::transition(10,'approved'); return is_wp_error($r)&&$r->get_error_code()==='bdc_review_reviewer_forbidden'&&count($GLOBALS['comments'])===0; });
test_case('edit_post_required', function(){ $GLOBALS['caps']['edit_post']=false; $r=Review_Store::transition(10,'in_review'); return is_wp_error($r)&&count($GLOBALS['comments'])===0; });
test_case('needs_changes_note_required', function(){ seed_event('unreviewed','in_review'); $r=Review_Store::transition(10,'needs_changes',''); return is_wp_error($r)&&$r->get_error_code()==='bdc_review_note_required'&&count($GLOBALS['comments'])===1; });
test_case('excluded_note_required', function(){ $r=Review_Store::transition(10,'excluded',''); return is_wp_error($r)&&$r->get_error_code()==='bdc_review_note_required'&&count($GLOBALS['comments'])===0; });
test_case('note_sanitized', function(){ seed_event('unreviewed','in_review'); $r=Review_Store::transition(10,'needs_changes',' <b>Ajustar</b> '); $h=Review_Store::history(10); return !is_wp_error($r)&&!is_wp_error($h)&&$h[0]['note']==='Ajustar'; });
test_case('note_too_large_zero_write', function(){ $r=Review_Store::transition(10,'excluded',str_repeat('a',2001)); return is_wp_error($r)&&count($GLOBALS['comments'])===0; });
test_case('noop_zero_write', function(){ seed_event('unreviewed','in_review'); $before=count($GLOBALS['comments']); $r=Review_Store::transition(10,'in_review','ignored'); return !is_wp_error($r)&&$r['status']==='NO_CHANGE'&&count($GLOBALS['comments'])===$before; });
test_case('invalid_target_zero_write', function(){ $r=Review_Store::transition(10,'garbage'); return is_wp_error($r)&&count($GLOBALS['comments'])===0; });
test_case('invalid_transition_zero_write', function(){ seed_event('unreviewed','excluded','fora'); $before=count($GLOBALS['comments']); $r=Review_Store::transition(10,'approved'); return is_wp_error($r)&&$r->get_error_code()==='bdc_review_invalid_transition'&&count($GLOBALS['comments'])===$before; });
test_case('valid_chain_and_history', function(){ Review_Store::transition(10,'in_review'); Review_Store::transition(10,'needs_changes','corrigir'); $GLOBALS['caps']['edit_others_posts']=false; Review_Store::transition(10,'in_review'); $GLOBALS['caps']['edit_others_posts']=true; Review_Store::transition(10,'approved'); $r=Review_Store::read(10); $h=Review_Store::history(10); return !is_wp_error($r)&&$r['state']==='approved'&&!is_wp_error($h)&&count($h)===4&&$h[0]['to']==='approved'; });
test_case('insert_failure_fail_safe', function(){ $GLOBALS['insert_fail']=true; $r=Review_Store::transition(10,'in_review'); return is_wp_error($r)&&status_of_error($r)==='FAIL_SAFE'&&Review_Store::read(10)['state']==='unreviewed'; });
test_case('reread_mismatch_compensates', function(){ $GLOBALS['tamper_after_insert']=true; $r=Review_Store::transition(10,'in_review'); $GLOBALS['tamper_after_insert']=false; $after=Review_Store::read(10); return is_wp_error($r)&&status_of_error($r)==='FAIL_SAFE'&&!is_wp_error($after)&&$after['state']==='unreviewed'&&count($GLOBALS['comments'])===0; });
test_case('compensation_failure_critical', function(){ $GLOBALS['tamper_after_insert']=true; $GLOBALS['delete_fail']=true; $r=Review_Store::transition(10,'in_review'); return is_wp_error($r)&&status_of_error($r)==='PARTIAL_FAILURE_CRITICAL'&&count($GLOBALS['comments'])===1; });
test_case('malformed_latest_is_integrity_error', function(){ seed_event('unreviewed','in_review'); $GLOBALS['malformed_latest']=true; $r=Review_Store::read(10); return is_wp_error($r)&&$r->get_error_code()==='bdc_review_integrity_error'; });
test_case('history_pagination', function(){ Review_Store::transition(10,'in_review'); Review_Store::transition(10,'approved'); Review_Store::transition(10,'in_review'); $h=Review_Store::history(10,2,1); return !is_wp_error($h)&&count($h)===2&&$h[0]['to']==='approved'&&$h[1]['to']==='in_review'; });

$pass=count(array_filter($tests,fn($t)=>$t[1])); $fail=count($tests)-$pass;
foreach($tests as [$name,$ok,$msg]) echo ($ok?'PASS':'FAIL')."\t{$name}".($msg?"\t{$msg}":'')."\n";
echo "SUMMARY\tPASS={$pass}\tFAIL={$fail}\tTOTAL=".count($tests)."\n";
exit($fail===0?0:1);
