<?php
/**
 * V12.3 F11-FR-013 / F11-NFR-003 / F30-AT-10.
 * Exact-source Future30 object/edge read-outage behaviors (isolated fixtures).
 * These are NOT WordPress/MySQL/staging/browser/live tests.
 */
require __DIR__ . '/bootstrap.php';
if ( ! defined( 'ARRAY_A' ) ) define( 'ARRAY_A', 'ARRAY_A' );
function rest_ensure_response( $value ) { return $value; }
function esc_html__( $value, $domain = '' ) { return $value; }
function apply_filters( $name, $value, ...$args ) { return $value; }
function add_filter( $name, $callback, $priority=10, $args=1 ) { return true; }

final class RSV_Helpers {
    public static function no_cache_private() { return null; }
    public static function table( $name ) { return 'wp_rsv_' . $name; }
    public static function error( $code, $message, $status=400 ) {
        return new WP_Error( $code, $message, array( 'status'=>$status ) );
    }
    public static function json_decode( $input, $default=array() ) {
        $value = json_decode( (string) $input, true );
        return is_array( $value ) ? $value : $default;
    }
    public static function text( $input, $limit ) { return substr( (string) $input, 0, $limit ); }
}
final class RSV_Contracts { const CAP_MANAGE='manage_reels'; }
final class RSV_Security {
    public static function can_view_reel( $row, $user=0 ) { return true; }
    public static function can( $cap ) { return true; }
}
final class RSV_Repository {
    public static function find( $id, $public=false ) {
        global $wpdb;
        $wpdb->last_error = '';
        return array('id'=>27,'owner_id'=>get_current_user_id(),'public_id'=>'reel_any','topic'=>'clinical','language'=>'en-US');
    }
    public static function public_dto( $row ) { return array('duration_seconds'=>120); }
}
final class RSV_V123_Query_DB {
    public $last_error = '';
    public $objects = array();
    public $edges = array();
    public $objects_error = '';
    public $edges_error = '';
    public function prepare( $sql, ...$args ) { return $sql; }
    public function get_results( $sql, $output = ARRAY_A ) {
        $objects = strpos( $sql, 'wp_rsv_future_objects' ) !== false;
        $this->last_error = $objects ? $this->objects_error : $this->edges_error;
        return $objects ? $this->objects : $this->edges;
    }
}
require dirname(__DIR__) . '/11-reels-foundation/includes/trait-rsv-future30-storage.php';
require dirname(__DIR__) . '/11-reels-foundation/includes/trait-rsv-future30-feature-write.php';
require dirname(__DIR__) . '/11-reels-foundation/includes/trait-rsv-future30-experience.php';
require dirname(__DIR__) . '/11-reels-foundation/includes/trait-rsv-future30-user-features.php';
final class RSV_V123_Query_Service {
    use RSV_Future30_Storage_Trait;
    use RSV_Future30_Feature_Write_Trait;
    use RSV_Future30_Experience_Trait;
    use RSV_Future30_User_Features_Trait;
    public static function definitions() {
        return array( 'F11-FUT-017'=>array('slug'=>'chapters','owner'=>'File 11') );
    }
    public static function objectCheck() { return self::object_rows( 'F11-FUT-017', 27 ); }
    public static function edgeCheck() { return self::edge_rows( 'F11-FUT-017', 27 ); }
    public static function projectionCheck() { return self::public_projection( 'F11-FUT-017', array('id'=>27) ); }
    public static function seriesCheck() { return self::public_series_rows(); }
    public static function toolsCheck() { return self::public_tools( array('id'=>27) ); }
}
function row( $key ) {
    if ( $key === 'objects' ) return array(array(
        'public_id'=>'f30_1','owner_id'=>7,'title'=>'Lesson',
        'payload_json'=>'{"description":"safe"}','version'=>1
    ));
    return array(array(
        'public_id'=>'edge_1','target_owner'=>'File 11','target_ref'=>'reel_1',
        'start_second'=>0,'end_second'=>30,'payload_json'=>'{"title":"Chapter A"}'
    ));
}
$scenarios = array(
    array('01 object healthy empty','object',array(),'',null,'empty'),
    array('02 object healthy row','object',row('objects'),'',null,'decoded'),
    array('03 object SQL null','object',null,'DB timeout','rsv_future_objects_query_unavailable','error'),
    array('04 object SQL false','object',false,'','rsv_future_objects_query_unavailable','error'),
    array('05 object error with []','object',array(),'DB unavailable','rsv_future_objects_query_unavailable','error'),
    array('06 edge healthy empty','edge',array(),'',null,'empty'),
    array('07 edge healthy row','edge',row('edges'),'',null,'decoded'),
    array('08 edge SQL null','edge',null,'DB timeout','rsv_future_edges_query_unavailable','error'),
    array('09 edge SQL false','edge',false,'','rsv_future_edges_query_unavailable','error'),
    array('10 edge error with []','edge',array(),'DB unavailable','rsv_future_edges_query_unavailable','error'),
    array('11 projection object SQL outage','projection-object',null,'DB unavailable','rsv_future_objects_query_unavailable','error'),
    array('12 projection edge SQL outage','projection-edge',null,'DB unavailable','rsv_future_edges_query_unavailable','error'),
    array('13 series SQL outage','series',null,'DB unavailable','rsv_future_objects_query_unavailable','error'),
    array('14 path SQL outage','path',null,'DB unavailable','rsv_future_objects_query_unavailable','error'),
    array('15 generic GET SQL outage','feature-get',null,'DB unavailable','rsv_future_objects_query_unavailable','error'),
    array('16 public tools SQL outage','tools',null,'DB unavailable','rsv_future_objects_query_unavailable','error'),
    array('17 public study UI degraded','render',null,'DB unavailable',null,'degraded'),
    array('18 graph SQL outage','graph',null,'DB unavailable','rsv_future_objects_query_unavailable','error'),
    array('19 healthy public projection','projection-ok',array(),'',null,'projection'),
    array('20 healthy empty series','series-ok',array(),'',null,'empty'),
    array('21 AI context failed object read','ai-object',null,'DB unavailable','rsv_future_objects_query_unavailable','error'),
    array('22 AI context failed edge read','ai-edge',null,'DB unavailable','rsv_future_edges_query_unavailable','error'),
    array('23 AI context healthy empty','ai-ok',array(),'',null,'ai-context'),
);
$errors = array();
foreach ( $scenarios as $test ) {
    $db = new RSV_V123_Query_DB();
    $GLOBALS['wpdb']=$db;
    list($name,$mode,$rows,$sqlError,$errorCode,$expect)=$test;
    if ( in_array($mode,array('edge','projection-edge','ai-edge'),true) ) {
        $db->edges=$rows;
        $db->edges_error=$sqlError;
    } else {
        $db->objects=$rows;
        $db->objects_error=$sqlError;
    }
    $service = new RSV_V123_Query_Service();
    switch($mode) {
        case 'object': $actual=RSV_V123_Query_Service::objectCheck(); break;
        case 'edge': $actual=RSV_V123_Query_Service::edgeCheck(); break;
        case 'projection-object': case 'projection-edge': case 'projection-ok':
            $actual=RSV_V123_Query_Service::projectionCheck(); break;
        case 'series': case 'series-ok': $actual=$service->series_list(); break;
        case 'path': $actual=$service->path_list(); break;
        case 'feature-get': $actual=$service->reel_feature_get(array('feature'=>'F11-FUT-017','id'=>'reel_any')); break;
        case 'tools': $actual=RSV_V123_Query_Service::toolsCheck(); break;
        case 'render': $actual=RSV_V123_Query_Service::render_tools(array('id'=>27)); break;
        case 'graph': $actual=$service->knowledge_graph(array('id'=>'reel_any')); break;
        case 'ai-object': case 'ai-edge': case 'ai-ok': $actual=$service->ai_context(array('id'=>'reel_any')); break;
        default: $actual=null;
    }
    if ( $expect==='error' ) {
        if ( ! is_wp_error($actual) || $actual->get_error_code()!==$errorCode ||
             (int) ($actual->get_error_data()['status']??0)!==503 ) {
            $errors[]=$name.' expected typed 503 '.$errorCode.'; got '.(is_wp_error($actual)?$actual->get_error_code():gettype($actual));
        }
    } elseif ($expect==='empty' && ( ! is_array($actual) || count($actual)!==0 )) {
        $errors[]=$name.' expected healthy empty array';
    } elseif ($expect==='decoded' && (!is_array($actual)||!isset($actual[0]['payload'])||isset($actual[0]['payload_json']))) {
        $errors[]=$name.' expected decoded safe payload';
    } elseif ($expect==='degraded' && (!is_string($actual)||strpos($actual,'temporarily unavailable')===false||strpos($actual,'role="status"')===false)) {
        $errors[]=$name.' did not emit an accessible temporary-unavailable message';
    } elseif ($expect==='ai-context' && (!is_array($actual)||($actual['owner']??'')!=='File 16'||($actual['execute_ai']??true)!==false||!is_array($actual['context']??null))) {
        $errors[]=$name.' did not return safe nonexecuting File16 context';
    } elseif ($expect==='projection' && (!is_array($actual)||!array_key_exists('objects',$actual)||!array_key_exists('edges',$actual))) {
        $errors[]=$name.' expected healthy projection object/edges';
    }
}
if($errors){fwrite(STDERR,"V12.3 read truth failures:\n".implode("\n",$errors)."\n");exit(1);}
echo 'V12.3 Future30 object/edge read-truth '.count($scenarios).'/'.count($scenarios).
    " isolated cases PASS (not WordPress/staging/live)\n";
