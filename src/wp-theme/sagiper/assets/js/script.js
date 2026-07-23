jQuery(document).ready(function ($) {
  showQuickView($);
  triggerChangeSelects($);
  showStories($);
  movingSelectFilter($);
  customFilterProjects($);
  initQuickViewVariationForm(document, $);
  $(document).on("opened.quickview", function (e, modalContent) {
    initQuickViewVariationForm(modalContent, $);
  });
  customScrollHeader($);
  removeAnchor($);
  showSubmenu($);
  customAnimation($);
  customPointer($);
  customBtnTechnical($);
  triggerShowSample($);
  architectAjaxFilter($);
  acrhitextCustomFilter($);
  updateHeaderHeights();
  initScrollUpButton($);
  ajaxInventoryFilter($);
  projectDetailSlider($);
});

function projectDetailSlider($) {
  if ($(".pg-main").length == 0) return;
  $(".pg-main").slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: true,
    fade: true,
    asNavFor: ".pg-thumbs",
    prevArrow: $(".pg-prev"),
    nextArrow: $(".pg-next"),
  });

  $(".pg-thumbs").slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    asNavFor: ".pg-main",
    dots: false,
    centerMode: false,
    focusOnSelect: true,
    arrows: false,
    variableWidth: true,
    infinite: false,
  });
}

function ajaxInventoryFilter($) {
  if ($("#inventory-filter-form").length == 0) return;
  $("#inventory-filter-form select").on("change", function () {
    let category = $("#category-filter").val();
    let profile = $("#profile-filter").val();
    let length = $("#length-filter").val();
    let location = $("#location-filter").val();

    $.ajax({
      url: ajax_object.ajax_url,
      type: "POST",

      data: {
        action: "inventory_filter",
        category: category,
        profile: profile,
        length: length,
        location: location,
      },

      beforeSend: function () {
        $(".inventory-loading").addClass("active");
      },

      success: function (response) {
        $("#inventory-table tbody").html(response.html);
        $(".inventory-loading").removeClass("active");
      },
    });
  });
}

function initScrollUpButton($) {
  const $scrollUp = $(".scroll_up_container");
  if (!$scrollUp.length) return;
  const showAfter = 200;
  $scrollUp.hide();
  $(window).on("scroll", function () {
    if ($(this).scrollTop() > showAfter) {
      if (!$scrollUp.is(":visible")) {
        $scrollUp.stop(true, true).fadeIn(200);
      }
    } else {
      if ($scrollUp.is(":visible")) {
        $scrollUp.stop(true, true).fadeOut(200);
      }
    }
  });
}

window.addEventListener("resize", updateHeaderHeights);

function updateHeaderHeights() {
  const header = document.querySelector(".header_container");
  const blueHeader = document.querySelector(".blue_header");

  if (header) {
    document.documentElement.style.setProperty(
      "--header-height",
      header.offsetHeight + "px",
    );
  }

  if (blueHeader) {
    document.documentElement.style.setProperty(
      "--blue-header-height",
      blueHeader.offsetHeight + "px",
    );
  }
}

function acrhitextCustomFilter($) {
  if (!$(".toolbox-container").length) return;

  let wasMobile = null; // track previous state

  const toggleClass = () => {
    const isMobile = window.innerWidth < 742;

    $(".toolbox-container").toggleClass("custom-form", isMobile);

    // Only call when state actually changes
    if (wasMobile !== isMobile) {
      customFilterProjects($);
      wasMobile = isMobile;
    }
  };

  toggleClass(); // run on load

  $(window).on("resize", toggleClass);
}

function architectAjaxFilter($) {
  if ($(".architect-filter").length == 0) return;

  $(".architect-filter .e-filter-item").on("click", function (e) {
    e.preventDefault();

    var slug = $(this).data("filter");

    $(".architect-filter .e-filter-item").attr("aria-pressed", "false");
    $(this).attr("aria-pressed", "true");

    $.ajax({
      url: ajax_object.ajax_url,
      type: "POST",

      data: {
        action: "load_architect_filter",
        slug: slug,
      },

      beforeSend: function () {
        $("#architect-results").html("<p>Loading...</p>");
      },

      success: function (response) {
        $("#architect-results").html(response);
      },

      error: function () {
        $("#architect-results").html("<p>Error loading data</p>");
      },
    });
  });
}

jQuery(function ($) {
  let running = false;
  let observer = null;

  function getFilterFromURL() {
    const params = new URLSearchParams(window.location.search);
    for (const [k, v] of params.entries()) {
      if (k.includes("product_brand")) return v;
    }
    return null;
  }

  function addSeparator() {
    if (running) return;
    running = true;

    const activeFilter = getFilterFromURL();

    if (!activeFilter || activeFilter === "__all") {
      running = false;
      return;
    }

    const brandClass = "product_brand-" + activeFilter;
    const $grid = $(".product-grid");

    if (!$grid.length) {
      running = false;
      return;
    }

    $grid.find(".brand-separator").remove();

    const $items = $grid.find(".e-loop-item");
    if (!$items.length) {
      running = false;
      return;
    }

    const pureItems = [];
    const multiItems = [];

    $items.each(function () {
      const brandClasses = this.className
        .split(/\s+/)
        .filter((c) => c.indexOf("product_brand-") === 0);

      if (!brandClasses.includes(brandClass)) return;

      if (brandClasses.length === 1) {
        pureItems.push(this);
      } else {
        multiItems.push(this);
      }
    });

    if (pureItems.length === 0) {
      running = false;
      return;
    }

    if (multiItems.length === 0) {
      running = false;
      return;
    }

    const target = multiItems[0];

    const targetClasses = target.className
      .split(/\s+/)
      .filter((c) => c.indexOf("product_brand-") === 0);

    const otherClass = targetClasses.find((c) => c !== brandClass);

    const otherSlug = otherClass
      ? otherClass.replace("product_brand-", "")
      : "other";

    const label = otherSlug
      .replace(/-/g, " ")
      .replace(/\b\w/g, (c) => c.toUpperCase());

    if (observer) observer.disconnect();

    $('<div class="brand-separator">' + label + "</div>").insertBefore(target);

    watchGrid();

    running = false;
  }

  function watchGrid() {
    const grid = document.querySelector(".product-grid");
    if (!grid) return;

    if (observer) observer.disconnect();

    observer = new MutationObserver(function (mutations) {
      const relevant = mutations.some(function (m) {
        return Array.from(m.addedNodes).some(function (n) {
          if (n.nodeType !== 1) return false;

          if (n.classList && n.classList.contains("e-loop-item")) {
            return true;
          }

          if (n.querySelector && n.querySelector(".e-loop-item")) {
            return true;
          }

          return false;
        });
      });

      if (relevant) addSeparator();
    });

    observer.observe(grid, {
      childList: true,
      subtree: true,
    });
  }

  // init
  addSeparator();
  watchGrid();
});

jQuery(function ($) {
  function activateWoodgrain(scope) {
    const $scope = $(scope || document);
    const $btn = $scope.find('.e-filter [data-filter*="woodgrain"]').first();
    if (!$btn.length) return;
    if ($btn.hasClass("e-active")) return;
    $btn[0].click();
  }

  function tryActivate() {
    if ($('.e-filter [data-filter*="woodgrain"]').length) {
      if (!$(".samples-loop").length) {
        // dont apply in samples loop
        activateWoodgrain(document);
      }
    }
  }

  setTimeout(tryActivate, 300);

  if (window.elementorFrontend) {
    $(window).on("elementor/frontend/init", function () {
      elementorFrontend.hooks.addAction(
        "frontend/element_ready/loop-grid.default",
        tryActivate,
      );
    });
  }
});

function triggerShowSample($) {
  const params = new URLSearchParams(window.location.search);
  const id = params.get("product-sample");

  if (!id) return;

  const $target = $("#sample-" + id + " .quick-view_btn").find("a");

  if ($target.length) {
    // Scroll to its parent (#sample-id)
    $("html, body").animate(
      {
        scrollTop: $("#show-samples-product").offset().top,
      },
      400,
    );

    // Wait 0.5 seconds then click
    setTimeout(function () {
      $target.trigger("click");
    }, 500);
  }
}

window.technicalFileUrl = ""; // global var

function customBtnTechnical($) {
  const $btnTechnical = $(".btn-pop-technical");
  if (!$btnTechnical.length) return;

  $btnTechnical.on("click", function (e) {
    e.preventDefault();

    const itemID = $(this).data("item");

    if (itemID) {
      $.ajax({
        url: ajax_object.ajax_url,
        type: "POST",
        data: {
          action: "get_technical_detail",
          itemId: itemID,
          nonce: ajax_object.nonce,
        },
        success: function (response) {
          if (response.success) {
            const data = response.data[0];
            window.technicalFileUrl = data.file; // store globally

            const fileName = data.name;
            $(".elementor-popup-modal .downloaded-file-name input").val(
              fileName,
            );
          }
        },
      });
    }
  });

  $(document).on("wpformsAjaxSubmitSuccess", function (event, formData, data) {
    const fileUrl = window.technicalFileUrl;
    if (!fileUrl) return;
    const waitForConfirmation = setInterval(() => {
      const $success = $(".wpforms-confirmation-container-full");
      if ($success.length) {
        clearInterval(waitForConfirmation);
        $success.append(`
            <a id="pop-download" href="${fileUrl}" class="btn-download" target="_blank">Download File</a>
          `);
      }
    }, 100);
  });
}

jQuery(window).on("elementor/frontend/init", function () {
  elementorFrontend.on("components:init", function () {
    var popupId = 31888;

    function showMyPopup() {
      elementorFrontend.documentsManager.documents[popupId].showModal();
    }

    jQuery(".hamburger").on("click", function (e) {
      e.preventDefault();
      showMyPopup();
    });
  });
});

function customPointer($) {
  jQuery(function ($) {
    const $cursorArrow = $(`
    <div class="cursor-arrow">
      <div id="cta">
        <span class="arrow primera next"></span>
        <span class="arrow segunda next"></span>
      </div>
    </div>
  `);

    $("body").append($cursorArrow);

    let lastX = 0;

    $(".cursor-link")
      .on("mouseenter", function () {
        $cursorArrow.addClass("active");
      })
      .on("mouseleave", function () {
        $cursorArrow.removeClass("active");
      })
      .on("mousemove", function (e) {
        $cursorArrow.css({
          left: e.clientX + 12,
          top: e.clientY + 12,
          transform: `translate(-50%, -50%) scale(1) rotate(0 deg)`,
        });

        lastX = e.clientX;
      });

    // Disable on touch devices
    if ("ontouchstart" in window) {
      $cursorArrow.remove();
    }
  });
}

function customAnimation($) {
  var elements = $(".custom-animate.elementor-invisible");

  if (!elements.length) return;

  const observer = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          var $el = $(entry.target);
          var settings = $el.data("settings") || {};
          var animation = settings._animation || "fadeIn";
          var delay = settings._animation_delay || 0;

          if (animation) {
            setTimeout(function () {
              $el
                .removeClass("elementor-invisible")
                .addClass("animated " + animation);
            }, delay);
          }

          // unobserve after first animation
          observer.unobserve(entry.target);
        }
      });
    },
    {
      threshold: 0.2,
    },
  ); // 20% of element visible

  elements.each(function () {
    observer.observe(this);
  });
}

function showSubmenu($) {
  const $header = $(".header_container");
  if (!$header.length) return;

  let hideTimeout;
  let showTimeout;
  const $submenu = $(".submenu-container");
  const HOVER_DELAY = 250;

  $(".menu-item.has-child").hover(
    function () {
      const $item = $(this);
      const id = $item.attr("id");

      // Cancel hiding
      clearTimeout(hideTimeout);

      // Delay showing
      showTimeout = setTimeout(() => {
        $(".menu-item.has-child.active").removeClass("active");
        $(".submenu-items.active").removeClass("active");

        $item.addClass("active");
        $submenu.addClass("active");
        $('.submenu-items[data-menu="' + id + '"]').addClass("active");
      }, HOVER_DELAY);
    },
    function () {
      const $item = $(this);

      // Cancel pending show if user leaves early
      clearTimeout(showTimeout);

      hideTimeout = setTimeout(() => {
        const submenuEl = $submenu.get(0);

        if (
          !$item.get(0)?.matches(":hover") &&
          !(submenuEl && submenuEl.matches(":hover"))
        ) {
          $(".menu-item.has-child.active").removeClass("active");
          $(".submenu-items.active").removeClass("active");
          $submenu.removeClass("active");
        }
      }, 200);
    },
  );

  $submenu.on("mouseleave", function () {
    hideTimeout = setTimeout(() => {
      if (
        !Array.from($(".menu-item.has-child")).some((el) =>
          el.matches(":hover"),
        )
      ) {
        closeMenu($);
      }
    }, 150);
  });

  $(".menu-close").on("click", function () {
    clearTimeout(hideTimeout); // prevent delayed reopen
    closeMenu($);
  });
}

function closeMenu($) {
  $(".menu-item.has-child.active").removeClass("active");
  $(".submenu-items.active").removeClass("active");
  $(".submenu-container").removeClass("active");
}

function removeAnchor($) {
  $(".remove-anchor a").each(function () {
    const href = $(this).attr("href");

    if (href === "#") {
      $(this).removeAttr("href");
      $(this).css("cursor", "pointer");
    }
  });
}

function customScrollHeader() {
  var headerSelector = ".elementor-location-header";
  var header = document.querySelector(headerSelector);
  var headerContainer = document.querySelector(".custom-menu-container");
  var headerBlack = document.querySelector(".custom-menu-container.-default");

  if (header) {
    let lastScrollY = window.pageYOffset;
    let isHeaderVisible = true;

    const blueHeader = header.querySelector(".blue_header");
    const headerContent = header.querySelector(
      ".elementor-element.header_container",
    );
    if (!headerContent) return;
    window.addEventListener("scroll", function () {
      const currentScrollY = window.pageYOffset;

      if (currentScrollY === 0) {
        // At the very top: show header without styles
        blueHeader.classList.remove("is-hidden");
        headerContent.classList.remove("blue-hidden");
        headerContent.style.background = "unset";
        headerContent.style.backdropFilter = "none";
        headerContent.style.webkitBackdropFilter = "none";
        headerContent.style.boxShadow = "none";
        isHeaderVisible = true;
        if (!headerBlack) {
          headerContainer.classList.remove("black-header");
        }
      } else if (currentScrollY > lastScrollY) {
        // Scrolling down: hide header
        if (isHeaderVisible) {
          blueHeader.classList.add("is-hidden");
          headerContent.classList.add("blue-hidden");
          headerContent.style.background = "white";
          headerContent.style.boxShadow = "rgb(0 0 0 / 9%) 0px 0px 2px 2px";
          isHeaderVisible = false;
          if (!headerBlack) {
            headerContainer.classList.add("black-header");
          }
        }
      } else if (currentScrollY < lastScrollY) {
        // Scrolling up: show header with styles
        if (!isHeaderVisible) {
          blueHeader.classList.remove("is-hidden");
          headerContent.classList.remove("blue-hidden");
          headerContent.style.background = "white";
          headerContent.style.backdropFilter = "blur(1px)";
          headerContent.style.webkitBackdropFilter = "blur(1px)";
          headerContent.style.boxShadow = "rgb(0 0 0 / 9%) 0px 0px 2px 2px";
          isHeaderVisible = true;
        }
      }

      lastScrollY = currentScrollY;
    });
  }
}

function customFilterProjects($) {
  const $forms = $(".custom-form");
  if (!$forms.length) return;
  toggleOpenForm($, $forms);
}

function toggleOpenForm($, $forms) {
  $forms.each(function () {
    const $form = $(this);
    const $filter = $form.find(".e-filter");
    if (!$filter.length) return;

    const $toggle = $filter.find('.e-filter-item[aria-pressed="true"]');
    const $options = $filter.find(".e-filter-item").not($toggle);

    if (!$toggle.length || !$options.length) return;

    $filter.on("click", function (e) {
      if ($(e.target).hasClass("e-filter-item")) return;
      $filter.toggleClass("is-open");
    });

    $options.on("click", function (e) {
      e.preventDefault();
      e.stopPropagation();

      const $option = $(this);

      // $toggle.html($option.html());

      $filter.find(".e-filter-item").attr("aria-pressed", "false");
      $toggle.attr("aria-pressed", "true");

      // Close dropdown
      $filter.removeClass("is-open");
    });

    $(document).on("click", function (e) {
      if (!$form.is(e.target) && !$form.has(e.target).length) {
        $filter.removeClass("is-open");
      }
    });
  });
}

function movingSelectFilter($) {
  if ($(".moving-blue-bg").length < 0) {
    return;
  }

  $bgPosition = 0;
  $bgWidth = 0;
  $total = $(".moving-blue-bg search button").length;
  $search = $(".moving-blue-bg search");
  $searchBtns = $(".moving-blue-bg search button");

  function calculatePositions() {
    $active = $search.find('button[aria-pressed="true"]');
    $searchBtns.each(function (index) {
      $btn = $(this);
      $width = $btn.outerWidth();
      $leftPos = $btn.position().left;
      $height = $btn.outerHeight();
      $topPos = $btn.position().top;
      $btn.attr("data-width", $width);
      $btn.attr("data-height", $height);
      $btn.attr("data-position-left", $leftPos);
      $btn.attr("data-position-top", $topPos);

      if ($btn.attr("aria-pressed") === "true" && $active.length > 0) {
        setPosition($width, $height, $leftPos, $topPos);
      } else if ($active.length === 0 && index === $total - 1) {
        setPosition($width, $height, $leftPos, $topPos);
      }
    });
  }

  calculatePositions();

  $(document).on("click", ".moving-blue-bg search button", function () {
    $btn = $(this);
    setPosition(
      $btn.attr("data-width"),
      $btn.attr("data-height"),
      $btn.attr("data-position-left"),
      $btn.attr("data-position-top"),
    );
  });

  function setPosition($width, $height, $left, $top) {
    $search.css({
      "--bg-left": $left + "px",
      "--bg-top": $top + "px",
      "--bg-width": $width + "px",
      "--bg-height": $height + "px",
    });
  }

  let resizeTimer;
  $(window).on("resize", function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      calculatePositions();
    }, 100);
  });
}

function showStories($) {
  if ($(".title-list-container") < 0 && $(".story-loop-custom ") < 0) {
    return;
  }

  $titleList = $(".title-list-container");
  $titleBtn = $(".title-list-container a");
  $storyContent = $(".story-loop-custom .type-our-story");

  $titleBtn.on("click", function (e) {
    e.preventDefault();
    $titleBtn.removeClass("active");
    $(this).addClass("active");
    $id = $(this).data("id");

    $storyContent.removeClass("active");
    $(".story-loop-custom .type-our-story." + $id).addClass("active");
  });

  // Prev / Next buttons handler
  if ($(".story-btn").length > 0) {
    $prev = $(".story-btn.prev");
    $next = $(".story-btn.next");

    $next.on("click", function () {
      let current = $titleBtn.filter(".active");
      let nextBtn = current.next("a");
      if (nextBtn.length === 0) {
        nextBtn = $titleBtn.first();
      }

      nextBtn.trigger("click");
    });

    $prev.on("click", function () {
      let current = $titleBtn.filter(".active");
      let prevBtn = current.prev("a");

      if (prevBtn.length === 0) {
        prevBtn = $titleBtn.last();
      }

      prevBtn.trigger("click");
    });
  }

  $titleBtn.first().trigger("click");
}

function triggerChangeSelects($) {
  $(document).on("change", ".variation-radios input:checked", function () {
    const $radio = $(this);
    $('select[name="' + $radio.attr("name") + '"]')
      .val($radio.val())
      .trigger("change");
  });
}

function initQuickViewVariationForm(context, $) {
  const $form = $(context).find("form.variations_form");
  if ($form.length) {
    if (typeof $form.wc_variation_form === "function") {
      $form.wc_variation_form(); // initialize variation logic
      $form.find(".variations select").trigger("change"); // update price
    } else {
      console.warn("wc_variation_form() is not yet available. Retrying...");
      setTimeout(() => initQuickViewVariationForm(context, $), 300);
    }
  }
}

function wooVariationsChange($) {
  $("form.variations_form").each(function () {
    $(this).wc_variation_form();
    $(this).find(".variations select").change();
  });
}

function showQuickView($) {
  const quickViewCache = {};

  $(document).on("click", ".quick-view_btn a", function (e) {
    e.preventDefault();

    let $productId = $(this).attr("id");

    if (!$productId) return;

    $(".quickview_container.e-parent").addClass("is-loading");

    /*
    ==========================
    CACHE HIT
    ==========================
    */
    if (quickViewCache[$productId]) {
      setTimeout(function () {
        const $container = $(document).find(".quickview_container");

        $container.empty().html(quickViewCache[$productId]);

        initQuickViewVariationForm(document, $);
      }, 100);

      $(".quickview_container.e-parent").removeClass("is-loading");

      return;
    }

    $.ajax({
      url: ajax_object.ajax_url,

      type: "POST",

      data: {
        action: "quickview_product",
        productId: $productId,
        nonce: ajax_object.nonce,
      },

      success: function (response) {
        if (response.success) {
          let $data = response.data;

          // SAVE TO CACHE
          quickViewCache[$productId] = $data;

          $(".quickview_container").empty().html($data);

          setTimeout(function () {
            initQuickViewVariationForm(document, $);
          }, 50);
        } else {
          console.error(response.data.error);

          $(".quickview_container").html(
            '<div class="qv-error">Failed to load product</div>',
          );
        }
      },

      error: function (xhr, status, error) {
        console.error("AJAX error:", error);

        $(".quickview_container").html(
          '<div class="qv-error">Connection error. Please try again.</div>',
        );
      },

      complete: function () {
        $(".quickview_container.e-parent").removeClass("is-loading");
      },
    });
  });
}

// function to remove the url for element that has child on mobile
jQuery(function ($) {
  function removeSubmenuHref() {
    $(".mobile_menu a.elementor-item.has-submenu").each(function () {
      $(this).removeAttr("href");
    });
  }

  /* --- When popup opens --- */
  $(document).on("elementor/popup/show", function (event, id, instance) {
    removeSubmenuHref();
  });

  /* --- Also try on click (safety) --- */
  $(document).on(
    "click",
    ".mobile_menu a.elementor-item.has-submenu",
    function (e) {
      e.preventDefault();
      $(this).removeAttr("href");
    },
  );
});
