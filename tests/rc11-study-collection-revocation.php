<?php
/** File 11 F11-FUT-020: stop withdrawn/private Reel IDs leaking through old saved collection state. */
require __DIR__ . '/bootstrap.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/trait-rsv-future30-user-features.php';
final class RSV_Collection_Public_Probe {
    use RSV_Future30_User_Features_Trait;
    private static function canonical_ref_public_valid( $owner, $ref, $purpose ) {
        return 'File 11' === $owner
            && 'study-collection-current-read' === $purpose
            && in_array( $ref, array( 'public-reel-a', 'public-reel-b' ), true );
    }
}
function verify( $pass, $label ) {
    if ( ! $pass ) { fwrite( STDERR, 'FAIL: ' . $label . "\n" ); exit( 1 ); }
}
$read = new ReflectionMethod( RSV_Collection_Public_Probe::class, 'current_study_collections' );
$old = array(
    'version' => 8, 'updated_at' => '2026-10-20 11:00:00', 'user_id' => 55,
    'payload' => array(
        'private_patient_note' => 'must not expose',
        'collections' => array(
            'clinical' => array(
                'name' => 'Learning', 'private' => true, 'updated_at' => '2026-10-20 10:00:00',
                'reels' => array( 'public-reel-a', 'withdrawn-reel', 'public-reel-a', 'public-reel-b', 'private-reel' ),
                'owner_id' => 55, 'secret_token' => 'secret',
            ),
            'empty' => array( 'name'=>'Saved', 'private'=>true, 'reels'=>array('withdrawn-reel') ),
            'invalid' => array( 'name'=>'', 'reels'=>array('public-reel-a') ),
        ),
    ),
);
$got = $read->invoke( null, $old );
verify( 8 === $got['version'], 'Optimistic version changed' );
verify( ! isset( $got['user_id'] ) && ! isset( $got['payload']['private_patient_note'] ), 'Top-level PII leaked' );
verify( 2 === count( $got['payload']['collections'] ), 'Unapproved collection names escaped' );
verify( $got['payload']['collections']['clinical']['reels'] === array( 'public-reel-a', 'public-reel-b' ), 'Withdrawn, private or duplicate Reel ref escaped' );
verify( array() === $got['payload']['collections']['empty']['reels'], 'Withdrawn-only collection was not safely emptied' );
verify( ! isset( $got['payload']['collections']['clinical']['owner_id'] ) &&
    ! isset( $got['payload']['collections']['clinical']['secret_token'] ), 'Private collection metadata escaped' );
verify( array() === $read->invoke( null, array() )['payload']['collections'], 'Empty state must be safe' );
$invalid = $old;
$invalid['payload']['collections']['clinical']['reels'][] = array( 'public-reel-a' );
$got = $read->invoke( null, $invalid );
verify( $got['payload']['collections']['clinical']['reels'] === array( 'public-reel-a', 'public-reel-b' ), 'Malformed nested Reel ref escaped' );
echo "RC11 revoked/private Study Collection read behavioral tests PASS\n";
