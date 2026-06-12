jQuery(function () {
	initGranvilleFloorplans();
	initSwiperProgressPagination();

	if (window.elementorFrontend) {
		initElementorSwiperProgress();
	}
});

jQuery(window).on("elementor/frontend/init", initElementorSwiperProgress);

function initGranvilleFloorplans() {
	function syncFloorplan(root, index) {
		var tabs = root.querySelectorAll(".gv-floorplans__tab");
		var panels = root.querySelectorAll(".gv-floorplans__panel");
		var subtitle = root.querySelector(".gv-floorplans__subtitle");

		tabs.forEach(function (tab, i) {
			var active = i === index;
			tab.classList.toggle("is-active", active);
			tab.setAttribute("aria-selected", active ? "true" : "false");
		});

		panels.forEach(function (panel, i) {
			var active = i === index;
			panel.classList.toggle("is-active", active);
			panel.hidden = !active;

			if (active && subtitle) {
				subtitle.textContent = panel.getAttribute("data-subtitle") || "";
			}
		});
	}

	document.addEventListener("click", function (event) {
		var tab = event.target.closest(".gv-floorplans__tab");

		if (!tab) {
			return;
		}

		var root = tab.closest("[data-gv-floorplans]");

		if (!root) {
			return;
		}

		var index = parseInt(tab.getAttribute("data-floorplan-index"), 10);

		if (Number.isNaN(index)) {
			return;
		}

		syncFloorplan(root, index);
	});

	document.addEventListener("keydown", function (event) {
		var tab = event.target.closest(".gv-floorplans__tab");

		if (!tab) {
			return;
		}

		if (event.key !== "ArrowLeft" && event.key !== "ArrowRight") {
			return;
		}

		var root = tab.closest("[data-gv-floorplans]");

		if (!root) {
			return;
		}

		var tabs = Array.prototype.slice.call(root.querySelectorAll(".gv-floorplans__tab"));
		var current = tabs.indexOf(tab);

		if (current === -1) {
			return;
		}

		event.preventDefault();

		var next = event.key === "ArrowRight" ? current + 1 : current - 1;

		if (next < 0) {
			next = tabs.length - 1;
		}

		if (next >= tabs.length) {
			next = 0;
		}

		tabs[next].focus();
		syncFloorplan(root, next);
	});
}

function initElementorSwiperProgress() {
	if (window.elementorFrontend && elementorFrontend.hooks) {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/image-carousel.default",
			function ($scope) {
				if (!$scope[0].classList.contains("swiper-progress-pagination")) {
					return;
				}

				$scope[0].querySelectorAll(".swiper").forEach(bindSwiperCarousel);
			}
		);
	}

	initSwiperProgressPagination();
}

function initSwiperProgressPagination() {
	document
		.querySelectorAll(".swiper-progress-pagination .swiper")
		.forEach(bindSwiperCarousel);
}

function bindSwiperCarousel(swiperEl) {
	if (swiperEl.dataset.gvSwiperBound) {
		return;
	}

	function attach() {
		var swiper = swiperEl.swiper;

		if (!swiper) {
			return false;
		}

		swiperEl.dataset.gvSwiperBound = "true";

		var pagination = swiper.pagination && swiper.pagination.el;

		if (!pagination) {
			return true;
		}

		function updateFromSwiper() {
			var bullets = pagination.querySelectorAll(".swiper-pagination-bullet");
			var total = bullets.length;

			if (!total) {
				return;
			}

			var index =
				typeof swiper.realIndex === "number" ? swiper.realIndex : swiper.activeIndex;

			pagination.style.setProperty(
				"--gv-swiper-progress",
				String((index + 1) / total)
			);
		}

		swiper.on("init", updateFromSwiper);
		swiper.on("slideChange", updateFromSwiper);
		swiper.on("realIndexChange", updateFromSwiper);
		updateFromSwiper();
		bindSwiperProgressPagination(pagination);

		return true;
	}

	if (attach()) {
		return;
	}

	var attempts = 0;
	var timer = window.setInterval(function () {
		if (attach() || ++attempts >= 50) {
			window.clearInterval(timer);
		}
	}, 100);
}

function bindSwiperProgressPagination(pagination) {
	if (!pagination || pagination.dataset.gvProgressBound) {
		return;
	}

	pagination.dataset.gvProgressBound = "true";

	function updateProgress() {
		var bullets = pagination.querySelectorAll(".swiper-pagination-bullet");
		var active = pagination.querySelector(".swiper-pagination-bullet-active");

		if (!bullets.length || !active) {
			return;
		}

		var index = Array.prototype.indexOf.call(bullets, active);

		if (index < 0) {
			return;
		}

		pagination.style.setProperty(
			"--gv-swiper-progress",
			String((index + 1) / bullets.length)
		);
	}

	updateProgress();

	new MutationObserver(updateProgress).observe(pagination, {
		attributes: true,
		subtree: true,
		attributeFilter: ["class"],
	});
}
