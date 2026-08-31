(function () {
	'use strict';

	function initFeaturedServices(section) {
		var preview = section.querySelector('[data-mh-preview-img]');
		var items = section.querySelectorAll('.mh-featured-services__item');
		var list = section.querySelector('.mh-featured-services__list');

		if (!preview || !items.length) {
			return;
		}

		var firstItem = items[0];
		var defaultImage = firstItem.dataset.image || '';
		var defaultAlt = firstItem.dataset.alt || '';

		function setActive(item) {
			items.forEach(function (el) {
				el.classList.remove('is-active');
			});
			item.classList.add('is-active');

			if (item.dataset.image) {
				preview.src = item.dataset.image;
			}
			preview.alt = item.dataset.alt || '';
		}

		function resetToDefault() {
			setActive(firstItem);
			if (defaultImage) {
				preview.src = defaultImage;
			}
			preview.alt = defaultAlt;
		}

		items.forEach(function (item) {
			item.addEventListener('mouseenter', function () {
				setActive(item);
			});
		});

		if (list) {
			list.addEventListener('mouseleave', resetToDefault);
		}
	}

	document.querySelectorAll('[data-mh-featured-services]').forEach(initFeaturedServices);
})();
