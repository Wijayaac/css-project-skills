/**
 * ACF Floorplan Gallery — synced main / thumbs / lightbox slider.
 */
(function () {
	'use strict';

	var OPEN_CLASS = 'cfs-floorplan-gallery-open';
	var SWIPE_MIN = 40;

	/**
	 * @param {HTMLElement} root
	 */
	function initGallery(root) {
		if (root.dataset.cfsFpgReady === '1') {
			return;
		}
		root.dataset.cfsFpgReady = '1';

		var count = parseInt(root.getAttribute('data-cfs-count') || '1', 10);
		var index = 0;

		var mainStage = root.querySelector('[data-cfs-main-stage]');
		var mainTrack = root.querySelector('[data-cfs-main-track]');
		var thumbsEl = root.querySelector('[data-cfs-thumbs]');
		var thumbs = root.querySelectorAll('[data-cfs-thumb]');
		var maximizeBtn = root.querySelector('[data-cfs-maximize]');
		var minimizeBtn = root.querySelector('[data-cfs-minimize]');
		var lightbox = root.querySelector('[data-cfs-lightbox]');
		var lightboxTrack = root.querySelector('[data-cfs-lightbox-track]');
		var lightboxHome = lightbox ? lightbox.parentNode : null;

		function clampIndex(i) {
			if (count <= 0) {
				return 0;
			}
			return ((i % count) + count) % count;
		}

		function setTrack(track, i) {
			if (!track) {
				return;
			}
			track.style.transform = 'translate3d(' + (-i * 100) + '%, 0, 0)';
			track.querySelectorAll('[data-index]').forEach(function (slide) {
				var slideIndex = parseInt(slide.getAttribute('data-index') || '0', 10);
				slide.classList.toggle('is-active', slideIndex === i);
			});
		}

		function scrollThumbIntoView(i) {
			if (!thumbsEl || !thumbs[i]) {
				return;
			}
			var thumb = thumbs[i];
			var left = thumb.offsetLeft;
			var right = left + thumb.offsetWidth;
			var viewLeft = thumbsEl.scrollLeft;
			var viewRight = viewLeft + thumbsEl.clientWidth;

			if (left < viewLeft) {
				thumbsEl.scrollTo({ left: left, behavior: 'smooth' });
			} else if (right > viewRight) {
				thumbsEl.scrollTo({ left: right - thumbsEl.clientWidth, behavior: 'smooth' });
			}
		}

		function goTo(i, opts) {
			opts = opts || {};
			index = clampIndex(i);

			setTrack(mainTrack, index);
			setTrack(lightboxTrack, index);

			thumbs.forEach(function (thumb, t) {
				var active = t === index;
				thumb.classList.toggle('is-active', active);
				thumb.setAttribute('aria-selected', active ? 'true' : 'false');
			});

			if (!opts.skipThumbScroll) {
				scrollThumbIntoView(index);
			}
		}

		function next() {
			goTo(index + 1);
		}

		function prev() {
			goTo(index - 1);
		}

		function onDocKeydown(event) {
			if (event.key === 'Escape') {
				closeLightbox();
				return;
			}
			if (event.key === 'ArrowRight') {
				event.preventDefault();
				next();
			} else if (event.key === 'ArrowLeft') {
				event.preventDefault();
				prev();
			}
		}

		function openLightbox() {
			if (!lightbox || !lightbox.hidden) {
				return;
			}

			var accent = getComputedStyle(root).getPropertyValue('--cfs-fpg-accent').trim() || '#c36645';
			lightbox.style.setProperty('--cfs-fpg-accent', accent);

			document.body.appendChild(lightbox);
			lightbox.hidden = false;
			lightbox.setAttribute('aria-hidden', 'false');
			document.body.classList.add(OPEN_CLASS);
			document.addEventListener('keydown', onDocKeydown);

			goTo(index, { skipThumbScroll: true });

			if (minimizeBtn) {
				minimizeBtn.focus();
			}
		}

		function closeLightbox() {
			if (!lightbox || lightbox.hidden) {
				return;
			}

			lightbox.hidden = true;
			lightbox.setAttribute('aria-hidden', 'true');
			document.body.classList.remove(OPEN_CLASS);
			document.removeEventListener('keydown', onDocKeydown);

			if (lightboxHome && lightbox.parentNode !== lightboxHome) {
				lightboxHome.appendChild(lightbox);
			}

			if (mainStage) {
				mainStage.focus();
			} else if (maximizeBtn) {
				maximizeBtn.focus();
			}
		}

		/**
		 * Touch / pointer swipe on a viewport.
		 * @param {HTMLElement|null} el
		 * @param {function(): void} onPrev
		 * @param {function(): void} onNext
		 */
		function bindSwipe(el, onPrev, onNext) {
			if (!el || count < 2) {
				return;
			}

			var startX = 0;
			var startY = 0;
			var tracking = false;

			el.addEventListener(
				'touchstart',
				function (event) {
					if (!event.touches || !event.touches[0]) {
						return;
					}
					tracking = true;
					startX = event.touches[0].clientX;
					startY = event.touches[0].clientY;
				},
				{ passive: true }
			);

			el.addEventListener(
				'touchend',
				function (event) {
					if (!tracking || !event.changedTouches || !event.changedTouches[0]) {
						return;
					}
					tracking = false;
					var dx = event.changedTouches[0].clientX - startX;
					var dy = event.changedTouches[0].clientY - startY;
					if (Math.abs(dx) < SWIPE_MIN || Math.abs(dx) < Math.abs(dy)) {
						return;
					}
					if (dx < 0) {
						onNext();
					} else {
						onPrev();
					}
				},
				{ passive: true }
			);
		}

		// Main nav arrows
		root.querySelectorAll('[data-cfs-nav]').forEach(function (btn) {
			btn.addEventListener('click', function (event) {
				event.stopPropagation();
				if (btn.getAttribute('data-cfs-nav') === 'next') {
					next();
				} else {
					prev();
				}
			});
		});

		// Thumb strip scroll arrows
		root.querySelectorAll('[data-cfs-thumbs-nav]').forEach(function (btn) {
			btn.addEventListener('click', function (event) {
				event.stopPropagation();
				if (!thumbsEl) {
					return;
				}
				var step = Math.max(thumbsEl.clientWidth * 0.8, 120);
				var dir = btn.getAttribute('data-cfs-thumbs-nav') === 'next' ? 1 : -1;
				thumbsEl.scrollBy({ left: dir * step, behavior: 'smooth' });
			});
		});

		// Lightbox nav
		if (lightbox) {
			lightbox.querySelectorAll('[data-cfs-lightbox-nav]').forEach(function (btn) {
				btn.addEventListener('click', function (event) {
					event.stopPropagation();
					if (btn.getAttribute('data-cfs-lightbox-nav') === 'next') {
						next();
					} else {
						prev();
					}
				});
			});
		}

		thumbs.forEach(function (thumb) {
			thumb.addEventListener('click', function () {
				var i = parseInt(thumb.getAttribute('data-index') || '0', 10);
				goTo(i);
			});
		});

		if (mainStage) {
			mainStage.addEventListener('click', function (event) {
				if (event.target.closest('[data-cfs-maximize]') || event.target.closest('[data-cfs-nav]')) {
					return;
				}
				openLightbox();
			});
			mainStage.addEventListener('keydown', function (event) {
				if (event.key === 'Enter' || event.key === ' ') {
					event.preventDefault();
					openLightbox();
				} else if (event.key === 'ArrowRight') {
					event.preventDefault();
					next();
				} else if (event.key === 'ArrowLeft') {
					event.preventDefault();
					prev();
				}
			});
		}

		if (maximizeBtn) {
			maximizeBtn.addEventListener('click', function (event) {
				event.stopPropagation();
				openLightbox();
			});
		}

		if (minimizeBtn) {
			minimizeBtn.addEventListener('click', function (event) {
				event.stopPropagation();
				closeLightbox();
			});
		}

		if (lightbox) {
			lightbox.addEventListener('click', function (event) {
				if (event.target.closest('[data-cfs-lightbox-image]')) {
					return;
				}
				if (event.target.closest('[data-cfs-lightbox-slide]')) {
					return;
				}
				if (event.target.closest('[data-cfs-minimize]')) {
					return;
				}
				if (event.target.closest('[data-cfs-lightbox-nav]')) {
					return;
				}
				closeLightbox();
			});
		}

		bindSwipe(root.querySelector('[data-cfs-main-viewport]'), prev, next);
		bindSwipe(root.querySelector('[data-cfs-lightbox-viewport]'), prev, next);

		goTo(0, { skipThumbScroll: true });
	}

	function boot() {
		document.querySelectorAll('[data-cfs-floorplan-gallery]').forEach(initGallery);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}

	if (typeof window.jQuery !== 'undefined') {
		window.jQuery(window).on('elementor/frontend/init', function () {
			if (!window.elementorFrontend || !window.elementorFrontend.hooks) {
				return;
			}

			window.elementorFrontend.hooks.addAction(
				'frontend/element_ready/cfs_acf_floorplan_gallery.default',
				function ($scope) {
					var el = $scope[0];
					if (!el) {
						return;
					}
					var root = el.querySelector('[data-cfs-floorplan-gallery]');
					if (root) {
						delete root.dataset.cfsFpgReady;
						initGallery(root);
					}
				}
			);
		});
	}
})();
