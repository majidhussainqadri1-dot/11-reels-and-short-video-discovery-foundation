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
echo "RC12 Reel feed SQL outage 503 vs healthy zero-result behavioral tests PASS\n";
