<?php
/**
 * File11 V12.3 / P00 exact-deployed-state READ-ONLY WP-CLI collector.
 * Usage (operator-approved host): wp eval-file tools/v123-readonly-freeze.php
 *
 * No user/patient rows, credentials, arbitrary wp_options values or logs printed.
 * Table "rows" are database metadata ESTIMATES, never a verified count.
 * A symlink, unreadable file or oversized file makes its source manifest INCOMPLETE.
 * This script itself cannot verify staging, source/deploy parity or a live fix.
 */
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI || ! class_exists( 'WP_CLI' ) ) {
    fwrite( STDERR, "Operator-only WP-CLI required.\n" );
    exit( 1 );
}
global $wpdb;
$errors = array();
$active = (array) get_option( 'active_plugins', array() );
if ( is_multisite() ) $active = array_merge( $active, array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) );
$active = array_values( array_unique( array_map( 'strval', $active ) ) );
sort( $active, SORT_STRING );
$plugin_root = realpath( WP_PLUGIN_DIR );
$plugin_details = array();
foreach ( $active as $basename ) {
    if ( ! preg_match( '/reel|rsv|short.video|video.wall|vwlb|membership|search.discovery/i', $basename ) ) continue;
    $real = realpath( WP_PLUGIN_DIR . '/' . $basename );
    if ( ! $real || ! is_file( $real ) || ! $plugin_root || strpos( $real, $plugin_root . DIRECTORY_SEPARATOR ) !== 0 ) {
        $errors[] = 'relevant-plugin-path-unverifiable:' . $basename;
        continue;
    }
    if ( ! function_exists( 'get_plugin_data' ) ) require_once ABSPATH . 'wp-admin/includes/plugin.php';
    $info = get_plugin_data( $real, false, false );
    $plugin_dir = dirname( $real );
    $files = array();
    $complete = true;
    $file_limit = 1500;
    $seen = 0;
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $plugin_dir, FilesystemIterator::SKIP_DOTS )
        );
        foreach ( $iterator as $entry ) {
            ++$seen;
            if ( $seen > $file_limit ) {
                $errors[] = 'relevant-plugin-manifest-over-limit:' . $basename;
                $complete = false;
                break;
            }
            if ( $entry->isLink() ) {
                $errors[] = 'relevant-plugin-symlink-omitted:' . $basename;
                $complete = false;
                continue;
            }
            if ( ! $entry->isFile() ) continue;
            if ( $entry->getSize() > 10485760 ) {
                $errors[] = 'relevant-plugin-large-file-unhashed:' . $basename;
                $complete = false;
                continue;
            }
            $file_path = $entry->getRealPath();
            if ( ! $file_path || strpos( $file_path, $plugin_dir . DIRECTORY_SEPARATOR ) !== 0 ) {
                $errors[] = 'relevant-plugin-file-path-unverifiable:' . $basename;
                $complete = false;
                continue;
            }
            $relative = str_replace( DIRECTORY_SEPARATOR, '/', substr( $file_path, strlen( $plugin_dir ) + 1 ) );
            $sha = hash_file( 'sha256', $file_path );
            if ( ! is_string( $sha ) || strlen( $sha ) !== 64 ) {
                $errors[] = 'relevant-plugin-hash-failed:' . $basename;
                $complete = false;
                continue;
            }
            $files[ $relative ] = $sha;
        }
    } catch ( Throwable $error ) {
        $errors[] = 'relevant-plugin-enumeration-error:' . $basename;
        $complete = false;
    }
    ksort( $files, SORT_STRING );
    $plugin_details[] = array(
        'plugin_basename' => $basename,
        'header_name' => (string) ( $info['Name'] ?? '' ),
        'header_version' => (string) ( $info['Version'] ?? '' ),
        'manifest_complete' => $complete,
        'file_count' => count( $files ),
        'file_sha256' => $files,
        'manifest_sha256' => $complete ? hash( 'sha256', (string) wp_json_encode( $files ) ) : null,
    );
}
if ( ! $plugin_details ) $errors[] = 'no-matching-active-relevant-plugin';
$table_prefix = (string) $wpdb->prefix;
if ( ! preg_match( '/^[A-Za-z0-9_]+$/D', $table_prefix ) ) {
    $errors[] = 'table-prefix-unverifiable';
    $candidate_tables = array();
} else {
    $candidate_tables = $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_prefix ) . '%' ) );
    if ( ! is_array( $candidate_tables ) || '' !== (string) ( $wpdb->last_error ?? '' ) ) {
        $errors[] = 'table-discovery-error';
        $candidate_tables = array();
    }
}
$schemas = array();
$count_tables = 0;
foreach ( $candidate_tables as $table ) {
    $table = (string) $table;
    if ( strpos( $table, $table_prefix ) !== 0 || ! preg_match( '/rsv|reel|vwlb|smc|file26/i', $table ) ) continue;
    if ( ! preg_match( '/^[A-Za-z0-9_]+$/D', $table ) ) {
        $errors[] = 'unusual-table-identifier-redacted';
        continue;
    }
    if ( ++$count_tables > 80 ) {
        $errors[] = 'relevant-table-limit-exceeded';
        break;
    }
    $q = chr( 96 ) . $table . chr( 96 );
    $columns = $wpdb->get_results( 'SHOW FULL COLUMNS FROM ' . $q, ARRAY_A );
    $cols_ok = is_array( $columns ) && '' === (string) ( $wpdb->last_error ?? '' );
    $indexes = $wpdb->get_results( 'SHOW INDEX FROM ' . $q, ARRAY_A );
    $indexes_ok = is_array( $indexes ) && '' === (string) ( $wpdb->last_error ?? '' );
    $metadata = $wpdb->get_row(
        $wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', $wpdb->esc_like( $table ) ),
        ARRAY_A
    );
    $meta_ok = is_array( $metadata ) && '' === (string) ( $wpdb->last_error ?? '' );
    if ( ! $cols_ok || ! $indexes_ok || ! $meta_ok ) $errors[] = 'relevant-schema-read-failed:' . $table;
    $schemas[ $table ] = array(
        'engine' => $meta_ok ? (string) ( $metadata['Engine'] ?? '' ) : null,
        'row_count_estimate_not_exact' => $meta_ok && isset( $metadata['Rows'] ) ? (int) $metadata['Rows'] : null,
        'columns' => $cols_ok ? array_map( static function ( $r ) {
            return array(
                'name' => (string) ( $r['Field'] ?? '' ),
                'type' => (string) ( $r['Type'] ?? '' ),
                'nullable' => (string) ( $r['Null'] ?? '' ),
            );
        }, $columns ) : null,
        'indexes' => $indexes_ok ? array_map( static function ( $r ) {
            return array(
                'name' => (string) ( $r['Key_name'] ?? '' ),
                'column' => (string) ( $r['Column_name'] ?? '' ),
                'sequence' => (int) ( $r['Seq_in_index'] ?? 0 ),
                'unique' => isset( $r['Non_unique'] ) && 0 === (int) $r['Non_unique'],
                'sub_part' => isset( $r['Sub_part'] ) ? (int) $r['Sub_part'] : null,
            );
        }, $indexes ) : null,
    );
}
ksort( $schemas, SORT_STRING );
$allowed_marker_keys = array(
    'rsv_schema_version',
    'rsv_top20_schema_version',
    'rsv_future30_schema_version',
    'rsv_future30_contract_version',
    'rsv_future30_plan_revision',
    'rsv_migration_checkpoint',
    'rsv_migration_lock',
    'rsv_pre_migration_snapshot',
    'rsv_legacy_cutover_enabled',
);
$markers = array();
foreach ( $allowed_marker_keys as $key ) $markers[ $key ] = array( 'present'=>false, 'value'=>null );
$placeholders = implode( ',', array_fill( 0, count( $allowed_marker_keys ), '%s' ) );
$rows = $wpdb->get_results(
    $wpdb->prepare( 'SELECT option_name, option_value FROM ' . $wpdb->options . ' WHERE option_name IN (' . $placeholders . ')', $allowed_marker_keys ),
    ARRAY_A
);
if ( ! is_array( $rows ) || '' !== (string) ( $wpdb->last_error ?? '' ) ) {
    $errors[] = 'migration-marker-read-failed';
} else {
    foreach ( $rows as $row ) {
        if ( ! is_array( $row ) ) { $errors[] = 'migration-marker-format-invalid'; continue; }
        $key = (string) ( $row['option_name'] ?? '' );
        if ( ! array_key_exists( $key, $markers ) ) continue;
        $value = (string) ( $row['option_value'] ?? '' );
        $safe_scalar = preg_match( '/^[a-zA-Z0-9_.-]{1,64}$/D', $value );
        $plain_key = preg_match( '/^(rsv_(schema_version|top20_schema_version|future30_schema_version|future30_contract_version|future30_plan_revision|migration_checkpoint|legacy_cutover_enabled))$/D', $key );
        $markers[ $key ] = array(
            'present' => true,
            'value' => $plain_key && $safe_scalar ? $value : null,
            'redacted_or_structured' => ! ( $plain_key && $safe_scalar ),
        );
    }
}
$report = array(
    'mode' => 'READ_ONLY_METADATA_ONLY',
    'plan' => 'File11 V12.3 P00',
    'runtime' => array(
        'wordpress' => (string) get_bloginfo( 'version' ),
        'php' => PHP_VERSION,
        'multisite' => is_multisite(),
        'wp_debug_enabled' => defined( 'WP_DEBUG' ) && WP_DEBUG,
    ),
    'active_relevant_plugins' => $plugin_details,
    'relevant_table_schema_and_estimated_rows' => $schemas,
    'allowlisted_migration_markers' => $markers,
    'errors' => array_values( array_unique( $errors ) ),
    'does_not_certify' => array(
        'exact content of private rows',
        'actual migration completion or parity',
        'runtime errors or provider health',
        'real Staging integration',
        'artifact matches GitHub without independent SHA comparison',
        'R3, R4, R5 or resolved live incident',
    ),
);
WP_CLI::line( (string) wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
