<?php
$root = dirname( __DIR__ ) . '/11-reels-foundation';
$read = static function ( $path ) { $v = file_get_contents( $path ); if ( false === $v ) throw new RuntimeException( "Cannot read $path" ); return $v; };
$f = array(
 'bootstrap'=>$read($root.'/11-reels-foundation.php'),
 'contracts'=>$read($root.'/includes/class-rsv-contracts.php'),
 'plan'=>$read($root.'/includes/class-rsv-current-plan.php'),
 'rest'=>$read($root.'/includes/class-rsv-rest.php'),
 'admin'=>$read($root.'/includes/class-rsv-admin.php'),
 'integrations'=>$read($root.'/includes/class-rsv-integrations.php'),
 'file10'=>$read($root.'/includes/class-rsv-file10.php'),
 'context'=>$read($root.'/includes/trait-rsv-top20-context.php'),
 'frontend'=>$read($root.'/includes/class-rsv-frontend.php'),
 'js'=>$read($root.'/assets/js/rsv.js'),
);
$must = array(
 ['bootstrap','Version: 1.1.0-rc7'],
 ['bootstrap',"define( 'RSV_CONTRACT_VERSION', 6 )"],
 ['bootstrap',"'class-rsv-current-plan.php'"],
 ['integrations','const PROVIDER_VERSION = 3'],
 ['contracts',"'F11-CEN-01'"],
 ['contracts',"'CV-239'"], ['contracts',"'CV-250'"], ['contracts',"'CV-254'"], ['contracts',"'CV-285'"],
 ['contracts',"'AJ-17'"], ['contracts',"'AJ-40'"],
 ['contracts',"'harm', 'false-claim', 'impersonation', 'privacy', 'abuse', 'copyright', 'scam', 'child-safety'"],
 ['contracts',"const RISK_TIERS = array( 'low', 'medium', 'high', 'critical' )"],
 ['rest','RSV_Contracts::normalize_report_reason'],
 ['plan','Educational content only.'],
 ['plan','autonomous diagnosis, prescription, dose selection, or emergency-care replacement'],
 ['plan','verified local emergency or qualified professional care'],
 ['plan',"'child-safety'  => array( 'tier' => 'critical'"],
 ['plan',"'donor_advantage']           = false"],
 ['integrations','not payment, donation status, or follower count alone'],
 ['file10','$duration < 60 || $duration > 600'],
 ['context','rsv_source_safety_required'],
 ['context','rsv_caption_track_required'],
 ['js','let autoplay = false'],
 ['frontend','data-rsv-clear-history'],
 ['admin','RSV_Current_Plan::report_risk_profile'],
);
foreach ($must as [$key,$needle]) {
  if (false === strpos($f[$key], $needle)) { fwrite(STDERR, "Missing current-plan marker [$key]: $needle\n"); exit(1); }
}
$forbidden = array(
 ['integrations','paid placement'],
 ['plan','diagnose the user'],
);
foreach ($forbidden as [$key,$needle]) {
  if (false !== strpos($f[$key], $needle)) { fwrite(STDERR, "Forbidden current-plan pattern [$key]: $needle\n"); exit(1); }
}
echo "RC7 rewritten-governing-plan contracts PASS\n";
