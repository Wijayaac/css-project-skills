(function () {
	'use strict';

	function initFeaturedServices(section) {
		var images = section.querySelectorAll('[data-mh-preview-img]');
		var items = section.querySelectorAll('.mh-featured-services__item');
		var list = section.querySelector('.mh-featured-services__list');

		if (!images.length || !items.length) {
			return;
		}

		function showPreview(index) {
			var target = String(index);

			images.forEach(function (img) {
				img.classList.toggle('is-visible', img.getAttribute('data-mh-preview-img') === target);
			});
		}

		items.forEach(function (item) {
			item.addEventListener('mouseenter', function () {
				showPreview(item.getAttribute('data-preview-index'));
			});
		});

		if (list) {
			list.addEventListener('mouseleave', function () {
				showPreview(0);
			});
		}
	}

	document.querySelectorAll('[data-mh-featured-services]').forEach(initFeaturedServices);
})();
