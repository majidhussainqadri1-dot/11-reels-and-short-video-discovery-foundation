<?php
require __DIR__.'/bootstrap.php';
$passed=0;$failed=0;
function ok($condition,$label){global $passed,$failed;if($condition){$passed++;echo "PASS: $label\n";}else{$failed++;echo "FAIL: $label\n";}}
ok(RSV_Contracts::event('ReelPublished')==='ReelPublished.v1','event names preserve canonical case');
ok(count(RSV_Contracts::REQUIREMENTS)===25,'all 15 FR and 10 NFR identifiers declared');
ok(RSV_Helpers::bcp47('ur_pk')==='ur-PK','BCP47 normalization');
ok(RSV_Helpers::bcp47('not a locale','')==='','invalid locale fails closed');
$cursor=RSV_Helpers::cursor_encode(array('sort'=>'rank','score'=>42.5,'time'=>'2026-08-06 12:00:00','id'=>9));$decoded=RSV_Helpers::cursor_decode($cursor);
ok($decoded['sort']==='rank'&&(float)$decoded['score']===42.5&&(int)$decoded['id']===9,'signed cursor round trip');
ok(RSV_Helpers::cursor_decode($cursor.'x')===null,'tampered cursor rejected');
ok(RSV_State_Machine::allowed('draft','review'),'valid Reel transition accepted');
ok(!RSV_State_Machine::allowed('draft','published'),'invalid Reel transition rejected');
ok(RSV_State_Machine::allowed('submitted','triaged','report'),'valid report triage accepted');
ok(!RSV_State_Machine::allowed('submitted','action','report'),'report cannot skip triage');
$base=array('rights_status'=>'verified','consent_status'=>'approved','captions_status'=>'ready','cover_id'=>2,'caption'=>'Educational','published_at'=>'2026-08-05 12:00:00','safety_labels_json'=>'[]');
$good=RSV_Ranking::score($base,array(),array('completion_rate'=>.8,'positive_rate'=>.8,'open_reports'=>0));$bad=RSV_Ranking::score($base,array(),array('completion_rate'=>.1,'positive_rate'=>.1,'open_reports'=>5));
ok($good>$bad,'ranking rewards quality and penalizes open safety reports');
ok($good<=100&&$bad>=-100,'ranking remains bounded');
echo "Passed: $passed\nFailed: $failed\n";exit($failed?1:0);
