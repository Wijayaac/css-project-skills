<?php

/**
 * Theme functions and definitions.
 *
 * @package Copperwood
 */

if (! defined('ABSPATH')) {
	exit;
}

if (! defined('WP_POST_REVISIONS')) {
	define('WP_POST_REVISIONS', 5);
}

define('COPPERWOOD_CHILD_VERSION', '1.0.0');
define('COPPERWOOD_THEME_ASSETS', get_stylesheet_directory_uri() . '/assets');

$copperwood_floorplans_file = get_stylesheet_directory() . '/inc/copperwood-floorplans.php';
$copperwood_map_file        = get_stylesheet_directory() . '/inc/copperwood-map.php';

if (file_exists($copperwood_floorplans_file)) {
	require_once $copperwood_floorplans_file;
}

if (file_exists($copperwood_map_file)) {
	require_once $copperwood_map_file;
}

add_filter('acf/settings/save_json', function ($path) {
	return get_stylesheet_directory() . '/acf-json';
});

add_filter('acf/settings/load_json', function ($paths) {
	$paths[] = get_stylesheet_directory() . '/acf-json';

	return $paths;
});

if (! function_exists('chld_thm_cfg_locale_css')) :
	function chld_thm_cfg_locale_css($uri)
	{
		if (empty($uri) && is_rtl() && file_exists(get_template_directory() . '/rtl.css')) {
			$uri = get_template_directory_uri() . '/rtl.css';
		}

		return $uri;
	}
endif;
add_filter('locale_stylesheet_uri', 'chld_thm_cfg_locale_css');

if (! function_exists('child_theme_configurator_css')) :
	function child_theme_configurator_css()
	{
		wp_enqueue_style(
			'chld_thm_cfg_child',
			trailingslashit(get_stylesheet_directory_uri()) . 'style.css',
			array('hello-elementor', 'hello-elementor-theme-style', 'hello-elementor-header-footer'),
			COPPERWOOD_CHILD_VERSION
		);
	}
endif;
add_action('wp_enqueue_scripts', 'child_theme_configurator_css', 10);

function copperwood_support_assets()
{
	wp_enqueue_style(
		'copperwood_custom_style',
		COPPERWOOD_THEME_ASSETS . '/css/custom.css',
		array('chld_thm_cfg_child'),
		COPPERWOOD_CHILD_VERSION
	);
	$script_deps = array('jquery');

	if (wp_script_is('elementor-frontend', 'registered')) {
		$script_deps[] = 'elementor-frontend';
	}

	wp_enqueue_script(
		'copperwood_custom',
		COPPERWOOD_THEME_ASSETS . '/js/script.js',
		$script_deps,
		COPPERWOOD_CHILD_VERSION,
		true
	);
}
add_action('wp_enqueue_scripts', 'copperwood_support_assets');

/**
 * Replace login logo with Customizer logo (Hello Elementor).
 */
function child_theme_login_logo()
{
	$custom_logo_id = get_theme_mod('custom_logo');

	if (! $custom_logo_id) {
		return;
	}

	$logo = wp_get_attachment_image_src($custom_logo_id, 'full');

	if (! $logo) {
		return;
	}

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
