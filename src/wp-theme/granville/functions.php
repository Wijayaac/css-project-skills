<?php
/**
 * Theme functions and definitions
 */

/**
 * Limit post revisions to 5
 */
if (!defined('WP_POST_REVISIONS')) {
    define('WP_POST_REVISIONS', 5);
}

/**
 * Replace login logo with Customizer logo (Hello Elementor)
 */
function child_theme_login_logo() {

    // Get logo ID from customizer
    $custom_logo_id = get_theme_mod('custom_logo');

    if (!$custom_logo_id) return;

    // Get logo URL
    $logo = wp_get_attachment_image_src($custom_logo_id, 'full');

    if (!$logo) return;

    $logo_url = esc_url($logo[0]);
    ?>

    <style type="text/css">
        body.login h1 a {
            background-image: url('<?php echo $logo_url; ?>') !important;
            background-size: contain !important;
            background-position: center center;
            background-repeat: no-repeat;
            width: 100%;
            height: 90px;
        }
    </style>

    <?php
}
add_action('login_enqueue_scripts', 'child_theme_login_logo');

define('GRANVILLE_CHILD_VERSION', '1.0.4');

$granville_floorplans_file = get_stylesheet_directory() . '/inc/granville-floorplans.php';

if (file_exists($granville_floorplans_file)) {
	require_once $granville_floorplans_file;
}

if (function_exists('acf')) {
	add_filter('acf/settings/save_json', function ($path) {
		return get_stylesheet_directory() . '/acf-json';
	});

	add_filter('acf/settings/load_json', function ($paths) {
		$paths[] = get_stylesheet_directory() . '/acf-json';

		return $paths;
	});
}

function granville_enqueue_assets()
{
	wp_enqueue_style(
		'granville-custom',
		get_stylesheet_directory_uri() . '/assets/css/custom.css',
		array(),
		GRANVILLE_CHILD_VERSION
	);

	$script_deps = array('jquery');

	if (wp_script_is('elementor-frontend', 'registered')) {
		$script_deps[] = 'elementor-frontend';
	}

	wp_enqueue_script(
		'granville-custom',
		get_stylesheet_directory_uri() . '/assets/js/script.js',
		$script_deps,
		GRANVILLE_CHILD_VERSION,
		true
	);
}
add_action('wp_enqueue_scripts', 'granville_enqueue_assets');
