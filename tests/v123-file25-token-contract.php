<?php
/** V12.3 File11/File20/File25 source-token contract; not a visual/browser pass. */
$css=file_get_contents(dirname(__DIR__).'/11-reels-foundation/assets/css/rsv.css');
if(!is_string($css)){fwrite(STDERR,"Missing Reels stylesheet\n");exit(1);}
$keys=array(
 '--sabri-visual-primary','--sabri-visual-primary-strong','--sabri-visual-surface',
 '--sabri-visual-text','--sabri-visual-muted','--sabri-visual-border',
 '--sabri-visual-danger','--sabri-visual-focus','--sabri-visual-radius-card',
 '--sabri-visual-content-wide','--sabri-shell-max-width',
 '#087A4E','#065C3B',
);
$fail=[];
foreach($keys as $k)if(strpos($css,$k)===false)$fail[]=$k.' missing from source CSS';
$header=explode("\n",$css)[0];
if(substr_count($header,'(')!==substr_count($header,')'))$fail[]='unbalanced token fallback parentheses';
if(strpos($header,'max-width:1120px')!==false)$fail[]='File11 must not hardcode width ahead of File20';
if(strpos($css,'.rsv-reel:focus-visible')===false)$fail[]='reel focus affordance missing';
if(strpos($css,'@media(prefers-reduced-motion:reduce)')===false)$fail[]='reduced-motion rule missing';
if($fail){fwrite(STDERR,"File25 source-token violations:\n".implode("\n",$fail)."\n");exit(1);}
echo "V12.3 File25 semantic-token and File20 shell-width source assertions ".count($keys)."/".count($keys)." PASS (not visual acceptance)\n";
