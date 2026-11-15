<?php
/**
 * File11 V12.3 / Issue46 source-only route map and private-state atomicity gate.
 * This test prevents a regression of owner routing and keeps native method
 * bypass risk visible. It does NOT prove installed hooks or MySQL rollback.
 */
$root=dirname(__DIR__).'/11-reels-foundation/includes/';
$paths=array(
    'f30'=>$root.'class-rsv-future30.php',
    'fresh'=>$root.'class-rsv-fresh20-hardening.php',
    'storage'=>$root.'trait-rsv-future30-storage.php',
    'user'=>$root.'trait-rsv-future30-user-features.php',
    'experience'=>$root.'trait-rsv-future30-experience.php',
    'integrity'=>$root.'class-rsv-future30-private-state-integrity.php',
    'db'=>$root.'class-rsv-db.php',
);
$src=array();
foreach($paths as $key=>$path){
    $src[$key]=file_get_contents($path);
    if(!is_string($src[$key])){fwrite(STDERR,"Missing File11 source $key\n");exit(1);}
}
$errors=array();$n=0;
$assert=static function($okay,$name)use(&$errors,&$n){++$n;if(!$okay)$errors[]=$name;};
$has=static function($source,$needle){return strpos($source,$needle)!==false;};
$assert($has($src['fresh'],"'rest_request_before_callbacks'")&&
        $has($src['fresh'],"array( __CLASS__, 'before_callbacks' ), 5, 3"),
    'Fresh20 must register before-callback at priority5');
$assert($has($src['integrity'],"array( __CLASS__, 'before_callbacks' ), 20, 3"),
    'secondary collection guard priority20 follows Fresh20');
foreach(array(
    "quiz/attempt",
    "future30/collections",
    "/notes",
    "future30/feed-preferences",
    "future30/accessibility"
) as $fragment){
    $assert($has($src['fresh'],$fragment),'transactional Fresh20 route missing: '.$fragment);
}
foreach(array(
    'private static function quiz_attempt',
    'private static function collection_write',
    'private static function note_write',
    'private static function feed_preferences_write',
    'private static function accessibility_write',
    'private static function private_write_read_error'
) as $method){
    $assert($has($src['fresh'],$method),'guarded Fresh20 mutation method missing: '.$method);
}
$assert($has($src['fresh'],'RSV_DB::transaction'),
    'Fresh20 protected writes must still use transaction');
$assert($has($src['user'],"self::state_put('F11-FUT-025'") &&
        $has($src['experience'],"self::state_put('F11-FUT-029'"),
    'native feed and accessibility direct state_put paths must remain visible as an unresolved R3 bypass risk');
$assert($has($src['storage'],'ON DUPLICATE KEY UPDATE') &&
        preg_match('/private static function state_put\s*\(.*?self::state_get/s',$src['storage'])===1,
    'state_put UPSERT plus subsequent SELECT must remain identified');
$assert($has($src['db'],"'START TRANSACTION'") &&
        $has($src['db'],"'ROLLBACK'") &&
        $has($src['db'],"'COMMIT'"),
    'owner transaction must expose explicit commit/rollback semantics');
$assert($has($src['f30'],"'/future30/feed-preferences'") &&
        $has($src['f30'],"'/future30/accessibility'") &&
        $has($src['f30'],"WP_REST_Server::EDITABLE"),
    'actual method registration must remain reviewed, not inferred');
$assert($has($src['fresh'],"'POST', 'PUT', 'PATCH'"),
    'Fresh20 must intercept each eligible private EDITABLE HTTP method');
if($errors){fwrite(STDERR,"V12.3 private route-map regressions:\n".implode("\n",$errors)."\n");exit(1);}
echo "V12.3 Issue46 private-state hook route-map $n/$n SOURCE assertions PASS; R3 runtime/atomicity UNVERIFIED\n";
