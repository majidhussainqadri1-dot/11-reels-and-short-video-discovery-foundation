<?php
/**
 * RC11 read-time revocation and historical content privacy.
 * Actual PHP behavioral assertions, plus source-consumer integration markers.
 */
require __DIR__ . '/bootstrap.php';
function add_filter( ...$args ) { return true; }
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-future30-public-safety.php';
require dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-future30-user-features.php';
final class RSV_RC11_Collections_Probe {
    use RSV_Future30_User_Features_Trait;
    private static function canonical_ref_public_valid( $owner, $ref, $purpose ) {
        return $owner === 'File 11' && $purpose === 'study-collection-current-read'
            && in_array($ref, array('public-a','public-b'), true);
    }
}
function assert_valid( $ok, $label ) {
    if ( ! $ok ) { fwrite(STDERR,"FAIL: $label\n"); exit(1); }
}
$method = new ReflectionMethod(RSV_RC11_Collections_Probe::class,'current_study_collections');
$stored = array(
  'version'=>9,'user_id'=>88,'payload'=>array('private_patient_note'=>'SECRET',
    'collections'=>array(
      'research'=>array('name'=>'Research','private'=>true,'secret'=>'SECRET',
        'reels'=>array('public-a','removed-reel','public-a','private-reel','public-b'),
        'updated_at'=>'2026-10-20'),
      'withdrawn'=>array('name'=>'Old','reels'=>array('removed-reel'))
    )));
$out=$method->invoke(null,$stored);
assert_valid($out['version']===9,'Collection version changed');
assert_valid(!isset($out['user_id'])&&!isset($out['payload']['private_patient_note']),'Private user keys exposed');
assert_valid($out['payload']['collections']['research']['reels']===array('public-a','public-b'),'Revoked/hidden/duplicate IDs exposed');
assert_valid($out['payload']['collections']['withdrawn']['reels']===array(),'Withdrawn-only collection leaked');
assert_valid(!isset($out['payload']['collections']['research']['secret']),'Private collection metadata leaked');
assert_valid($method->invoke(null,array())['payload']['collections']===array(),'Malformed/empty collection state mishandled');
$stored['payload']['collections']['research']['reels'][]=array('public-a');
$out=$method->invoke(null,$stored);
assert_valid($out['payload']['collections']['research']['reels']===array('public-a','public-b'),'Nested object disguised as Reel ID');

$payload=array(
  'reel_version'=>7,
  'snapshot_hash'=>str_repeat('a',64),
  'caption'=>'SECRET: deleted unsafe old medical caption',
  'title'=>'SECRET: old title',
  'medical_record'=>'SECRET: patient',
  'owner_id'=>88);
$min=RSV_Future30_Public_Safety::public_version_snapshot_payload($payload);
assert_valid(array_keys($min)===array('reel_version','snapshot_hash'),'Historical public DTO extra keys leaked');
assert_valid($min['reel_version']===7 && $min['snapshot_hash']===str_repeat('a',64),'Version/hash lost');
assert_valid(!in_array('SECRET: patient',array_values($min),true),'Historical patient data exposed');
$bad=RSV_Future30_Public_Safety::public_version_snapshot_payload(array('snapshot_hash'=>'not-a-hash'));
assert_valid($bad['snapshot_hash']==='','Malformed version hash exposed');
$safe=new ReflectionMethod(RSV_Future30_Public_Safety::class,'safe_objects');
$sample=array('public_id'=>'obj_public','title'=>'SECRET: patient name','owner_id'=>88,
  'reel_id'=>77,'feature_id'=>'F11-FUT-007','payload'=>$payload);
$result=$safe->invoke(null,'F11-FUT-007',array($sample),array());
assert_valid(count($result)===1,'Version record dropped unexpectedly');
assert_valid($result[0]['title']==='Version 7','Unsafe snapshot label leaked');
assert_valid(!isset($result[0]['owner_id'])&&!isset($result[0]['reel_id']),'Owner IDs leaked');
assert_valid(!isset($result[0]['payload']['caption'])&&!isset($result[0]['payload']['title']),'Historical text leaked through REST projection');
$storage=file_get_contents(dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-future30-storage.php');
assert_valid(strpos($storage,"'F11-FUT-007' === \$feature_id")!==false
    && strpos($storage,"RSV_Future30_Public_Safety::public_version_snapshot_payload")!==false,
    'Generic read projection bypassed safe history contract');
echo "RC11 behavioral revoked Collection and historical version privacy checks PASS\n";
