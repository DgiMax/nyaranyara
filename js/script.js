(function () {
    var form = document.getElementById('waitlist-form');
    var success = document.getElementById('form-success');

    var formError = document.getElementById('form-error');
    var formButton = form ? form.querySelector('button[type="submit"]') : null;

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            if (formError) {
                formError.hidden = true;
                formError.textContent = '';
            }

            var endpoint = form.getAttribute('action') || 'waitlist.php';
            var body = new FormData(form);
            var defaultLabel = formButton ? formButton.textContent : '';

            if (formButton) {
                formButton.disabled = true;
                formButton.textContent = 'Joining…';
            }

            fetch(endpoint, {
                method: 'POST',
                body: body,
                headers: { Accept: 'application/json' }
            })
                .then(function (res) {
                    return res.json().then(function (data) {
                        return { ok: res.ok && data && data.ok, data: data, status: res.status };
                    }).catch(function () {
                        return { ok: false, data: null, status: res.status };
                    });
                })
                .then(function (result) {
                    if (!result.ok) {
                        var message = (result.data && result.data.error)
                            ? result.data.error
                            : 'Something went wrong. Please try again.';
                        if (formError) {
                            formError.textContent = message;
                            formError.hidden = false;
                        }
                        return;
                    }
                    form.style.display = 'none';
                    if (success) success.classList.add('is-visible');
                })
                .catch(function () {
                    if (formError) {
                        formError.textContent = 'Could not reach the server. Please try again.';
                        formError.hidden = false;
                    }
                })
                .finally(function () {
                    if (formButton) {
                        formButton.disabled = false;
                        formButton.textContent = defaultLabel;
                    }
                });
        });
    }

    // Hero "Why join?" rotating benefits
    var whyLines = document.querySelectorAll('.hero-why-line');
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (whyLines.length > 1 && !reduceMotion) {
        var whyIndex = 0;
        setInterval(function () {
            whyLines[whyIndex].classList.remove('is-active');
            whyIndex = (whyIndex + 1) % whyLines.length;
            whyLines[whyIndex].classList.add('is-active');
        }, 4200);
    }

    // Count-up when support section enters view
    var stats = document.querySelectorAll('[data-count]');
    var animated = false;

    function animateCounts() {
        if (animated) return;
        animated = true;
        stats.forEach(function (el) {
            var target = parseInt(el.getAttribute('data-count'), 10);
            var suffix = el.querySelector('span');
            var suffixHtml = suffix ? suffix.outerHTML : '';
            var duration = 1400;
            var start = performance.now();

            function tick(now) {
                var t = Math.min(1, (now - start) / duration);
                var eased = 1 - Math.pow(1 - t, 3);
                var value = Math.round(target * eased);
                el.innerHTML = value.toLocaleString() + suffixHtml;
                if (t < 1) requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        });
    }

    var support = document.getElementById('support');
    if (support && 'IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounts();
                    io.disconnect();
                }
            });
        }, { threshold: 0.35 });
        io.observe(support);
    } else {
        animateCounts();
    }

    // AI media notice
    var notice = document.getElementById('media-notice');
    var noticeClose = document.getElementById('media-notice-close');
    var noticeKey = 'nyara-media-notice-seen';

    if (notice && !sessionStorage.getItem(noticeKey)) {
        var noticeTimer;
        var noticeHideMs = 9000;

        function dismissNotice() {
            if (!notice || notice.classList.contains('is-leaving')) return;
            notice.classList.remove('is-visible');
            notice.classList.add('is-leaving');
            sessionStorage.setItem(noticeKey, '1');
            window.clearTimeout(noticeTimer);
            window.setTimeout(function () {
                notice.hidden = true;
                notice.classList.remove('is-leaving');
            }, 400);
        }

        notice.hidden = false;
        window.requestAnimationFrame(function () {
            notice.classList.add('is-visible');
        });
        noticeTimer = window.setTimeout(dismissNotice, noticeHideMs);
        if (noticeClose) noticeClose.addEventListener('click', dismissNotice);
    }

    // Minimal video players: play/pause + progress only
    document.querySelectorAll('.done-player').forEach(function (player) {
        var video = player.querySelector('video');
        var toggle = player.querySelector('.done-player-toggle');
        if (!video || !toggle) return;

        var bar = player.querySelector('.done-player-bar');
        var fill = player.querySelector('.done-player-progress-fill');
        if (!bar || !fill) return;

        function setPlaying(playing) {
            player.classList.toggle('is-playing', playing);
            toggle.setAttribute('aria-label', playing ? 'Pause' : 'Play');
        }

        function setProgress(ratio) {
            var pct = Math.max(0, Math.min(1, ratio)) * 100;
            fill.style.width = pct + '%';
            bar.setAttribute('aria-valuenow', String(Math.round(pct)));
        }

        function syncProgress() {
            if (!video.duration) return;
            setProgress(video.currentTime / video.duration);
        }

        function seekFromEvent(e) {
            if (!video.duration) return;
            var rect = bar.getBoundingClientRect();
            var ratio = (e.clientX - rect.left) / rect.width;
            setProgress(ratio);
            video.currentTime = ratio * video.duration;
        }

        toggle.addEventListener('click', function () {
            if (video.paused) {
                document.querySelectorAll('.done-player video').forEach(function (other) {
                    if (other !== video) other.pause();
                });
                video.play();
            } else {
                video.pause();
            }
        });

        video.addEventListener('play', function () { setPlaying(true); });
        video.addEventListener('pause', function () { setPlaying(false); });
        video.addEventListener('ended', function () {
            setPlaying(false);
            setProgress(0);
            video.currentTime = 0;
        });
        video.addEventListener('timeupdate', syncProgress);
        video.addEventListener('loadedmetadata', syncProgress);

        bar.addEventListener('click', seekFromEvent);
    });
})();
