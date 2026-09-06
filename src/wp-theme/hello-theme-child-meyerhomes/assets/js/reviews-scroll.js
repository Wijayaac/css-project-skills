/**
 * Testimonials — horizontal marquee (≈3.8 columns visible).
 * Continuous scroll; page-scroll velocity gives a temporary speed boost.
 */
(function () {
  "use strict";

  // Tunable knobs — raise VELOCITY_SCALE / MAX_BOOST if scroll boost feels weak.
  var BASE_SPEED = 0.5; // idle marquee px/frame (~30px/s)
  var MAX_BOOST = 8; // max extra px/frame while scrolling (BASE + this)
  var BOOST_DECAY = 0.965; // closer to 1 = boost lasts longer
  var VELOCITY_SCALE = 1.5; // how hard page-scroll pushes the marquee

  var instances = [];
  var scrollBound = false;
  var resizeBound = false;
  var rafId = 0;
  var running = false;
  var scrollBoost = 0;
  var lastScrollY = 0;
  var lastScrollTs = 0;

  function prefersReducedMotion() {
    return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  }

  function clamp(value, min, max) {
    return Math.min(max, Math.max(min, value));
  }

  function getScrollY() {
    if (window.lenis && typeof window.lenis.scroll === "number") {
      return window.lenis.scroll;
    }
    return window.scrollY || window.pageYOffset || 0;
  }

  function getScrollVelocity() {
    if (window.lenis && typeof window.lenis.velocity === "number") {
      return window.lenis.velocity;
    }
    return 0;
  }

  function duplicateTrack(track) {
    if (track.getAttribute("data-mh-marquee-cloned") === "1") {
      return;
    }

    var children = Array.prototype.slice.call(track.children);
    if (!children.length) {
      return;
    }

    children.forEach(function (child) {
      var clone = child.cloneNode(true);
      clone.setAttribute("aria-hidden", "true");
      track.appendChild(clone);
    });

    track.setAttribute("data-mh-marquee-cloned", "1");
  }

  function measureHalf(track) {
    // After cloning, half the scroll width is one full set.
    return track.scrollWidth / 2;
  }

  function createInstance(section) {
    var viewport = section.querySelector(".mh-reviews__viewport");
    var track = section.querySelector(".mh-reviews__track");

    if (!viewport || !track) {
      return null;
    }

    if (section.getAttribute("data-marquee") === "off" || prefersReducedMotion()) {
      return null;
    }

    duplicateTrack(track);

    return {
      section: section,
      viewport: viewport,
      track: track,
      offset: 0,
      half: measureHalf(track),
    };
  }

  function onScrollBoost() {
    var now = performance.now();
    var y = getScrollY();
    var lenisVelocity = Math.abs(getScrollVelocity());
    var deltaVelocity = 0;

    if (lastScrollTs) {
      var dt = Math.max(8, now - lastScrollTs);
      deltaVelocity = (Math.abs(y - lastScrollY) / dt) * 16.67;
    }

    lastScrollY = y;
    lastScrollTs = now;

    // Use whichever reading is stronger (Lenis can report tiny mid-lerp values).
    var velocity = Math.max(lenisVelocity, deltaVelocity);
    var boost = velocity * VELOCITY_SCALE;
    scrollBoost = clamp(Math.max(scrollBoost, boost), 0, MAX_BOOST);
    startLoop();
  }

  function tick() {
    if (prefersReducedMotion() || !instances.length) {
      running = false;
      rafId = 0;
      return;
    }

    // Additive boost: idle = BASE_SPEED, scrolling adds up to MAX_BOOST px/frame.
    var speed = BASE_SPEED + scrollBoost;
    scrollBoost *= BOOST_DECAY;
    if (scrollBoost < 0.01) {
      scrollBoost = 0;
    }

    instances.forEach(function (instance) {
      if (!instance.half) {
        return;
      }

      instance.offset -= speed;
      if (Math.abs(instance.offset) >= instance.half) {
        instance.offset += instance.half;
      }

      instance.track.style.transform = "translate3d(" + instance.offset.toFixed(2) + "px, 0, 0)";
    });

    rafId = window.requestAnimationFrame(tick);
  }

  function startLoop() {
    if (running) {
      return;
    }
    running = true;
    rafId = window.requestAnimationFrame(tick);
  }

  function stopLoop() {
    if (rafId) {
      window.cancelAnimationFrame(rafId);
    }
    running = false;
    rafId = 0;
  }

  function remeasure() {
    instances.forEach(function (instance) {
      instance.half = measureHalf(instance.track);
      if (instance.half > 0 && Math.abs(instance.offset) >= instance.half) {
        instance.offset = instance.offset % instance.half;
      }
    });
  }

  function ensureListeners() {
    if (!scrollBound) {
      scrollBound = true;
      window.addEventListener("scroll", onScrollBoost, { passive: true });

      function bindLenis(lenis) {
        if (!lenis || typeof lenis.on !== "function") {
          return;
        }
        lenis.on("scroll", onScrollBoost);
      }

      if (window.lenis) {
        bindLenis(window.lenis);
      } else {
        document.addEventListener("mh-lenis-ready", function (event) {
          bindLenis(event.detail && event.detail.lenis);
        });
      }
    }

    if (!resizeBound) {
      resizeBound = true;
      window.addEventListener("resize", function () {
        remeasure();
      });
    }
  }

  function bindSection(section) {
    if (section.getAttribute("data-mh-reviews-bound") === "1") {
      return;
    }
    section.setAttribute("data-mh-reviews-bound", "1");

    var instance = createInstance(section);
    if (!instance) {
      return;
    }

    instances.push(instance);
    ensureListeners();
    startLoop();
  }

  function destroyInScope(scope) {
    var root = scope && scope.querySelectorAll ? scope : document;
    root.querySelectorAll("[data-mh-reviews]").forEach(function (section) {
      instances = instances.filter(function (instance) {
        return instance.section !== section;
      });
      section.removeAttribute("data-mh-reviews-bound");
      var track = section.querySelector(".mh-reviews__track");
      if (track) {
        track.style.transform = "";
        track.removeAttribute("data-mh-marquee-cloned");
        track.querySelectorAll('[aria-hidden="true"]').forEach(function (node) {
          node.remove();
        });
      }
    });

    if (!instances.length) {
      stopLoop();
    }
  }

  function init(scope) {
    var root = scope && scope.querySelectorAll ? scope : document;
    root.querySelectorAll("[data-mh-reviews]").forEach(bindSection);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      init(document);
    });
  } else {
    init(document);
  }

  window.addEventListener("elementor/frontend/init", function () {
    if (!window.elementorFrontend || !elementorFrontend.hooks) {
      return;
    }
    elementorFrontend.hooks.addAction("frontend/element_ready/mh_testimonials_masonry.default", function ($el) {
      var el = $el && $el[0] ? $el[0] : $el;
      if (el) {
        destroyInScope(el);
        init(el);
      }
    });
  });
})();
