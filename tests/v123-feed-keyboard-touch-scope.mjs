'use strict';
/**
 * File11 V12.3 FR-005 / NFR-005 keyboard and touch scope regression.
 * Isolated DOM fixture, not a real browser, screenreader or Staging pass.
 */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../11-reels-foundation/assets/js/rsv.js'), 'utf8');
const keyListeners = {};
const status = { textContent: '' };
function article(id) {
    return {
        id, dataset: { rsvReel: id },
        classList: { isActive: false, toggle(cls, val) { if (cls === 'is-active') this.isActive = val; } },
        querySelector() { return null; },
        querySelectorAll() { return []; },
        setAttribute(name, val) { this[name] = String(val); },
        scrollIntoView() {},
        focus() {}
    };
}
const articles = [article('reel_01'), article('reel_02')];
const root = {
    dataset: {},
    addEventListener(event, fn) { keyListeners[event] = fn; },
    querySelectorAll(selector) { return selector === '[data-rsv-reel]' ? articles : []; },
};
const docListeners = {};
const document = {
    hidden: false,
    body: { appendChild() {} },
    querySelector(selector) {
        if (selector === '[data-rsv-feed]') return root;
        if (selector === '.rsv-status') return status;
        return null;
    },
    querySelectorAll() { return []; },
    addEventListener(event, fn) { docListeners[event] = fn; }
};
const window = { matchMedia() { return { matches: false }; }, addEventListener() {} };
const context = {
    document, window,
    navigator: { connection: { saveData: false } },
    RSV: { loggedIn: false, root: '/wp-json/rsv/v1', nonce: 'test', i18n: { position: '%1$d of %2$d', error: 'Error' } },
    console,
    URL,
    Promise,
    Date,
    Math,
};
vm.runInNewContext(source, context, { filename: 'rsv.js' });
assert.equal(typeof keyListeners.keydown, 'function');
assert.equal(typeof keyListeners.pointerdown, 'function');
assert.equal(typeof keyListeners.pointerup, 'function');
assert.equal(articles[0].classList.isActive, true);
let checks = 0;
function eventTarget(kind) {
    return {
        tagName: kind.toUpperCase(),
        closest(selector) {
            const cls = selector.toLowerCase();
            if (kind === 'svg-in-button') return cls.includes('button') ? { tagName: 'BUTTON' } : null;
            if (kind === 'nested-contenteditable') return cls.includes('contenteditable') ? { isContentEditable: true } : null;
            return cls.split(/[,\s]+/).some(term => term === kind) ? this : null;
        }
    };
}
function sendKey(key, target, options={}) {
    let prevented=0;
    const e = {
        key, target, defaultPrevented: !!options.defaultPrevented,
        altKey: !!options.altKey, ctrlKey: !!options.ctrlKey,
        metaKey: !!options.metaKey,
        preventDefault() { ++prevented; this.defaultPrevented=true; }
    };
    keyListeners.keydown(e);
    return prevented;
}
const interactive = [
    ['textarea',' '], ['textarea','ArrowDown'], ['input','ArrowUp'],
    ['select','PageDown'], ['button',' '], ['a','PageUp'],
    ['summary',' '], ['svg-in-button',' '], ['nested-contenteditable','ArrowDown']
];
for (const [target, key] of interactive) {
    assert.equal(sendKey(key, eventTarget(target)), 0, target + ' ' + key + ' should retain native keyboard handling');
    assert.equal(articles[0].classList.isActive, true, 'interactive control must not navigate Reel feed');
    ++checks;
}
assert.equal(sendKey('ArrowDown', root), 1);
assert.equal(articles[1].classList.isActive, true);
++checks;
assert.equal(sendKey('ArrowUp', root, { ctrlKey:true }), 0);
assert.equal(articles[1].classList.isActive, true);
++checks;
assert.equal(sendKey('ArrowUp', root, {defaultPrevented:true}), 0);
assert.equal(articles[1].classList.isActive, true);
++checks;
assert.equal(sendKey('ArrowUp', root), 1);
assert.equal(articles[0].classList.isActive, true);
++checks;
assert.equal(sendKey(' ', root), 1);
++checks;
const touchTarget=eventTarget('textarea');
keyListeners.pointerdown({ pointerType:'touch', clientX:50, clientY:100, target:touchTarget });
keyListeners.pointerup({ pointerType:'touch', clientX:50, clientY:20, target:touchTarget });
assert.equal(articles[0].classList.isActive, true, 'vertical text-area swipe must not switch video');
++checks;
keyListeners.pointerdown({ pointerType:'touch', clientX:50, clientY:100, target:root });
keyListeners.pointerup({ pointerType:'touch', clientX:50, clientY:20, target:root });
assert.equal(articles[1].classList.isActive, true, 'free-space vertical swipe still navigates');
++checks;
console.log('File11 V12.3 input/key scope: ' + checks + '/' + checks + ' isolated DOM assertions PASS (no browser/WCAG certification)');
