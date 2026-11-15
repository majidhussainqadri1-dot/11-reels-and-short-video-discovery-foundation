<?php
/**
 * File11 V12.3 P09 / F11-FUT-006 — sequential second-correction regression.
 * A separate idempotency key MUST NOT insert an existing active supersession
 * a second time. Does NOT prove concurrent two-writer MySQL safety (Issue #38).
 */
require __DIR__.'/bootstrap.php';
final class RSV_Helpers {
    public static function text($v,$n){return substr((string)$v,0,$n);}
    public static function now(){return '2026-11-15 11:00:00';}
    public static function error($code,$msg,$status=503){return new WP_Error($code,$msg,array('status'=>$status));}
}
require dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-future30-feature-write.php';
final class V123_Supersession_Probe {
    use RSV_Future30_Feature_Write_Trait;
    public static $existing=array();
    public static $valid=true;
    public static $writes=0;
    public static $replacement='reel_bbbbbbbb-2222-4333-8444-555555555555';
    private static function canonical_ref_public_valid($owner,$ref,$purpose){
        return self::$valid && $owner==='File 11' && $purpose==='supersession';
    }
    private static function edge_rows($id,$reel,$limit=100){return self::$existing;}
    private static function put_edge($feature,$reel,$mode,$owner,$ref,$data,$start=0,$end=0){
        ++self::$writes;
        return array('public_id'=>'edge_c0ffee','version'=>1);
    }
    public static function write($target=null) {
        return self::feature_write('F11-FUT-006',
            array('id'=>101,'public_id'=>'reel_aaaaaaaa-2222-4333-8444-555555555555'),
            array('replacement_reel_ref'=>$target??self::$replacement,'reason'=>'Correction')
        );
    }
}
$bad=[];$n=0;
$ok=static function($valid,$why)use(&$bad,&$n){++$n;if(!$valid)$bad[]=$why;};
$reset=static function($existing,$valid=true){
    V123_Supersession_Probe::$existing=$existing;
    V123_Supersession_Probe::$valid=$valid;
    V123_Supersession_Probe::$writes=0;
};
$reset(array());
$r=V123_Supersession_Probe::write();
$ok(is_array($r)&&V123_Supersession_Probe::$writes===1,
    'legitimate first correction is inserted once');
$reset(array(array('target_ref'=>V123_Supersession_Probe::$replacement)));
$r=V123_Supersession_Probe::write();
$ok(is_wp_error($r)&&$r->get_error_code()==='rsv_supersession_already_active'&&
    ($r->get_error_data()['status']??0)===409&&V123_Supersession_Probe::$writes===0,
    'second same-target different idempotency key must not insert duplicate');
$reset(array(array('target_ref'=>'reel_eeeeeeee-2222-4333-8444-555555555555')));
$r=V123_Supersession_Probe::write();
$ok(is_wp_error($r)&&$r->get_error_code()==='rsv_supersession_already_active'&&
    V123_Supersession_Probe::$writes===0,
    'second different target blocked without mutation');
$reset(array(
    array('target_ref'=>V123_Supersession_Probe::$replacement),
    array('target_ref'=>'reel_eeeeeeee-2222-4333-8444-555555555555')
));
$r=V123_Supersession_Probe::write();
$ok(is_wp_error($r)&&$r->get_error_code()==='rsv_supersession_ambiguous'&&
    V123_Supersession_Probe::$writes===0,
    'existing ambiguous lineage not hidden by first row');
$reset(new WP_Error('rsv_future_edges_unavailable','SQL failure',array('status'=>503)));
$r=V123_Supersession_Probe::write();
$ok(is_wp_error($r)&&$r->get_error_code()==='rsv_future_edges_unavailable'&&
    V123_Supersession_Probe::$writes===0,
    'failed SQL lookup cannot be treated as no existing active edge');
$reset(array());
$r=V123_Supersession_Probe::write('reel_aaaaaaaa-2222-4333-8444-555555555555');
$ok(is_wp_error($r)&&$r->get_error_code()==='rsv_supersession_cycle'&&
    V123_Supersession_Probe::$writes===0,
    'same Reel cannot supersede itself even if pre-dispatch guard was not called');
$reset(array(),false);
$r=V123_Supersession_Probe::write();
$ok(is_wp_error($r)&&$r->get_error_code()==='rsv_replacement_reel_invalid'&&
    V123_Supersession_Probe::$writes===0,
    'invalid current target stays rejected');
$reset(array(array('not_a_ref'=>true)));
$r=V123_Supersession_Probe::write();
$ok(is_wp_error($r)&&$r->get_error_code()==='rsv_supersession_lookup_invalid'&&
    V123_Supersession_Probe::$writes===0,
    'malformed active edge cannot be silently ignored');
if($bad){fwrite(STDERR,"V12.3 sequential supersession regression:\n".implode("\n",$bad)."\n");exit(1);}
echo "V12.3 sequential supersession mutation guard $n/$n isolated assertions PASS; concurrency still unverified\n";
