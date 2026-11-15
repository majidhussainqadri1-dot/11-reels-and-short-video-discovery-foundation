<?php
/**
 * File11 V12.3 P10 STAGING-ONLY, READ-ONLY File10 media-duration acceptance.
 * Operator must prepare 4 public, rights-cleared, consent-safe test videos.
 *
 * Example:
 * F11_TEST_VIDEO_59=101 F11_TEST_VIDEO_60=102 F11_TEST_VIDEO_600=103 \
 * F11_TEST_VIDEO_601=104 wp eval-file tools/v123-p10-file10-duration-staging.php
 *
 * This checks READ-ONLY File10 video state and File11 boundary decisions.
 * It does NOT create, publish, scan, transcode, send email or modify a row.
 * Does NOT prove real REST publishing, rights lifecycle or end-to-end AJ-17.
 */
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ||
    ! class_exists( 'WP_CLI' ) || ! class_exists( 'RSV_File10' ) ||
    ! function_exists( 'wp_get_environment_type' ) ) {
    fwrite( STDERR, "Authorized WordPress WP-CLI with File11 required.\n" );
    exit( 1 );
}
$report = array(
    'mode'=>'STAGING_ONLY_READ_ONLY_OWNER_DURATION',
    'plan'=>'V12.3 F11-FR-002 F11-CEN-01 AJ-17',
    'site_environment'=>wp_get_environment_type(),
    'file10_contract_ready'=>false,
    'cases_passed'=>0,
    'cases_failed'=>0,
    'cases'=>array(),
    'real_file10_provider_installation_verified'=>false,
    'real_rest_reel_creation_tested'=>false,
    'migration_parity_verified'=>false,
    'live_retest_completed'=>false,
    'release_authorized'=>false,
);
$emit = static function ( $report ) {
    WP_CLI::line( (string) wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
};
if ( 'staging' !== wp_get_environment_type() ) {
    $report['status'] = 'DENIED_NOT_STAGING';
    $emit( $report );
    return;
}
if ( ! RSV_File10::ready() ) {
    $report['status'] = 'FILE10_CONTRACT_UNAVAILABLE';
    $emit( $report );
    return;
}
$report['file10_contract_ready'] = true;
$inputs = array();
foreach ( array( 59,60,600,601 ) as $seconds ) {
    $value = getenv( 'F11_TEST_VIDEO_' . $seconds );
    if ( ! is_string( $value ) || ! ctype_digit( $value ) || (int) $value < 1 ) {
        $report['status'] = 'FOUR_TEST_IDS_REQUIRED';
        $emit( $report );
        return;
    }
    $inputs[$seconds] = (int) $value;
}
if ( count( array_unique( array_values( $inputs ) ) ) !== 4 ) {
    $report['status'] = 'TEST_IDS_MUST_BE_DISTINCT';
    $emit( $report );
    return;
}
foreach ( $inputs as $expected_duration=>$id ) {
    $video = RSV_File10::video( $id );
    $result = RSV_File10::validate_for_reel( $id, 0 );
    $actual_duration = is_array( $video ) ? absint( $video['duration_seconds'] ?? 0 ) : null;
    $expected_allowed = in_array( (int) $expected_duration, array(60,600), true );
    $result_matches = $expected_allowed
        ? is_array( $result )
        : ( is_wp_error( $result ) &&
            'rsv_duration_invalid' === $result->get_error_code() &&
            422 === (int) ( (array) $result->get_error_data() )['status'] );
    $passed = $actual_duration === (int) $expected_duration && $result_matches;
    $report['cases'][] = array(
        'expected_seconds'=>(int) $expected_duration,
        'server_verified_duration_matches_fixture'=>$actual_duration === (int) $expected_duration,
        'accepted_as_reel'=>$expected_allowed,
        'result_matches_policy'=>$result_matches,
        'passed'=>$passed,
        'failure_class'=>$passed ? null : ( is_wp_error( $result ) ? $result->get_error_code() : 'owner_data_or_contract_mismatch' ),
    );
    if ( $passed ) ++$report['cases_passed'];
    else ++$report['cases_failed'];
}
$report['status'] = $report['cases_failed'] ? 'STAGING_DURATION_ACCEPTANCE_FAILED' : 'STAGING_DURATION_OWNER_READ_ACCEPTED';
$report['real_file10_provider_installation_verified'] = true;
$emit( $report );
