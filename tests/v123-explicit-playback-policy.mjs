import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
const __dirname=path.dirname(fileURLToPath(import.meta.url));
const src=fs.readFileSync(path.join(__dirname,'../11-reels-foundation/assets/js/rsv.js'),'utf8');
const listeners={};
const docListeners={};
let reduced=false,saveData=false,hidden=false,plays=[],pauses=[];
const videos=[];
function video(id){
    return {id, currentTime:0, dataset:{resume:'0'}, muted:false,
        play(){plays.push(id);return Promise.resolve();},
        pause(){pauses.push(id);}
    };
}
function article(id){
    const player=video(id);videos.push(player);
    return { dataset:{rsvReel:id},player,classList:{toggle(){}},
        querySelector(selector){return selector==='video'?player:null;},
        querySelectorAll(){return [];},
        setAttribute(){},scrollIntoView(){},focus(){}
    };
}
const articles=[article('A'),article('B')];
const root={addEventListener(name,callback){listeners[name]=callback;},
    querySelectorAll(selector){return selector==='[data-rsv-reel]'?articles:[];}};
const pauseButton={
    title:'',
    querySelector(selector){return selector==='span'?this.child:null;},
    child:{textContent:''},
    setAttribute(name,value){this[name]=value;}
};
const autoButton={
    querySelector(selector){return selector==='span'?this.child:null;},
    child:{textContent:''},
    setAttribute(name,value){this[name]=value;}
};
const doc={
    get hidden(){return hidden;},
    querySelector(selector){
        if(selector==='[data-rsv-feed]')return root;
        if(selector==='[data-rsv-pause]')return pauseButton;
        if(selector==='[data-rsv-autoplay]')return autoButton;
        return null;
    },
    querySelectorAll(selector){return selector==='[data-rsv-pause]'?[pauseButton]:[];},
    addEventListener(name,cb){docListeners[name]=cb;},
    body:{appendChild(){}},
};
const context={
    document:doc,
    window:{matchMedia(){return {matches:reduced};},addEventListener(){}},
    navigator:{connection:{get saveData(){return saveData;}}},
    RSV:{loggedIn:false,root:'/wp-json/rsv/v1',nonce:'x',i18n:{
        position:'%d of %d',error:'Error',
        resume:'Resume Reel',pause:'Pause Reel',enableAutoplay:'Enable autoplay',
        disableAutoplay:'Disable autoplay'
    }},
    Promise,URL,Date,Math,console,
};
vm.runInNewContext(src,context,{filename:'rsv.js'});
const actions={pause:'[data-rsv-pause]',autoplay:'[data-rsv-autoplay]',next:'[data-rsv-next]'};
function click(type){
    const target={closest(selector){return actions[type]===selector? (type==='pause'?pauseButton:autoButton):null;}};
    docListeners.click({target,preventDefault(){}});
}
function arrow(key){const e={key, target:root,defaultPrevented:false,altKey:false,ctrlKey:false,metaKey:false,
    preventDefault(){this.defaultPrevented=true;}};listeners.keydown(e);}
let n=0;
function check(ok,msg){assert.ok(ok,msg);++n;}
check(plays.length===0,'first Reel must not autoplay when autoplay defaults OFF');
check(pauseButton.child.textContent==='Resume Reel','initial paused state label should invite explicit play');
click('pause');
check(plays.at(-1)==='A','user pressing Resume must actually play current Reel even if autoplay OFF');
check(pauseButton.child.textContent==='Pause Reel','playing state label should show Pause');
click('pause');
check(pauses.at(-1)==='A','explicit Pause must pause active Reel');
check(pauseButton.child.textContent==='Resume Reel','paused state label restored');
click('pause');
const beforeNavigation=plays.length;
arrow('ArrowDown');
check(plays.length===beforeNavigation,'moving to next Reel must not autoplay if autoplay OFF');
check(pauseButton.child.textContent==='Resume Reel','navigation to next Reel resets manual-pause state');
click('pause');
check(plays.at(-1)==='B','explicit Resume plays next Reel without enabling global autoplay');
hidden=true;
docListeners.visibilitychange();
check(pauses.at(-1)==='B','background page pauses video');
check(pauseButton.child.textContent==='Resume Reel','background pause button reflects stopped state');
hidden=false;
docListeners.visibilitychange();
check(plays.at(-1)==='B','tab visibility should not trigger a new playback without consent');
const manualBefore=plays.length;
reduced=true;saveData=true;
click('pause');
check(plays.length===manualBefore+1,'explicit user playback remains available with reduced motion/data saver');
arrow('ArrowUp');
check(plays.length===manualBefore+1,'reduced-motion/data saver prevents background swipe autoplay');
reduced=false;saveData=false;
click('autoplay');
check(autoButton['aria-pressed']==='true','autoplay requires explicit opt-in');
check(plays.at(-1)==='A','opt-in autoplay may play active Reel');
const p=plays.length;
arrow('ArrowDown');
check(plays.length===p+1&&plays.at(-1)==='B','opt-in autoplay advances on navigation');
click('autoplay');
check(autoButton['aria-pressed']==='false','disable autoplay updates state');
check(pauses.at(-1)==='B','disabling autoplay pauses media');
const afterDisable=plays.length;
arrow('ArrowUp');
check(plays.length===afterDisable,'autoplay disabled again: navigation stays paused');
console.log('V12.3 explicit playback/manual consent: '+n+'/'+n+' isolated browser-shim assertions PASS (not real browser)');
