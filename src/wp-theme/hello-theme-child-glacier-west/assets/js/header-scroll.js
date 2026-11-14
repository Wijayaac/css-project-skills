/**
 * Toggle .header-container background when the page is scrolled.
 * Works with native scroll and Lenis smooth scroll.
 */
(function () {
	"use strict";

	var headerBar = document.querySelector(".header-container");
	if (!headerBar) {
		return;
	}

	var ticking = false;

	function getScrollY() {
		if (window.lenis && typeof window.lenis.scroll === "number") {
			return window.lenis.scroll;
		}
		return window.scrollY || window.pageYOffset || 0;
	}

	function updateHeaderScrollState() {
		if (ticking) {
			return;
		}

		ticking = true;
		window.requestAnimationFrame(function () {
			ticking = false;
			headerBar.classList.toggle("is-scrolled", getScrollY() > 0);
		});
	}

	function bindLenis(lenis) {
		if (!lenis || typeof lenis.on !== "function") {
			return;
		}
		lenis.on("scroll", updateHeaderScrollState);
		updateHeaderScrollState();
	}

	updateHeaderScrollState();
	window.addEventListener("scroll", updateHeaderScrollState, { passive: true });
	window.addEventListener("resize", updateHeaderScrollState);

	if (window.lenis) {
		bindLenis(window.lenis);
	} else {
		document.addEventListener("mh-lenis-ready", function (event) {
			bindLenis(event.detail && event.detail.lenis);
		});
	}
})();
