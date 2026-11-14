<?php
/**
 * V12.3 F11-FR-013 + NFR-003: base Future30 reel() read SQL truth.
 * Isolated fixture ONLY; not an actual WordPress/MySQL/live journey.
 */
require __DIR__ . '/bootstrap.php';
final class RSV_Contracts { const CAP_MANAGE = 'manage_reels'; }
final class RSV_Helpers {
    public static function error( $code, $message, $status = 400 ) {
        return new WP_Error( $code, $message, array( 'status' => $status ) );
    }
}
final class RSV_Security {
    public static $manage = false;
    public static function can( $cap ) { return self::$manage; }
}
final class RSV_V123_Repo {
    public static $reel = null;
    public static $sql_error = '';
}
final class RSV_Repository {
    public static function find( $id, $public = false ) {
        global $wpdb;
        $wpdb->last_error = RSV_V123_Repo::$sql_error;
        return RSV_V123_Repo::$reel;
    }
}
final class RSV_V123_TestDB { public $last_error = ''; }
require dirname(__DIR__) . '/11-reels-foundation/includes/trait-rsv-future30-storage.php';
final class RSV_V123_Trait_Wrapper {
    use RSV_Future30_Storage_Trait;
    public static function check( $ref, $owner = false ) {
        return self::reel( $ref, $owner );
    }
}
$cases = array(
    array( '01 SQL null unavailable',null,'SQL disconnected',false,false,'rsv_future30_reel_unavailable',503 ),
    array( '02 SQL stale row unavailable',array('id'=>12,'owner_id'=>7),'SQL timeout',false,false,'rsv_future30_reel_unavailable',503 ),
    array( '03 absent authorized 404',null,'',false,false,'rsv_not_found',404 ),
    array( '04 owner reads owned reel',array('id'=>12,'owner_id'=>7),'',true,false,'',0 ),
    array( '05 nonowner denied',array('id'=>12,'owner_id'=>77),'',true,false,'rsv_forbidden',403 ),
    array( '06 manager authorized',array('id'=>12,'owner_id'=>77),'',true,true,'',0 ),
    array( '07 public nonowner lookup',array('id'=>12,'owner_id'=>77),'',false,false,'',0 ),
);
$errors = array();
foreach ( $cases as $test ) {
    $GLOBALS['wpdb'] = new RSV_V123_TestDB();
    RSV_V123_Repo::$reel = $test[1];
    RSV_V123_Repo::$sql_error = $test[2];
    RSV_Security::$manage = $test[4];
    $result = RSV_V123_Trait_Wrapper::check( 'reel_a', $test[3] );
    $code = is_wp_error( $result ) ? $result->get_error_code() : '';
    $status = is_wp_error( $result ) ? (int) $result->get_error_data()['status'] : 0;
    if ( $code !== $test[5] || $status !== $test[6] ) {
        $errors[] = $test[0] . ' expected ' . $test[5] . '/' . $test[6] . ' got ' . $code . '/' . $status;
    }
}
if ( $errors ) { fwrite(STDERR, implode("\n",$errors)."\n"); exit(1); }
echo "V12.3 Future30 pre-GET Reel SQL " . count($cases) .
    "/" . count($cases) . " isolated scenarios PASS (not WordPress/staging/live)\n";
