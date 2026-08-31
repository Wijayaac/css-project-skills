(function () {
	"use strict";

	var sections = document.querySelectorAll("[data-mh-reviews]");
	if (!sections.length) {
		return;
	}

	if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
		return;
	}

	var mobileQuery = window.matchMedia("(max-width: 640px)");
	var maxOffset = 120;
	var activeSections = new Set();
	var ticking = false;

	function getSectionProgress(section) {
		var rect = section.getBoundingClientRect();
		var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
		var total = rect.height + viewportHeight;

		if (total <= 0) {
			return 0;
		}

		var progress = (viewportHeight - rect.top) / total;
		return Math.min(1, Math.max(0, progress));
	}

	function updateSection(section) {
		var progress = getSectionProgress(section);
		var columns = section.querySelectorAll(".mh-reviews__col");

		columns.forEach(function (column) {
			var speed = parseFloat(column.getAttribute("data-speed") || "0");
			var offset = progress * speed * maxOffset;

			column.style.transform = "translate3d(0, " + offset + "px, 0)";
		});
	}

	function updateActiveSections() {
		activeSections.forEach(function (section) {
			updateSection(section);
		});
		ticking = false;
	}

	function requestTick() {
		if (!ticking) {
			ticking = true;
			window.requestAnimationFrame(updateActiveSections);
		}
	}

	function bindSection(section) {
		if (mobileQuery.matches) {
			section.querySelectorAll(".mh-reviews__col").forEach(function (column) {
				column.style.transform = "";
			});
			return;
		}

		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						activeSections.add(section);
						updateSection(section);
					} else {
						activeSections.delete(section);
					}
				});
			},
			{
				root: null,
				rootMargin: "20% 0px 20% 0px",
				threshold: 0,
			}
		);

		observer.observe(section);
		activeSections.add(section);
		updateSection(section);
	}

	sections.forEach(bindSection);

	window.addEventListener(
		"scroll",
		function () {
			if (!mobileQuery.matches) {
				requestTick();
			}
		},
		{ passive: true }
	);

	window.addEventListener("resize", function () {
		if (mobileQuery.matches) {
			activeSections.forEach(function (section) {
				section.querySelectorAll(".mh-reviews__col").forEach(function (column) {
					column.style.transform = "";
				});
			});
			return;
		}

		requestTick();
	});
})();
