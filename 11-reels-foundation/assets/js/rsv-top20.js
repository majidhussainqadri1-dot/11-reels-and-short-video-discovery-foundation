(function () {
	'use strict';
	if (!window.RSV_TOP20) return;
	const cfg = window.RSV_TOP20;
	const translate = window.wp && window.wp.i18n && typeof window.wp.i18n.__ === 'function' ? window.wp.i18n.__ : function (text) { return text; };
	cfg.i18n = Object.assign({
		stories: translate('Stories / Status', 'reels-short-video-discovery'),
		wellbeing: translate('Well-being', 'reels-short-video-discovery'),
		valueInsights: translate('Value insights', 'reels-short-video-discovery'),
		sourceSafety: translate('Source, transcript and safety', 'reels-short-video-discovery'),
		source: translate('Source', 'reels-short-video-discovery'),
		safety: translate('Safety', 'reels-short-video-discovery'),
		transcript: translate('Transcript', 'reels-short-video-discovery'),
		response: translate('Create an attributed response/remix', 'reels-short-video-discovery'),
		naturalStop: translate('Natural pause: take a moment before continuing.', 'reels-short-video-discovery'),
		sessionLimit: translate('Your chosen Reel session limit has been reached.', 'reels-short-video-discovery'),
		lateNight: translate('Late-night reminder: consider resting and returning later.', 'reels-short-video-discovery'),
		continue: translate('Continue by choice', 'reels-short-video-discovery'),
		error: translate('Additional Reel context is temporarily unavailable.', 'reels-short-video-discovery')
	}, cfg.i18n || {});
	const prefs = cfg.preferences || {};
	let activeId = '';
	let changes = 0;
	let naturalStopShown = false;
	let sessionStopShown = false;
	let lateNightShown = false;

	function api(path, options) {
		options = options || {};
		options.credentials = 'same-origin';
		options.headers = Object.assign({ 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce }, options.headers || {});
		return fetch(cfg.root + path, options).then(async function (response) {
			const body = await response.json().catch(function () { return {}; });
			if (!response.ok) throw new Error(body.message || cfg.i18n.error);
			return body;
		});
	}

	function textElement(tag, className, text) {
		const el = document.createElement(tag);
		if (className) el.className = className;
		el.textContent = text || '';
		return el;
	}

	function addNavLink(nav, href, label) {
		if (!nav || nav.querySelector('a[href="' + href + '"]')) return;
		const link = document.createElement('a');
		link.href = href;
		link.textContent = label;
		nav.appendChild(link);
	}

	function enhanceNav() {
		document.querySelectorAll('.rsv-local-nav').forEach(function (nav) {
			addNavLink(nav, cfg.storiesUrl, cfg.i18n.stories);
			addNavLink(nav, cfg.preferencesUrl, cfg.i18n.wellbeing);
			if (cfg.canSubmit) addNavLink(nav, cfg.insightsUrl, cfg.i18n.valueInsights);
		});
	}

	function safeLink(url, label, signal, reelId) {
		if (!url) return null;
		let parsed;
		try { parsed = new URL(url, window.location.href); } catch (e) { return null; }
		if (parsed.protocol !== 'http:' && parsed.protocol !== 'https:') return null;
		const link = document.createElement('a');
		link.href = parsed.href;
		link.textContent = label;
		link.rel = 'noopener noreferrer nofollow';
		if (parsed.origin !== window.location.origin) link.target = '_blank';
		if (signal) link.addEventListener('click', function () { recordSignal(reelId, signal); });
		return link;
	}

	function appendContext(article, context) {
		if (!article || article.querySelector('[data-rsv-top20-context]')) return;
		const box = document.createElement('details');
		box.className = 'rsv-top20-context';
		box.dataset.rsvTop20Context = '1';
		const summary = textElement('summary', '', cfg.i18n.sourceSafety);
		box.appendChild(summary);
		const list = document.createElement('dl');
		const add = function (term, valueNode) {
			if (!valueNode) return;
			list.appendChild(textElement('dt', '', term));
			const dd = document.createElement('dd');
			if (valueNode instanceof Node) dd.appendChild(valueNode); else dd.textContent = String(valueNode || '');
			list.appendChild(dd);
		};
		const reelId = article.dataset.rsvReel;
		const sourceLabel = context.source_title || '—';
		const sourceWrap = document.createElement('span');
		sourceWrap.appendChild(document.createTextNode(sourceLabel));
		const sourceLink = safeLink(context.source_url, cfg.i18n.source, 'source-open', reelId);
		if (sourceLink) { sourceWrap.appendChild(document.createTextNode(' — ')); sourceWrap.appendChild(sourceLink); }
		add(cfg.i18n.source, sourceWrap);
		add(cfg.i18n.safety, context.safety_summary || '—');
		const transcript = safeLink(context.transcript_url, cfg.i18n.transcript, '', reelId);
		if (transcript) add(cfg.i18n.transcript, transcript);
		box.appendChild(list);
		if (context.response_allowed && cfg.canSubmit) {
			const response = document.createElement('a');
			response.href = cfg.respondUrl + '?source=' + encodeURIComponent(reelId);
			response.textContent = cfg.i18n.response;
			response.className = 'rsv-top20-response-link';
			box.appendChild(response);
		}
		const target = article.querySelector('.rsv-overlay') || article;
		target.appendChild(box);
	}

	function enhanceReels(scope) {
		(scope || document).querySelectorAll('[data-rsv-reel]:not([data-rsv-top20-bound])').forEach(function (article) {
			article.dataset.rsvTop20Bound = '1';
			const id = article.dataset.rsvReel;
			api('/reels/' + encodeURIComponent(id) + '/context', { method: 'GET' })
				.then(function (context) { appendContext(article, context || {}); })
				.catch(function () { article.dataset.rsvContextState = 'unavailable'; });
		});
	}

	function recordSignal(reelId, signal) {
		if (!reelId || !signal) return;
		api('/reels/' + encodeURIComponent(reelId) + '/value-signal', { method: 'POST', body: JSON.stringify({ signal: signal }) }).catch(function () {});
	}

	function pauseMedia() {
		document.querySelectorAll('.rsv-video').forEach(function (video) { try { video.pause(); } catch (e) {} });
		const auto = document.querySelector('[data-rsv-autoplay][aria-pressed="true"]');
		if (auto) auto.click();
	}

	function showChoice(message, signal) {
		if (document.querySelector('[data-rsv-wellbeing-choice]')) return;
		pauseMedia();
		const region = document.createElement('section');
		region.className = 'rsv-wellbeing-choice';
		region.dataset.rsvWellbeingChoice = '1';
		region.setAttribute('role', 'status');
		region.setAttribute('aria-live', 'polite');
		region.appendChild(textElement('p', '', message));
		const button = document.createElement('button');
		button.type = 'button';
		button.textContent = cfg.i18n.continue;
		button.addEventListener('click', function () { region.remove(); button.blur(); });
		region.appendChild(button);
		(document.querySelector('.rsv-shell') || document.body).appendChild(region);
		button.focus();
		if (signal && activeId) recordSignal(activeId, signal);
	}

	function activeChanged() {
		const active = document.querySelector('[data-rsv-reel].is-active');
		if (!active) return;
		const id = active.dataset.rsvReel || '';
		if (!id || id === activeId) return;
		activeId = id;
		changes += 1;
		const stopEvery = Math.max(5, Number(prefs.natural_stop_every || 10));
		if (!naturalStopShown && changes >= stopEvery) {
			naturalStopShown = true;
			showChoice(cfg.i18n.naturalStop, 'natural-stop');
		}
	}

	function watchFeed() {
		const root = document.querySelector('[data-rsv-feed]');
		if (!root) return;
		activeChanged();
		const observer = new MutationObserver(function (mutations) {
			let changed = false;
			mutations.forEach(function (mutation) {
				if (mutation.type === 'attributes' && mutation.attributeName === 'class') changed = true;
				if (mutation.type === 'childList') enhanceReels(root);
			});
			if (changed) activeChanged();
		});
		observer.observe(root, { attributes: true, attributeFilter: ['class'], subtree: true, childList: true });
	}

	function scheduleWellbeing() {
		const minutes = Math.max(5, Math.min(60, Number(prefs.session_limit_minutes || 15)));
		window.setTimeout(function () {
			if (!sessionStopShown) { sessionStopShown = true; showChoice(cfg.i18n.sessionLimit, 'natural-stop'); }
		}, minutes * 60 * 1000);
		const hour = new Date().getHours();
		if (prefs.late_night_reminder && (hour >= 22 || hour < 5) && !lateNightShown) {
			lateNightShown = true;
			window.setTimeout(function () { showChoice(cfg.i18n.lateNight, ''); }, 1500);
		}
	}

	document.addEventListener('click', function (event) {
		const share = event.target.closest('[data-rsv-share]');
		if (share) {
			const article = share.closest('[data-rsv-reel]');
			if (article) recordSignal(article.dataset.rsvReel, 'share');
		}
	});

	document.addEventListener('DOMContentLoaded', function () {
		enhanceNav();
		enhanceReels(document);
		watchFeed();
		scheduleWellbeing();
	});
}());
