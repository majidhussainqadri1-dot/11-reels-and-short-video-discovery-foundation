<?php
/**
 * V12.3 F11-FR-010 / FUT-003 / FUT-004 / FUT-016 canonical link acceptance.
 * All WordPress data and provider assertions are synthetic. NOT R3 or live.
 */
require __DIR__ . '/bootstrap.php';
if ( ! defined( 'ARRAY_A' ) ) define( 'ARRAY_A', 'ARRAY_A' );
$GLOBALS['link_fixture'] = null;
$GLOBALS['provider_current'] = true;
function add_action( ...$args ) { return true; }
function add_filter( ...$args ) { return true; }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_html__( $s, ...$unused ) { return esc_html( $s ); }
function esc_url( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function home_url( $path = '/' ) { return 'https://sabrihomeopathy.com' . $path; }
function wp_parse_url( $s ) { return parse_url( $s ); }
function apply_filters( $hook, $value, ...$args ) {
    if ( $hook === 'rsv_future30_public_ref_valid' )
        return $GLOBALS['provider_current'] === true;
    if ( $hook === 'rsv_future30_verified_public_url' )
        return $GLOBALS['link_fixture'];
    if ( $hook === 'rsv_future30_transcript_ref_public_valid' )
        return $GLOBALS['provider_current'] === true;
    return $value;
}
final class RSV_Helpers {
    public static function table( $n ) { return 'wp_rsv_' . $n; }
    public static function text( $v, $limit ) { return substr( (string) $v, 0, $limit ); }
    public static function json_decode( $value, $default = array() ) {
        return json_decode( (string) $value, true ) ?? $default;
    }
    public static function error( $code, $text, $status = 503 ) {
        return new WP_Error( $code, $text, array( 'status' => $status ) );
    }
}
final class RSV_Repository {
    public static function find( $ref, $public = false ) {
        return array( 'id' => 44, 'public_id' => 'reel_11111111-2222-4333-8444-555555555555' );
    }
    public static function public_dto( $reel ) { return array( 'duration_seconds' => 120 ); }
}
final class RSV_Security {
    public static function can_view_reel( ...$args ) { return true; }
}
final class V123_Link_Mock_DB {
    public $last_error = '';
    public $reads = 0;
    public function prepare( $sql, ...$args ) { return $sql; }
    public function get_results( $sql, $format ) {
        ++$this->reads;
        if ( $this->reads === 1 ) return array( array(
            'public_id'=>'edge1','start_second'=>32,'end_second'=>42,'target_owner'=>'File 06',
            'target_ref'=>'source-1','payload_json'=>'{}'
        ) );
        if ( $this->reads === 2 ) return array( array(
            'public_id'=>'edge2','start_second'=>0,'end_second'=>0,'target_owner'=>'File 05',
            'target_ref'=>'course-2','payload_json'=>'{}'
        ) );
        if ( $this->reads === 5 ) return array( array(
            'public_id'=>'obj1','title'=>'Transcript','payload_json'=>'{"transcript_ref":"vwlb-track-3","reviewed":true}'
        ) );
        return array();
    }
}
require dirname(__DIR__) . '/11-reels-foundation/includes/class-rsv-fresh20-hardening.php';
$fail = array();
$checked = 0;
$assert = static function ( $ok, $reason ) use ( &$fail, &$checked ) {
    ++$checked;
    if ( ! $ok ) $fail[] = $reason;
};
$method = static function ( $name, ...$args ) {
    return ( new ReflectionMethod( RSV_Fresh20_Hardening::class, $name ) )->invoke( null, ...$args );
};
$binding = static function ( $url, $owner = 'File 06', $ref = 'source-1' ) {
    return array( 'owner'=>$owner, 'ref'=>$ref, 'visibility'=>'public',
        'public_current'=>true, 'verified'=>true, 'url'=>$url );
};
$good = 'https://sabrihomeopathy.com/encyclopedia/entry/source-1';
$GLOBALS['link_fixture'] = $binding( $good );
$assert( $method( 'verified_reference_href', 'File 06', 'source-1', 'citation' ) === $good,
    'exact provider-verified same-origin public URL allowed' );
$GLOBALS['provider_current'] = false;
$assert( $method( 'verified_reference_href', 'File 06', 'source-1', 'citation' ) === '',
    'revoked public ref cannot become a link' );
$GLOBALS['provider_current'] = true;
foreach ( array(
    'unbound'=>null,
    'unverified'=>array( 'owner'=>'File 06','ref'=>'source-1','visibility'=>'public',
        'public_current'=>true,'verified'=>false,'url'=>$good ),
    'private'=>array( 'owner'=>'File 06','ref'=>'source-1','visibility'=>'private',
        'public_current'=>true,'verified'=>true,'url'=>$good ),
    'revoked'=>array( 'owner'=>'File 06','ref'=>'source-1','visibility'=>'public',
        'public_current'=>false,'verified'=>true,'url'=>$good ),
    'wrong-owner'=>$binding( $good, 'File 10' ),
    'wrong-ref'=>$binding( $good, 'File 06', 'different' ),
    'external'=>$binding( 'https://attacker.example/entry/source-1' ),
    'external-subdomain'=>$binding( 'https://sabrihomeopathy.com.attacker.example/entry/source-1' ),
    'scheme-downgrade'=>$binding( 'http://sabrihomeopathy.com/entry/source-1' ),
    'javascript'=>$binding( 'javascript:alert(1)' ),
    'userinfo'=>$binding( 'https://admin@sabrihomeopathy.com/entry/source-1' ),
    'wrong-port'=>$binding( 'https://sabrihomeopathy.com:443/entry/source-1' ),
    'crlf'=>$binding( "https://sabrihomeopathy.com/entry/\r\nLocation:evil" ),
    'encoded-crlf'=>$binding( 'https://sabrihomeopathy.com/entry/%0dredirect' ),
    'relative'=>$binding( '/encyclopedia/entry/source-1' ),
    'malformed'=>$binding( '%%%?' ),
    'oversized'=>$binding( 'https://sabrihomeopathy.com/'.str_repeat('x', 2100) ),
) as $name=>$candidate ) {
    $GLOBALS['link_fixture'] = $candidate;
    $assert( $method( 'verified_reference_href', 'File 06', 'source-1', 'citation' ) === '',
        'reject ' . $name );
}
$GLOBALS['link_fixture'] = null;
$plain = $method( 'verified_reference_markup', 'File 06', 'source-1', 'citation' );
$assert( strpos( $plain, '<a' ) === false && strpos( $plain, 'File 06' ) !== false,
    'no provider means plain reference, never a guessed href' );
$GLOBALS['link_fixture'] = $binding( $good );
$clickable = $method( 'verified_reference_markup', 'File 06', 'source-1', 'citation' );
$assert( strpos( $clickable, 'href="' . $good . '"' ) !== false &&
    strpos( $clickable, 'class="rsv-canonical-source"' ) !== false,
    'only verified source link renders as anchor' );
$GLOBALS['link_fixture'] = null;
$GLOBALS['wpdb'] = new V123_Link_Mock_DB();
$html = $method( 'render_safe_tools', array( 'id'=>44, 'public_id'=>'reel_11111111-2222-4333-8444-555555555555' ) );
$assert( strpos( $html, 'Timestamp sources' ) !== false &&
    strpos( $html, 'Related knowledge' ) !== false &&
    strpos( $html, 'Reviewed transcript:' ) !== false &&
    strpos( $html, 'href="' ) === false,
    'complete public study card renders without fabricated links on unavailable provider' );
$assert( $GLOBALS['wpdb']->reads === 5, 'only expected bounded read calls' );
if ( $fail ) {
    fwrite( STDERR, "Canonical source-link fixture failure:\n" . implode( "\n", $fail ) . "\n" );
    exit( 1 );
}
echo "V12.3 File11 canonical owner URL: $checked/$checked isolated checks PASS (not integrated owner/browser acceptance)\n";
