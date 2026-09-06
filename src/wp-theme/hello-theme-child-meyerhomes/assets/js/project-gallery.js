/**
 * Project Gallery — client-side taxonomy filter.
 */
(function () {
	'use strict';

	function setActive(buttons, active) {
		buttons.forEach(function (btn) {
			var on = btn === active;
			btn.classList.toggle('is-active', on);
			btn.setAttribute('aria-selected', on ? 'true' : 'false');
		});
	}

	function filterItems(root, slug) {
		var items = root.querySelectorAll('.mh-project-gallery__item');
		items.forEach(function (item) {
			if (slug === 'all') {
				item.classList.remove('is-hidden');
				return;
			}
			var types = (item.getAttribute('data-types') || '').split(/\s+/);
			var match = types.indexOf(slug) !== -1;
			item.classList.toggle('is-hidden', !match);
		});
	}

	function bindGallery(root) {
		if (root.getAttribute('data-mh-gallery-bound') === '1') {
			return;
		}
		root.setAttribute('data-mh-gallery-bound', '1');

		var buttons = Array.prototype.slice.call(
			root.querySelectorAll('.mh-project-gallery__filter-btn')
		);
		if (!buttons.length) {
			return;
		}

		buttons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var slug = btn.getAttribute('data-filter') || 'all';
				setActive(buttons, btn);
				filterItems(root, slug);
			});
		});
	}

	function init(scope) {
		var root = scope && scope.querySelectorAll ? scope : document;
		root.querySelectorAll('[data-mh-project-gallery]').forEach(bindGallery);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			init(document);
		});
	} else {
		init(document);
	}

	// Elementor editor / SPA re-renders.
	window.addEventListener('elementor/frontend/init', function () {
		if (!window.elementorFrontend || !elementorFrontend.hooks) {
			return;
		}
		elementorFrontend.hooks.addAction(
			'frontend/element_ready/mh_project_gallery.default',
			function ($el) {
				var el = $el && $el[0] ? $el[0] : $el;
				if (el) {
					init(el);
				}
			}
		);
	});
})();
