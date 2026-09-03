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
function mh_acf_json_save_path( $path ) {
	return get_stylesheet_directory() . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'mh_acf_json_save_path' );

/**
 * Load ACF JSON from the child theme.
 *
 * @param string[] $paths Load paths.
 * @return string[]
 */
function mh_acf_json_load_paths( $paths ) {
	$paths[] = get_stylesheet_directory() . '/acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'mh_acf_json_load_paths' );

$mh_reviews_shortcode_file = get_stylesheet_directory() . '/inc/reviews-shortcode.php';
if ( file_exists( $mh_reviews_shortcode_file ) ) {
	require_once $mh_reviews_shortcode_file;
}

$mh_featured_services_shortcode_file = get_stylesheet_directory() . '/inc/featured-services-shortcode.php';
if ( file_exists( $mh_featured_services_shortcode_file ) ) {
	require_once $mh_featured_services_shortcode_file;
}

/**
 * Detect whether the current request needs review assets.
 *
 * @return bool
 */
function mh_reviews_should_enqueue_assets() {
	if ( ! empty( $GLOBALS['mh_reviews_shortcode_used'] ) ) {
		return true;
	}

	if ( ! is_singular() ) {
		return false;
	}

	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	if ( has_shortcode( $post->post_content, 'meyer_reviews' ) ) {
		return true;
	}

	$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
	if ( is_string( $elementor_data ) && str_contains( $elementor_data, 'meyer_reviews' ) ) {
		return true;
	}

	return false;
}

/**
 * Detect whether the current request needs featured services assets.
 *
 * @return bool
 */
function mh_featured_services_should_enqueue_assets() {
	if ( ! empty( $GLOBALS['mh_featured_services_used'] ) ) {
		return true;
	}

	if ( ! is_singular() ) {
		return false;
	}

	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	if ( has_shortcode( $post->post_content, 'meyer_featured_services' ) ) {
		return true;
	}

	$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
	if ( is_string( $elementor_data ) && str_contains( $elementor_data, 'meyer_featured_services' ) ) {
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
		array(
			'hello-elementor-theme-style',
		),
		HELLO_ELEMENTOR_CHILD_VERSION
	);

	wp_enqueue_script(
		'hello-elementor-child-swiper-offset',
		get_stylesheet_directory_uri() . '/assets/js/swiper-offset.js',
		array(),
		HELLO_ELEMENTOR_CHILD_VERSION,
		true
	);

	wp_enqueue_script(
		'hello-elementor-child-header-scroll',
		get_stylesheet_directory_uri() . '/assets/js/header-scroll.js',
		array(),
		HELLO_ELEMENTOR_CHILD_VERSION,
		true
	);

}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );

/**
 * Enqueue featured services assets when the shortcode is present.
 *
 * @return void
 */
function mh_featured_services_enqueue_assets() {
	if ( ! mh_featured_services_should_enqueue_assets() ) {
		return;
	}

	wp_enqueue_style(
		'mh-featured-services',
		get_stylesheet_directory_uri() . '/assets/css/featured-services.css',
		[],
		HELLO_ELEMENTOR_CHILD_VERSION
	);

	wp_enqueue_script(
		'mh-featured-services',
		get_stylesheet_directory_uri() . '/assets/js/featured-services.js',
		[],
		HELLO_ELEMENTOR_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'mh_featured_services_enqueue_assets', 30 );

/**
 * Enqueue review masonry assets when the shortcode is present.
 *
 * @return void
 */
function mh_reviews_enqueue_assets() {
	if ( ! mh_reviews_should_enqueue_assets() ) {
		return;
	}

	wp_enqueue_style(
		'mh-reviews',
		get_stylesheet_directory_uri() . '/assets/css/reviews.css',
		array(),
		HELLO_ELEMENTOR_CHILD_VERSION
	);

	wp_enqueue_script(
		'mh-reviews-scroll',
		get_stylesheet_directory_uri() . '/assets/js/reviews-scroll.js',
		array(),
		HELLO_ELEMENTOR_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'mh_reviews_enqueue_assets', 30 );
