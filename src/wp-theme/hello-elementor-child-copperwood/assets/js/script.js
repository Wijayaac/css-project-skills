jQuery(function () {
	stickyActive();
	initSliderCustomOffset();
	initCopperwoodFloorplans();
	initCopperwoodMaps();
});

function initCopperwoodMaps() {
	var maps = document.querySelectorAll("[data-cw-map]");

	if (!maps.length) {
		return;
	}

	function boot() {
		maps.forEach(function (root) {
			initCopperwoodMap(root);
		});
	}

	if (window.google && window.google.maps) {
		boot();
		return;
	}

	var attempts = 0;
	var timer = setInterval(function () {
		if (window.google && window.google.maps) {
			clearInterval(timer);
			boot();
			return;
		}

		attempts += 1;

		if (attempts >= 50) {
			clearInterval(timer);
		}
	}, 100);
}

function initCopperwoodMap(root) {
	var canvas = root.querySelector(".cw-map__canvas");

	if (!canvas || canvas.dataset.cwMapReady === "1") {
		return;
	}

	var defaults = window.copperwoodMapDefaults || {};
	var address = canvas.getAttribute("data-address") || "";
	var title = canvas.getAttribute("data-title") || address;
	var zoom = parseInt(canvas.getAttribute("data-zoom"), 10);
	var mapType = canvas.getAttribute("data-map-type") || "hybrid";
	var lat = parseFloat(canvas.getAttribute("data-lat"));
	var lng = parseFloat(canvas.getAttribute("data-lng"));
	var markerIcon = defaults.markerIcon || "";
	var markerWidth = parseInt(defaults.markerWidth, 10) || 77;
	var markerHeight = parseInt(defaults.markerHeight, 10) || 89;

	if (Number.isNaN(zoom)) {
		zoom = 13;
	}

	var map = new google.maps.Map(canvas, {
		zoom: zoom,
		maxZoom: 18,
		scrollwheel: false,
		draggable: !("ontouchend" in document),
		mapTypeId: mapType,
	});

	function placeMarker(position) {
		new google.maps.Marker({
			map: map,
			position: position,
			title: title,
			icon: markerIcon
				? {
						url: markerIcon,
						scaledSize: new google.maps.Size(markerWidth, markerHeight),
						anchor: new google.maps.Point(
							Math.round(markerWidth / 2),
							markerHeight
						),
				  }
				: undefined,
		});

		map.setCenter(position);
	}

	canvas.dataset.cwMapReady = "1";

	if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
		placeMarker({ lat: lat, lng: lng });
		return;
	}

	if (!address) {
		return;
	}

	var geocoder = new google.maps.Geocoder();

	geocoder.geocode({ address: address }, function (results, status) {
		if (status !== "OK" || !results || !results[0]) {
			return;
		}

		placeMarker(results[0].geometry.location);
	});
}

function initCopperwoodFloorplans() {
	function syncFloorplan(root, index) {
		var tabs = root.querySelectorAll(".cw-floorplans__tab");
		var panels = root.querySelectorAll(".cw-floorplans__panel");
		var subtitle = root.querySelector(".cw-floorplans__subtitle");

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
		var tab = event.target.closest(".cw-floorplans__tab");

		if (!tab) {
			return;
		}

		var root = tab.closest("[data-cw-floorplans]");

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
		var tab = event.target.closest(".cw-floorplans__tab");

		if (!tab) {
			return;
		}

		if (event.key !== "ArrowLeft" && event.key !== "ArrowRight") {
			return;
		}

		var root = tab.closest("[data-cw-floorplans]");

		if (!root) {
			return;
		}

		var tabs = Array.prototype.slice.call(root.querySelectorAll(".cw-floorplans__tab"));
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

function syncSliderCustomOffset($root) {
	var $boxedInner = $root.children(".e-con-boxed").find("> .e-con-inner").first();
	var $carousel = $root.children(".elementor-widget-image-carousel").first();

	if (!$boxedInner.length || !$carousel.length) {
		return;
	}

	var offsetLeft = Math.max(0, Math.round($boxedInner[0].getBoundingClientRect().left));
	$carousel[0].style.setProperty("--slider-offset-left", offsetLeft + "px");
}

function initSliderCustomOffset() {
	function setupCarousel($scope) {
		var $root = $scope.closest(".slider-custom-offset");

		if (!$root.length) {
			return;
		}

		var $widget = $root.children(".elementor-widget-image-carousel").first();
		var swiperEl = $widget.find(".swiper")[0];

		if (!swiperEl || !swiperEl.swiper) {
			return;
		}

		var swiper = swiperEl.swiper;
		var gap = 25;
		var settings = $widget.data("settings");

		if (!settings) {
			try {
				settings = JSON.parse($widget.attr("data-settings") || "{}");
			} catch (error) {
				settings = {};
			}
		}

		if (settings.image_spacing_custom && settings.image_spacing_custom.size) {
			gap = parseInt(settings.image_spacing_custom.size, 10) || gap;
		}

		syncSliderCustomOffset($root);

		swiper.params.slidesPerView = "auto";
		swiper.params.spaceBetween = gap;
		swiper.params.watchOverflow = true;
		swiper.params.slidesOffsetAfter = 0;
		swiper.update();

		if ($root.data("slider-offset-bound")) {
			return;
		}

		$root.data("slider-offset-bound", true);

		var resizeHandler = function () {
			syncSliderCustomOffset($root);
			if (swiperEl.swiper) {
				swiperEl.swiper.update();
			}
		};

		window.addEventListener("resize", resizeHandler, { passive: true });

		if ("ResizeObserver" in window) {
			var ro = new ResizeObserver(resizeHandler);
			ro.observe($root[0]);
		}
	}

	function registerElementorHook() {
		if (
			typeof elementorFrontend === "undefined" ||
			!elementorFrontend.hooks ||
			typeof elementorFrontend.hooks.addAction !== "function"
		) {
			return false;
		}

		if (registerElementorHook.isBound) {
			return true;
		}

		elementorFrontend.hooks.addAction(
			"frontend/element_ready/image-carousel.default",
			setupCarousel
		);

		registerElementorHook.isBound = true;

		return true;
	}

	function bootSliderCustomOffset() {
		if (!registerElementorHook()) {
			return;
		}

		jQuery(".slider-custom-offset .elementor-widget-image-carousel").each(function () {
			setupCarousel(jQuery(this));
		});
	}

	jQuery(window).on("elementor/frontend/init", bootSliderCustomOffset);

	if (
		typeof elementorFrontend !== "undefined" &&
		elementorFrontend.hooks &&
		typeof elementorFrontend.hooks.addAction === "function"
	) {
		bootSliderCustomOffset();
	}
}

function stickyActive() {
	var headerSelector = ".elementor-location-header";
	var header = document.querySelector(headerSelector);

	if (!header) {
		return;
	}

	var headerContent = header.querySelector(".header_container");
	var hero = document.querySelector(
		document.documentElement.getAttribute("data-header-hero") || ".hero"
	);

	function updateHeaderLogo() {
		var home = document.body.classList.contains("home");
		var useWhite = false;

		if (home && headerContent) {
			var headerH =
				headerContent.getBoundingClientRect().height ||
				headerContent.offsetHeight ||
				0;

			if (hero) {
				var r = hero.getBoundingClientRect();
				useWhite = r.bottom > 0 && r.top < headerH;
			} else {
				useWhite = window.pageYOffset === 0;
			}
		}

		header.classList.toggle("header--logo-white", useWhite);
		header.classList.toggle("header--logo-red", !useWhite);
	}

	var ticking = false;

	function onScrollOrResize() {
		if (ticking) {
			return;
		}

		ticking = true;
		requestAnimationFrame(function () {
			ticking = false;
			var currentScrollY = window.pageYOffset;

			if (headerContent) {
				if (currentScrollY === 0) {
					headerContent.style.boxShadow = "none";
					headerContent.style.marginTop = "0";
					headerContent.classList.remove("is-scrolled");
				} else {
					headerContent.style.boxShadow = "0 4px 6px rgba(0, 0, 0, 0.2)";
					headerContent.classList.add("is-scrolled");
				}
			}

			updateHeaderLogo();
		});
	}

	onScrollOrResize();

	window.addEventListener("scroll", onScrollOrResize, { passive: true });
	window.addEventListener("resize", onScrollOrResize);

	if ("ResizeObserver" in window && headerContent) {
		var ro = new ResizeObserver(onScrollOrResize);
		ro.observe(headerContent);
		if (hero) {
			ro.observe(hero);
		}
	}
}
