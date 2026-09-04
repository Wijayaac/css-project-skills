/**
 * ACF FAQ Accordion — height-calculated open/close with CSS transition.
 */
(function () {
	'use strict';

	function getDurationMs(root) {
		var raw = getComputedStyle(root).getPropertyValue('--faq-animation-duration').trim();
		if (!raw) {
			return 400;
		}
		if (raw.endsWith('ms')) {
			return parseFloat(raw) || 0;
		}
		if (raw.endsWith('s')) {
			return (parseFloat(raw) || 0) * 1000;
		}
		return parseFloat(raw) || 400;
	}

	function prefersReducedMotion() {
		return (
			typeof window.matchMedia === 'function' &&
			window.matchMedia('(prefers-reduced-motion: reduce)').matches
		);
	}

	function getParts(item) {
		return {
			trigger: item.querySelector('.mh-faq-accordion__trigger'),
			panel: item.querySelector('.mh-faq-accordion__panel'),
			inner: item.querySelector('.mh-faq-accordion__panel-inner'),
		};
	}

	function setExpanded(item, trigger, panel, expanded) {
		item.classList.toggle('is-open', expanded);
		trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
		panel.setAttribute('aria-hidden', expanded ? 'false' : 'true');
	}

	function openItem(root, item) {
		var parts = getParts(item);
		if (!parts.trigger || !parts.panel || !parts.inner) {
			return;
		}

		var panel = parts.panel;
		var duration = prefersReducedMotion() ? 0 : getDurationMs(root);

		setExpanded(item, parts.trigger, panel, true);

		if (duration === 0) {
			panel.style.height = 'auto';
			return;
		}

		// From 0 → measured content height, then settle to auto.
		panel.style.height = '0px';
		// Force reflow before transitioning.
		void panel.offsetHeight;
		panel.style.height = parts.inner.scrollHeight + 'px';

		window.clearTimeout(panel._mhFaqTimer);
		panel._mhFaqTimer = window.setTimeout(function () {
			if (item.classList.contains('is-open')) {
				panel.style.height = 'auto';
			}
		}, duration);
	}

	function closeItem(root, item) {
		var parts = getParts(item);
		if (!parts.trigger || !parts.panel || !parts.inner) {
			return;
		}

		var panel = parts.panel;
		var duration = prefersReducedMotion() ? 0 : getDurationMs(root);

		window.clearTimeout(panel._mhFaqTimer);

		if (duration === 0) {
			panel.style.height = '0px';
			setExpanded(item, parts.trigger, panel, false);
			return;
		}

		// auto/current → explicit px → 0
		panel.style.height = parts.inner.scrollHeight + 'px';
		void panel.offsetHeight;
		panel.style.height = '0px';
		setExpanded(item, parts.trigger, panel, false);
	}

	function closeOthers(root, current) {
		root.querySelectorAll('[data-mh-faq-item].is-open').forEach(function (item) {
			if (item !== current) {
				closeItem(root, item);
			}
		});
	}

	function onTriggerClick(root, item) {
		if (root.dataset.mhFaqAnimating === '1') {
			return;
		}

		var isOpen = item.classList.contains('is-open');
		var duration = prefersReducedMotion() ? 0 : getDurationMs(root);

		root.dataset.mhFaqAnimating = '1';
		window.setTimeout(function () {
			root.dataset.mhFaqAnimating = '0';
		}, duration);

		if (isOpen) {
			closeItem(root, item);
			return;
		}

		closeOthers(root, item);
		openItem(root, item);
	}

	function bindAccordion(root) {
		if (!root || root.dataset.mhFaqBound === '1') {
			return;
		}
		root.dataset.mhFaqBound = '1';

		root.querySelectorAll('[data-mh-faq-item]').forEach(function (item) {
			var parts = getParts(item);
			if (!parts.trigger || !parts.panel) {
				return;
			}

			// Closed default: height 0, content stays in DOM for measurement.
			parts.panel.style.height = '0px';
			setExpanded(item, parts.trigger, parts.panel, false);

			parts.trigger.addEventListener('click', function () {
				onTriggerClick(root, item);
			});
		});
	}

	function init(context) {
		var scope = context || document;
		if (!scope.querySelectorAll) {
			return;
		}
		scope.querySelectorAll('[data-mh-faq-accordion]').forEach(bindAccordion);
	}

	function onReady(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	onReady(function () {
		init();
	});

	function bindElementorHook() {
		if (
			typeof window.elementorFrontend === 'undefined' ||
			!window.elementorFrontend.hooks ||
			typeof window.elementorFrontend.hooks.addAction !== 'function'
		) {
			return;
		}

		window.elementorFrontend.hooks.addAction(
			'frontend/element_ready/mh_acf_faq_accordion.default',
			function ($scope) {
				var el = $scope && $scope[0] ? $scope[0] : $scope;
				init(el);
			}
		);
	}

	onReady(bindElementorHook);

	if (typeof window.jQuery !== 'undefined') {
		window.jQuery(window).on('elementor/frontend/init', bindElementorHook);
	}
})();
