<?php
/** File11→File26 read-only and deletion-gated contract regression. */
$root=dirname(__DIR__);
$src=file_get_contents($root.'/11-reels-foundation/includes/class-rsv-file26-connector.php');
$entry=file_get_contents($root.'/11-reels-foundation/11-reels-foundation.php');
$must=array(
 "'status' => 'proposed'",
 "'entity_types' => array('reel')",
 "'privacy_classes' => array('public')",
 "'deletion_semantics' => 'tombstone'",
 "'list_batch' => array(__CLASS__, 'list_batch')",
 "'can_view' => array(__CLASS__, 'can_view')",
 "'health' => array(__CLASS__, 'health')",
 'RSV_Security::can_view_reel($row, 0)',
 'RSV_Security::can_view_reel($native, 0)',
 "status=%s AND visibility=%s",
 "source_owner_file' => 'File 11'",
);
foreach($must as $value){if(strpos($src,$value)===false){fwrite(STDERR,"FAIL: File11 native search adapter contract missing: $value\n");exit(1);}}
if(strpos($src,"'status' => 'active'")!==false){fwrite(STDERR,"FAIL: File11 automatically activates an unverified connector\n");exit(1);}
if(strpos($entry, 'sabri_file26_owner_connector_adapters')===false || strpos($entry,'class-rsv-file26-connector.php')===false) {fwrite(STDERR,"FAIL: adapter not registered before File26 boot\n");exit(1);}
echo "PASS: File11 native proposed File26 read-only adapter contract\n";
