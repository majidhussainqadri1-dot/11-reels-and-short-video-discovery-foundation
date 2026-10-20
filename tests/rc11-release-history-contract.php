<?php
/** Release history cannot be overwritten each time a new RC is cut. */
$root=dirname(__DIR__);
$readme=file_get_contents($root.'/11-reels-foundation/readme.txt');
$changelog=file_get_contents($root.'/CHANGELOG.md');
preg_match_all('/^= (1\\.2\\.0-rc[0-9]+) =$/m',$readme,$r);
preg_match_all('/^## (1\\.2\\.0-rc[0-9]+) —/m',$changelog,$c);
$expected=array_map(static function($n){return '1.2.0-rc'.$n;},range(11,1));
if(array_slice($r[1],0,11)!==$expected){
    fwrite(STDERR,'FAIL: historical WordPress readme RC identity overwritten or reordered'.PHP_EOL);
    exit(1);
}
if(array_slice($c[1],0,11)!==$expected){
    fwrite(STDERR,'FAIL: changelog RC history out of sync with readme'.PHP_EOL);
    exit(1);
}
echo 'RC11 immutable chronological release history (11 RCs) PASS'.PHP_EOL;
