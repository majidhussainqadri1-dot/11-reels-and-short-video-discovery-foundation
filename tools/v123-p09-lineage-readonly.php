<?php
/**
 * File11 V12.3 P09 / Issue #38 — operator-only READ-ONLY supersession snapshot.
 *
 * Usage: wp eval-file tools/v123-p09-lineage-readonly.php
 * NO writes, NO patient data, NO source/target IDs in output.
 * A zero-ambiguity snapshot CANNOT certify concurrent atomicity.
 * Never use this tool to repair production data.
 */
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI || ! class_exists( 'WP_CLI' ) ) {
    fwrite( STDERR, "Operator-only WP-CLI required.\n" );
    exit( 1 );
}
global $wpdb;
$prefix = (string) $wpdb->prefix;
$report = array(
    'mode' => 'READ_ONLY_AGGREGATE_ONLY',
    'plan' => 'V12.3 F11-FUT-006 / F30-AT-11',
    'status' => 'unverified',
    'active_supersession_edges' => null,
    'distinct_source_reels' => null,
    'ambiguous_source_groups' => null,
    'table_engine' => null,
    'row_estimate_not_exact' => null,
    'concurrency_atomicity_proven' => false,
    'real_two_writer_test_completed' => false,
    'live_resolved' => false,
    'safety_note' => 'Zero duplicates at one instant does not prove concurrent single-active-lineage safety.',
);
$emit = static function ( $report ) {
    WP_CLI::line( (string) wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
};
if ( ! preg_match( '/^[A-Za-z0-9_]+$/D', $prefix ) ) {
    $report['status'] = 'invalid_prefix';
    $emit( $report );
    return;
}
$table = $prefix . 'rsv_future_edges';
$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
if ( '' !== (string) ( $wpdb->last_error ?? '' ) ) {
    $report['status'] = 'schema_discovery_unavailable';
    $emit( $report );
    return;
}
if ( ! is_string( $exists ) || $table !== $exists ) {
    $report['status'] = 'required_table_missing';
    $emit( $report );
    return;
}
$quoted = chr( 96 ) . $table . chr( 96 );
$columns = $wpdb->get_col( 'SHOW COLUMNS FROM ' . $quoted );
if ( ! is_array( $columns ) || '' !== (string) ( $wpdb->last_error ?? '' ) ) {
    $report['status'] = 'columns_unavailable';
    $emit( $report );
    return;
}
foreach ( array( 'feature_id', 'source_reel_id', 'edge_type', 'status' ) as $required ) {
    if ( ! in_array( $required, $columns, true ) ) {
        $report['status'] = 'incomplete_schema';
        $emit( $report );
        return;
    }
}
$metadata = $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', $wpdb->esc_like( $table ) ), ARRAY_A );
if ( is_array( $metadata ) && '' === (string) ( $wpdb->last_error ?? '' ) ) {
    $report['table_engine'] = (string) ( $metadata['Engine'] ?? '' );
    $report['row_estimate_not_exact'] = isset( $metadata['Rows'] ) ? (int) $metadata['Rows'] : null;
}
$summary = $wpdb->get_row(
    $wpdb->prepare(
        'SELECT COUNT(*) AS active_edges, COUNT(DISTINCT source_reel_id) AS distinct_sources FROM ' . $quoted .
        ' WHERE feature_id=%s AND edge_type=%s AND status=%s',
        'F11-FUT-006', 'supersedes', 'active'
    ), ARRAY_A
);
if ( ! is_array( $summary ) || '' !== (string) ( $wpdb->last_error ?? '' ) ||
    ! isset( $summary['active_edges'], $summary['distinct_sources'] ) ||
    ! ctype_digit( (string) $summary['active_edges'] ) ||
    ! ctype_digit( (string) $summary['distinct_sources'] ) ) {
    $report['status'] = 'aggregate_read_unavailable';
    $emit( $report );
    return;
}
$duplicates = $wpdb->get_var(
    $wpdb->prepare(
        'SELECT COUNT(*) FROM (SELECT source_reel_id FROM ' . $quoted .
        ' WHERE feature_id=%s AND edge_type=%s AND status=%s GROUP BY source_reel_id HAVING COUNT(*)>1) AS duplicate_sources',
        'F11-FUT-006', 'supersedes', 'active'
    )
);
if ( '' !== (string) ( $wpdb->last_error ?? '' ) ||
    ! is_scalar( $duplicates ) || ! ctype_digit( (string) $duplicates ) ) {
    $report['status'] = 'duplicate_group_read_unavailable';
    $emit( $report );
    return;
}
$edge_count = (int) $summary['active_edges'];
$source_count = (int) $summary['distinct_sources'];
$dupe_count = (int) $duplicates;
if ( $source_count > $edge_count || ( $dupe_count === 0 && $source_count !== $edge_count ) ||
    ( $dupe_count > 0 && $edge_count <= $source_count ) ) {
    $report['status'] = 'inconsistent_aggregate_reads';
    $emit( $report );
    return;
}
$report['active_supersession_edges'] = $edge_count;
$report['distinct_source_reels'] = $source_count;
$report['ambiguous_source_groups'] = $dupe_count;
$report['status'] = $dupe_count > 0 ? 'existing_ambiguous_lineage_detected' : 'no_existing_ambiguity_in_this_snapshot';
$emit( $report );
