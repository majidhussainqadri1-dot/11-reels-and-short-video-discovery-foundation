<?php
/**
 * File11 V12.3 P06/P07 read-only owner-link provider readiness inventory.
 *
 * On an explicitly authorized WordPress installation:
 *   wp eval-file tools/v123-p07-owner-link-readiness.php
 *
 * Captures hook REGISTRATION ONLY. Never calls source resolvers, traverses user
 * records, fetches a URL or reads the DB. A registered filter is not proof that
 * an owner can resolve/revalidate a current, rights-cleared public destination.
 */
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ||
    ! class_exists( 'WP_CLI' ) || ! function_exists( 'has_filter' ) ) {
    fwrite( STDERR, "Authorized read-only WP-CLI required.\n" );
    exit( 1 );
}
$requirements = array(
    'canonical_public_url' => 'rsv_future30_verified_public_url',
    'public_owner_reference' => 'rsv_future30_public_ref_valid',
    'reviewed_file10_transcript' => 'rsv_future30_transcript_ref_public_valid',
    'public_file05_course' => 'rsv_future30_file05_public_ref_valid',
    'file10_dubbing_track' => 'rsv_future30_file10_track_ref_valid',
);
$registered = array();
$missing = 0;
foreach ( $requirements as $capability=>$hook ) {
    // No callback is executed; has_filter() queries only registration.
    $present = false !== has_filter( $hook );
    if ( ! $present ) ++$missing;
    $registered[] = array(
        'capability' => $capability,
        'hook_name' => $hook,
        'any_callback_registered' => $present,
        'verified_owner_specific_resolver' => false,
        'live_public_url_checked' => false,
    );
}
$report = array(
    'mode' => 'READ_ONLY_HOOK_METADATA',
    'governing' => 'V12.3 F11-FR-010 F11-FUT-003/004/016 P06/P07 AJ-17',
    'plugin_version_header' => defined( 'RSV_VERSION' ) ? (string) RSV_VERSION : null,
    'required_registrations' => count( $requirements ),
    'missing_registrations' => $missing,
    'capability_hooks' => $registered,
    'status' => $missing ? 'OWNER_LINK_PROVIDER_HOOKS_INCOMPLETE' : 'HOOKS_PRESENT_OWNER_TRUTH_UNVERIFIED',
    'public_canonical_navigation_verified' => false,
    'source_revocation_on_click_verified' => false,
    'file05_file06_file10_deployed_parity_verified' => false,
    'real_browser_rtl_accessibility_verified' => false,
    'live_retest_completed' => false,
    'release_authorized' => false,
);
WP_CLI::line( (string) wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
