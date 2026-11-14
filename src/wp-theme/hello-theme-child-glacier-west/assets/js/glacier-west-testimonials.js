/* Glacier West – Testimonials carousel controls + counter + progress bar ([gw_testimonials] + [gw_testimonials_nav]). */
(function () {
	'use strict';

	function initCarousel(root) {
		var track = root.querySelector('.gw-tcarousel__track');
		var countEl = root.querySelector('.gw-tcarousel__count');
		var fillEl = root.querySelector('.gw-tcarousel__fill');
		var slides = Array.prototype.slice.call(root.querySelectorAll('.gw-tcard-slide'));
		if (!track || !slides.length) return;

		var section = root.closest('.gw-sec, section, .e-con.e-parent') || document;
		var nav = section.querySelector('.gw-tcarousel__nav') || document.querySelector('.gw-tcarousel__nav');
		var prevBtn = nav ? nav.querySelector('[data-gw-t-prev]') : null;
		var nextBtn = nav ? nav.querySelector('[data-gw-t-next]') : null;
		var total = slides.length;
		var rafId = 0;

		function pad2(n) { return (n < 10 ? '0' : '') + n; }

		function stepWidth() {
			if (slides.length > 1) {
				var d = slides[1].offsetLeft - slides[0].offsetLeft;
				if (d > 0) return d;
			}
			var gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || '24') || 24;
			return slides[0].getBoundingClientRect().width + gap;
		}

		function update() {
			rafId = 0;
			var maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
			var sl = track.scrollLeft;
			var step = stepWidth();
			var idx = step > 0 ? Math.round(sl / step) : 0;
			if (maxScroll > 0 && sl >= maxScroll - 4) {
				idx = total - 1;
			}
			idx = Math.max(0, Math.min(total - 1, idx));

			if (countEl) {
				countEl.textContent = pad2(idx + 1) + ' / ' + pad2(total);
			}
			if (fillEl) {
				var ratio = total <= 1 ? 1 : (maxScroll > 0 ? (idx + 1) / total : 1);
				fillEl.style.width = Math.max(100 / total, ratio * 100) + '%';
			}
			if (prevBtn) {
				var atStart = sl <= 4;
				prevBtn.disabled = atStart;
				prevBtn.classList.toggle('is-disabled', atStart);
			}
			if (nextBtn) {
				var atEnd = maxScroll <= 4 || sl >= maxScroll - 4;
				nextBtn.disabled = atEnd;
				nextBtn.classList.toggle('is-disabled', atEnd);
			}
		}

		function scheduleUpdate() {
			if (!rafId) rafId = window.requestAnimationFrame(update);
		}

		function move(dir) {
			var step = stepWidth();
			var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			track.scrollBy({ left: dir * step, behavior: reduced ? 'auto' : 'smooth' });
		}

		if (prevBtn) prevBtn.addEventListener('click', function () { move(-1); });
		if (nextBtn) nextBtn.addEventListener('click', function () { move(1); });
		track.addEventListener('scroll', scheduleUpdate, { passive: true });
		window.addEventListener('resize', scheduleUpdate);
		update();
	}

	function boot() {
		var list = document.querySelectorAll('[data-gw-tcarousel]');
		for (var i = 0; i < list.length; i++) initCarousel(list[i]);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
