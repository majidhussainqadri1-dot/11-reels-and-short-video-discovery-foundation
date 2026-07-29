(function () {
    'use strict';

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    ready(function () {
        var config = window.srlData || {};
        var reels = Array.prototype.slice.call(document.querySelectorAll('.srl-reel'));
        var feed = document.querySelector('.srl-feed');
        var live = document.querySelector('.srl-live');
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var active = null;
        var timer = null;
        var remoteElapsed = new WeakMap();
        var replayPending = new WeakMap();

        function announce(message) {
            if (live) {
                live.textContent = message || '';
            }
        }

        function providerCommand(iframe, command) {
            if (!iframe || !iframe.contentWindow) {
                return;
            }
            var provider = iframe.getAttribute('data-provider');
            if (provider === 'youtube') {
                iframe.contentWindow.postMessage(JSON.stringify({event: 'command', func: command === 'play' ? 'playVideo' : 'pauseVideo', args: []}), 'https://www.youtube-nocookie.com');
            } else if (provider === 'vimeo') {
                iframe.contentWindow.postMessage({method: command === 'play' ? 'play' : 'pause'}, 'https://player.vimeo.com');
            }
        }

        function pauseReel(reel) {
            if (!reel) {
                return;
            }
            var video = reel.querySelector('video');
            var iframe = reel.querySelector('.srl-remote-player');
            if (video) {
                video.pause();
            }
            if (iframe) {
                providerCommand(iframe, 'pause');
            }
        }

        function playReel(reel) {
            if (!reel || reduceMotion) {
                return;
            }
            var video = reel.querySelector('video');
            var iframe = reel.querySelector('.srl-remote-player');
            if (video) {
                video.muted = true;
                video.setAttribute('playsinline', '');
                video.play().catch(function () {});
            }
            if (iframe) {
                providerCommand(iframe, 'play');
            }
        }

        function currentProgress(reel) {
            var video = reel.querySelector('video');
            if (video && isFinite(video.currentTime)) {
                return Math.max(0, Math.floor(video.currentTime));
            }
            return Math.max(1, Math.floor(remoteElapsed.get(reel) || 1));
        }

        function sendProgress(reel, useBeacon) {
            if (!reel || !config.loggedIn || !config.url || !config.progressNonce) {
                return;
            }
            var params = new URLSearchParams();
            params.append('action', 'srl_progress');
            params.append('nonce', config.progressNonce);
            params.append('reel', reel.getAttribute('data-reel') || '0');
            params.append('progress', String(currentProgress(reel)));
            if (replayPending.get(reel)) {
                params.append('replay', '1');
                replayPending.set(reel, false);
            }
            if (useBeacon && navigator.sendBeacon) {
                navigator.sendBeacon(config.url, params);
                return;
            }
            fetch(config.url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: params.toString()
            }).catch(function () {});
        }

        function startTracking(reel) {
            stopTracking(false);
            active = reel;
            remoteElapsed.set(reel, remoteElapsed.get(reel) || 1);
            sendProgress(reel, false);
            timer = window.setInterval(function () {
                if (!active) {
                    return;
                }
                if (!active.querySelector('video')) {
                    var duration = parseInt(active.getAttribute('data-duration') || '0', 10);
                    remoteElapsed.set(active, Math.min(duration || 600, (remoteElapsed.get(active) || 1) + 5));
                }
                sendProgress(active, false);
            }, 5000);
        }

        function stopTracking(flush) {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
            if (flush && active) {
                sendProgress(active, false);
            }
            active = null;
        }

        reels.forEach(function (reel) {
            var video = reel.querySelector('video');
            if (video) {
                video.addEventListener('ended', function () {
                    sendProgress(reel, false);
                    replayPending.set(reel, true);
                });
                video.addEventListener('seeking', function () {
                    sendProgress(reel, false);
                });
            }
        });

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting && entry.intersectionRatio >= 0.72) {
                        reels.forEach(function (other) {
                            if (other !== entry.target) {
                                pauseReel(other);
                            }
                        });
                        playReel(entry.target);
                        startTracking(entry.target);
                    } else if (entry.target === active) {
                        pauseReel(entry.target);
                        stopTracking(true);
                    }
                });
            }, {threshold: [0, 0.72, 1]});
            reels.forEach(function (reel) { observer.observe(reel); });
        }

        function move(direction) {
            if (!reels.length) {
                return;
            }
            var current = active || document.activeElement.closest && document.activeElement.closest('.srl-reel');
            var index = Math.max(0, reels.indexOf(current));
            var next = reels[Math.max(0, Math.min(reels.length - 1, index + direction))];
            if (next) {
                next.scrollIntoView({behavior: reduceMotion ? 'auto' : 'smooth', block: 'start'});
                next.focus({preventScroll: true});
            }
        }

        if (feed) {
            feed.addEventListener('keydown', function (event) {
                if (event.target.closest('button, a, input, textarea, select, summary')) {
                    return;
                }
                if (event.key === 'ArrowDown' || event.key === 'PageDown') {
                    event.preventDefault();
                    move(1);
                } else if (event.key === 'ArrowUp' || event.key === 'PageUp') {
                    event.preventDefault();
                    move(-1);
                }
            });
        }

        document.addEventListener('click', function (event) {
            var button = event.target.closest('[data-svw-action]');
            if (!button) {
                return;
            }
            event.preventDefault();
            if (!config.loggedIn) {
                return;
            }
            var box = button.closest('[data-video-id]');
            if (!box) {
                return;
            }
            button.disabled = true;
            var data = new URLSearchParams({
                action: 'svw_action',
                nonce: config.interactionNonce || '',
                videoId: box.getAttribute('data-video-id') || '0',
                kind: button.getAttribute('data-svw-action') || ''
            });
            fetch(config.url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: data.toString()
            }).then(function (response) {
                return response.json().then(function (json) {
                    if (!response.ok || !json.success) {
                        throw new Error(json.data && json.data.message ? json.data.message : (config.messages && config.messages.error));
                    }
                    announce(config.messages && config.messages.saved ? config.messages.saved : 'Action saved.');
                    window.location.reload();
                });
            }).catch(function (error) {
                button.disabled = false;
                announce(error.message || 'The action could not be completed.');
            });
        });

        document.addEventListener('submit', function (event) {
            var form = event.target.closest('form[data-svw-report]');
            if (!form) {
                return;
            }
            event.preventDefault();
            var box = form.closest('[data-video-id]');
            if (!box) {
                return;
            }
            var data = new FormData(form);
            data.append('action', 'svw_action');
            data.append('nonce', config.interactionNonce || '');
            data.append('videoId', box.getAttribute('data-video-id') || '0');
            data.append('kind', 'report');
            fetch(config.url, {method: 'POST', credentials: 'same-origin', body: data})
                .then(function (response) {
                    return response.json().then(function (json) {
                        if (!response.ok || !json.success) {
                            throw new Error(json.data && json.data.message ? json.data.message : 'Report failed.');
                        }
                        form.innerHTML = '<p role="status">Report received.</p>';
                        announce(config.messages && config.messages.report ? config.messages.report : 'Report received.');
                    });
                })
                .catch(function (error) { announce(error.message || 'Report failed.'); });
        });

        window.addEventListener('pagehide', function () {
            if (active) {
                sendProgress(active, true);
            }
        });
    });
}());
