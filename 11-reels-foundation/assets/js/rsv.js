(function () {
	'use strict';
	const root = document.querySelector('[data-rsv-feed]');
	const status = document.querySelector('.rsv-status');
	const shell = document.querySelector('.rsv-shell[data-rsv-sort]');
	const viewSessions = new Map();
	const lastSent = new WeakMap();
	const items = () => root ? Array.from(root.querySelectorAll('[data-rsv-reel]')) : [];
	let active = 0;
	let feedPaused = true;
	let autoplay = false;
	let observer;
	let touchStart = null;

	function format(template) {
		const args = Array.prototype.slice.call(arguments, 1);
		let index = 0;
		return String(template).replace(/%\d+\$[ds]|%s|%d/g, function () { return String(args[index++] ?? ''); });
	}

	function api(path, options) {
		options = options || {};
		options.credentials = 'same-origin';
		options.headers = Object.assign({ 'Content-Type': 'application/json', 'X-WP-Nonce': RSV.nonce }, options.headers || {});
		return fetch(RSV.root + path, options).then(async function (response) {
			const body = await response.json().catch(function () { return {}; });
			if (!response.ok) throw new Error(body.message || RSV.i18n.error);
			return body;
		});
	}

	function announce(message, el) {
		let target = el || status;
		if (!target) {
			target = document.querySelector('[data-rsv-global-status]');
			if (!target) {
				target = document.createElement('div');
				target.className = 'rsv-status rsv-visually-hidden';
				target.dataset.rsvGlobalStatus = '1';
				target.setAttribute('role', 'status');
				target.setAttribute('aria-live', 'polite');
				document.body.appendChild(target);
			}
		}
		target.textContent = message;
	}
	function targetOrigin(frame) { return frame && frame.dataset.controlOrigin ? frame.dataset.controlOrigin : ''; }
	function pauseMedia(item) {
		if (!item) return;
		const video = item.querySelector('video');
		if (video) video.pause();
		const frame = item.querySelector('iframe');
		const origin = targetOrigin(frame);
		if (frame && frame.contentWindow && origin) {
			frame.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":[]}', origin);
			frame.contentWindow.postMessage({ method: 'pause' }, origin);
		}
	}
	function canAutoplay() {
		const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		const saveData = navigator.connection && navigator.connection.saveData;
		return autoplay && !feedPaused && !document.hidden && !reduced && !saveData;
	}
	function playMedia(item) {
		if (!item || !canAutoplay()) return;
		const video = item.querySelector('video');
		if (video) {
			const resume = Number(video.dataset.resume || 0);
			if (resume && !video.currentTime) video.currentTime = resume;
			video.muted = true;
			video.play().catch(function () {});
		}
	}
	function setInteractive(item, enabled) {
		if (!item) return;
		item.querySelectorAll('a,button,input,select,textarea,summary,video').forEach(function (control) {
			if (enabled) {
				if (control.dataset.rsvOldTabindex !== undefined) {
					const old = control.dataset.rsvOldTabindex;
					old === '' ? control.removeAttribute('tabindex') : control.setAttribute('tabindex', old);
					delete control.dataset.rsvOldTabindex;
				}
			} else {
				if (control.dataset.rsvOldTabindex === undefined) control.dataset.rsvOldTabindex = control.getAttribute('tabindex') || '';
				control.setAttribute('tabindex', '-1');
			}
		});
		if ('inert' in item) item.inert = !enabled;
	}
	function activate(index, focus) {
		const list = items();
		if (!list.length) return;
		index = Math.max(0, Math.min(index, list.length - 1));
		list.forEach(function (item, i) {
			const enabled = i === index;
			item.classList.toggle('is-active', enabled);
			item.setAttribute('aria-hidden', enabled ? 'false' : 'true');
			setInteractive(item, enabled);
			if (!enabled) pauseMedia(item);
		});
		active = index;
		const item = list[active];
		if (focus !== false) {
			item.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'center' });
			item.focus({ preventScroll: true });
		}
		playMedia(item);
		announce(format(RSV.i18n.position, active + 1, list.length));
	}

	function ensureViewSession(item) {
		if (!RSV.loggedIn || !item || !item.querySelector('video')) return Promise.resolve('');
		const id = item.dataset.rsvReel;
		if (viewSessions.has(id)) return Promise.resolve(viewSessions.get(id));
		return api('/reels/' + id + '/view-session', { method: 'POST', body: '{}' }).then(function (data) {
			viewSessions.set(id, data.session_id);
			return data.session_id;
		});
	}
	function saveProgress(item, force) {
		if (!RSV.loggedIn || !item) return;
		const video = item.querySelector('video');
		if (!video || !Number.isFinite(video.currentTime)) return;
		const now = Date.now();
		const previous = lastSent.get(video) || { at: 0, seconds: -1 };
		const seconds = Math.floor(video.currentTime || 0);
		if (!force && (now - previous.at < 9000 || seconds - previous.seconds < 5)) return;
		lastSent.set(video, { at: now, seconds: seconds });
		ensureViewSession(item).then(function (sessionId) {
			if (!sessionId) return;
			return api('/reels/' + item.dataset.rsvReel + '/progress', { method: 'POST', body: JSON.stringify({ session_id: sessionId, seconds: seconds }) });
		}).catch(function () {});
	}

	function observeItems() {
		if (!root || !('IntersectionObserver' in window)) return;
		if (!observer) {
			observer = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting && entry.intersectionRatio >= 0.7) {
						const index = items().indexOf(entry.target);
						if (index >= 0 && index !== active) { saveProgress(items()[active], true); activate(index, false); }
					}
				});
			}, { threshold: [0.7] });
		}
		items().forEach(function (item) { if (!item.dataset.rsvObserved) { observer.observe(item); item.dataset.rsvObserved = '1'; } });
	}

	if (root) {
		observeItems();
		root.addEventListener('keydown', function (event) {
			if (event.key === 'ArrowDown' || event.key === 'PageDown') { event.preventDefault(); saveProgress(items()[active], true); activate(active + 1); }
			if (event.key === 'ArrowUp' || event.key === 'PageUp') { event.preventDefault(); saveProgress(items()[active], true); activate(active - 1); }
			if (event.key === ' ') { event.preventDefault(); feedPaused = !feedPaused; feedPaused ? pauseMedia(items()[active]) : playMedia(items()[active]); }
		});
		root.addEventListener('pointerdown', function (event) { if (event.pointerType === 'touch') touchStart = { x: event.clientX, y: event.clientY }; });
		root.addEventListener('pointerup', function (event) {
			if (!touchStart || event.pointerType !== 'touch') return;
			const dx = event.clientX - touchStart.x, dy = event.clientY - touchStart.y; touchStart = null;
			if (Math.abs(dy) > 55 && Math.abs(dy) > Math.abs(dx) * 1.2) { saveProgress(items()[active], true); activate(active + (dy < 0 ? 1 : -1)); }
		});
		activate(0, false);
	}

	document.addEventListener('click', function (event) {
		const back = event.target.closest('[data-rsv-back]');
		if (back) { try { if (document.referrer && new URL(document.referrer).origin === location.origin) history.back(); else location.href = RSV.feedUrl || RSV.homeUrl; } catch (e) { location.href = RSV.homeUrl; } return; }
		if (event.target.closest('[data-rsv-prev]')) { saveProgress(items()[active], true); activate(active - 1); }
		if (event.target.closest('[data-rsv-next]')) { saveProgress(items()[active], true); activate(active + 1); }
		const pause = event.target.closest('[data-rsv-pause]');
		if (pause) { feedPaused = !feedPaused; pause.textContent = feedPaused ? RSV.i18n.resume : RSV.i18n.pause; feedPaused ? pauseMedia(items()[active]) : playMedia(items()[active]); }
		const auto = event.target.closest('[data-rsv-autoplay]');
		if (auto) { autoplay = !autoplay; auto.setAttribute('aria-pressed', autoplay ? 'true' : 'false'); auto.textContent = autoplay ? RSV.i18n.disableAutoplay : RSV.i18n.enableAutoplay; if (autoplay) { feedPaused = false; playMedia(items()[active]); } else pauseMedia(items()[active]); }
		const action = event.target.closest('[data-rsv-action]');
		if (action) {
			const reel = action.closest('[data-rsv-reel]');
			if (!RSV.loggedIn) { announce(RSV.i18n.login, reel.querySelector('.rsv-item-status')); return; }
			api('/reels/' + reel.dataset.rsvReel + '/interact', { method: 'POST', body: JSON.stringify({ type: action.dataset.rsvAction }) }).then(function (data) { action.setAttribute('aria-pressed', data.active ? 'true' : 'false'); announce(RSV.i18n.saved, reel.querySelector('.rsv-item-status')); }).catch(function (error) { announce(error.message, reel.querySelector('.rsv-item-status')); });
		}
		const share = event.target.closest('[data-rsv-share]');
		if (share) {
			const url = share.dataset.url;
			const output = share.closest('.rsv-reel').querySelector('.rsv-item-status');
			if (navigator.share) navigator.share({ url: url }).catch(function () {});
			else if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(url).then(function () { announce(RSV.i18n.copied, output); }).catch(function () { window.prompt(RSV.i18n.copyLink, url); });
			else window.prompt(RSV.i18n.copyLink, url);
		}
		const clear = event.target.closest('[data-rsv-clear-history]');
		if (clear && window.confirm(RSV.i18n.clearHistory)) api('/history', { method: 'DELETE' }).then(function () { location.reload(); }).catch(function (error) { announce(error.message); });
		const load = event.target.closest('[data-rsv-load-more]');
		if (load && root) {
			load.disabled = true; load.textContent = RSV.i18n.loading;
			const sort = shell ? shell.dataset.rsvSort : 'recommended';
			api('/reels?format=html&limit=12&sort=' + encodeURIComponent(sort) + '&cursor=' + encodeURIComponent(load.dataset.cursor), { method: 'GET' }).then(function (data) {
				(data.html || []).forEach(function (html) { const template = document.createElement('template'); template.innerHTML = html.trim(); root.appendChild(template.content.firstElementChild); });
				if (data.next_cursor) { load.dataset.cursor = data.next_cursor; load.disabled = false; load.textContent = RSV.i18n.loadMore; } else load.remove();
				observeItems();
				bindVideos(root);
			}).catch(function (error) { load.disabled = false; load.textContent = RSV.i18n.loadMore; announce(error.message); });
		}
	});

	document.addEventListener('submit', function (event) {
		const reportForm = event.target.closest('[data-rsv-report-form]');
		if (reportForm) {
			event.preventDefault(); const reel = reportForm.closest('[data-rsv-reel]');
			if (!RSV.loggedIn) { announce(RSV.i18n.login, reel.querySelector('.rsv-item-status')); return; }
			const data = Object.fromEntries(new FormData(reportForm).entries());
			api('/reels/' + reel.dataset.rsvReel + '/report', { method: 'POST', headers: { 'Idempotency-Key': window.crypto && window.crypto.randomUUID ? window.crypto.randomUUID() : String(Date.now()) + '-' + Math.random().toString(36).slice(2) }, body: JSON.stringify(data) }).then(function () { reportForm.reset(); reportForm.closest('details').open = false; announce(RSV.i18n.reportSubmitted, reel.querySelector('.rsv-item-status')); }).catch(function (error) { announce(error.message, reel.querySelector('.rsv-item-status')); });
		}
		const createForm = event.target.closest('[data-rsv-create]');
		if (createForm) {
			event.preventDefault(); const button = createForm.querySelector('button[type="submit"]'), out = createForm.querySelector('.rsv-form-status'); button.disabled = true;
			api('/reels', { method: 'POST', headers: { 'Idempotency-Key': window.crypto && window.crypto.randomUUID ? window.crypto.randomUUID() : String(Date.now()) + '-' + Math.random().toString(36).slice(2) }, body: JSON.stringify(Object.fromEntries(new FormData(createForm).entries())) }).then(function (reel) { announce(format(RSV.i18n.draftCreated, reel.id), out); createForm.reset(); }).catch(function (error) { announce(error.message, out); }).finally(function () { button.disabled = false; });
		}
	});

	function bindVideos(scope) {
		(scope || document).querySelectorAll('.rsv-video:not([data-rsv-bound])').forEach(function (video) {
			video.dataset.rsvBound = '1';
			video.addEventListener('play', function () { ensureViewSession(video.closest('[data-rsv-reel]')).catch(function () {}); });
			video.addEventListener('timeupdate', function () { saveProgress(video.closest('[data-rsv-reel]'), false); });
			video.addEventListener('ended', function () { saveProgress(video.closest('[data-rsv-reel]'), true); });
		});
	}
	bindVideos(document);
	document.addEventListener('visibilitychange', function () { if (document.hidden) { saveProgress(items()[active], true); pauseMedia(items()[active]); } else playMedia(items()[active]); });
	window.addEventListener('offline', function () { announce(RSV.i18n.error); pauseMedia(items()[active]); });
}());
