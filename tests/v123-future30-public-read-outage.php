<?php
/**
 * V12.3 F11-FR-013 / F11-NFR-003: Future30 post-read current-Reel lookup.
 *
 * Behavioral fixture only. This is NOT WordPress, MySQL, staging, or live QA.
 * A SQL read outage must not be reported as a removed/private Reel (HTTP 404).
 */
require __DIR__ . '/bootstrap.php';

function add_filter( $name, $callback, $priority = 10, $args = 1 ) { return true; }
function rest_ensure_response( $data ) { return new WP_REST_Response( $data ); }

final class WP_REST_Response {
    private $data, $status;
    public function __construct( $data = array(), $status = 200 ) {
        $this->data = $data; $this->status = $status;
    }
    public function get_data() { return $this->data; }
    public function set_data( $data ) { $this->data = $data; }
    public function get_status() { return $this->status; }
    public function set_status( $status ) { $this->status = $status; }
}
final class RSV_Helpers {
    public static function text( $value, $length ) { return substr( (string) $value, 0, $length ); }
}
final class RSV_Security {
    public static $allow = true;
    public static function can_view_reel( $reel, $user_id = 0 ) { return self::$allow; }
}
final class RSV_Repository {
    public static $reel = null;
    public static $sql_error = '';
    public static $calls = 0;
    public static function find( $id, $public = false ) {
        global $wpdb;
        self::$calls++;
        $wpdb->last_error = self::$sql_error;
        return self::$reel;
    }
}
final class RSV_V123_Read_DB { public $last_error = ''; }
final class RSV_V123_Read_Request {
    private $route, $method;
    public function __construct( $method = 'GET', $route = null ) {
        $this->method = $method;
        $this->route = $route ?: '/rsv/v1/reels/reel_11111111-2222-4333-8444-555555555555/future30/F11-FUT-005';
    }
    public function get_method() { return $this->method; }
    public function get_route() { return $this->route; }
}
require dirname(__DIR__) . '/11-reels-foundation/includes/class-rsv-future30-public-safety.php';

$cases = array(
    array( '01 SQL outage with null row',         null, 'SQL timed out', true,  'GET', true, 503, 'rsv_future30_reel_read_unavailable' ),
    array( '02 SQL outage with stale row',        array('id'=>7,'public_id'=>'reel_a'), 'SQL refused', true, 'GET', true, 503, 'rsv_future30_reel_read_unavailable' ),
    array( '03 legitimate missing reel',         null, '', true, 'GET', true, 404, 'rsv_not_found' ),
    array( '04 legitimate denied private reel',  array('id'=>7,'public_id'=>'reel_a'), '', false, 'GET', true, 404, 'rsv_not_found' ),
    array( '05 accessible reel',                 array('id'=>7,'public_id'=>'reel_a'), '', true, 'GET', true, 200, '' ),
    array( '06 alternate method ignored',        null, 'SQL refused', true, 'POST', true, 200, '' ),
    array( '07 missing feature payload ignored', null, 'SQL refused', true, 'GET', false, 200, '' ),
    array( '08 zero-error empty-string SQL',      null, '', true, 'GET', true, 404, 'rsv_not_found' ),
    array( '09 other route unchanged',           null, 'SQL refused', true, 'GET', true, 200, '', '/other/v1/route' ),
);
$errors = array();
foreach ( $cases as $case ) {
    $GLOBALS['wpdb'] = new RSV_V123_Read_DB();
    RSV_Repository::$reel = $case[1];
    RSV_Repository::$sql_error = $case[2];
    RSV_Repository::$calls = 0;
    RSV_Security::$allow = $case[3];
    $request = new RSV_V123_Read_Request( $case[4], $case[9] ?? null );
    $payload = $case[5] ? array('feature_id'=>'F11-FUT-005','objects'=>array(),'edges'=>array()) : array('objects'=>array());
    $response = new WP_REST_Response( $payload );
    $actual = RSV_Future30_Public_Safety::filter_response( $response, null, $request );
    $code = (array) $actual->get_data();
    $error_code = (string) ( $code['code'] ?? '' );
    if ( $actual->get_status() !== $case[6] || $error_code !== $case[7] ) {
        $errors[] = $case[0] . ' expected ' . $case[6] . '/' . $case[7] .
            ' got ' . $actual->get_status() . '/' . $error_code;
    }
    if ( in_array( $case[6], array(404,503), true ) &&
         ( array_key_exists('objects',$code) || array_key_exists('edges',$code) ) ) {
        $errors[] = $case[0] . ' leaked partial provider payload on a denied/unavailable projection';
    }
    if ( $case[6] === 200 && $actual !== $response ) {
        $errors[] = $case[0] . ' replaced an otherwise valid response unexpectedly';
    }
}
if ( $errors ) {
    fwrite(STDERR, "V12.3 Future30 read-truth failures:\n" . implode("\n",$errors) . "\n");
    exit(1);
}
echo 'V12.3 Future30 read-truth ' . count($cases) . '/' . count($cases) .
    " isolated scenarios PASS (not WordPress/staging/live)\n";
