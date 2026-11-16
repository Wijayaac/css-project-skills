/* Glacier West – scroll text fill for two-tone headings (see glacier-west-templates.css, "Scroll text fill"). */
(function () {
	'use strict';
	if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

	function init() {
		var nodes = [];
		document.querySelectorAll('.elementor-widget-heading .elementor-heading-title span[style*="8A8A8A" i], .elementor-widget-heading .elementor-heading-title span[style*="138, 138, 138"]').forEach(function (s) { nodes.push(s); });
		document.querySelectorAll('.gw-text-fill .elementor-heading-title').forEach(function (h) {
			if (!h.querySelector('.gw-text-fill-on, span[style]')) nodes.push(h);
		});
		if (!nodes.length) return;

		nodes.forEach(function (el) {
			var cs = getComputedStyle(el);
			var heading = el.closest('.elementor-heading-title');
			var to = heading && heading !== el ? getComputedStyle(heading).color : '#000000';
			el.style.setProperty('--gw-fill-from', cs.color);
			el.style.setProperty('--gw-fill-to', to);
			el.style.setProperty('--gw-fill', '0');
			el.classList.add('gw-text-fill-on');
		});

		var visible = new Set();
		var ticking = false;
		function update() {
			ticking = false;
			var vh = window.innerHeight;
			var start = vh * 0.85, end = vh * 0.35;
			visible.forEach(function (el) {
				var top = el.getBoundingClientRect().top;
				var p = (start - top) / (start - end);
				p = Math.max(0, Math.min(1, p));
				el.style.setProperty('--gw-fill', (p * 106).toFixed(1));
			});
		}
		function request() { if (!ticking) { ticking = true; requestAnimationFrame(update); } }

		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) { if (e.isIntersecting) visible.add(e.target); else visible.delete(e.target); });
			request();
		}, { rootMargin: '10% 0px 10% 0px' });
		nodes.forEach(function (el) { io.observe(el); });
		window.addEventListener('scroll', request, { passive: true });
		window.addEventListener('resize', request);
	}

	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
	else init();
})();
