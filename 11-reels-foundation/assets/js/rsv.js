(function () {
	'use strict';
	const root = document.querySelector('[data-rsv-feed]');
	const status = document.querySelector('.rsv-status');
	const items = () => root ? Array.from(root.querySelectorAll('[data-rsv-reel]')) : [];
	let active = 0;
	let feedPaused = false;
	let lastStart = Date.now();

	function api(path, options) {
		options = options || {};
		options.headers = Object.assign({
			'Content-Type': 'application/json',
			'X-WP-Nonce': RSV.nonce
		}, options.headers || {});
		return fetch(RSV.root + path, options).then(async function (response) {
			const body = await response.json().catch(function () { return {}; });
			if (!response.ok) throw new Error(body.message || RSV.i18n.error);
			return body;
		});
	}

	function announce(message, el) {
		(el || status || document.body).textContent = message;
	}

	function pauseMedia(item) {
		if (!item) return;
		const video = item.querySelector('video');
		if (video) video.pause();
		const frame = item.querySelector('iframe');
		if (frame && frame.contentWindow) {
			frame.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":[]}', '*');
			frame.contentWindow.postMessage({ method: 'pause' }, '*');
		}
	}

	function playMedia(item) {
		if (!item || feedPaused || document.hidden) return;
		const video = item.querySelector('video');
		if (video && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			const resume = Number(video.dataset.resume || 0);
			if (resume && !video.currentTime) video.currentTime = resume;
			video.muted = true;
			video.play().catch(function () {});
		}
	}

	function activate(index) {
		const list = items();
		if (!list.length) return;
		index = Math.max(0, Math.min(index, list.length - 1));
		list.forEach(function (item, i) {
			item.classList.toggle('is-active', i === index);
			item.setAttribute('aria-hidden', i === index ? 'false' : 'true');
			if (i !== index) pauseMedia(item);
		});
		active = index;
		const item = list[active];
		item.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'center' });
		item.focus({ preventScroll: true });
		lastStart = Date.now();
		playMedia(item);
		announce((active + 1) + ' of ' + list.length);
	}

	function saveProgress(item, completed, rapid) {
		if (!RSV.loggedIn || !item) return;
		const video = item.querySelector('video');
		const seconds = video ? Math.floor(video.currentTime || 0) : Math.floor((Date.now() - lastStart) / 1000);
		const duration = Number(item.dataset.duration || (video ? video.duration : 0) || 0);
		api('/reels/' + item.dataset.rsvReel + '/progress', {
			method: 'POST',
			body: JSON.stringify({ seconds: seconds, duration: duration, completed: !!completed, rapid: !!rapid })
		}).catch(function () {});
	}

	if (root) {
		const observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting && entry.intersectionRatio >= 0.7) {
					const index = items().indexOf(entry.target);
					if (index >= 0 && index !== active) {
						saveProgress(items()[active], false, Date.now() - lastStart < 1500);
						activate(index);
					}
				}
			});
		}, { threshold: [0.7] });
		items().forEach(function (item) { observer.observe(item); });
		root.addEventListener('keydown', function (event) {
			if (event.key === 'ArrowDown' || event.key === 'PageDown') { event.preventDefault(); activate(active + 1); }
			if (event.key === 'ArrowUp' || event.key === 'PageUp') { event.preventDefault(); activate(active - 1); }
			if (event.key === ' ') { event.preventDefault(); feedPaused = !feedPaused; feedPaused ? pauseMedia(items()[active]) : playMedia(items()[active]); }
		});
		activate(0);
	}

	document.addEventListener('click', function (event) {
		const back = event.target.closest('[data-rsv-back]');
		if (back) {
			if (document.referrer && new URL(document.referrer).origin === location.origin) history.back();
			else location.href = '/';
			return;
		}
		const prev = event.target.closest('[data-rsv-prev]');
		const next = event.target.closest('[data-rsv-next]');
		const pause = event.target.closest('[data-rsv-pause]');
		if (prev) activate(active - 1);
		if (next) activate(active + 1);
		if (pause) {
			feedPaused = !feedPaused;
			pause.textContent = feedPaused ? 'Resume feed' : 'Pause feed';
			feedPaused ? pauseMedia(items()[active]) : playMedia(items()[active]);
		}
		const action = event.target.closest('[data-rsv-action]');
		if (action) {
			if (!RSV.loggedIn) { announce(RSV.i18n.login, action.closest('.rsv-reel').querySelector('.rsv-item-status')); return; }
			const reel = action.closest('[data-rsv-reel]');
			api('/reels/' + reel.dataset.rsvReel + '/interact', { method: 'POST', body: JSON.stringify({ type: action.dataset.rsvAction }) })
				.then(function (data) { action.setAttribute('aria-pressed', data.active ? 'true' : 'false'); announce(RSV.i18n.saved, reel.querySelector('.rsv-item-status')); })
				.catch(function (error) { announce(error.message, reel.querySelector('.rsv-item-status')); });
		}
		const report = event.target.closest('[data-rsv-report]');
		if (report) {
			if (!RSV.loggedIn) { announce(RSV.i18n.login, report.closest('.rsv-reel').querySelector('.rsv-item-status')); return; }
			const reason = window.prompt('Report reason: medical-claim, patient-privacy, harassment, copyright, spam, other');
			if (!reason) return;
			const reel = report.closest('[data-rsv-reel]');
			api('/reels/' + reel.dataset.rsvReel + '/report', {
				method: 'POST',
				headers: { 'Idempotency-Key': crypto.randomUUID ? crypto.randomUUID() : String(Date.now()) },
				body: JSON.stringify({ reason: reason, details: '' })
			}).then(function () { announce('Report submitted.', reel.querySelector('.rsv-item-status')); })
			  .catch(function (error) { announce(error.message, reel.querySelector('.rsv-item-status')); });
		}
		const clear = event.target.closest('[data-rsv-clear-history]');
		if (clear && window.confirm('Clear all private Reel history?')) {
			api('/history', { method: 'DELETE' }).then(function () { location.reload(); }).catch(function (error) { announce(error.message); });
		}
	});

	document.querySelectorAll('.rsv-video').forEach(function (video) {
		video.addEventListener('timeupdate', function () {
			const reel = video.closest('[data-rsv-reel]');
			if (Math.floor(video.currentTime) % 10 === 0) saveProgress(reel, false, false);
		});
		video.addEventListener('ended', function () { saveProgress(video.closest('[data-rsv-reel]'), true, false); });
	});

	const form = document.querySelector('[data-rsv-create]');
	if (form) {
		form.addEventListener('submit', function (event) {
			event.preventDefault();
			const button = form.querySelector('button[type="submit"]');
			const out = form.querySelector('.rsv-form-status');
			button.disabled = true;
			const data = Object.fromEntries(new FormData(form).entries());
			api('/reels', {
				method: 'POST',
				headers: { 'Idempotency-Key': crypto.randomUUID ? crypto.randomUUID() : String(Date.now()) },
				body: JSON.stringify(data)
			}).then(function (reel) {
				announce('Draft created. Submit it for review from the publishing dashboard. Reel ID: ' + reel.id, out);
				form.reset();
			}).catch(function (error) { announce(error.message, out); })
			  .finally(function () { button.disabled = false; });
		});
	}

	document.addEventListener('visibilitychange', function () {
		if (document.hidden) pauseMedia(items()[active]);
		else playMedia(items()[active]);
	});
}());
