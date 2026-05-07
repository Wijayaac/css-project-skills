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

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

define('HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0');

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
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
define('THEME_DIR', get_template_directory() . '/');
define('THEME_URL', get_template_directory_uri());
define('THEME_URL_ASSETS', THEME_URL . '-child/assets');
define('BASE_URL', get_site_url());

function support_assets()
{
	wp_enqueue_style('custom_style', THEME_URL_ASSETS . '/css/custom.css', array(), null);
	wp_enqueue_script('custom', THEME_URL_ASSETS . '/js/script.js', array('jquery'), null);
}
add_action('wp_enqueue_scripts', 'support_assets');


// Expertise points grid shortcode
function expertise_points_grid_shortcode($atts)
{
	// Optional: allow custom class from shortcode
	$atts = shortcode_atts(
		array(
			'class' => '',
		),
		$atts,
		'expertise_points_grid'
	);

	// If the repeater is attached to the current post/page:
	if (! have_rows('expertise_point')) {
		return '';
	}

	ob_start();
?>
	<div class="expertise-points-grid <?php echo esc_attr($atts['class']); ?>">
		<?php
		$index = 0;
		while (have_rows('expertise_point')) :
			the_row();
			$title       = get_sub_field('title');
			$description = get_sub_field('description');
			if (! $title && ! $description) {
				continue;
			}
		?>
			<div class="expertise-card expertise-card-<?php echo esc_attr(++$index); ?>">
				<?php if ($title) : ?>
					<p class="expertise-card__title">
						<?php echo esc_html($title); ?>
					</p>
				<?php endif; ?>

				<?php if ($description) : ?>
					<p class="expertise-card__description">
						<?php echo esc_html($description); ?>
					</p>
				<?php endif; ?>
			</div>
		<?php endwhile; ?>
	</div>
<?php

	return ob_get_clean();
}
add_shortcode('expertise_points_grid', 'expertise_points_grid_shortcode');
