<?php
/** Final-current release metadata truth; historical rc1-rc12 entries must remain intact. */
$root = dirname( __DIR__ );
$expect = array(
    'README.md' => array(
        '**Current repository candidate:** `1.2.0-rc13`',
        'bash tools/build-package.sh packages/reels-foundation-11-1.2.0-rc13.zip',
    ),
    'STATUS.md' => array(
        '# File 11 Status — 1.2.0-rc13 Future30',
        '| Repository source | `1.2.0-rc13`, File 11 contract `19` |',
    ),
    'RELEASE-MANIFEST.md' => array(
        '# Release Manifest — File 11 v1.2.0-rc13',
        '- Plugin version: `1.2.0-rc13`',
        '- Package: `reels-foundation-11-1.2.0-rc13.zip`',
        '- File 11 contract: `19`',
    ),
    '11-reels-foundation/11-reels-foundation.php' => array(
        'Version: 1.2.0-rc13',
        "define( 'RSV_VERSION', '1.2.0-rc13' )",
        "define( 'RSV_CONTRACT_VERSION', 19 )",
    ),
    '11-reels-foundation/readme.txt' => array(
        'Stable tag: 1.2.0-rc13',
        '= 1.2.0-rc12 =',
    ),
    '.github/workflows/file11-release.yml' => array(
        'reels-foundation-11-1.2.0-rc13.zip',
        'file11-reels-1.2.0-rc13',
    ),
);
$assertions = 0;
foreach ( $expect as $path => $markers ) {
    $content = file_get_contents( $root . '/' . $path );
    if ( false === $content ) throw new RuntimeException( "Cannot read $path" );
    foreach ( $markers as $needle ) {
        ++$assertions;
        if ( false === strpos( $content, $needle ) ) {
            fwrite( STDERR, "CURRENT RC13 RELEASE METADATA MISMATCH: $path missing $needle\n" );
            exit( 1 );
        }
    }
}
echo "RC13 exact-source version/contract/package/doc parity ($assertions checks) PASS\n";
