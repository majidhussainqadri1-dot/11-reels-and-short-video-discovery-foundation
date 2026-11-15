/**
 * V12.3 P00 offline source parity verifier (isolated synthetic directory).
 * No WordPress connection, no patient data and no actual deployed evidence.
 */
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { spawnSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { fileURLToPath } from 'node:url';
const dir = path.dirname(fileURLToPath(import.meta.url));
const tool = path.join(dir, '../tools/v123-p00-offline-parity.php');
const tmp = fs.mkdtempSync(path.join(os.tmpdir(), 'file11-p00-local-parity-'));
const candidate = path.join(tmp, 'candidate');
fs.mkdirSync(path.join(candidate, 'includes'), { recursive: true });
const mainRel = '11-reels-foundation.php';
const main = path.join(candidate, mainRel);
const secondaryRel = 'includes/example.php';
const secondary = path.join(candidate, secondaryRel);
const mainData = '<?php\n/**\n * Plugin Name: File11 fixture\n * Version: 1.2.0-rc21\n */\n';
const secondData = '<?php\n// Synthetic and disposable fixture\n';
fs.writeFileSync(main, mainData);
fs.writeFileSync(secondary, secondData);
const pluginName = '11-reels-foundation/' + mainRel;
const evidencePath = path.join(tmp, 'evidence.json');
const sha = data => createHash('sha256').update(data).digest('hex');
function manifestHash(map) {
    const keys = Object.keys(map).sort();
    const ordered = Object.fromEntries(keys.map(k=>[k,map[k]]));
    return sha(JSON.stringify(ordered).replace(/\//g, '\\/'));
}
function makeEvidence() {
    const entries = {
        [mainRel]:sha(fs.readFileSync(main)),
        [secondaryRel]:sha(fs.readFileSync(secondary))
    };
    const ordered = Object.fromEntries(Object.keys(entries).sort().map(x=>[x,entries[x]]));
    return {
        mode:'READ_ONLY_METADATA_ONLY',
        runtime:{wordpress:'7.0.1',php:'8.3.0'},
        active_relevant_plugins:[{
            plugin_basename:pluginName,
            header_name:'File11 fixture',
            header_version:'1.2.0-rc21',
            manifest_complete:true,
            file_count:2,
            file_sha256:ordered,
            manifest_sha256:manifestHash(ordered)
        }]
    };
}
function run(snapshot,expectedStatus,expectedExit) {
    fs.writeFileSync(evidencePath,JSON.stringify(snapshot));
    const result = spawnSync('php',[tool,evidencePath,candidate,pluginName],{
        encoding:'utf8',timeout:15000
    });
    assert.equal(result.status,expectedExit,'exit for '+expectedStatus+' / '+result.stderr+' / '+result.stdout);
    let obj;
    try{obj=JSON.parse(result.stdout);}catch(e){throw new Error('Output must be parseable safe JSON: '+result.stdout);}
    assert.equal(obj.status,expectedStatus);
    assert.equal(obj.release_authorized,false);
    assert.equal(obj.database_parity,'UNVERIFIED');
    assert.equal(obj.migration_parity,'UNVERIFIED');
    assert.equal(obj.live_retest,'NOT_PERFORMED');
    return obj;
}
let checks=0;
try{
    let snapshot=makeEvidence();
    let out=run(snapshot,'CODE_MATCH_ONLY',0);
    assert.equal(out.source_code_parity,'MATCH');
    assert.equal(out.deployed_evidence_files,2);
    checks++;

    fs.appendFileSync(secondary,'// changed\n');
    out=run(snapshot,'CODE_MISMATCH',2);
    assert.equal(out.sha256_mismatches,1);
    checks++;
    fs.writeFileSync(secondary,secondData);

    fs.writeFileSync(path.join(candidate,'unexpected.tmp'),'not deployed');
    out=run(snapshot,'CODE_MISMATCH',2);
    assert.equal(out.unexpected_candidate_files,1);
    checks++;
    fs.rmSync(path.join(candidate,'unexpected.tmp'));

    fs.rmSync(secondary);
    out=run(snapshot,'CODE_MISMATCH',2);
    assert.equal(out.missing_candidate_files,1);
    checks++;
    fs.writeFileSync(secondary,secondData);

    snapshot=makeEvidence();
    snapshot.active_relevant_plugins[0].manifest_complete=false;
    run(snapshot,'INCOMPLETE_DEPLOYED_EVIDENCE',2);
    checks++;

    snapshot=makeEvidence();
    snapshot.active_relevant_plugins[0].manifest_sha256='0'.repeat(64);
    run(snapshot,'INVALID_EVIDENCE_MANIFEST',2);
    checks++;

    snapshot=makeEvidence();
    snapshot.active_relevant_plugins[0].header_version='9.0.0-suspicious';
    run(snapshot,'VERSION_HEADER_MISMATCH',2);
    checks++;

    snapshot=makeEvidence();
    snapshot.active_relevant_plugins[0].file_sha256['../outside.php']='a'.repeat(64);
    snapshot.active_relevant_plugins[0].file_count=3;
    snapshot.active_relevant_plugins[0].manifest_sha256=manifestHash(snapshot.active_relevant_plugins[0].file_sha256);
    run(snapshot,'INVALID_EVIDENCE_MANIFEST',2);
    checks++;

    if(process.platform!=='win32'){
        const link=path.join(candidate,'rogue-symlink.php');
        fs.symlinkSync(main,link);
        run(makeEvidence(),'INCOMPLETE_CANDIDATE_EVIDENCE',2);
        fs.rmSync(link);
        checks++;
    }
    console.log('V12.3 P00 offline source parity '+checks+'/'+checks+' synthetic safety scenarios PASS (not live/staging parity)');
}finally{
    fs.rmSync(tmp,{recursive:true,force:true});
}
