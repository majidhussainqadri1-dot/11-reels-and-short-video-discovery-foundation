<?php
/** RC14: five remaining SQL query families must distinguish outage and a genuinely empty result. */
require __DIR__ . '/bootstrap.php';
if (!defined('ARRAY_A')) define('ARRAY_A','ARRAY_A');
final class RSV_RC14_DB {
    public $prefix='wp_'; public $last_error=''; public $response=array(); public $results=array();
    public function prepare($sql,...$args){return $sql;}
    public function esc_like($q){return (string)$q;}
    public function get_results($sql,$mode){return count($this->results) ? array_shift($this->results) : $this->response;}
}
$GLOBALS['wpdb']=new RSV_RC14_DB();
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-contracts.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-repository.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-reels.php';
function apply_filters($name,$value){return $value;}
function rc14_assert($ok,$label){if(!$ok){fwrite(STDERR,"FAIL: $label\n");exit(1);}}
$wpdb=$GLOBALS['wpdb'];
$GLOBALS['rc14_reel']=array('published_at'=>'2026-08-12 12:00:00','updated_at'=>'2026-08-12 12:00:00','id'=>1);
$cases=array(
 'history'=>static fn()=>RSV_Repository::history(7),
 'neighbors'=>static fn()=>RSV_Repository::neighbors($GLOBALS['rc14_reel']),
 'author'=>static fn()=>RSV_Repository::public_by_author(7),
 'search'=>static fn()=>RSV_Repository::search_public('fever'),
 'insights'=>static fn()=>RSV_Reels::insights(7),
);
foreach($cases as $name=>$run) {
    $wpdb->last_error='';$wpdb->response=null;$wpdb->results=array();
    $value=$run();
    rc14_assert(is_wp_error($value),"$name: null query returned false empty success");
    rc14_assert(($value->get_error_data()['status']??null)===503,"$name: expected HTTP 503");
    $wpdb->last_error='SQL failure';$wpdb->response=array();$wpdb->results=array();
    rc14_assert(is_wp_error($run()),"$name: last_error returned false empty success");
    $wpdb->last_error='';$wpdb->response=array();$wpdb->results=array();
    rc14_assert(is_array($run()),"$name: healthy empty result should succeed");
}
$wpdb->last_error='';$wpdb->response=array();$wpdb->results=array(array(),null);
rc14_assert(is_wp_error(RSV_Repository::neighbors($GLOBALS['rc14_reel'])),'neighbors: second query failure was concealed');
echo "RC14 five native SQL read families: 16 failure/empty assertions PASS\n";
