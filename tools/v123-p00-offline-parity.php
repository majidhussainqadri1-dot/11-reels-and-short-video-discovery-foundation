<?php
/**
 * File 11 V12.3 P00 — OFFLINE, READ-ONLY source checksum parity verifier.
 *
 * php tools/v123-p00-offline-parity.php SNAPSHOT.json EXTRACTED_PLUGIN_ROOT PLUGIN_BASENAME
 *
 * SNAPSHOT.json must come from authorized WP-CLI tools/v123-readonly-freeze.php.
 * The code reads a local folder and an evidence file ONLY; it never accesses
 * WordPress, remote URLs, databases or credentials, and never mutates files.
 * Even CODE_MATCH_ONLY is not proof of DB version, migration state, live tests,
 * provider parity, acceptance or release authorization.
 */
declare(strict_types=1);
if ( PHP_SAPI !== 'cli' || $argc !== 4 ) {
    fwrite( STDERR, "Usage: php tools/v123-p00-offline-parity.php SNAPSHOT.json EXTRACTED_PLUGIN_ROOT PLUGIN_BASENAME\n" );
    exit( 2 );
}
$report = array(
    'mode' => 'READ_ONLY_OFFLINE_SOURCE_COMPARE',
    'status' => 'UNVERIFIED',
    'manifest_evidence' => 'UNVERIFIED',
    'source_code_parity' => 'UNVERIFIED',
    'candidate_files' => 0,
    'deployed_evidence_files' => 0,
    'missing_candidate_files' => null,
    'unexpected_candidate_files' => null,
    'sha256_mismatches' => null,
    'database_parity' => 'UNVERIFIED',
    'migration_parity' => 'UNVERIFIED',
    'active_runtime_capabilities' => 'UNVERIFIED',
    'staging_acceptance' => 'NOT_PERFORMED',
    'live_retest' => 'NOT_PERFORMED',
    'release_authorized' => false,
    'errors' => array(),
);
$emit = static function ( $status, $errors, $exit_status ) use ( &$report ) {
    $report['status'] = $status;
    $report['errors'] = array_values( array_unique( $errors ) );
    echo json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ), "\n";
    exit( $exit_status );
};
$evidence_path = realpath( (string) $argv[1] );
$candidate_root = realpath( (string) $argv[2] );
$basename = (string) $argv[3];
if ( ! $evidence_path || ! is_file( $evidence_path ) || ! is_readable( $evidence_path ) ||
    filesize( $evidence_path ) > 3145728 || ! $candidate_root || ! is_dir( $candidate_root ) ||
    ! is_readable( $candidate_root ) ) {
    $emit( 'INPUT_UNVERIFIED', array( 'evidence-or-candidate-unavailable' ), 2 );
}
if ( ! preg_match( '#^[a-zA-Z0-9_-]+/[a-zA-Z0-9_.-]+\.php$#D', $basename ) ) {
    $emit( 'INPUT_UNVERIFIED', array( 'invalid-plugin-basename' ), 2 );
}
$evidence = json_decode( (string) file_get_contents( $evidence_path ), true );
if ( ! is_array( $evidence ) || 'READ_ONLY_METADATA_ONLY' !== ( $evidence['mode'] ?? null ) ||
    ! is_array( $evidence['active_relevant_plugins'] ?? null ) ) {
    $emit( 'INPUT_UNVERIFIED', array( 'invalid-readonly-evidence-format' ), 2 );
}
$matched = array_values( array_filter( $evidence['active_relevant_plugins'], static function ( $entry ) use ( $basename ) {
    return is_array( $entry ) && ( $entry['plugin_basename'] ?? null ) === $basename;
} ) );
if ( count( $matched ) !== 1 ) $emit( 'INPUT_UNVERIFIED', array( 'zero-or-multiple-matching-plugin-manifests' ), 2 );
$installed = $matched[0];
if ( true !== ( $installed['manifest_complete'] ?? null ) ||
    ! is_array( $installed['file_sha256'] ?? null ) ||
    ! is_int( $installed['file_count'] ?? null ) ||
    (int) $installed['file_count'] < 1 ||
    ! is_string( $installed['manifest_sha256'] ?? null ) ) {
    $emit( 'INCOMPLETE_DEPLOYED_EVIDENCE', array( 'deployed-file-manifest-incomplete' ), 2 );
}
$deployed_files = $installed['file_sha256'];
if ( count( $deployed_files ) !== (int) $installed['file_count'] ) {
    $emit( 'INVALID_EVIDENCE_MANIFEST', array( 'deployed-file-count-mismatch' ), 2 );
}
foreach ( $deployed_files as $relative => $sha ) {
    if ( ! is_string( $relative ) || ! preg_match( '#^[a-zA-Z0-9_./-]+$#D', $relative ) ||
        str_starts_with( $relative, '/' ) || str_contains( '/' . $relative . '/', '/../' ) ||
        str_contains( '/' . $relative . '/', '/./' ) || ! is_string( $sha ) ||
        ! preg_match( '/^[a-f0-9]{64}$/D', $sha ) ) {
        $emit( 'INVALID_EVIDENCE_MANIFEST', array( 'unsafe-or-invalid-deployed-file-digest' ), 2 );
    }
}
ksort( $deployed_files, SORT_STRING );
$evidence_digest = hash( 'sha256', (string) json_encode( $deployed_files ) );
if ( ! hash_equals( $evidence_digest, (string) $installed['manifest_sha256'] ) ) {
    $emit( 'INVALID_EVIDENCE_MANIFEST', array( 'deployed-manifest-digest-invalid' ), 2 );
}
$report['manifest_evidence'] = 'CONSISTENT_WITH_SNAPSHOT';
$report['deployed_evidence_files'] = count( $deployed_files );
$files = array();
$errors = array();
$seen = 0;
try {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator( $candidate_root, FilesystemIterator::SKIP_DOTS )
    );
    foreach ( $iterator as $item ) {
        if ( ++$seen > 1500 ) {
            $errors[] = 'candidate-manifest-over-limit';
            break;
        }
        if ( $item->isLink() ) {
            $errors[] = 'candidate-symlink-omitted';
            continue;
        }
        if ( ! $item->isFile() ) continue;
        if ( $item->getSize() > 10485760 ) {
            $errors[] = 'candidate-large-file-unhashed';
            continue;
        }
        $real = $item->getRealPath();
        if ( ! $real || strpos( $real, $candidate_root . DIRECTORY_SEPARATOR ) !== 0 ) {
            $errors[] = 'candidate-file-path-unverifiable';
            continue;
        }
        $relative = str_replace( DIRECTORY_SEPARATOR, '/', substr( $real, strlen( $candidate_root ) + 1 ) );
        if ( ! preg_match( '#^[a-zA-Z0-9_./-]+$#D', $relative ) ) {
            $errors[] = 'candidate-path-invalid';
            continue;
        }
        $digest = hash_file( 'sha256', $real );
        if ( ! is_string( $digest ) || ! preg_match( '/^[a-f0-9]{64}$/D', $digest ) ) {
            $errors[] = 'candidate-file-hash-failed';
            continue;
        }
        $files[ $relative ] = $digest;
    }
} catch ( Throwable $error ) {
    $errors[] = 'candidate-directory-enumeration-unavailable';
}
$report['candidate_files'] = count( $files );
if ( $errors ) $emit( 'INCOMPLETE_CANDIDATE_EVIDENCE', $errors, 2 );
ksort( $files, SORT_STRING );
$main_file = substr( $basename, strpos( $basename, '/' ) + 1 );
$main_path = $candidate_root . DIRECTORY_SEPARATOR . $main_file;
if ( ! is_file( $main_path ) || ! isset( $files[ $main_file ] ) ) {
    $emit( 'INCOMPLETE_CANDIDATE_EVIDENCE', array( 'candidate-plugin-entrypoint-missing' ), 2 );
}
$header_source = file_get_contents( $main_path, false, null, 0, 8192 );
if ( ! is_string( $header_source ) || ! preg_match( '/^\s*\*?\s*Version\s*:\s*(\S+)/mi', $header_source, $matches ) ||
    ! hash_equals( (string) ( $installed['header_version'] ?? '' ), (string) $matches[1] ) ) {
    $emit( 'VERSION_HEADER_MISMATCH', array( 'candidate-header-version-does-not-match-snapshot' ), 2 );
}
$missing = array_diff_key( $deployed_files, $files );
$extra = array_diff_key( $files, $deployed_files );
$changed = 0;
foreach ( array_intersect_key( $deployed_files, $files ) as $key => $sha ) {
    if ( ! hash_equals( $sha, $files[ $key ] ) ) ++$changed;
}
$report['missing_candidate_files'] = count( $missing );
$report['unexpected_candidate_files'] = count( $extra );
$report['sha256_mismatches'] = $changed;
$actual_manifest_digest = hash( 'sha256', (string) json_encode( $files ) );
if ( $missing || $extra || $changed || ! hash_equals( $evidence_digest, $actual_manifest_digest ) ) {
    $report['source_code_parity'] = 'MISMATCH';
    $emit( 'CODE_MISMATCH', array( 'candidate-does-not-match-exact-deployed-file-checksums' ), 2 );
}
$report['source_code_parity'] = 'MATCH';
$emit( 'CODE_MATCH_ONLY', array( 'Source checksum equality is not DB/migration/runtime acceptance or permission to deploy.' ), 0 );
