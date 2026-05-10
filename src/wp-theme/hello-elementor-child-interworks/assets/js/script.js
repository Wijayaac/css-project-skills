/* Child theme custom scripts */
function getTeamPostIdFromTrigger($el) {
  const fromData = ($el.attr("data-team-id") || "").trim();
  if (fromData) {
    return fromData;
  }
  const fromPostId = ($el.closest("[data-post-id]").attr("data-post-id") || "").trim();
  if (fromPostId) {
    return fromPostId;
  }
  const $loop = $el.closest('[data-elementor-type="loop-item"]');
  const cls = $loop.attr("class") || "";
  const m = cls.match(/\bpost-(\d+)\b/);
  return m ? m[1] : "";
}

jQuery(function ($) {
  $(document).on("click", ".team-popup-trigger", function (e) {
    e.preventDefault();
    const postId = getTeamPostIdFromTrigger($(this));
    if (!postId) {
      return;
    }
    ajaxPopupTeam(postId, $);
  });
});

function resetTeamPopup($) {
  $(".bio_picture").css("background-image", "none");
  $(".normal_name p").text("");
  $(".normal_position p").html("");
  $(".bio_details").html("");
}

function ajaxPopupTeam(postId, $) {
  const { ajaxurl, nonce } = ajax_object;
  const $popup = $("#bio-popup");

  resetTeamPopup($);
  $("body").addClass("team-popup-loading");
  $popup.addClass("is-loading").removeClass("is-loaded");

  $.post(
    ajaxurl,
    {
      action: "ajax_popup_team",
      nonce: nonce,
      post_id: postId,
    },
    function (res) {
      if (!res.success) return;
      preloadAndReveal(res.data, $);
    },
    "json",
  ).fail(function () {
    $("body").removeClass("team-popup-loading");
    $popup.removeClass("is-loading");
  });
}

/**
 * Preload the bio image, then swap content + fade in.
 * Falls back to revealing immediately after IMAGE_TIMEOUT_MS so a slow
 * CDN can't hang the popup forever.
 */
function preloadAndReveal(d, $) {
  const $popup = $("#bio-popup");
  const IMAGE_TIMEOUT_MS = 1500;

  const reveal = function () {
    $(".bio_picture").css("background-image", d.image ? `url(${d.image})` : "none");
    $(".normal_name p").text(d.name);
    $(".bio_details").html(d.bio);

    if (d.position && d.position.trim() !== "") {
      $(".normal_position p").html(d.position);
      $(".normal_position").show();
    } else {
      $(".normal_position").hide();
    }

    $popup.removeClass("is-loading").addClass("active is-loaded");
    $("body").removeClass("team-popup-loading");
  };

  if (!d.image) {
    reveal();
    return;
  }

  let done = false;
  const finish = function () {
    if (done) return;
    done = true;
    reveal();
  };

  const img = new Image();
  img.onload = finish;
  img.onerror = finish;
  img.src = d.image;
  setTimeout(finish, IMAGE_TIMEOUT_MS);
}
