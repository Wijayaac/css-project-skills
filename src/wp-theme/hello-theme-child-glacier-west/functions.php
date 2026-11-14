<?php
/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0' );

/**
 * Glacier West services, projects & testimonials (post types, taxonomy and fields load from acf-json/; needs ACF).
 */
$gw_cpt_file = get_stylesheet_directory() . '/inc/glacier-west-cpt.php';
if ( class_exists( 'ACF' ) && file_exists( $gw_cpt_file ) ) {
	require_once $gw_cpt_file;
}

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function hello_elementor_child_scripts_styles() {

	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		HELLO_ELEMENTOR_CHILD_VERSION
	);

	// Site-wide template & component CSS (copied from Elementor Pro custom_css). Version = file time, so edits bust the cache.
	$gw_templates_css = get_stylesheet_directory() . '/assets/css/glacier-west-templates.css';
	if ( file_exists( $gw_templates_css ) ) {
		wp_enqueue_style(
			'hello-elementor-child-glacier-west-templates',
			get_stylesheet_directory_uri() . '/assets/css/glacier-west-templates.css',
			[
				'hello-elementor-child-style',
			],
			(string) filemtime( $gw_templates_css )
		);
	}

	// Button hover sweep (.gw-btn-sweep), site-wide. Version = file time, so edits bust the cache.
	$gw_buttons_css = get_stylesheet_directory() . '/assets/css/glacier-west-buttons.css';
	if ( file_exists( $gw_buttons_css ) ) {
		wp_enqueue_style(
			'hello-elementor-child-glacier-west-buttons',
			get_stylesheet_directory_uri() . '/assets/css/glacier-west-buttons.css',
			[
				'hello-elementor-child-style',
			],
			(string) filemtime( $gw_buttons_css )
		);
	}

	wp_enqueue_script(
		'hello-elementor-child-header-scroll',
		get_stylesheet_directory_uri() . '/assets/js/header-scroll.js',
		[],
		HELLO_ELEMENTOR_CHILD_VERSION,
		true
	);

}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );
