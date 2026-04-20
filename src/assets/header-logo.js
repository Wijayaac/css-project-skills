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
    const triggerMarkup = '<button type="button" class="custom-form__trigger" aria-expanded="true">' + '<span class="custom-form__trigger-text">' + heading + "</span>" + '<span class="custom-form__chevron" aria-hidden="true"></span>' + "</button>";

    $filter.before(triggerMarkup);
  });
}

function setAllState($filter) {
  const $allBtn = $filter.find('.e-filter-item[data-filter="__all"]');
  const $specific = $filter.find(".e-filter-item").not($allBtn);
  const anySpecific = $specific.filter('[aria-pressed="true"]').length > 0;

  if (!anySpecific && $allBtn.length) {
    $filter.find(".e-filter-item").attr("aria-pressed", "false");
    $allBtn.attr("aria-pressed", "true");
  } else if (anySpecific) {
    $allBtn.attr("aria-pressed", "false");
  }
}

function toggleOpenForm($, $forms) {
  $(document).off("click.customFilter");

  $forms.each(function () {
    const $form = $(this);
    const $filter = $form.find(".e-filter").first();
    const $trigger = $form.find(".custom-form__trigger").first();
    const $items = $filter.find(".e-filter-item");

    if (!$filter.length || !$trigger.length || !$items.length) return;

    setAllState($filter);
    $items.off("click.customFilterItem");
    $trigger.off("click.customFilterTrigger");

    const expanded = $trigger.attr("aria-expanded") !== "false";
    $form.toggleClass("is-collapsed", !expanded);

    $trigger.on("click.customFilterTrigger", function (e) {
      e.preventDefault();
      const willExpand = $form.hasClass("is-collapsed");

      $forms.not($form).each(function () {
        const $otherForm = $(this);
        const $otherTrigger = $otherForm.find(".custom-form__trigger").first();
        $otherForm.addClass("is-collapsed");
        $otherTrigger.attr("aria-expanded", "false");
      });

      $form.toggleClass("is-collapsed", !willExpand);
      $trigger.attr("aria-expanded", willExpand ? "true" : "false");
    });

    $items.on("click.customFilterItem", function (e) {
      e.preventDefault();
      e.stopPropagation();

      const $option = $(this);
      const isAll = $option.data("filter") === "__all";
      const isPressed = $option.attr("aria-pressed") === "true";

      if (isAll) {
        $items.attr("aria-pressed", "false");
        $option.attr("aria-pressed", "true");
        return;
      }

      $filter.find('.e-filter-item[data-filter="__all"]').attr("aria-pressed", "false");
      $option.attr("aria-pressed", isPressed ? "false" : "true");
      setAllState($filter);
    });
  });

  $(document).on("click.customFilter", function (e) {
    const $target = $(e.target);

    $forms.each(function () {
      const $form = $(this);
      const $trigger = $form.find(".custom-form__trigger").first();

      if ($form.is($target) || $form.has($target).length) return;

      $form.addClass("is-collapsed");
      $trigger.attr("aria-expanded", "false");
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
      const $items = $filter.find(".e-filter-item");
      const $allBtn = $filter.find('.e-filter-item[data-filter="__all"]');

      $items.attr("aria-pressed", "false");
      $allBtn.attr("aria-pressed", "true");
      setAllState($filter);
    });
  });
}
