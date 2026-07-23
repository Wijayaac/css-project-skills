<?php
// Prevent direct access for security
if (! defined('ABSPATH')) exit;

// Register field shopify_variant_id //
// Show field in the variation edit panel
add_action('woocommerce_variation_options_pricing', function ($loop, $variation_data, $variation) {
    $value = get_post_meta($variation->ID, '_shopify_variant_id', true);
    echo '<p class="form-field variable form-row ">
    <label for="shopify_variant_id[' . $variation->ID . ']">Shopify Variant ID</label>
    <input type="text" name="shopify_variant_id[' . $variation->ID . ']" value="' . esc_attr($value) . '" />
</p>';
}, 10, 3);

// Save the field when the variation is updated
add_action('woocommerce_save_product_variation', function ($variation_id, $i) {
    if (isset($_POST['shopify_variant_id'][$variation_id])) {
        update_post_meta(
            $variation_id,
            '_shopify_variant_id',
            sanitize_text_field($_POST['shopify_variant_id'][$variation_id])
        );
    }
}, 10, 2);

// Add _shopify_variant_id into Woo's variation JSON (works if $variation is object OR array)
add_filter('woocommerce_available_variation', function ($data, $product, $variation) {
    // Get the variation ID robustly
    $variation_id = null;

    // Case 1: modern Woo – object
    if (is_object($variation) && method_exists($variation, 'get_id')) {
        $variation_id = $variation->get_id();
    }

    // Case 2: some contexts – array payload
    if (!$variation_id && is_array($variation) && !empty($variation['variation_id'])) {
        $variation_id = (int) $variation['variation_id'];
    }

    // Case 3: Woo already put the ID into $data
    if (!$variation_id && !empty($data['variation_id'])) {
        $variation_id = (int) $data['variation_id'];
    }

    if ($variation_id) {
        $gid = get_post_meta($variation_id, '_shopify_variant_id', true);
        if (!empty($gid)) {
            $data['shopify_variant_id'] = $gid; // this will appear in the variation JSON on the PDP
        }
    }

    return $data;
}, 10, 3);

// add shopify js
add_action('wp_enqueue_scripts', function () {
    // Path to your JS file (put the file there in step 2)
    $handle = 'shopify-cart';
    $src    = get_stylesheet_directory_uri() . '/assets/js/shopify-cart.js';

    // Make sure jQuery loads first
    wp_enqueue_script($handle, $src, ['jquery'], null, true);

    // Pass config safely from PHP → JS
$cfg = [
        'domain' => 'b8rqfg-6n.myshopify.com',   // use your myshopify.com domain
        'token'  => 'bc2c27603244090c87dfb27230198f28', // Storefront API token (Headless/Custom Storefront)
        'api'    => '2024-07',                     // API version enabled in your shop
    ];
// 	$cfg = [ test api key on dev
//         'domain' => 'sagipertest.myshopify.com',   // use your myshopify.com domain
//         'token'  => '4e0bb1f962d332fbfd55010a2119d0b9', // Storefront API token (Headless/Custom Storefront)
//         'api'    => '2024-07',                     // API version enabled in your shop
//     ];
    wp_localize_script($handle, 'ShopifyCartCfg', $cfg);
});

add_shortcode('shopify_order_email', function () {
    $token = isset($_GET['session']) ? sanitize_text_field($_GET['session']) : '';
    if (!$token) return '<p>Missing session token.</p>';

    $data = get_option('wp_shopify_order_' . sanitize_key($token));
    if (!$data) {
        return '<p>We are confirming your order… please refresh in a moment.</p>';
    }

    $order = $data['order_name'] ?? '';
    $email = $data['email'] ?? '';

    $html  = '';
    $html .= '<p class="order_confirmed"><span>Order Number</span> <span class="order-number">' . esc_html($order) . '</span></p>';
    $html .= '<p class="order_confirmed"><span>Email Address</span> <span class="order-email">' . esc_html($email) . '</span></p>';
    return $html;
});

add_shortcode('shopify_order_products', function () {
    $token = isset($_GET['session']) ? sanitize_text_field($_GET['session']) : '';
    if (!$token) return '<p>Missing session token.</p>';

    $data = get_option('wp_shopify_order_' . sanitize_key($token));
    if (!$data) {
        return '<p>We are confirming your order… please refresh in a moment.</p>';
    }
    $html = "";
    // Samples Requested (2)
    $items = $data['line_items'] ?? [];
    $count = 0;
    $productList = "";
    if (!empty($items) && is_array($items)) {
        $productList .= '<div style="display:flex;flex-direction:column;gap:12px;">';

        foreach ($items as $it) {
            $count++;
            $title = $it['title'] ?? '';
            $qty   = (int)($it['quantity'] ?? 1);
            $img   = $it['image'] ?? '';

            $productList .= '<div style="display:flex;gap:12px;align-items:center;padding-bottom:12px;">';

            if ($img) {
                $productList .= '<img src="' . esc_url($img) . '" alt="' . esc_attr($title) . '" style="width:72px;height:72px;object-fit:cover;border:1px solid #eee;border-radius:6px;" />';
            } else {
                $productList .= '<div style="width:72px;height:72px;border:1px solid #eee;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;color:#777;">No image</div>';
            }

            $productList .= '<div>';
            $productList .= '<div style="font-weight:600;">' . esc_html($title) . '</div>';
            $productList .= '</div>';

            $productList .= '</div>';
        }

        $productList .= '</div>';
    }

    if ($productList) {
        $html = "<div class='product-confirmed-container'>
                <h3> Samples Requested ($count)</h3>
                $productList
                </div>";
    }

    return $html;
});


add_action('rest_api_init', function () {
    register_rest_route('shopify/v1', '/orders-paid', [
        'methods'  => 'POST',
        'callback' => 'wp_shopify_orders_paid_webhook',
        'permission_callback' => '__return_true',
    ]);
});

function wp_shopify_orders_paid_webhook(WP_REST_Request $req)
{
    $raw = $req->get_body();
    $hmac = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';

    $secret = 'b11e3501215f45e2fe8320dd97645441effa069a212b1e9f7ec7e7c54a1abab3';
    $calc = base64_encode(hash_hmac('sha256', $raw, $secret, true));
    if (!hash_equals($calc, $hmac)) {
        return new WP_REST_Response(['ok' => false, 'error' => 'Invalid HMAC'], 401);
    }

    $order = json_decode($raw, true);
    if (!$order) return new WP_REST_Response(['ok' => false, 'error' => 'Bad JSON'], 400);

    // Find _wp_session in note_attributes
    $token = '';
    foreach (($order['note_attributes'] ?? []) as $a) {
        if (($a['name'] ?? '') === '_wp_session') {
            $token = (string)($a['value'] ?? '');
            break;
        }
    }
    if (!$token) return new WP_REST_Response(['ok' => true, 'skipped' => 'No _wp_session'], 200);


    $items = [];
    foreach (($order['line_items'] ?? []) as $li) {
        $img = '';
        // Shopify order webhook often has: line_items[].image.src
        if (!empty($li['image']['src'])) {
            $img = (string)$li['image']['src'];
        } elseif (!empty($li['image'])) {
            // sometimes image may already be a string/URL
            $img = is_string($li['image']) ? $li['image'] : '';
        }

        $items[] = [
            'title'    => $li['title'] ?? '',
            'quantity' => $li['quantity'] ?? 1,
            'image'    => $img,
            'variant_id' => $li['variant_id'] ?? null,
            'product_id' => $li['product_id'] ?? null,
        ];
    }

    $payload = [
        'order_id'   => $order['id'] ?? null,
        'order_name' => $order['name'] ?? null,
        'email'      => $order['email'] ?? null,
        'currency'   => $order['currency'] ?? null,
        'total_price' => $order['total_price'] ?? null,
        'line_items' => $items,
        'created_at' => $order['created_at'] ?? null,
        'financial_status' => $order['financial_status'] ?? null,
    ];

    update_option('wp_shopify_order_' . sanitize_key($token), $payload, false);

    return new WP_REST_Response(['ok' => true], 200);
}


// add inline script top redirects if checkout was started
function add_checkout_redirect_script()
{
    wp_register_script(
        'checkout-redirect',
        false,
        array(),
        null,
        true
    );

    wp_enqueue_script('checkout-redirect');

    wp_add_inline_script(
        'checkout-redirect',
        '(function () {
            try {
                var token = localStorage.getItem("wp_checkout_session");
                var started = localStorage.getItem("wp_checkout_started") === "1";
                var startedAt = parseInt(localStorage.getItem("wp_checkout_started_at") || "0", 10);

                if (!token || !started) return;

                // Optional: only within 2 hours
                if (startedAt && (Date.now() - startedAt) > 2 * 60 * 60 * 1000) return;

                // prevent loop
                if (location.pathname.indexOf("/order-confirmed") !== -1) return;

                location.href = "/order-confirmed/?session=" + encodeURIComponent(token);
            } catch (e) {}
        })();'
    );
}
add_action('wp_enqueue_scripts', 'add_checkout_redirect_script');


// Clear the flags on /order-confirmed/ page
function clear_checkout_storage_on_page_31822_old()
{
    if (! is_page(31822)) {
        return;
    }

    // Register an empty footer script
    wp_register_script(
        'clear-checkout-storage',
        false,
        array(),
        null,
        true
    );

    wp_enqueue_script('clear-checkout-storage');

    wp_add_inline_script(
        'clear-checkout-storage',
        'try {
            localStorage.removeItem("wp_checkout_started");
            localStorage.removeItem("wp_checkout_started_at");
            // optionally clear token after successful display
            // localStorage.removeItem("wp_checkout_session");
        } catch(e) {}'
    );
}
// add_action('wp_enqueue_scripts', 'clear_checkout_storage_on_page_31822');
// 

function clear_checkout_storage_on_page_31822()
{
    if (!is_page(31822)) {
        return;
    }

    wp_register_script(
        'clear-checkout-storage',
        false,
        array(),
        null,
        true
    );

    wp_enqueue_script('clear-checkout-storage');

    wp_add_inline_script(
        'clear-checkout-storage',
        '(function(){

            try {

                var token = new URLSearchParams(window.location.search).get("session");

                if (!token) return;

                // wait until confirmation content exists
                var checkConfirmed = function(){

                    var confirmed = document.querySelector(".order_confirmed");

                    if (confirmed) {

                        localStorage.removeItem("wp_checkout_started");
                        localStorage.removeItem("wp_checkout_started_at");
                        localStorage.removeItem("wp_checkout_session");

                        return;

                    }

                    setTimeout(checkConfirmed, 500);

                };

                checkConfirmed();

            } catch(e){}

        })();'
    );
}
add_action('wp_enqueue_scripts', 'clear_checkout_storage_on_page_31822');
