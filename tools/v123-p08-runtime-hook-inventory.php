<?php
/**
 * File11 V12.3 P08 / Issue46: READ-ONLY registered WordPress REST hook snapshot.
 *
 * Operator authorized WP CLI:
 *   wp eval-file tools/v123-p08-runtime-hook-inventory.php
 *
 * No REST requests are dispatched. No DB queries, record values, user IDs,
 * secrets or request payloads are read or emitted. The snapshot proves only
 * hook registrations at capture time, NOT their actual execution/authorization,
 * transaction isolation, deployed source SHA, DB version or R3/R4/R5 acceptance.
 */
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ||
    ! class_exists( 'WP_CLI' ) || ! function_exists( 'has_filter' ) ) {
    fwrite( STDERR, "Read-only operator WP-CLI environment required.\n" );
    exit( 1 );
}
$requirements = array(
    array( 'hook'=>'rest_request_before_callbacks', 'callback'=>array( 'RSV_Fresh20_Hardening', 'before_callbacks' ), 'expected_priority'=>5, 'purpose'=>'transactional-private-write-pre-dispatch' ),
    array( 'hook'=>'rest_request_before_callbacks', 'callback'=>array( 'RSV_Future30_Private_Route_Guard', 'guard' ), 'expected_priority'=>5, 'purpose'=>'current-File00-private-eligibility' ),
    array( 'hook'=>'rest_request_before_callbacks', 'callback'=>array( 'RSV_Future30_Private_State_Integrity', 'before_callbacks' ), 'expected_priority'=>20, 'purpose'=>'secondary-collections-mutation-guard' ),
    array( 'hook'=>'rest_pre_dispatch', 'callback'=>array( 'RSV_Future30_Public_Safety', 'pre_dispatch' ), 'expected_priority'=>20, 'purpose'=>'supersession-cycle-precondition' ),
    array( 'hook'=>'rest_post_dispatch', 'callback'=>array( 'RSV_Fresh20_Hardening', 'after_dispatch' ), 'expected_priority'=>99, 'purpose'=>'private-response-and-public-source-minimization' ),
);
$records = array();
$drift = 0;
foreach ( $requirements as $check ) {
    $priority = has_filter( $check['hook'], $check['callback'] );
    $present = false !== $priority;
    $correct = $present && (int) $priority === $check['expected_priority'];
    if ( ! $correct ) ++$drift;
    $records[] = array(
        'hook' => $check['hook'],
        'callback_class' => $check['callback'][0],
        'callback_method' => $check['callback'][1],
        'purpose' => $check['purpose'],
        'expected_priority' => $check['expected_priority'],
        'registered' => $present,
        'observed_priority' => $present ? (int) $priority : null,
        'matches_candidate' => $correct,
    );
}
$report = array(
    'mode' => 'READ_ONLY_HOOK_REGISTRATION_METADATA',
    'governing' => 'File11 V12.3 P00/P08/P09 and Issue46',
    'candidate_version_header' => defined( 'RSV_VERSION' ) ? (string) RSV_VERSION : null,
    'candidate_contract_constant' => defined( 'RSV_CONTRACT_VERSION' ) ? (int) RSV_CONTRACT_VERSION : null,
    'total_expected_hooks' => count( $requirements ),
    'missing_or_unexpected_hooks' => $drift,
    'observed_hooks' => $records,
    'status' => $drift ? 'HOOK_REGISTRATION_DRIFT_OR_MISSING' : 'HOOK_REGISTRATION_MATCHES_CANDIDATE',
    'live_source_parity' => 'UNVERIFIED',
    'transaction_atomicity' => 'UNVERIFIED',
    'actual_REST_callback_execution' => 'NOT_TESTED',
    'database_migration_parity' => 'UNVERIFIED',
    'release_authorized' => false,
);
WP_CLI::line( (string) wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
