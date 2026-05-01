<?php

/**
 * Hello Elementor Child functions.
 *
 * @package Hello_Elementor_Child
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Enqueue parent and child styles.
 */
function hello_elementor_child_enqueue_styles()
{
	wp_enqueue_style(
		'hello-elementor-parent',
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme('hello-elementor')->get('Version')
	);

	wp_enqueue_style(
		'hello-elementor-child',
		get_stylesheet_uri(),
		array('hello-elementor-parent'),
		wp_get_theme()->get('Version')
	);

	wp_enqueue_style(
		'hello-elementor-child-custom',
		get_stylesheet_directory_uri() . '/assets/css/custom.css',
		array('hello-elementor-child'),
		wp_get_theme()->get('Version')
	);

	wp_enqueue_script(
		'hello-elementor-child-script',
		get_stylesheet_directory_uri() . '/assets/js/script.js',
		array(),
		wp_get_theme()->get('Version'),
		true
	);
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles');
