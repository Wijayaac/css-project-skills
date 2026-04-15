document.addEventListener("DOMContentLoaded", function () {
	stickyActive();
});

function stickyActive() {
	var headerSelector = ".elementor-location-header";
	var header = document.querySelector(headerSelector);

	if (!header) return;

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
		if (ticking) return;
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
		if (hero) ro.observe(hero);
	}
}
