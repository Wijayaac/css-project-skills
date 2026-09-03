/**
 * Toggle .header_container background when the page is scrolled.
 */
(function () {
  "use strict";

  var headerBar = document.querySelector(".header-container");
  if (!headerBar) {
    return;
  }

  var ticking = false;

  function updateHeaderScrollState() {
    if (ticking) {
      return;
    }

    ticking = true;
    window.requestAnimationFrame(function () {
      ticking = false;
      headerBar.classList.toggle("is-scrolled", window.scrollY > 0);
    });
  }

  updateHeaderScrollState();

  window.addEventListener("scroll", updateHeaderScrollState, { passive: true });
  window.addEventListener("resize", updateHeaderScrollState);
})();
