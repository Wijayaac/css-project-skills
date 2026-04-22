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
// ===== Verlin - 12 creative dev - Code start
define('THEME_DIR', get_template_directory() . '/');
define('THEME_URL', get_template_directory_uri());
define('THEME_URL_ASSETS', THEME_URL . '-child/assets');
define('BASE_URL', get_site_url());

$file_path = get_stylesheet_directory() . '/inc/function-custom-map.php';

if (file_exists($file_path)) {
    require_once $file_path;
}

function support_assets()
{
    wp_enqueue_style('custom_style', THEME_URL_ASSETS . '/css/custom.css', array(), null);
    wp_enqueue_script('custom', THEME_URL_ASSETS . '/js/script.js', array('jquery'), null);
}
add_action('wp_enqueue_scripts', 'support_assets');

function my_google_map_api( $api ){
    $api['key'] = 'AIzaSyAAcR8xj9zBAlsXFYscwp78Sd4UkuTCEh8';
    return $api;
}
add_filter('acf/fields/google_map/api', 'my_google_map_api');


if (! function_exists('get_the_floorplans')) {
	function get_the_floorplans()
	{
		$post_id    = get_the_ID();
		$output     = '';
		$iconLight  = THEME_URL_ASSETS . "/images/ArrowsOut.svg";
		$items      = array();
		static $script_printed = false;

		if (have_rows('floorplans', $post_id)) {
			$index = 0;
			while (have_rows('floorplans', $post_id)) {
				the_row();

				$title = get_sub_field('title');
				$image = get_sub_field('picture');
				$size  = get_sub_field('size_floorplan');

				if (! $image) {
					continue;
				}

				$items[] = array(
					'title' => $title ? $title : 'Floor ' . ($index + 1),
					'image' => $image,
					'size'  => $size,
				);
				$index++;
			}

			if (! empty($items)) {
				$instance = function_exists('wp_unique_id') ? wp_unique_id('floorplans-') : uniqid('floorplans-');
				$slideshow_group = $instance . '-lightbox';
				$output .= '<div class="floorplans floorplans--toggle" data-floorplans-toggle id="' . esc_attr($instance) . '">';
				$output .= '<div class="floorplans__sidebar">';
				$output .= '<h2 class="floorplans__heading">Floorplan</h2>';
				$output .= '<div class="floorplans__tabs" role="tablist" aria-label="Floorplan levels">';

				foreach ($items as $i => $item) {
					$is_active = $i === 0;
					$tab_id = $instance . '-tab-' . $i;
					$panel_id = $instance . '-panel-' . $i;
					$output .= '<button type="button" id="' . esc_attr($tab_id) . '" class="floorplans__tab' . ($is_active ? ' is-active' : '') . '" role="tab" aria-controls="' . esc_attr($panel_id) . '" aria-selected="' . ($is_active ? 'true' : 'false') . '" data-floorplan-index="' . esc_attr((string) $i) . '">' . esc_html($item['title']) . '</button>';
				}

				$output .= '</div>';
				$output .= '</div>';
				$output .= '<div class="floorplans__viewer">';

				foreach ($items as $i => $item) {
					$is_active = $i === 0;
					$tab_id = $instance . '-tab-' . $i;
					$panel_id = $instance . '-panel-' . $i;
					$alt = ! empty($item['image']['alt']) ? $item['image']['alt'] : $item['title'];
					$output .= '<div id="' . esc_attr($panel_id) . '" class="floorplans__panel' . ($is_active ? ' is-active' : '') . '" role="tabpanel" aria-labelledby="' . esc_attr($tab_id) . '"' . ($is_active ? '' : ' hidden') . '>';
					$output .= '<a class="floorplans__media" href="' . esc_url($item['image']['url']) . '" data-elementor-open-lightbox="yes" data-elementor-lightbox-slideshow="' . esc_attr($slideshow_group) . '">';
					$output .= '<img src="' . esc_url($item['image']['url']) . '" alt="' . esc_attr($alt) . '" class="floorplans__image"/>';
					$output .= '<img src="' . esc_url($iconLight) . '" alt="full screen" class="floorplans__icon"/>';
					$output .= '</a>';
					if (! empty($item['size'])) {
						$output .= '<p class="floorplans__size">' . esc_html($item['size']) . ' Sq. ft.</p>';
					}
					$output .= '</div>';
				}

				$output .= '</div>';
				$output .= '</div>';
			}
		}

		if (! empty($items) && ! $script_printed) {
			$script_printed = true;
			$output .= '<script>
				(function () {
					function syncFloorplan(root, index) {
						var tabs = root.querySelectorAll(".floorplans__tab");
						var panels = root.querySelectorAll(".floorplans__panel");
						tabs.forEach(function (tab, i) {
							var active = i === index;
							tab.classList.toggle("is-active", active);
							tab.setAttribute("aria-selected", active ? "true" : "false");
						});
						panels.forEach(function (panel, i) {
							var active = i === index;
							panel.classList.toggle("is-active", active);
							panel.hidden = !active;
						});
					}

					document.addEventListener("click", function (event) {
						var tab = event.target.closest(".floorplans__tab");
						if (!tab) return;
						var root = tab.closest("[data-floorplans-toggle]");
						if (!root) return;
						var index = parseInt(tab.getAttribute("data-floorplan-index"), 10);
						if (Number.isNaN(index)) return;
						syncFloorplan(root, index);
					});

					document.addEventListener("keydown", function (event) {
						var tab = event.target.closest(".floorplans__tab");
						if (!tab) return;
						if (event.key !== "ArrowDown" && event.key !== "ArrowUp") return;
						var root = tab.closest("[data-floorplans-toggle]");
						if (!root) return;
						var tabs = Array.prototype.slice.call(root.querySelectorAll(".floorplans__tab"));
						var current = tabs.indexOf(tab);
						if (current === -1) return;
						event.preventDefault();
						var next = event.key === "ArrowDown" ? current + 1 : current - 1;
						if (next < 0) next = tabs.length - 1;
						if (next >= tabs.length) next = 0;
						tabs[next].focus();
						syncFloorplan(root, next);
					});
				})();
			</script>';
		}

		if (empty($items)) {
			$output = '<p>No floorplans available.</p>';
		}

		return $output;
	}
}

add_shortcode('get_the_floorplans_shortcode_v2', 'get_the_floorplans');
