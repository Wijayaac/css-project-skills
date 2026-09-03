/**
 * Expandable content — auto-rotating accordion with progress bar.
 *
 * Advance is driven by the progress bar's animationend event.
 * Pause on keyboard focus / offscreen via animation-play-state.
 */
(function () {
	'use strict';

	var SELECTOR = '[data-sb-expandable]';
	var REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)');

	/**
	 * @param {HTMLElement} root
	 */
	function initExpandable(root) {
		var items = Array.prototype.slice.call(
			root.querySelectorAll('.sb-expandable__item')
		);

		if (items.length === 0) {
			return;
		}

		var durationMs = parseInt(root.getAttribute('data-duration') || '6000', 10);
		if (!Number.isFinite(durationMs) || durationMs < 1000) {
			durationMs = 6000;
		}

		root.style.setProperty('--sb-duration', durationMs + 'ms');

		var current = 0;
		var reduced = REDUCED_MOTION.matches;
		var focusPaused = false;
		var offscreenPaused = false;

		/**
		 * @returns {void}
		 */
		function syncPaused() {
			root.classList.toggle(
				'is-paused',
				focusPaused || offscreenPaused
			);
		}

		/**
		 * Restart the progress animation on the active item.
		 *
		 * @param {HTMLElement} item
		 * @returns {void}
		 */
		function restartProgress(item) {
			var bar = item.querySelector('.sb-expandable__progress');
			if (!bar) {
				return;
			}

			bar.classList.remove('is-running');
			// Force reflow so removing/re-adding the class restarts the animation.
			void bar.offsetWidth;
			if (!reduced) {
				bar.classList.add('is-running');
			}
		}

		/**
		 * Open an item by index and restart its timer.
		 *
		 * @param {number} index
		 * @returns {void}
		 */
		function open(index) {
			if (index < 0 || index >= items.length) {
				return;
			}

			current = index;

			items.forEach(function (item, i) {
				var isActive = i === index;
				var trigger = item.querySelector('.sb-expandable__trigger');
				var bar = item.querySelector('.sb-expandable__progress');

				item.classList.toggle('is-active', isActive);

				if (trigger) {
					trigger.setAttribute('aria-expanded', isActive ? 'true' : 'false');
				}

				if (bar) {
					bar.classList.remove('is-running');
				}
			});

			if (!reduced) {
				restartProgress(items[index]);
			}
		}

		/**
		 * Advance to the next item, wrapping to 0.
		 *
		 * @returns {void}
		 */
		function advance() {
			open((current + 1) % items.length);
		}

		items.forEach(function (item, index) {
			var trigger = item.querySelector('.sb-expandable__trigger');
			var bar = item.querySelector('.sb-expandable__progress');

			if (trigger) {
				trigger.addEventListener('click', function () {
					open(index);
				});
			}

			if (bar) {
				bar.addEventListener('animationend', function (event) {
					// Pseudo-element animations report on the originating element.
					if (event.animationName !== 'sb-progress-fill') {
						return;
					}
					if (index !== current || reduced) {
						return;
					}
					advance();
				});
			}
		});

		root.addEventListener('focusin', function () {
			focusPaused = true;
			syncPaused();
		});

		root.addEventListener('focusout', function (event) {
			var next = event.relatedTarget;
			if (next instanceof Node && root.contains(next)) {
				return;
			}
			focusPaused = false;
			syncPaused();
		});

		if ('IntersectionObserver' in window) {
			var observer = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						offscreenPaused = !entry.isIntersecting;
						syncPaused();
					});
				},
				{ threshold: 0.15 }
			);
			observer.observe(root);
		}

		REDUCED_MOTION.addEventListener('change', function (event) {
			reduced = event.matches;
			if (reduced) {
				items.forEach(function (item) {
					var bar = item.querySelector('.sb-expandable__progress');
					if (bar) {
						bar.classList.remove('is-running');
					}
				});
			} else {
				restartProgress(items[current]);
			}
		});

		// Start with the first item (or whichever already has is-active).
		var initial = 0;
		for (var i = 0; i < items.length; i++) {
			if (items[i].classList.contains('is-active')) {
				initial = i;
				break;
			}
		}
		open(initial);
	}

	function boot() {
		var roots = document.querySelectorAll(SELECTOR);
		Array.prototype.forEach.call(roots, initExpandable);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
