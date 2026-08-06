(function () {
	'use strict';
	const app = document.querySelector('[data-rsv-app]');
	if (!app) return;
	const feed = app.querySelector('[data-rsv-feed]');
	const globalStatus = app.querySelector('.rsv-status');
	const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const saveData = navigator.connection && navigator.connection.saveData;
	let active = 0;
	let paused = false;
	let startedAt = Date.now();
	let activatedCount = 0;
	let reportReel = null;
	const progressSent = new Map();
	const sessionIds = new Map();

	function uuid() {
		return window.crypto && typeof window.crypto.randomUUID === 'function' ? window.crypto.randomUUID() : 'rsv-' + Date.now() + '-' + Math.random().toString(16).slice(2);
	}
	function items() { return feed ? Array.from(feed.querySelectorAll('[data-rsv-reel]')) : []; }
	function announce(message, target) { (target || globalStatus || app).textContent = message || ''; }
	function api(path, options) {
		options = options || {};
		options.headers = Object.assign({ 'Content-Type': 'application/json' }, RSV.loggedIn ? { 'X-WP-Nonce': RSV.nonce } : {}, options.headers || {});
		return fetch(RSV.root + path, options).then(async function (response) {
			const body = await response.json().catch(function () { return {}; });
			if (!response.ok) throw new Error(body.message || RSV.i18n.error);
			return body;
		});
	}
	function safeOrigin(url) { try { return new URL(url, location.href).origin; } catch (e) { return location.origin; } }
	function pauseMedia(item) {
		if (!item) return;
		const video = item.querySelector('video'); if (video) video.pause();
		const frame = item.querySelector('iframe');
		if (frame && frame.contentWindow) {
			const origin = safeOrigin(frame.src);
			frame.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":[]}', origin);
			frame.contentWindow.postMessage({ method: 'pause' }, origin);
		}
	}
	function playMedia(item) {
		if (!item || paused || document.hidden) return;
		const video = item.querySelector('video');
		if (video && !reduced && !saveData) {
			const resume = Number(video.dataset.resume || 0); if (resume && !video.currentTime) video.currentTime = resume;
			video.muted = true; video.play().catch(function () {});
		}
	}
	function playbackMarkup(item, result) {
		const box = item.querySelector('[data-rsv-media]');
		const data = result && result.playback && result.playback.playback ? result.playback.playback : null;
		const session = result && result.playback && result.playback.session ? result.playback.session : {};
		if (!box || !data || !data.url) throw new Error(RSV.i18n.error);
		box.textContent = '';
		if (data.type === 'iframe' || data.provider === 'youtube' || data.provider === 'vimeo') {
			const frame = document.createElement('iframe'); frame.src = data.url; frame.title = item.querySelector('h2').textContent; frame.loading = 'lazy'; frame.allow = data.allow || 'autoplay; fullscreen; picture-in-picture'; frame.sandbox = data.sandbox || 'allow-scripts allow-same-origin allow-presentation'; frame.allowFullscreen = true; box.appendChild(frame);
		} else {
			const video = document.createElement('video'); video.className = 'rsv-video'; video.controls = true; video.playsInline = true; video.preload = saveData ? 'none' : 'metadata'; video.src = data.url; if (data.poster) video.poster = data.poster; video.dataset.resume = String(session.resume_seconds || 0);
			if (data.captions_url) { const track = document.createElement('track'); track.kind = 'captions'; track.src = data.captions_url; track.default = true; video.appendChild(track); }
			bindVideo(video); box.appendChild(video);
		}
		if (data.transcript_url) { const link = document.createElement('a'); link.className = 'rsv-transcript'; link.href = data.transcript_url; link.textContent = 'Transcript'; box.appendChild(link); }
	}
	function loadMedia(item, autoplay) {
		if (!item || item.dataset.mediaLoaded === '1') { if (autoplay) playMedia(item); return Promise.resolve(); }
		item.dataset.mediaLoaded = 'loading';
		return api('/reels/' + encodeURIComponent(item.dataset.rsvReel), { method: 'GET' }).then(function (result) {
			playbackMarkup(item, result); item.dataset.mediaLoaded = '1'; if (autoplay) playMedia(item);
		}).catch(function (error) { item.dataset.mediaLoaded = 'error'; announce(error.message, item.querySelector('.rsv-item-status')); });
	}
	function setInert(item, value) {
		if ('inert' in item) item.inert = value;
		item.classList.toggle('is-inactive', value);
	}
	function activate(index, scroll) {
		const list = items(); if (!list.length) return;
		index = Math.max(0, Math.min(index, list.length - 1));
		if (index !== active && list[active]) saveProgress(list[active], false, Date.now() - startedAt < 1500);
		list.forEach(function (item, i) { item.classList.toggle('is-active', i === index); setInert(item, i !== index); if (i !== index) pauseMedia(item); });
		active = index; const item = list[active]; startedAt = Date.now(); activatedCount++;
		if (scroll !== false) item.scrollIntoView({ behavior: reduced ? 'auto' : 'smooth', block: 'center' });
		item.focus({ preventScroll: true });
		loadMedia(item, true);
		if (list[active + 1] && !saveData) loadMedia(list[active + 1], false);
		announce((active + 1) + ' / ' + list.length + ': ' + item.querySelector('h2').textContent);
		if (activatedCount > 0 && activatedCount % 10 === 0) { paused = true; pauseMedia(item); announce(RSV.i18n.break); }
	}
	function currentSeconds(item) { const video = item && item.querySelector('video'); return video ? Math.floor(video.currentTime || 0) : Math.floor((Date.now() - startedAt) / 1000); }
	function sessionId(item) { if (!sessionIds.has(item.dataset.rsvReel)) sessionIds.set(item.dataset.rsvReel, uuid()); return sessionIds.get(item.dataset.rsvReel); }
	function saveProgress(item, completed, rapid) {
		if (!item) return;
		const seconds = currentSeconds(item); const key = item.dataset.rsvReel; const previous = progressSent.get(key) || -20;
		if (!completed && seconds - previous < 15) return;
		progressSent.set(key, seconds);
		const body = { seconds: seconds, completed: !!completed, rapid: !!rapid, session_id: sessionId(item) };
		const path = RSV.loggedIn ? '/reels/' + encodeURIComponent(key) + '/progress' : '/reels/' + encodeURIComponent(key) + '/impression';
		api(path, { method: 'POST', body: JSON.stringify(body), keepalive: true }).catch(function () {});
	}
	function bindVideo(video) {
		let last = -20;
		video.addEventListener('timeupdate', function () { const sec = Math.floor(video.currentTime || 0); if (sec - last >= 15) { last = sec; saveProgress(video.closest('[data-rsv-reel]'), false, false); } });
		video.addEventListener('ended', function () { saveProgress(video.closest('[data-rsv-reel]'), true, false); });
		video.addEventListener('error', function () { announce(RSV.i18n.error, video.closest('.rsv-reel').querySelector('.rsv-item-status')); });
	}
	function observeNew(item) { if (observer) observer.observe(item); }
	let observer = null;
	if (feed && 'IntersectionObserver' in window) {
		observer = new IntersectionObserver(function (entries) { entries.forEach(function (entry) { if (entry.isIntersecting && entry.intersectionRatio >= 0.72) { const index = items().indexOf(entry.target); if (index >= 0 && index !== active) activate(index, false); } }); }, { threshold: [0.72] });
		items().forEach(observeNew);
	}
	if (feed) {
		feed.addEventListener('keydown', function (event) { if (event.key === 'ArrowDown' || event.key === 'PageDown') { event.preventDefault(); activate(active + 1); } if (event.key === 'ArrowUp' || event.key === 'PageUp') { event.preventDefault(); activate(active - 1); } if (event.key === ' ') { event.preventDefault(); paused = !paused; paused ? pauseMedia(items()[active]) : playMedia(items()[active]); } });
		let startY = null;
		feed.addEventListener('pointerdown', function (e) { if (e.pointerType !== 'mouse') startY = e.clientY; }, { passive: true });
		feed.addEventListener('pointerup', function (e) { if (startY === null) return; const delta = e.clientY - startY; startY = null; if (Math.abs(delta) > 60) activate(active + (delta < 0 ? 1 : -1)); }, { passive: true });
		activate(0, false);
	}
	function createCard(item) {
		const article = document.createElement('article'); article.className = 'rsv-reel is-inactive'; article.dataset.rsvReel = item.id; article.dataset.duration = item.duration_seconds || 0; article.tabIndex = -1;
		const media = document.createElement('div'); media.className = 'rsv-media'; media.dataset.rsvMedia = '';
		const load = document.createElement('button'); load.type = 'button'; load.className = 'rsv-play-placeholder'; load.dataset.rsvLoadMedia = ''; load.setAttribute('aria-label', 'Load and play ' + item.title); if (item.cover_url) { const img = document.createElement('img'); img.src = item.cover_url; img.alt = ''; img.loading = 'lazy'; load.appendChild(img); } const icon = document.createElement('span'); icon.textContent = '▶'; load.appendChild(icon); media.appendChild(load);
		const copy = document.createElement('div'); copy.className = 'rsv-copy'; const topic = document.createElement('p'); topic.className = 'rsv-topic'; topic.textContent = item.topic; const h2 = document.createElement('h2'); const link = document.createElement('a'); link.href = item.url; link.textContent = item.title; h2.appendChild(link); const caption = document.createElement('p'); caption.textContent = item.caption; copy.append(topic, h2, caption);
		const author = document.createElement('p'); author.className = 'rsv-author'; const profile = document.createElement('a'); profile.href = item.owner.profile_url; profile.textContent = item.owner.name; const label = document.createElement('span'); label.textContent = item.owner.label; author.append(profile, document.createTextNode(' '), label); copy.appendChild(author);
		const actions = document.createElement('div'); actions.className = 'rsv-actions'; actions.setAttribute('role','group'); [['like','👍 Like'],['dislike','👎 Dislike'],['save','🔖 Save']].forEach(function (pair) { const b=document.createElement('button');b.type='button';b.dataset.rsvAction=pair[0];b.setAttribute('aria-pressed','false');b.textContent=pair[1];actions.appendChild(b); }); const share=document.createElement('button');share.type='button';share.dataset.rsvShare='';share.textContent='↗ Share';const report=document.createElement('button');report.type='button';report.dataset.rsvReport='';report.textContent='⚑ Report';actions.append(share,report);copy.appendChild(actions);
		const status=document.createElement('div');status.className='rsv-item-status';status.setAttribute('role','status');status.setAttribute('aria-live','polite');copy.appendChild(status);article.append(media,copy);return article;
	}
	async function loadMore(button) {
		const cursor = feed.dataset.nextCursor; if (!cursor) return;
		button.disabled = true; button.setAttribute('aria-busy','true');
		try { const query = '/reels?limit=8&cursor=' + encodeURIComponent(cursor) + '&sort=' + encodeURIComponent(feed.dataset.sort || 'rank') + '&topic=' + encodeURIComponent(feed.dataset.topic || ''); const data = await api(query, { method: 'GET' }); data.items.forEach(function (item) { const card=createCard(item);feed.appendChild(card);observeNew(card); }); feed.dataset.nextCursor=data.next_cursor||''; if (!data.next_cursor) button.remove(); announce(data.items.length + ' Reels loaded.'); }
		catch (error) { announce(error.message); }
		finally { if (button.isConnected) { button.disabled=false;button.removeAttribute('aria-busy'); } }
	}
	const dialog = app.querySelector('[data-rsv-report-dialog]');
	document.addEventListener('click', function (event) {
		const back=event.target.closest('[data-rsv-back]');if(back){if(document.referrer){try{if(new URL(document.referrer).origin===location.origin){history.back();return;}}catch(e){}}location.href=RSV.homeUrl;return;}
		const prev=event.target.closest('[data-rsv-prev]');if(prev)activate(active-1);const next=event.target.closest('[data-rsv-next]');if(next)activate(active+1);
		const pause=event.target.closest('[data-rsv-pause]');if(pause){paused=!paused;pause.textContent=paused?RSV.i18n.resume:RSV.i18n.pause;paused?pauseMedia(items()[active]):playMedia(items()[active]);}
		const load=event.target.closest('[data-rsv-load-media]');if(load)loadMedia(load.closest('[data-rsv-reel]'),true);
		const more=event.target.closest('[data-rsv-load-more]');if(more)loadMore(more);
		const action=event.target.closest('[data-rsv-action]');if(action){const reel=action.closest('[data-rsv-reel]');if(!RSV.loggedIn){announce(RSV.i18n.login,reel.querySelector('.rsv-item-status'));return;}api('/reels/'+encodeURIComponent(reel.dataset.rsvReel)+'/interact',{method:'POST',body:JSON.stringify({type:action.dataset.rsvAction})}).then(function(data){action.setAttribute('aria-pressed',data.active?'true':'false');announce(RSV.i18n.saved,reel.querySelector('.rsv-item-status'));}).catch(function(error){announce(error.message,reel.querySelector('.rsv-item-status'));});}
		const share=event.target.closest('[data-rsv-share]');if(share){const reel=share.closest('[data-rsv-reel]');const url=reel.querySelector('h2 a').href;if(navigator.share)navigator.share({title:reel.querySelector('h2').textContent,url:url}).catch(function(){});else if(navigator.clipboard)navigator.clipboard.writeText(url).then(function(){announce(RSV.i18n.saved,reel.querySelector('.rsv-item-status'));});}
		const report=event.target.closest('[data-rsv-report]');if(report){if(!RSV.loggedIn){announce(RSV.i18n.login,report.closest('.rsv-reel').querySelector('.rsv-item-status'));return;}reportReel=report.closest('[data-rsv-reel]');if(dialog&&dialog.showModal)dialog.showModal();}
		const cancel=event.target.closest('[data-rsv-report-cancel]');if(cancel&&dialog)dialog.close();
		const clear=event.target.closest('[data-rsv-clear-history]');if(clear&&window.confirm('Clear all private Reel history?'))api('/history',{method:'DELETE'}).then(function(){location.reload();}).catch(function(error){announce(error.message);});
	});
	const reportForm=app.querySelector('[data-rsv-report-form]');if(reportForm)reportForm.addEventListener('submit',function(event){event.preventDefault();if(!reportReel)return;const fd=new FormData(reportForm);const out=reportForm.querySelector('.rsv-report-status');api('/reels/'+encodeURIComponent(reportReel.dataset.rsvReel)+'/report',{method:'POST',headers:{'Idempotency-Key':uuid()},body:JSON.stringify({reason:fd.get('reason'),details:fd.get('details')})}).then(function(){announce(RSV.i18n.reportSubmitted,out);setTimeout(function(){dialog.close();reportForm.reset();},600);}).catch(function(error){announce(error.message,out);});});
	const form=app.querySelector('[data-rsv-create]');if(form)form.addEventListener('submit',function(event){event.preventDefault();const button=form.querySelector('button[type="submit"]');const out=form.querySelector('.rsv-form-status');button.disabled=true;const data=Object.fromEntries(new FormData(form).entries());api('/reels',{method:'POST',headers:{'Idempotency-Key':uuid()},body:JSON.stringify(data)}).then(function(reel){announce('Draft created: '+reel.id,out);form.reset();}).catch(function(error){announce(error.message,out);}).finally(function(){button.disabled=false;});});
	document.addEventListener('visibilitychange',function(){const item=items()[active];if(document.hidden){saveProgress(item,false,false);pauseMedia(item);}else playMedia(item);});
	window.addEventListener('pagehide',function(){saveProgress(items()[active],false,false);});
	window.addEventListener('offline',function(){announce(RSV.i18n.offline);pauseMedia(items()[active]);});
	window.addEventListener('online',function(){announce('');});
}());
