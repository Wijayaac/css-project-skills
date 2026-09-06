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

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.1.2' );

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

$mh_elementor_widgets_file = get_stylesheet_directory() . '/inc/elementor/class-widgets-loader.php';
if ( file_exists( $mh_elementor_widgets_file ) ) {
	require_once $mh_elementor_widgets_file;
}

/**
 * Detect whether the current request needs review / testimonials assets.
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
	if ( ! is_string( $elementor_data ) || $elementor_data === '' ) {
		return false;
	}

	$markers = array(
		'meyer_reviews',
		'mh_testimonials_masonry',
		'mh-reviews',
	);

	foreach ( $markers as $marker ) {
		if ( str_contains( $elementor_data, $marker ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Register Testimonials Masonry assets (widget pulls via get_*_depends).
 *
 * @return void
 */
function mh_reviews_register_assets() {
	wp_register_style(
		'mh-reviews',
		get_stylesheet_directory_uri() . '/assets/css/reviews.css',
		array( 'hello-elementor-child-style' ),
		HELLO_ELEMENTOR_CHILD_VERSION
	);

	wp_register_script(
		'mh-reviews-scroll',
		get_stylesheet_directory_uri() . '/assets/js/reviews-scroll.js',
		array( 'mh-lenis-init' ),
		HELLO_ELEMENTOR_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'mh_reviews_register_assets', 5 );
add_action( 'elementor/frontend/after_register_styles', 'mh_reviews_register_assets' );
add_action( 'elementor/frontend/after_register_scripts', 'mh_reviews_register_assets' );

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
 * Register ACF FAQ accordion script for Elementor widget dependency.
 *
 * @return void
 */
function mh_acf_faq_register_scripts() {
	wp_register_script(
		'mh-acf-faq-accordion',
		get_stylesheet_directory_uri() . '/assets/js/acf-faq-accordion.js',
		array(),
		HELLO_ELEMENTOR_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'mh_acf_faq_register_scripts', 5 );
add_action( 'elementor/frontend/after_register_scripts', 'mh_acf_faq_register_scripts' );

/**
 * Register Project Gallery assets (widget pulls via get_*_depends).
 *
 * @return void
 */
function mh_project_gallery_register_assets() {
	wp_register_style(
		'mh-project-gallery',
		get_stylesheet_directory_uri() . '/assets/css/project-gallery.css',
		array( 'hello-elementor-child-style' ),
		HELLO_ELEMENTOR_CHILD_VERSION
	);

	wp_register_script(
		'mh-project-gallery',
		get_stylesheet_directory_uri() . '/assets/js/project-gallery.js',
		array(),
		HELLO_ELEMENTOR_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'mh_project_gallery_register_assets', 5 );
add_action( 'elementor/frontend/after_register_styles', 'mh_project_gallery_register_assets' );
add_action( 'elementor/frontend/after_register_scripts', 'mh_project_gallery_register_assets' );

/**
 * Register Project Partners assets (widget pulls via get_style_depends).
 *
 * @return void
 */
function mh_project_partners_register_assets() {
	wp_register_style(
		'mh-project-partners',
		get_stylesheet_directory_uri() . '/assets/css/project-partners.css',
		array( 'hello-elementor-child-style' ),
		HELLO_ELEMENTOR_CHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'mh_project_partners_register_assets', 5 );
add_action( 'elementor/frontend/after_register_styles', 'mh_project_partners_register_assets' );

/**
 * Register service-details stylesheet (widgets declare it via get_style_depends).
 *
 * @return void
 */
function mh_service_details_register_styles() {
	wp_register_style(
		'mh-service-details',
		get_stylesheet_directory_uri() . '/assets/css/service-details.css',
		array( 'hello-elementor-child-style' ),
		HELLO_ELEMENTOR_CHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'mh_service_details_register_styles', 5 );
add_action( 'elementor/frontend/after_register_styles', 'mh_service_details_register_styles' );

/**
 * Detect whether the current request needs service-details CSS.
 *
 * Primary: singular `service` CPT. Fallback: Elementor data contains related
 * CSS classes or custom widgets (covers reuse outside the CPT).
 *
 * @return bool
 */
function mh_service_details_should_enqueue_assets() {
	if ( is_singular( 'service' ) ) {
		return true;
	}

	if ( ! is_singular() ) {
		return false;
	}

	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
	if ( ! is_string( $elementor_data ) || $elementor_data === '' ) {
		return false;
	}

	$markers = array(
		'carousel-faded',
		'container-accordion-align',
		'container-numbering-items',
		'mh_acf_faq_accordion',
		'mh_acf_image_list_badge',
		'mh-faq-accordion',
		'mh-image-list-badge',
	);

	foreach ( $markers as $marker ) {
		if ( str_contains( $elementor_data, $marker ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Enqueue service-details CSS when needed on the frontend.
 *
 * @return void
 */
function mh_service_details_enqueue_assets() {
	if ( ! mh_service_details_should_enqueue_assets() ) {
		return;
	}

	wp_enqueue_style( 'mh-service-details' );
}
add_action( 'wp_enqueue_scripts', 'mh_service_details_enqueue_assets', 30 );

/**
 * Register Lenis smooth-scroll assets.
 *
 * @return void
 */
function mh_lenis_register_assets() {
	wp_register_style(
		'mh-lenis',
		get_stylesheet_directory_uri() . '/assets/css/lenis.css',
		array( 'hello-elementor-child-style' ),
		HELLO_ELEMENTOR_CHILD_VERSION
	);

	wp_register_script(
		'mh-lenis',
		get_stylesheet_directory_uri() . '/assets/js/lenis.min.js',
		array(),
		HELLO_ELEMENTOR_CHILD_VERSION,
		true
	);

	wp_register_script(
		'mh-lenis-init',
		get_stylesheet_directory_uri() . '/assets/js/lenis-init.js',
		array( 'mh-lenis' ),
		HELLO_ELEMENTOR_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'mh_lenis_register_assets', 5 );
add_action( 'elementor/frontend/after_register_styles', 'mh_lenis_register_assets' );
add_action( 'elementor/frontend/after_register_scripts', 'mh_lenis_register_assets' );

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

	wp_enqueue_style( 'mh-lenis' );
	wp_enqueue_script( 'mh-lenis' );
	wp_enqueue_script( 'mh-lenis-init' );

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
		array( 'mh-lenis-init' ),
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
 * Enqueue review masonry assets when the shortcode / widget is present.
 *
 * @return void
 */
function mh_reviews_enqueue_assets() {
	if ( ! mh_reviews_should_enqueue_assets() ) {
		return;
	}

	wp_enqueue_style( 'mh-reviews' );
	wp_enqueue_script( 'mh-reviews-scroll' );
}
add_action( 'wp_enqueue_scripts', 'mh_reviews_enqueue_assets', 30 );
