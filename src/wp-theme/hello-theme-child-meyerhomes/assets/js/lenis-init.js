/**
 * Lenis smooth scroll init for Meyer Homes.
 *
 * Exposes window.mhLenis / window.lenis for other scripts.
 */
(function () {
	"use strict";

	if (typeof Lenis === "undefined") {
		return;
	}

	// Skip in Elementor editor / preview canvas.
	if (
		document.body.classList.contains("elementor-editor-active") ||
		document.body.classList.contains("elementor-editor-preview") ||
		(window.elementorFrontend &&
			elementorFrontend.isEditMode &&
			elementorFrontend.isEditMode())
	) {
		return;
	}

	if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
		return;
	}

	var lenis = new Lenis({
		lerp: 0.1,
		smoothWheel: true,
		syncTouch: false,
		autoRaf: true,
		anchors: true,
		respectReducedMotion: true,
		prevent: function (node) {
			if (!(node instanceof Element)) {
				return false;
			}
			return Boolean(
				node.closest(
					[
						"[data-lenis-prevent]",
						".elementor-popup-modal",
						".dialog-widget",
						".dialog-message",
						".dialog-lightbox-widget",
						".elementor-lightbox",
						".pswp",
						".mfp-wrap",
						".select2-container",
						".flatpickr-calendar",
					].join(", ")
				)
			);
		},
	});

	window.mhLenis = lenis;
	window.lenis = lenis;

	document.documentElement.classList.add("mh-lenis");

	// Recalc after late layout changes (Elementor lazy widgets, fonts, images).
	window.addEventListener("load", function () {
		if (lenis && typeof lenis.resize === "function") {
			lenis.resize();
		}
	});

	document.dispatchEvent(new CustomEvent("mh-lenis-ready", { detail: { lenis: lenis } }));
})();
