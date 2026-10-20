<?php
/** Release history cannot be overwritten each time a new RC is cut. */
$root=dirname(__DIR__);
$readme=file_get_contents($root.'/11-reels-foundation/readme.txt');
$changelog=file_get_contents($root.'/CHANGELOG.md');
preg_match_all('/^= (1\\.2\\.0-rc[0-9]+) =$/m',$readme,$r);
preg_match_all('/^## (1\\.2\\.0-rc[0-9]+) —/m',$changelog,$c);
$expected=array_map(static function($n){return '1.2.0-rc'.$n;},range(14,1));
if(array_slice($r[1],0,14)!==$expected){
    fwrite(STDERR,'FAIL: historical WordPress readme RC identity overwritten or reordered'.PHP_EOL);
    exit(1);
}
if(array_slice($c[1],0,14)!==$expected){
    fwrite(STDERR,'FAIL: changelog RC history out of sync with readme'.PHP_EOL);
    exit(1);
}
$bootstrap=file_get_contents($root.'/11-reels-foundation/11-reels-foundation.php');
if ( ! preg_match("/define\\( 'RSV_VERSION', '([^']+)' \\)/", $bootstrap, $match) ||
     ! preg_match('/^Stable tag: (.+)$/m', $readme, $tag) ||
     $tag[1] !== $match[1] ) {
    fwrite(STDERR,'FAIL: WordPress stable tag and immutable runtime release identity mismatch'.PHP_EOL);
    exit(1);
}
echo 'RC14 immutable chronological release history (14 RCs) PASS'.PHP_EOL;
