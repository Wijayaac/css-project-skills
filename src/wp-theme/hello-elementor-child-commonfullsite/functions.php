<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'hello-elementor','hello-elementor-theme-style','hello-elementor-header-footer' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

// END ENQUEUE PARENT ACTION

/**
 * Boot custom Elementor widgets (ACF Features List, etc.).
 */
$cfs_elementor_widgets_file = get_stylesheet_directory() . '/inc/elementor/class-widgets-loader.php';
if ( file_exists( $cfs_elementor_widgets_file ) ) {
	require_once $cfs_elementor_widgets_file;
}

/**
 * Register Features List stylesheet for Elementor widget dependency.
 *
 * @return void
 */
function cfs_features_list_register_styles(): void {
	wp_register_style(
		'cfs-features-list',
		get_stylesheet_directory_uri() . '/assets/css/features-list.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'cfs_features_list_register_styles' );
add_action( 'elementor/frontend/after_register_styles', 'cfs_features_list_register_styles' );

/**
 * Register Floorplan Gallery assets for Elementor widget dependency.
 *
 * @return void
 */
function cfs_floorplan_gallery_register_assets(): void {
	$version = wp_get_theme()->get( 'Version' );

	wp_register_style(
		'cfs-floorplan-gallery',
		get_stylesheet_directory_uri() . '/assets/css/floorplan-gallery.css',
		array(),
		$version
	);

	wp_register_script(
		'cfs-floorplan-gallery',
		get_stylesheet_directory_uri() . '/assets/js/floorplan-gallery.js',
		array(),
		$version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'cfs_floorplan_gallery_register_assets' );
add_action( 'elementor/frontend/after_register_styles', 'cfs_floorplan_gallery_register_assets' );
add_action( 'elementor/frontend/after_register_scripts', 'cfs_floorplan_gallery_register_assets' );

/**
 * Register Room Dimensions stylesheet for Elementor widget dependency.
 *
 * @return void
 */
function cfs_room_dimensions_register_styles(): void {
	wp_register_style(
		'cfs-room-dimensions',
		get_stylesheet_directory_uri() . '/assets/css/room-dimensions.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'cfs_room_dimensions_register_styles' );
add_action( 'elementor/frontend/after_register_styles', 'cfs_room_dimensions_register_styles' );

// add smooth scrolling lenis
function add_lenis_script_to_footer()
{
    // Enqueue Lenis
    wp_enqueue_script(
        'lenis',
        'https://unpkg.com/lenis@1.1.2/dist/lenis.min.js',
        array('jquery'),
        null,
        true // load in footer
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
		
		// scroll header 
		jQuery(function ($) {
			let lastScrollTop = 0;
			$(window).on('scroll', function () {
				let scrollTop = $(this).scrollTop();
				if (scrollTop > 30) {
					$('.header_container').addClass('is-scrolled');
				} else {
					$('.header_container').removeClass('is-scrolled');
				}

				lastScrollTop = scrollTop;

			});
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
		
		.header_container.is-scrolled .transparant_header {
			display: none !important;
		}
		
		.header_container.is-scrolled .colored_header{
			display: flex !important;
		}
		
		.orange {
			color: #C36645;
		}

		.slider_image .swiper-wrapper {
			transition-timing-function: linear !important;
		}

		.slider_image img {
			height: 388px;
			object-fit: cover;
			width: 100%;
		}
		
		.zoom_image_on_hover:hover .image_container{
			transform: scale(1.1);
		}

		.height_100 {
			height: 100% !important;
			width: 100%;
			z-index: 2;
			display: flex;
			opacity: 0;
		}

		.elementor-slideshow__title{
			display: none !important;
		}

		@media (max-width: 1024px) {
			.slider_image img {
				height: 240px;
			}
		}
    </style>

<?php
}
add_action('wp_footer', 'add_lenis_script_to_footer');

add_filter('script_loader_tag', function ($tag, $handle, $src) {

    if ($handle === 'lenis') {
        return '<script src="' . esc_url($src) . '" defer></script>';
    }

    return $tag;
}, 10, 3);
