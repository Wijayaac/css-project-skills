(function () {
  // Config passed from PHP via wp_localize_script
  var cfg = window.ShopifyCartCfg || {};
  if (!cfg.domain || !cfg.token || !cfg.api) {
    console.error("[ShopifyCart] Missing config (domain/token/api).");
    return;
  }

  // ---------------- GraphQL helper ----------------
  function gql(query, variables) {
    var url = "https://" + cfg.domain + "/api/" + cfg.api + "/graphql.json";
    return fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Shopify-Storefront-Access-Token": cfg.token,
      },
      body: JSON.stringify({ query: query, variables: variables || {} }),
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (res) {
        if (res.errors && res.errors.length) {
          console.error(
            "[ShopifyCart] GraphQL errors:",
            res.errors,
            "query:",
            query,
            "vars:",
            variables
          );
          throw new Error(
            res.errors
              .map(function (e) {
                return e.message;
              })
              .join("; ")
          );
        }
        return res;
      });
  }

  // ---------------- helper to generate + persist token ----------------
  // We use 2 keys:
  // - wp_checkout_session: token that also gets attached to Shopify cart via _wp_session
  // - wp_checkout_started: flag so we only redirect to /order-confirmed/ AFTER user actually went to checkout
  function makeToken() {
    return "wp_" + Date.now() + "_" + Math.random().toString(16).slice(2);
  }
  function getWpSessionToken() {
    var k = "wp_checkout_session";
    var t = localStorage.getItem(k);
    if (!t) {
      t = makeToken();
      localStorage.setItem(k, t);
    }
    return t;
  }
  function markCheckoutStarted() {
    try {
      localStorage.setItem("wp_checkout_started", "1");
      localStorage.setItem("wp_checkout_started_at", String(Date.now()));
    } catch (e) {}
  }
  function clearCheckoutMarkers() {
    try {
      localStorage.removeItem("wp_checkout_started");
      localStorage.removeItem("wp_checkout_started_at");
      localStorage.removeItem("wp_checkout_session");
    } catch (e) {}
  }

  // ---------------- return redirect (Option 2: Shopify checkout backend only) ----------------
  // If user comes back to WP (via "Back to store"), redirect them to /order-confirmed/?s=TOKEN
  function maybeRedirectToConfirmation() {
    try {
      // Avoid loops
      if (window.location.pathname.indexOf("/order-confirmed") !== -1) {
        // On confirmation page, clear markers so future visits don't redirect again
        clearCheckoutMarkers();
        return;
      }

      var started = localStorage.getItem("wp_checkout_started");
      if (!started) return;

      var token = localStorage.getItem("wp_checkout_session");
      if (!token) return;

      // Optional TTL (30 minutes) so stale sessions won't keep redirecting
      var startedAt = parseInt(
        localStorage.getItem("wp_checkout_started_at") || "0",
        10
      );
      if (startedAt && Date.now() - startedAt > 30 * 60 * 1000) {
        clearCheckoutMarkers();
        return;
      }

      // Redirect to WP confirmation page
      window.location.href =
        "/order-confirmed/?session=" + encodeURIComponent(token);
    } catch (e) {}
  }

  // Run redirect as early as possible
  maybeRedirectToConfirmation();

  // ---------------- close elementor popup ----------------
  function closeElementorPopup(id) {
    try {
      // Prefer clicking the close button inside the modal; more reliable than closePopup() calls.
      var $open = jQuery("#elementor-popup-modal-" + id + ":visible").first();
      if (!$open.length) {
        $open = jQuery(
          ".elementor-popup-modal:visible, .dialog-type-lightbox:visible"
        ).first();
      }
      var btn = $open
        .find(".dialog-lightbox-close-button, .eicon-close")
        .get(0);
      if (btn) btn.click();
    } catch (error) {}
  }

  // ---------------- Local cart helpers ----------------
  function getCartId() {
    return localStorage.getItem("shopify_cart_id") || null;
  }
  function setCartId(id) {
    localStorage.setItem("shopify_cart_id", id);
  }

  function normalizeVariantGid(val) {
    if (!val) return val;
    var s = String(val).trim();
    // If user stored numeric ID by mistake, convert to global ID
    if (/^\d+$/.test(s)) return "gid://shopify/ProductVariant/" + s;
    return s;
  }

  // ---------------- Cart ops ----------------
  function ensureCart() {
    var query = [
      "mutation {",
      "  cartCreate {",
      "    cart { id checkoutUrl }",
      "    userErrors { field message }",
      "  }",
      "}",
    ].join("\n");

    return gql(query).then(function (res) {
      var ue =
        (res.data && res.data.cartCreate && res.data.cartCreate.userErrors) ||
        [];
      if (ue.length) {
        throw new Error(
          ue
            .map(function (e) {
              return e.message;
            })
            .join("; ")
        );
      }
      var id =
        res.data &&
        res.data.cartCreate &&
        res.data.cartCreate.cart &&
        res.data.cartCreate.cart.id;
      if (!id) throw new Error("No cart ID returned");
      setCartId(id);
      return id;
    });
  }

  function addLines(cartId, lines) {
    var query = [
      "mutation AddLines($cartId: ID!, $lines: [CartLineInput!]!) {",
      "  cartLinesAdd(cartId: $cartId, lines: $lines) {",
      "    cart { id }",
      "    userErrors { field message code }",
      "  }",
      "}",
    ].join("\n");

    return gql(query, { cartId: cartId, lines: lines }).then(function (res) {
      var errs =
        (res.data &&
          res.data.cartLinesAdd &&
          res.data.cartLinesAdd.userErrors) ||
        [];
      if (errs.length) {
        // Stock-related errors come through here too
        throw new Error(
          errs
            .map(function (e) {
              return e.message;
            })
            .join("; ")
        );
      }
      return cartId;
    });
  }

  function updateLine(cartId, lineId, quantity) {
    var query = [
      "mutation Update($cartId: ID!, $lines: [CartLineUpdateInput!]!) {",
      "  cartLinesUpdate(cartId: $cartId, lines: $lines) {",
      "    cart { id }",
      "    userErrors { field message }",
      "  }",
      "}",
    ].join("\n");

    return gql(query, {
      cartId: cartId,
      lines: [{ id: lineId, quantity: quantity }],
    }).then(function (res) {
      var ue =
        (res.data &&
          res.data.cartLinesUpdate &&
          res.data.cartLinesUpdate.userErrors) ||
        [];
      if (ue.length) {
        throw new Error(
          ue
            .map(function (e) {
              return e.message;
            })
            .join("; ")
        );
      }
      return cartId;
    });
  }

  function removeLine(cartId, lineId) {
    var query = [
      "mutation Remove($cartId: ID!, $lineIds: [ID!]!) {",
      "  cartLinesRemove(cartId: $cartId, lineIds: $lineIds) {",
      "    cart { id }",
      "    userErrors { field message }",
      "  }",
      "}",
    ].join("\n");

    return gql(query, { cartId: cartId, lineIds: [lineId] }).then(function (
      res
    ) {
      var ue =
        (res.data &&
          res.data.cartLinesRemove &&
          res.data.cartLinesRemove.userErrors) ||
        [];
      if (ue.length) {
        throw new Error(
          ue
            .map(function (e) {
              return e.message;
            })
            .join("; ")
        );
      }
      return cartId;
    });
  }

  // Fetch images + attributes for each line
  function getCart(cartId) {
    var query = [
      "query GetCart($id: ID!) {",
      "  cart(id: $id) {",
      "    id",
      "    checkoutUrl",
      "    cost {",
      "      subtotalAmount { amount currencyCode }",
      "    }",
      "    lines(first: 50) {",
      "      edges {",
      "        node {",
      "          id",
      "          quantity",
      "          attributes { key value }",
      "          merchandise {",
      "            ... on ProductVariant {",
      "              id",
      "              title",
      "              image { url altText }",
      "              product {",
      "                title",
      "                featuredImage { url altText }",
      "              }",
      "            }",
      "          }",
      "        }",
      "      }",
      "    }",
      "  }",
      "}",
    ].join("\n");

    return gql(query, { id: cartId }).then(function (res) {
      return res.data && res.data.cart;
    });
  }

  // Attach WP session token to Shopify cart (for webhook -> WP order confirmation mapping)
  function setCartSession(cartId, token) {
    var q = [
      "mutation($cartId: ID!, $attributes: [AttributeInput!]!) {",
      "  cartAttributesUpdate(cartId: $cartId, attributes: $attributes) {",
      "    cart { id }",
      "    userErrors { message }",
      "  }",
      "}",
    ].join("\n");

    return gql(q, {
      cartId: cartId,
      attributes: [{ key: "_wp_session", value: token }],
    }).then(function (res) {
      var errs =
        res &&
        res.data &&
        res.data.cartAttributesUpdate &&
        res.data.cartAttributesUpdate.userErrors
          ? res.data.cartAttributesUpdate.userErrors
          : [];
      if (errs.length) {
        throw new Error(
          errs
            .map(function (e) {
              return e.message;
            })
            .join("; ")
        );
      }
      return cartId;
    });
  }

  function goCheckout(e) {
    if (e && e.preventDefault) e.preventDefault();

    var cartId = getCartId();
    if (!cartId) {
      alert("Your cart is empty.");
      return;
    }

    var token = getWpSessionToken();

    // Mark checkout started so when user returns to WP we can redirect to /order-confirmed/
    markCheckoutStarted();

    setCartSession(cartId, token)
      .then(function () {
        return getCart(cartId);
      })
      .then(function (cart) {
        if (!cart || !cart.checkoutUrl) throw new Error("No checkout URL");
        window.location.href = cart.checkoutUrl;
      })
      .catch(function (err) {
        console.error(err);
        alert(
          "Checkout error: " + (err && err.message ? err.message : "Unknown")
        );
      });
  }

  function fmtMoney(amount, currency) {
    var n = Number(amount || 0);
    try {
      return new Intl.NumberFormat(undefined, {
        style: "currency",
        currency: currency || "USD",
      }).format(n);
    } catch (e) {
      return (currency || "USD") + " " + n.toFixed(2);
    }
  }

  // ---------------- Drawer UI helpers ----------------
  function pickLineImage(node) {
    // You removed picture-field, so checkout won't leak URLs.
    // Keep this fallback in case older carts still contain 'picture' attribute.
    var attrs = node.attributes || [];
    var picAttr = attrs.find
      ? attrs.find(function (a) {
          return a.key === "picture" || a.key === "_picture";
        })
      : null;
    if (picAttr && picAttr.value)
      return { url: picAttr.value, altText: "Custom image" };

    // Variant image
    var vimg = node.merchandise && node.merchandise.image;
    if (vimg && vimg.url) return vimg;

    // Product fallback
    var pimg =
      node.merchandise &&
      node.merchandise.product &&
      node.merchandise.product.featuredImage;
    if (pimg && pimg.url) return pimg;

    return null;
  }

  function renderCart(cart) {
    var el = document.getElementById("cart-lines");
    if (!el) return;

    if (!cart || !cart.lines || !cart.lines.edges.length) {
      el.innerHTML = "<p>Your cart is empty.</p>";
      toggleEmpty();
      return;
    }
    removeEmpty();

    var html = cart.lines.edges
      .map(function (edge) {
        var n = edge.node;
        var title =
          (n.merchandise &&
            n.merchandise.product &&
            n.merchandise.product.title) ||
          "Item";

        // Split variant title "A / B / C" into multiple small lines
        var variantTitle = "";
        if (
          n.merchandise &&
          n.merchandise.title &&
          n.merchandise.title !== "Default Title"
        ) {
          var parts = n.merchandise.title.split(" / ");
          variantTitle = parts
            .map(function (p) {
              return "<br><small>" + p.trim() + "</small>";
            })
            .join(" ");
        }

        var img = pickLineImage(n);
        var imgHtml = img
          ? '<img src="' +
            img.url +
            '" alt="' +
            (img.altText || "") +
            '" style="width:56px;height:56px;object-fit:cover;border:1px solid #eee;border-radius:4px;margin-right:8px;" />'
          : "";

        return (
          '<div data-line-id="' +
          n.id +
          '" style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin:8px 0;padding-bottom:8px;border-bottom:1px solid #eee;">' +
          '<div style="display:flex;align-items:center;gap:8px;flex:1 1 auto;">' +
          imgHtml +
          "<p class='title-container' style='margin:0;line-height:1.2'>" +
          "<span>" +
          title +
          "</span>" +
          variantTitle +
          "</p>" +
          "</div>" +
          '<div class="buttons-container flex">' +
          '<div class="qty-container flex space-between">' +
          '<button type="button" class="qty-dec">−</button>' +
          '<input type="number" class="qty" value="' +
          n.quantity +
          '" min="1">' +
          '<button type="button" class="qty-inc">+</button>' +
          "</div>" +
          '<button type="button" class="line-remove" aria-label="Remove">Remove</button>' +
          "</div>" +
          "</div>"
        );
      })
      .join("");

    var c = cart.cost || {};
    var cur = (c.subtotalAmount && c.subtotalAmount.currencyCode) || "USD";
    var subtotal = c.subtotalAmount
      ? fmtMoney(c.subtotalAmount.amount, cur)
      : "";

    var totalsHtml =
      '<div id="cart-summary">' +
      '<div class="flex space-between"><span>Subtotal</span><strong>' +
      subtotal +
      "</strong></div>" +
      "</div>";

    el.innerHTML = html + totalsHtml;
  }

  function openDrawer() {
    var d = document.getElementById("shopify-cart-drawer");
    if (d) d.style.display = "block";
  }
  function toggleEmpty() {
    var d = document.getElementById("shopify-cart-drawer");
    if (d) d.classList.add("empty");
  }
  function removeEmpty() {
    var d = document.getElementById("shopify-cart-drawer");
    if (d) d.classList.remove("empty");
  }
  function closeDrawer() {
    var d = document.getElementById("shopify-cart-drawer");
    if (d) d.style.display = "none";
  }

  // Expose minimal API
  window.ShopifyCart = {
    getCartId: getCartId,
    getCart: getCart,
    goCheckout: goCheckout,
    clearCheckoutMarkers: clearCheckoutMarkers,
  };

  // ---------------- Wire up UI ----------------
  jQuery(function ($) {
    var currentGid = null;

    // Grab our injected shopify_variant_id when a variation is found
    $(document)
      .off("found_variation.shopifyCart hide_variation.shopifyCart")
      .on(
        "found_variation.shopifyCart",
        "form.variations_form",
        function (_e, variation) {
          currentGid =
            variation && variation.shopify_variant_id
              ? variation.shopify_variant_id
              : null;
        }
      )
      .on("hide_variation.shopifyCart", "form.variations_form", function () {
        currentGid = null;
      });

    // Intercept PDP Add to Cart → add to Shopify (not Woo)
    $(document)
      .off("submit.shopifyCart", "form.cart")
      .on("submit.shopifyCart", "form.cart", function (e) {
        var gid =
          currentGid ||
          $(this).find(".shopify-variant-gid").val() ||
          window.defaultShopifyVariantId ||
          null;
        gid = normalizeVariantGid(gid);
        if (!gid) return; // no mapping → let Woo handle

        e.preventDefault();
        e.stopImmediatePropagation();
        e.stopPropagation();

        // Read qty robustly
        var $qtyInput = $(this)
          .find('input[name="quantity"], input.qty')
          .first();
        var raw = ($qtyInput.val() || "").toString().replace(/[^\d]/g, "");
        var qty = Math.max(1, parseInt(raw, 10) || 1);

        var line = { merchandiseId: gid, quantity: qty };

        var existing = getCartId();
        (existing ? Promise.resolve(existing) : ensureCart())
          .then(function (cartId) {
            return addLines(cartId, [line]);
          })
          .then(function (cartId) {
            return getCart(cartId);
          })
          .then(function (cart) {
            renderCart(cart);
            openDrawer();
            closeElementorPopup(349);
          })
          .catch(function (err) {
            console.error("[ShopifyCart] add-to-cart error:", err);
            alert(
              "Cart error: " + (err && err.message ? err.message : "Unknown")
            );
          });
      });

    // Drawer toggle
    $(document)
      .off("click.shopifyCart", "#cart-toggle")
      .on("click.shopifyCart", "#cart-toggle", function () {
        var d = document.getElementById("shopify-cart-drawer");
        if (!d) return;
        if (d.style.display === "block") {
          closeDrawer();
          return;
        }
        var id = getCartId();
        if (!id) {
          openDrawer();
          document.getElementById("cart-lines").innerHTML =
            "<p>Your cart is empty.</p>";
          toggleEmpty();
          return;
        }
        getCart(id).then(function (cart) {
          renderCart(cart);
          openDrawer();
        });
      });

    $(document)
      .off("click.shopifyCart", "#cart-close, #continue-shopping")
      .on("click.shopifyCart", "#cart-close, #continue-shopping", closeDrawer);

    // Close drawer when user clicks outside of it
    $(document)
      .off("click.shopifyCartOutside")
      .on("click.shopifyCartOutside", function (e) {
        try {
          var $drawer = $("#shopify-cart-drawer");
          var $modal = $(".elementor-popup-modal");
          if ($drawer.is(":hidden") || $modal.is(":visible")) return;

          if (
            $(e.target).closest("#shopify-cart-drawer").length ||
            $(e.target).closest("#cart-toggle").length
          )
            return;

          closeDrawer();
        } catch (error) {}
      });

    $(document)
      .off("click.shopifyCart", "#cart-view")
      .on("click.shopifyCart", "#cart-view", function () {
        var id = getCartId();
        if (!id) return;
        getCart(id).then(renderCart);
      });

    $(document)
      .off("click.shopifyCart", "#cart-checkout")
      .on("click.shopifyCart", "#cart-checkout", goCheckout);

    // Qty +/− and remove inside drawer
    $(document)
      .off("click.shopifyCart", ".qty-inc, .qty-dec, .line-remove")
      .on("click.shopifyCart", ".qty-inc, .qty-dec, .line-remove", function () {
        var id = getCartId();
        if (!id) return;
        var line = $(this).closest("[data-line-id]");
        var lineId = line.data("line-id");

        if ($(this).hasClass("line-remove")) {
          removeLine(id, lineId)
            .then(function () {
              return getCart(id);
            })
            .then(renderCart);
          return;
        }

        var qtyInput = line.find("input.qty");
        var qty = parseInt(qtyInput.val(), 10) || 1;
        qty += $(this).hasClass("qty-inc") ? 1 : -1;
        qty = Math.max(1, qty);

        updateLine(id, lineId, qty)
          .then(function () {
            return getCart(id);
          })
          .then(renderCart);
      });

    // --- Helpers for manual qty typing ---
    function clampQty(val) {
      var n = parseInt(val, 10);
      if (isNaN(n)) n = 1;
      if (n < 1) n = 1;
      return n;
    }

    function debounceInput($el, fn, wait) {
      clearTimeout($el.data("_debounceTimer"));
      var t = setTimeout(fn, wait);
      $el.data("_debounceTimer", t);
    }

    // Listen to manual edits in qty <input>
    $(document)
      .off("input.shopifyCart", "#shopify-cart-drawer input.qty")
      .on("input.shopifyCart", "#shopify-cart-drawer input.qty", function () {
        var $input = $(this);
        var id = getCartId();
        if (!id) return;
        var line = $input.closest("[data-line-id]");
        var lineId = line.data("line-id");

        debounceInput(
          $input,
          function () {
            var qty = clampQty($input.val());
            if (String(qty) !== String($input.val())) $input.val(qty);
            updateLine(id, lineId, qty)
              .then(function () {
                return getCart(id);
              })
              .then(renderCart)
              .catch(function (err) {
                console.error("[ShopifyCart] qty input update", err);
              });
          },
          400
        );
      });

    // Also handle Enter/blur to commit immediately
    $(document)
      .off("keydown.shopifyCart", "#shopify-cart-drawer input.qty")
      .on(
        "keydown.shopifyCart",
        "#shopify-cart-drawer input.qty",
        function (e) {
          if (e.key === "Enter") {
            e.preventDefault();
            $(this).trigger("change");
          }
        }
      );

    $(document)
      .off(
        "change.shopifyCart blur.shopifyCart",
        "#shopify-cart-drawer input.qty"
      )
      .on(
        "change.shopifyCart blur.shopifyCart",
        "#shopify-cart-drawer input.qty",
        function () {
          var $input = $(this);
          var id = getCartId();
          if (!id) return;
          var line = $input.closest("[data-line-id]");
          var lineId = line.data("line-id");
          var qty = clampQty($input.val());
          $input.val(qty);
          updateLine(id, lineId, qty)
            .then(function () {
              return getCart(id);
            })
            .then(renderCart)
            .catch(function (err) {
              console.error("[ShopifyCart] qty commit", err);
            });
        }
      );

    // Intercept Woo checkout buttons → go to Shopify
    $(document)
      .off(
        "click.shopifyCart",
        'a.checkout-button, button[name="proceed"], .wc-proceed-to-checkout a'
      )
      .on(
        "click.shopifyCart",
        'a.checkout-button, button[name="proceed"], .wc-proceed-to-checkout a',
        goCheckout
      );

    $(document)
      .off(
        "click.shopifyCart",
        ".widget_shopping_cart_content .checkout, .site-header .mini-cart .checkout"
      )
      .on(
        "click.shopifyCart",
        ".widget_shopping_cart_content .checkout, .site-header .mini-cart .checkout",
        goCheckout
      );

    $(document)
      .off("submit.shopifyCart", 'form.checkout, form[name="checkout"]')
      .on(
        "submit.shopifyCart",
        'form.checkout, form[name="checkout"]',
        goCheckout
      );

    // Auto-redirect if user lands on Woo /cart or /checkout directly
    if (
      document.body.classList.contains("woocommerce-cart") ||
      document.body.classList.contains("woocommerce-checkout")
    ) {
      setTimeout(function () {
        var id = getCartId();
        if (!id) return;
        getCart(id)
          .then(function (cart) {
            if (cart && cart.checkoutUrl)
              window.location.href = cart.checkoutUrl;
          })
          .catch(function () {});
      }, 150);
    }
  });
})();
