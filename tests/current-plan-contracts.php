<?php
$root = dirname( __DIR__ ) . '/11-reels-foundation';
$read = static function ( $path ) { $v = file_get_contents( $path ); if ( false === $v ) throw new RuntimeException( "Cannot read $path" ); return $v; };
$f = array(
 'bootstrap'=>$read($root.'/11-reels-foundation.php'),'contracts'=>$read($root.'/includes/class-rsv-contracts.php'),'plan'=>$read($root.'/includes/class-rsv-current-plan.php'),'rest'=>$read($root.'/includes/class-rsv-rest.php'),'admin'=>$read($root.'/includes/class-rsv-admin.php'),'integrations'=>$read($root.'/includes/class-rsv-integrations.php'),'file10'=>$read($root.'/includes/class-rsv-file10.php'),'context'=>$read($root.'/includes/trait-rsv-top20-context.php'),'frontend'=>$read($root.'/includes/class-rsv-frontend.php'),'js'=>$read($root.'/assets/js/rsv.js'),'future'=>$read($root.'/includes/class-rsv-future30.php').$read($root.'/includes/trait-rsv-future30-storage.php').$read($root.'/includes/trait-rsv-future30-feature-write.php').$read($root.'/includes/trait-rsv-future30-user-features.php').$read($root.'/includes/trait-rsv-future30-experience.php').$read($root.'/includes/class-rsv-fresh20-hardening.php'),
);
$must=array(
 ['bootstrap','Version: 1.2.0-rc2'],['bootstrap',"define( 'RSV_CONTRACT_VERSION', 8 )"],['bootstrap',"'class-rsv-current-plan.php'"],['bootstrap',"'class-rsv-future30.php'"],['bootstrap',"'class-rsv-fresh20-hardening.php'"],
 ['integrations','const PROVIDER_VERSION = 3'],['contracts',"'F11-CEN-01'"],['contracts',"'CV-239'"],['contracts',"'CV-285'"],['contracts',"'F11-FUT-001'"],['contracts',"'F11-FUT-030'"],
 ['contracts',"'AJ-17'"],['contracts',"'AJ-40'"],['rest','RSV_Contracts::normalize_report_reason'],['plan',"const REVISION = '2026-08-12'"],['plan','Educational content only.'],
 ['integrations','not payment, donation status, or follower count alone'],['file10','$duration < 60 || $duration > 600'],['context','rsv_caption_track_required'],['js','let autoplay = false'],['frontend','data-rsv-clear-history'],
 ['admin','ORDER BY CASE reason_code'],['future',"'payment_or_donation_reason'=>false"],['future','medical_authority'],['future','apply_feed_runtime_preferences'],
);
foreach($must as [$k,$n])if(false===strpos($f[$k],$n)){fwrite(STDERR,"Missing current-plan marker [$k]: $n\n");exit(1);}
echo "RC9/Future30 governing-plan contracts PASS\n";
