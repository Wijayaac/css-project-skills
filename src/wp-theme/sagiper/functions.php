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
    'woo-shopify-functions.php',
    'shortcode-register.php'
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
    wp_enqueue_script('script', THEME_URL_ASSETS . '/js/script.js', array('jquery'), null);
    wp_localize_script('script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('wuhu_spicy_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'support_assets');

function allow_dwg_upload( $mimes ) {
    $mimes['dwg'] = 'application/acad';
    return $mimes;
}
add_filter( 'upload_mimes', 'allow_dwg_upload' );

function allow_dwg_real_mime( $data, $file, $filename, $mimes, $real_mime ) {
    $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

    if ( $ext === 'dwg' ) {
        $data['ext'] = 'dwg';
        $data['type'] = 'application/acad';
    }

    return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'allow_dwg_real_mime', 10, 5 );


if (!function_exists("show_custom_drawer")) {
    add_shortcode('show_custom_drawer', 'show_custom_drawer');
    function show_custom_drawer()
    {
        $svgPath = get_stylesheet_directory() . '/assets/images/cart.svg';
        $icon = '';
        $sampleUrl = home_url('/request-samples');

        if (file_exists($svgPath)) {
            $icon = file_get_contents($svgPath);
        } else {
            $icon = 'Cart';
        }


        $html = '<div id="shopify-cart-drawer">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <strong>Your Cart</strong>
                        <button type="button" id="cart-close" aria-label="Close">×</button>
                    </div>
                    <div class="shopify-cart-inner">
                        <div id="cart-lines"></div>
                        <div class="checkout-container flex space-between">
                            <button type="button" id="cart-view" class="button">Refresh</button>
                            <button type="button" id="cart-checkout" class="button alt">Checkout</button>
                        </div>
                        <div class="continue-container space-between">
                            <button type="button" id="continue-shopping" class="button alt "><a href="' . $sampleUrl . '" aria-label="request sample">REQUEST SAMPLES</a></button>
                        </div>
                    </div>
                </div>
                <button id="cart-toggle" style="z-index:9998;"><span class="number" style="display: none;"></span>' . $icon . '</button>
            ';
        return $html;
    }
}

if (!function_exists("fetch_quickview_product_callback")) {
    // add_shortcode('get_quickview_product', 'get_quickview_product');

    add_action('wp_ajax_quickview_product', 'fetch_quickview_product_callback');
    add_action('wp_ajax_nopriv_quickview_product', 'fetch_quickview_product_callback');
    function fetch_quickview_product_callback()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wuhu_spicy_nonce')) {
            wp_send_json_error(array('error' => 'Invalid nonce.'));
        }

        $id = $_POST['productId'];
        $html = "";

        if (!empty($id) && $id > 0) {
            $product = wc_get_product($id);

            if ($product) {
                $form       = render_quickview_variation_form($product);
                $name       = $product->get_name();
                $imgId      = $product->get_image_id(); // Main image
                $picture    = wp_get_attachment_url($imgId);
                $description = $product->get_description();
                $descriptionHtml = "";
                if ($description) {
                    $descriptionHtml = "<h2 class='quick-view_title'>DESCRIPTION</h2>";
                    $descriptionHtml .= wp_kses_post($description);
                }

                if ($product->is_type('variable')) {
                    $min_price = $product->get_variation_price('min', true);
                    $max_price = $product->get_variation_price('max', true);

                    if ($min_price === $max_price) {
                        $price = $product->get_price_html();
                    } else {
                        $price_range = wc_format_price_range($min_price, $max_price);
                        $price = $price_range;
                    }
                } else {
                    $price = $product->get_price_html();
                }

                if ($picture) {
                    $picture = "<img src='$picture' alt='$name'>";
                }

                $html = "
                <div class='quickview_container'>
                    <div class='left-content'>
                        <div class='image_container'>
                            $picture
                        </div>
                        <div class='quick-view_description'>
                            $descriptionHtml
                        </div>
                    </div>
                    <div class='content_container'>
                        <p class='woo-title'>$name</p>
                        <p class='woo-price'>$price</p>
                        $form
                    </div>
                </div>
                ";
            }
        }
        wp_send_json_success($html);
        // return $html;

        die();
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
		if (empty($available_variations)) {
            // No variations found, return or handle accordingly
            return '<div class="no-variations">This product is not available.</div>';
        }
        $available_variations = array_map(function ($variation) {
            // Remove heavy/unneeded data
            unset($variation['image']);
            unset($variation['image_id']);

            unset($variation['dimensions']);
            unset($variation['dimensions_html']);

            unset($variation['weight']);
            unset($variation['weight_html']);

            unset($variation['availability_html']);

            unset($variation['sku']);
            unset($variation['variation_description']);
            unset($variation['backorders_allowed']);
            unset($variation['backorders_require_notification']);
            unset($variation['availability']);

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

        <div class="woocommerce-variation single_variation">
            <div class="woocommerce-variation-description"></div>
            <div class="woocommerce-variation-price"></div>
            <div class="woocommerce-variation-availability"></div>
        </div>

        <div class="woocommerce-variation-add-to-cart variations_button">
            <?php woocommerce_quantity_input(); ?>

            <button type="submit" class="single_add_to_cart_button button alt">
                <?php esc_html_e('Add to cart', 'woocommerce'); ?>
            </button>

            <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>">
            <input type="hidden" name="product_id" value="<?php echo esc_attr($product->get_id()); ?>">
            <input type="hidden" name="variation_id" class="variation_id" value="0">
        </div>

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

function my_query_by_post_meta($query)
{

    $meta_query = [
        'relation' => 'OR',

        [
            'relation' => 'AND',
            [
                'key'     => 'project_file_download',
                'compare' => 'EXISTS',
            ],
            [
                'key'     => 'project_file_download',
                'value'   => '',
                'compare' => '!=',
            ],
        ],

        [
            'relation' => 'AND',
            [
                'key'     => 'project_file_url_download',
                'compare' => 'EXISTS',
            ],
            [
                'key'     => 'project_file_url_download',
                'value'   => '',
                'compare' => '!=',
            ],
        ],
    ];

    $query->set('meta_query', $meta_query);
}
add_action('elementor/query/has_pdf', 'my_query_by_post_meta');


if (!function_exists("fetch_get_technical_detail_callback")) {
    add_action('wp_ajax_get_technical_detail', 'fetch_get_technical_detail_callback');
    add_action('wp_ajax_nopriv_get_technical_detail', 'fetch_get_technical_detail_callback');


    function fetch_get_technical_detail_callback()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wuhu_spicy_nonce')) {
            wp_send_json_error(['error' => 'Invalid nonce.']);
        }

        $id = $_POST['itemId'];
        $result = [];

        if ($id) {
            $fileName = get_the_title($id);
            $pdfUrl   = get_field('project_file_download', $id);
            $linkFile = get_field('project_file_url_download', $id);

            $pdfUrlValid = false;

            if ($pdfUrl) {
                $attachment_id = attachment_url_to_postid($pdfUrl);

                if ($attachment_id) {
                    $pdfPath = get_attached_file($attachment_id);

                    if ($pdfPath && file_exists($pdfPath)) {
                        $pdfSize = size_format(filesize($pdfPath));
                        $pdfUrlValid = !empty($pdfSize);
                    }
                }
            }

            $fileUrl = $pdfUrlValid ? $pdfUrl : $linkFile;

            $result[] = [
                "file" => $fileUrl,
                "name" => $fileName
            ];
        }

        wp_send_json_success($result);
    }
}

// // Function to get the woo product as option on ACF field
function acf_load_product_choices($field)
{
    $field['choices'] = array();
    $products = new WP_Query(array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1, // Get all products
        'orderby'        => 'title',
        'order'          => 'ASC',
    ));

    // Add the placeholder FIRST
    $field['choices'][''] = 'Please choose ...';

    if ($products->have_posts()) {
        while ($products->have_posts()) {
            $products->the_post();
            global $post;
            $field['choices'][$post->ID] = get_the_title();
        }
        wp_reset_postdata();
    }

    return $field;
}

add_filter('acf/load_field/name=product_select', 'acf_load_product_choices');

function my_acf_google_map_api($api)
{
    $api['key'] = 'AIzaSyAAcR8xj9zBAlsXFYscwp78Sd4UkuTCEh8';
    return $api;
}
add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');

// add smooth scrolling lenis
function add_lenis_script_to_head()
{

    // Enqueue Lenis
    wp_enqueue_script(
        'lenis',
        'https://unpkg.com/lenis@1.1.2/dist/lenis.min.js',
        array('jquery'),
        null,
        false // load in <head>
    );
    ?>

<script>
jQuery(function($) {
    window.lenis = new Lenis({
        smooth: true,
        prevent: function(node) {
            return $(node).closest(
                '.elementor-popup-modal, .dialog-message, .submenu-container, .bottom_menu'
            ).length;
        }
    });

    // RAF loop
    function raf(time) {
        window.lenis.raf(time);
        requestAnimationFrame(raf);
    }
    requestAnimationFrame(raf);

});
</script>

<style>
html.lenis,
html.lenis body {
    height: auto;
}

.lenis.lenis-smooth {
    scroll-behavior: auto !important;
}

.elementor-popup-modal,
.dialog-message {
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
}
</style>

<?php
}
add_action('wp_head', 'add_lenis_script_to_head');

add_action('elementor/query/sku_sort', function($query) { 
	$query->set('meta_key', '_sku');
	$query->set('orderby', 'meta_value');
	$query->set('order', 'ASC');
});

add_action('elementor/query/brand_priority', function ($query) {
    $tax_query = $query->get('tax_query');

    if (empty($tax_query)) return;

    $selected_brand = null;

    foreach ($tax_query as $tax) {
        if (
            isset($tax['taxonomy']) &&
            $tax['taxonomy'] === 'product_brand' &&
            !empty($tax['terms'])
        ) {
            $selected_brand = is_array($tax['terms'])
                ? $tax['terms'][0]
                : $tax['terms'];
            break;
        }
    }

    if (!$selected_brand) return;

    add_filter('posts_clauses', function ($clauses) use ($selected_brand) {
        global $wpdb;

        $selected_brand = esc_sql($selected_brand);

        $clauses['join'] .= "
            LEFT JOIN {$wpdb->term_relationships} trp ON ({$wpdb->posts}.ID = trp.object_id)
            LEFT JOIN {$wpdb->term_taxonomy} ttp ON (trp.term_taxonomy_id = ttp.term_taxonomy_id)
            LEFT JOIN {$wpdb->terms} tp ON (ttp.term_id = tp.term_id)
        ";

        $clauses['where'] .= " AND ttp.taxonomy = 'product_brand' ";

        $clauses['orderby'] = "
            (tp.slug = '{$selected_brand}') DESC,
            {$wpdb->posts}.menu_order ASC
        ";

        return $clauses;
    });
});

add_action( 'elementor/query/project_perfamily', function( $query ) {

    // Get current page slug
    $current_slug = get_post_field( 'post_name', get_queried_object_id() );

    if ( ! $current_slug ) {
        return;
    }

    // Modify query to filter by taxonomy term slug
    $query->set( 'tax_query', [
        [
            'taxonomy' => 'product-family',
            'field'    => 'slug',
            'terms'    => $current_slug,
        ]
    ]);

});

// add product family in the product used 
function acf_load_product_family_choices( $field ) {

    $field['choices'] = array();
    $field['choices'][''] = 'Please choose family...';

    $terms = get_terms(array(
        'taxonomy'   => 'product-family',
        'hide_empty' => false,
    ));
    if ( ! empty($terms) && ! is_wp_error($terms) ) {
        foreach ( $terms as $term ) {
            $url = get_field('url', 'product-family_' . $term->term_id);
            $data = array(
                'id'   => $term->term_id,
                'name' => $term->name,
                'url'  => $url ? $url : '',
            );
            $value = wp_json_encode($data);
            $field['choices'][$value] = $term->name;
        }
    }

    return $field;
}

add_filter('acf/load_field/name=family_select', 'acf_load_product_family_choices');


add_action('template_redirect', function () {
    if (is_404()) {
        wp_redirect(home_url('/'), 301);
        exit;
    }
});

add_action('wp_enqueue_scripts', function() {
    if (is_front_page()) {

        // WooCommerce styles
        wp_dequeue_style('woocommerce-general');
        wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('woocommerce-smallscreen');

        // WooCommerce scripts
        wp_dequeue_script('wc-add-to-cart');
        wp_dequeue_script('wc-cart-fragments');
        wp_dequeue_script('woocommerce');

    }
}, 99);

add_action('template_redirect', function() {

    if (class_exists('\Elementor\Plugin')) {
        add_filter('searchatlas_otto_allow_rewrite', '__return_false');
    }

});


function get_shopify_session_id()
{
    if (!isset($_GET['session'])) {
        return '';
    }

    return sanitize_text_field($_GET['session']);
}


function protect_order_confirmed_page()
{
    if (!is_page(31822)) {
        return;
    }

    if (!isset($_GET['session']) || empty($_GET['session'])) {

        ?>
<script>
(function() {

    try {

        var wpSession = localStorage.getItem("_wp_session");

        if (wpSession) {

            window.location.href =
                "/order-confirmed/?session=" +
                encodeURIComponent(wpSession);

            return;
        }

        // no session, go home
        window.location.href = "/";

    } catch (e) {

        window.location.href = "/";

    }

})();
</script>
<?php

        exit;

    }
}
add_action('template_redirect', 'protect_order_confirmed_page');

add_action('init', function () {
    register_taxonomy_for_object_type('product_brand', 'inventory');
}, 20);

/**
 * Replace CPT permalink with taxonomy slug.
 */
add_filter('post_type_link', function ($post_link, $post) {

    if ($post->post_type !== 'knowledge-center') {
        return $post_link;
    }

    $terms = get_the_terms($post->ID, 'knowledge-topic');

    if (!empty($terms) && !is_wp_error($terms)) {
        $term = reset($terms);

        return home_url(
            '/knowledge-center/' .
            $term->slug .
            '/' .
            $post->post_name .
            '/'
        );
    }

    // Fallback if no topic selected
    return home_url('/knowledge-center/' . $post->post_name . '/');

}, 10, 2);

/**
 * Support:
 * /knowledge-center/topic/article/
 */
add_action('init', function () {

    add_rewrite_rule(
        '^knowledge-center/([^/]+)/([^/]+)/?$',
        'index.php?post_type=knowledge-center&name=$matches[2]',
        'top'
    );

});

/**
 * Allow SVG uploads
 */
function allow_svg_uploads($mimes) {
    $mimes['svg']  = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';

    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_uploads');

function elementor_loop_reading_time() {
    // Dynamically targets the correct post ID inside the Elementor Loop Builder
    $post_id = get_the_ID();
    $content = get_post_field('post_content', $post_id);
    
    // Strip HTML tags and calculate word count
    $word_count = str_word_count(strip_tags($content));
    
    // Calculate minutes based on an average speed of 200 words per minute
    $reading_time = ceil($word_count / 200);
    
    // Format text output based on singular/plural minutes
    if ($reading_time <= 1) {
        $output = '1 min read';
    } else {
        $output = $reading_time . ' mins read';
    }
    
    return $output;
}
add_shortcode('loop_read_time', 'elementor_loop_reading_time');
