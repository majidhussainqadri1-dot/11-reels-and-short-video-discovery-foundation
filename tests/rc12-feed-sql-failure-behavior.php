<?php
/** F11-FR-005 + F30-AT-10: never fabricate an empty successful Reel feed on SQL outage. */
require __DIR__.'/bootstrap.php';
if(!defined('ARRAY_A'))define('ARRAY_A','ARRAY_A');
final class RSV_RC12_Wpdb {
    public $prefix='wp_';
    public $last_error='';
    public $response=null;
    public function prepare($query,...$args){return $query;}
    public function get_results($sql,$mode){return $this->response;}
}
$GLOBALS['wpdb']=new RSV_RC12_Wpdb();
$GLOBALS['rc19_file10_ready']=true;
final class RSV_File10 {
    public static function ready() { return ! empty( $GLOBALS['rc19_file10_ready'] ); }
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-contracts.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-repository.php';
function ensure_feed($ok,$label){if(!$ok){fwrite(STDERR,"FAIL: $label\n");exit(1);}}
$wpdb=$GLOBALS['wpdb'];
$wpdb->response=null; $wpdb->last_error='';
$failed=RSV_Repository::feed(array('limit'=>10,'sort'=>'latest'));
ensure_feed(is_wp_error($failed),'Null query result was incorrectly treated as empty feed');
ensure_feed($failed->get_error_code()==='rsv_feed_query_unavailable','SQL failure lost typed error');
ensure_feed(($failed->get_error_data()['status']??null)===503,'SQL failure must be 503');
$wpdb->response=array(); $wpdb->last_error='Database connection unavailable';
$failed=RSV_Repository::feed(array('limit'=>10));
ensure_feed(is_wp_error($failed),'Explicit SQL error incorrectly treated as a successful empty feed');
$wpdb->response=array(); $wpdb->last_error='';
$valid=RSV_Repository::feed(array('limit'=>10));
ensure_feed(is_array($valid),'Legitimate no-results query must be a normal array');
ensure_feed($valid['items']===array(),'Healthy empty feed must not fabricate results');
ensure_feed($valid['next_cursor']===null,'Healthy empty feed must not invent a pagination cursor');
$GLOBALS['rc19_file10_ready']=false;
$wpdb->response=array(); $wpdb->last_error='';
$unavailable=RSV_Repository::feed(array('limit'=>10));
ensure_feed(is_wp_error($unavailable),'File10 outage was fabricated as a legitimately empty Reel feed');
ensure_feed('rsv_feed_media_unavailable'===$unavailable->get_error_code(),'Missing File10 must return a stable owner-outage error');
ensure_feed(($unavailable->get_error_data()['status']??null)===503,'File10 outage must return HTTP 503');
$GLOBALS['rc19_file10_ready']=true;
ensure_feed(is_array(RSV_Repository::feed(array('limit'=>10))),'Reel feed did not recover when File10 became ready');
echo "RC12/RC19 Reel feed SQL/File10 owner outage 503 vs healthy zero-result behavioral tests PASS\n";
