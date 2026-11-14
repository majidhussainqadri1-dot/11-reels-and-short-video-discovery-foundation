<?php
/** Governance coverage validation only; NOT a runtime or acceptance test. */
declare(strict_types=1);
$path = dirname(__DIR__) . '/docs/F11-V12_3-REQUIREMENT-TRACEABILITY-2026-11-14.md';
if (!is_file($path)) { fwrite(STDERR, "V12.3 matrix missing\n"); exit(1); }
$txt = file_get_contents($path);
if ($txt === false) { fwrite(STDERR, "Cannot read matrix\n"); exit(1); }
$families = ['FR'=>15,'NFR'=>10,'FUT'=>30];
$failures=[];
foreach ($families as $type=>$count) {
  preg_match_all('/^\| (F11-' . $type . '-\d{3}) \|[^\r\n]+$/m', $txt, $matches);
  $actual=$matches[1];
  $wanted=[];
  for ($i=1;$i<=$count;$i++) $wanted[]=sprintf('F11-%s-%03d',$type,$i);
  if ($actual !== $wanted) $failures[]=sprintf('%s matrix: expected %d ordered unique IDs, found %d or out of order', $type,$count,count($actual));
}
preg_match_all('/^\| (F30-AT-\d{2}) \|[^\r\n]+$/m',$txt,$m);
$wanted=[];for($i=1;$i<=15;$i++)$wanted[]=sprintf('F30-AT-%02d',$i);
if($m[1]!==$wanted)$failures[]='Acceptance matrix must contain F30-AT-01 through F30-AT-15 exactly once in order';
foreach (['F11-FUT-001','Issue #38','R3','R4','R5','UNVERIFIED','BLOCKED','V12.3'] as $token) {
 if(strpos($txt,$token)===false)$failures[]='Missing governance marker: '.$token;
}
if($failures){foreach($failures as $f)fwrite(STDERR,$f."\n");exit(1);}
echo "V12.3 GOVERNANCE MATRIX: 55 IDs + 15 AT IDs documented; NOT runtime acceptance\n";
