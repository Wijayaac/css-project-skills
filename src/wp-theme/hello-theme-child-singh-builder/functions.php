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
 * Point ACF JSON sync to the child theme.
 *
 * @param string $path Default save path.
 * @return string
 */
function sb_acf_json_save_path( $path ) {
	return get_stylesheet_directory() . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'sb_acf_json_save_path' );

/**
 * Load ACF JSON from the child theme.
 *
 * @param string[] $paths Load paths.
 * @return string[]
 */
function sb_acf_json_load_paths( $paths ) {
	$paths[] = get_stylesheet_directory() . '/acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'sb_acf_json_load_paths' );

$sb_expandable_shortcode_file = get_stylesheet_directory() . '/inc/expandable-content-shortcode.php';
if ( file_exists( $sb_expandable_shortcode_file ) ) {
	require_once $sb_expandable_shortcode_file;
}

/**
 * Detect whether the current request needs expandable content assets.
 *
 * @return bool
 */
function sb_expandable_should_enqueue_assets() {
	if ( ! empty( $GLOBALS['sb_expandable_used'] ) ) {
		return true;
	}

	if ( ! is_singular() ) {
		return false;
	}

	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	if ( has_shortcode( $post->post_content, 'singh_expandable_content' ) ) {
		return true;
	}

	$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
	if ( is_string( $elementor_data ) && str_contains( $elementor_data, 'singh_expandable_content' ) ) {
		return true;
	}

	return false;
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

}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );

/**
 * Enqueue expandable content assets when the shortcode is present.
 *
 * @return void
 */
function sb_expandable_enqueue_assets() {
	if ( ! sb_expandable_should_enqueue_assets() ) {
		return;
	}

	wp_enqueue_style(
		'sb-expandable-content',
		get_stylesheet_directory_uri() . '/assets/css/expandable-content.css',
		[],
		HELLO_ELEMENTOR_CHILD_VERSION
	);

	wp_enqueue_script(
		'sb-expandable-content',
		get_stylesheet_directory_uri() . '/assets/js/expandable-content.js',
		[],
		HELLO_ELEMENTOR_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'sb_expandable_enqueue_assets', 30 );
