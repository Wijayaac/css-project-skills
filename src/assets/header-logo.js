jQuery(document).ready(function ($) {
  stickyActive();
  customFilterProjects($);
});

function stickyActive() {
  var headerSelector = ".elementor-location-header";
  var header = document.querySelector(headerSelector);

  if (!header) return;

  var headerContent = header.querySelector(".header_container");
  var hero = document.querySelector(document.documentElement.getAttribute("data-header-hero") || ".hero");

  function updateHeaderLogo() {
    var home = document.body.classList.contains("home");
    var useWhite = false;
    if (home && headerContent) {
      var headerH = headerContent.getBoundingClientRect().height || headerContent.offsetHeight || 0;
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

function customFilterProjects($) {
  const $forms = $(".taxonomy-filters .custom-form");
  if (!$forms.length) return;

  ensureFilterTriggers($, $forms);
  toggleOpenForm($, $forms);
  bindClearFilters($, $forms);
  bindClearButtonVisibility($, $forms);
}

function getFilterHeading($form) {
  const settingsRaw = $form.attr("data-settings");
  if (!settingsRaw) return "Filter";

  try {
    const settings = JSON.parse(settingsRaw);
    const taxonomy = (settings.taxonomy || "").toLowerCase();
    if (taxonomy === "home-type") return "Home Type";
    if (taxonomy === "neighbourhood") return "Neighbourhood";
    return taxonomy
      .split("-")
      .filter(Boolean)
      .map(function (part) {
        return part.charAt(0).toUpperCase() + part.slice(1);
      })
      .join(" ");
  } catch (error) {
    return "Filter";
  }
}

function ensureFilterTriggers($, $forms) {
  $forms.each(function () {
    const $form = $(this);
    const $filter = $form.find(".e-filter").first();
    if (!$filter.length) return;
    if ($form.find(".custom-form__trigger").length) return;

    const heading = getFilterHeading($form);
    const triggerMarkup =
      '<div class="custom-form__trigger" role="button" tabindex="0" aria-expanded="true">' + '<span class="custom-form__trigger-text">' + heading + "</span>" + '<span class="custom-form__chevron" aria-hidden="true"></span>' + "</div>";

    $filter.before(triggerMarkup);
  });
}

function setFormExpanded($form, expanded) {
  const $trigger = $form.find(".custom-form__trigger").first();
  $form.toggleClass("is-collapsed", !expanded);
  $trigger.attr("aria-expanded", expanded ? "true" : "false");
}

function toggleOpenForm($, $forms) {
  $(document).off("click.customFilter");

  $forms.each(function () {
    const $form = $(this);
    const $filter = $form.find(".e-filter").first();
    const $trigger = $form.find(".custom-form__trigger").first();
    const $items = $filter.find(".e-filter-item");

    if (!$filter.length || !$trigger.length || !$items.length) return;

    const isInitialized = $form.attr("data-custom-filter-init") === "true";
    if (!isInitialized) {
      setFormExpanded($form, false);
      $form.attr("data-custom-filter-init", "true");
    } else {
      const expanded = $trigger.attr("aria-expanded") !== "false";
      setFormExpanded($form, expanded);
    }

    $trigger.off("click.customFilterTrigger keydown.customFilterTrigger");
    $items.off("click.customFilterItem");

    $trigger.on("click.customFilterTrigger", function (e) {
      e.preventDefault();
      e.stopPropagation();

      const willExpand = $form.hasClass("is-collapsed");

      $forms.not($form).each(function () {
        setFormExpanded($(this), false);
      });

      setFormExpanded($form, willExpand);
    });

    $trigger.on("keydown.customFilterTrigger", function (e) {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        $(this).trigger("click.customFilterTrigger");
      }
    });

    // Keep Elementor in control of filter state and data store.
    $items.on("click.customFilterItem", function (e) {
      e.stopPropagation();
    });
  });

  $(document).on("click.customFilter", function (e) {
    const $target = $(e.target);

    $forms.each(function () {
      const $form = $(this);
      const $trigger = $form.find(".custom-form__trigger").first();

      if ($form.is($target) || $form.has($target).length) return;

      setFormExpanded($form, false);
    });
  });
}

function bindClearFilters($, $forms) {
  const $clearBtn = $(".taxonomy-filters__clear a, .taxonomy-filters__clear .elementor-button");
  if (!$clearBtn.length) return;

  $clearBtn.on("click", function (e) {
    e.preventDefault();

    $forms.each(function () {
      const $form = $(this);
      const $filter = $form.find(".e-filter").first();
      const $allBtn = $filter.find('.e-filter-item[data-filter="__all"]');

      if (!$allBtn.length) return;
      if ($allBtn.attr("aria-pressed") === "true") return;

      $allBtn.trigger("click");
    });
  });
}

function hasAnyActiveFilter($, $forms) {
  let isActive = false;

  $forms.each(function () {
    const $filter = $(this).find(".e-filter").first();
    const $specificPressed = $filter.find('.e-filter-item[aria-pressed="true"]').filter(function () {
      return $(this).data("filter") !== "__all";
    });

    if ($specificPressed.length) {
      isActive = true;
      return false;
    }
  });

  return isActive;
}

function bindClearButtonVisibility($, $forms) {
  const $root = $forms.first().closest(".taxonomy-filters");
  if (!$root.length) return;

  function syncClearButtonVisibility() {
    $root.toggleClass("has-active-filters", hasAnyActiveFilter($, $forms));
  }

  syncClearButtonVisibility();

  $forms.each(function () {
    const $items = $(this).find(".e-filter .e-filter-item");
    $items.off("click.customFilterClearState");
    $items.on("click.customFilterClearState", function () {
      requestAnimationFrame(syncClearButtonVisibility);
    });
  });

  const $clearBtn = $(".taxonomy-filters__clear a, .taxonomy-filters__clear .elementor-button");
  $clearBtn.off("click.customFilterClearState");
  $clearBtn.on("click.customFilterClearState", function () {
    requestAnimationFrame(syncClearButtonVisibility);
  });
}
