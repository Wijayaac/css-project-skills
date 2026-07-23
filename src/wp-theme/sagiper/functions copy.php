<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if (!function_exists('chld_thm_cfg_locale_css')):
    function chld_thm_cfg_locale_css($uri)
    {
        if (empty($uri) && is_rtl() && file_exists(get_template_directory() . '/rtl.css'))
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter('locale_stylesheet_uri', 'chld_thm_cfg_locale_css');

if (!function_exists('child_theme_configurator_css')):
    function child_theme_configurator_css()
    {
        wp_enqueue_style('chld_thm_cfg_child', trailingslashit(get_stylesheet_directory_uri()) . 'style.css', array('hello-elementor', 'hello-elementor-theme-style', 'hello-elementor-header-footer'));
    }
endif;
add_action('wp_enqueue_scripts', 'child_theme_configurator_css', 10);

// END ENQUEUE PARENT ACTION

// ===== Verlin - 12 creative dev - Code start
define('THEME_DIR', get_template_directory() . '');
define('THEME_URL', get_template_directory_uri());
define('THEME_URL_ASSETS', THEME_URL . '-child/assets');
define('BASE_URL', get_site_url());

// Load all custom function files.
$custom_files = [
    'woo-custom-functions.php',
    'woo-shopify-functions.php'
];

foreach ($custom_files as $file) {
    $file_path = THEME_DIR . '-child/inc/' . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}

function support_assets()
{
    wp_enqueue_style('styless', THEME_URL_ASSETS . '/css/custom.css', array(), null);
    // wp_enqueue_style('responsive_styles', THEME_URL_ASSETS . '/css/responsive.css', array(), null);
    wp_enqueue_script('script', THEME_URL_ASSETS . '/js/script.js', array('jquery'), null);
    wp_localize_script('script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('wuhu_spicy_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'support_assets');


if (!function_exists("show_custom_drawer")) {
    add_shortcode('show_custom_drawer', 'show_custom_drawer');
    function show_custom_drawer()
    {
        $html = '<div id="shopify-cart-drawer">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <strong>Your Cart</strong>
                        <button type="button" id="cart-close" aria-label="Close">×</button>
                    </div>
                    <div id="cart-lines"></div>
                    <div class="checkout-container flex space-between">
                        <button type="button" id="cart-view" class="button">Refresh</button>
                        <button type="button" id="cart-checkout" class="button alt">Checkout</button>
                    </div>
                </div>
                <button id="cart-toggle" style="position:fixed;right:20px;bottom:20px;padding:10px 14px;z-index:9998;">Cart</button>
            ';
        return $html;
    }
}

if (!function_exists("get_quickview_product")) {
    add_shortcode('get_quickview_product', 'get_quickview_product');
    function get_quickview_product()
    {
        $id = 78;
        $html = "";
        if (!empty($id) && $id > 0) {
            $product = wc_get_product($id);

            if ($product) {
                $form       = render_quickview_variation_form($product);
                $name       = $product->get_name();
                $imgId      = $product->get_image_id(); // Main image
                $picture    = wp_get_attachment_url($imgId);
                $description = $product->get_description();

                if ($product->is_type('variable')) {
                    $min_price = $product->get_variation_price('min', true);
                    $max_price = $product->get_variation_price('max', true);

                    $price_range = wc_format_price_range($min_price, $max_price);

                    $price = $price_range;
                } else {
                    $price = $product->get_price_html();
                }

                if ($picture) {
                    $picture = "<img src='$picture' alt='$name'>";
                }

                $html = "
                <div class='quickview_container'>
                    <div class='image_container'>
                    $picture
                    </div>
                    <div class='content_container'>
                        <p class='woo-title'>$name</p>
                        <p class='woo-price'>$price</p>
                        $form
                        <p>$description</p>
                    </div>
                </div>
                ";
            }
        }

        return $html;
    }

    function render_quickview_variation_form($product)
    {
        if (! $product || ! $product->is_type('variable')) {
            return;
        }
        $imgId      = $product->get_image_id();
        $picture    = wp_get_attachment_url($imgId);
        $pictureField = "";
        if ($picture) {
            $pictureField = "<input type='hidden' value='$picture' id='picture-field' name='picture-field'>";
        }
        $attributes = $product->get_variation_attributes();
        $available_variations = $product->get_available_variations();
        $available_variations = array_map(function ($variation) {
            // Remove the image array
            if (isset($variation['image'])) {
                unset($variation['image']);
            }

            return $variation;
        }, $available_variations);
        $variations_json = wp_json_encode($available_variations);
        $variations_attr = function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true);

        ob_start(); ?>

        <form class="variations_form cart" action="<?php echo esc_url($product->get_permalink()); ?>" method="post"
            enctype="multipart/form-data" data-product_id="<?php echo esc_attr($product->get_id()); ?>"
            data-product_variations="<?php echo esc_attr($variations_attr); ?>">
            <?= $pictureField; ?>
            <div class="variations" cellspacing="0">
                <?php foreach ($attributes as $attribute_name => $options) : ?>
                    <div>
                        <div class="label">
                            <label for="<?php echo esc_attr(sanitize_title($attribute_name)); ?>">
                                <?php echo wc_attribute_label($attribute_name); ?>
                            </label>
                        </div>
                        <div class="value">
                            <?php
                            wc_dropdown_variation_attribute_options([
                                'options'   => $options,
                                'attribute' => $attribute_name,
                                'product'   => $product,
                                'show_option_none' => __('Choose an option', 'woocommerce'),
                            ]);
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="single_variation_wrap">
                <?php
                /**
                 * These hooks output:
                 * - price (inside .single_variation)
                 * - stock info
                 * - add to cart button
                 */
                do_action('woocommerce_before_single_variation');

                echo '<div class="woocommerce-variation single_variation"></div>'; // Price + variation details

                do_action('woocommerce_before_add_to_cart_button');

                echo '<div class="woocommerce-variation-add-to-cart variations_button">';
                woocommerce_quantity_input();
                echo '<button type="submit" class="single_add_to_cart_button button alt">';
                esc_html_e('Add to cart', 'woocommerce');
                echo '</button>';
                echo '</div>';

                do_action('woocommerce_after_add_to_cart_button');
                do_action('woocommerce_after_single_variation');
                ?>
            </div>
        </form>

<?php
        return ob_get_clean();
    }
}

add_action('wp_enqueue_scripts', 'my_enqueue_wc_variation_script');
function my_enqueue_wc_variation_script()
{
    if (class_exists('WooCommerce')) {
        wp_enqueue_script('jquery');
        wp_enqueue_script('wc-add-to-cart');
        wp_enqueue_script('wc-add-to-cart-variation');
        wp_enqueue_script('wc-single-product');
    }
}
