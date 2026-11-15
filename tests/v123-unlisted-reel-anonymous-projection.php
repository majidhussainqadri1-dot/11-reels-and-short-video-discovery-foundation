<?php
/**
 * File11 public projection must not inherit the currently logged-in caller's
 * access to an unlisted Reel when requesting viewer_id=0. Isolated mock only.
 */
require __DIR__.'/bootstrap.php';
function apply_filters($hook,$value,...$rest){return $value;}
final class RSV_Contracts {
    public const VISIBILITIES=array('public','unlisted','member','private','entitled');
}
final class RSV_Helpers {
    public static function enum($value,$allowed,$default=''){
        return in_array($value,$allowed,true)?$value:$default;
    }
}
final class RSV_File10 {
    public static $eligible=true;
    public static $publiclyAllowed=true;
    public static $video=array('visibility'=>'public');
    public static function eligible_for_reel($id){return self::$eligible;}
    public static function publicly_eligible($id){return self::$publiclyAllowed;}
    public static function video($id){return self::$video;}
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-security.php';
$fail=[];$checks=0;
$check=static function($ok,$why)use(&$fail,&$checks){++$checks;if(!$ok)$fail[]=$why;};
$reel=array('id'=>15,'public_id'=>'reel_11111111-2222-4333-8444-555555555555',
    'video_id'=>21,'status'=>'published','visibility'=>'unlisted','owner_id'=>7);
RSV_File10::$video=array('visibility'=>'unlisted');
RSV_File10::$publiclyAllowed=true; // caller is authorized File10 owner.
$check(RSV_Security::can_view_reel($reel,0)===false,
    'explicit viewer 0 cannot inherit logged-in owner access to unlisted Reel');
$check(RSV_Security::can_view_reel($reel)===true,
    'separate direct owner-context check can still access authorized unlisted Reel');
RSV_File10::$video=array('visibility'=>'public');
$check(RSV_Security::can_view_reel($reel,0)===false,
    'even unlisted Reel with public File10 video is not a public projection');
$reel['visibility']='public';
$check(RSV_Security::can_view_reel($reel,0)===true,
    'public Reel and current public File10 media remain accessible');
RSV_File10::$video=array('visibility'=>'unlisted');
$check(RSV_Security::can_view_reel($reel,0)===false,
    'public Reel cannot turn unlisted File10 video into public media');
RSV_File10::$video=array('visibility'=>'public');
RSV_File10::$publiclyAllowed=false;
$check(RSV_Security::can_view_reel($reel,0)===false,
    'File10 revocation veto blocks public Reel');
RSV_File10::$publiclyAllowed=true;
RSV_File10::$eligible=false;
$check(RSV_Security::can_view_reel($reel,0)===false,
    'File10 withdrawn rights or soft deletion blocks public Reel');
RSV_File10::$eligible=true;
$reel['status']='restricted';
$check(RSV_Security::can_view_reel($reel,0)===false,
    'nonpublished Reel cannot be public');
if($fail){fwrite(STDERR,"File11 public projection tests:\n".implode("\n",$fail)."\n");exit(1);}
echo "File11 V12.3 explicit-anonymous unlisted projection: $checks/$checks isolated assertions PASS (not real File00/10 integration)\n";
