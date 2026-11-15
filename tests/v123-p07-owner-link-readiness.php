<?php
/** Synthetic File11 P07 owner-hook inventory, never a connected WP verification. */
require __DIR__ . '/bootstrap.php';
define( 'WP_CLI', true );
define( 'RSV_VERSION', '1.2.0-rc21' );
final class WP_CLI {
    public static $printed = '';
    public static function line( $text ) { self::$printed = $text; }
}
$GLOBALS['available_hooks'] = array();
function has_filter( $hook ) { return ! empty( $GLOBALS['available_hooks'][$hook] ); }
$path = dirname(__DIR__) . '/tools/v123-p07-owner-link-readiness.php';
$run = static function () use ( $path ) {
    WP_CLI::$printed = '';
    require $path;
    return json_decode( WP_CLI::$printed, true );
};
$failed = array(); $count = 0;
$check = static function ( $ok, $why ) use ( &$failed, &$count ) {
    ++$count;
    if ( ! $ok ) $failed[] = $why;
};
$a = $run();
$check( ( $a['required_registrations'] ?? -1 ) === 5
    && ( $a['missing_registrations'] ?? -1 ) === 5
    && ( $a['status'] ?? '' ) === 'OWNER_LINK_PROVIDER_HOOKS_INCOMPLETE',
    'no registered providers must not masquerade as working canonical URLs' );
$check( ( $a['release_authorized'] ?? true ) === false &&
    ( $a['public_canonical_navigation_verified'] ?? true ) === false &&
    ( $a['source_revocation_on_click_verified'] ?? true ) === false,
    'read-only metadata inventory never claims live/source-release acceptance' );
$GLOBALS['available_hooks']['rsv_future30_verified_public_url'] = true;
$b = $run();
$check( ( $b['missing_registrations'] ?? -1 ) === 4 &&
    ( $b['capability_hooks'][0]['any_callback_registered'] ?? false ) === true &&
    ( $b['capability_hooks'][0]['verified_owner_specific_resolver'] ?? true ) === false,
    'single registered URL callback still unverified per File05/06/10 owner' );
foreach ( $b['capability_hooks'] as $row ) {
    $check( isset( $row['hook_name'], $row['capability'] ) &&
        ( $row['live_public_url_checked'] ?? true ) === false,
        'no hook probes actual user/source data' );
}
foreach ( array(
    'rsv_future30_public_ref_valid',
    'rsv_future30_transcript_ref_public_valid',
    'rsv_future30_file05_public_ref_valid',
    'rsv_future30_file10_track_ref_valid',
) as $hook ) $GLOBALS['available_hooks'][$hook] = true;
$c = $run();
$check( ( $c['missing_registrations'] ?? -1 ) === 0
    && ( $c['status'] ?? '' ) === 'HOOKS_PRESENT_OWNER_TRUTH_UNVERIFIED'
    && ( $c['file05_file06_file10_deployed_parity_verified'] ?? true ) === false,
    'all filter registrations still do not establish provider semantics or parity' );
$check( strpos( WP_CLI::$printed, 'patient') === false &&
    strpos( WP_CLI::$printed, 'password') === false &&
    strpos( WP_CLI::$printed, 'private_record') === false,
    'output discloses no personal records or credentials' );
if ( $failed ) {
    fwrite( STDERR, "P07 owner-link inventory fixture failures:\n".implode("\n",$failed)."\n" );
    exit(1);
}
echo "V12.3 File11 P07 owner link readiness inventory: $count/$count synthetic checks PASS (not installed integration)\n";
