<?php
/**
 * Register custom Elementor widgets for the Meyer Homes child theme.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bootstrap Elementor widget registration.
 */
final class MH_Elementor_Widgets_Loader {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Hook into Elementor.
	 */
	private function __construct() {
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
	}

	/**
	 * Add a theme-specific Elementor category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elements manager.
	 * @return void
	 */
	public function register_category( $elements_manager ): void {
		$elements_manager->add_category(
			'meyer-homes',
			array(
				'title' => esc_html__( 'Meyer Homes', 'hello-elementor-child' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}

	/**
	 * Register custom widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager.
	 * @return void
	 */
	public function register_widgets( $widgets_manager ): void {
		require_once get_stylesheet_directory() . '/inc/elementor/widgets/class-acf-faq-accordion.php';
		require_once get_stylesheet_directory() . '/inc/elementor/widgets/class-acf-image-list-badge.php';
		require_once get_stylesheet_directory() . '/inc/elementor/widgets/class-project-gallery.php';
		require_once get_stylesheet_directory() . '/inc/elementor/widgets/class-project-partners.php';
		require_once get_stylesheet_directory() . '/inc/elementor/widgets/class-testimonials-masonry.php';

		$widgets_manager->register( new MH_ACF_FAQ_Accordion_Widget() );
		$widgets_manager->register( new MH_ACF_Image_List_Badge_Widget() );
		$widgets_manager->register( new MH_Project_Gallery_Widget() );
		$widgets_manager->register( new MH_Project_Partners_Widget() );
		$widgets_manager->register( new MH_Testimonials_Masonry_Widget() );
	}
}

/**
 * Boot the loader once Elementor is available.
 *
 * @return void
 */
function mh_elementor_widgets_boot(): void {
	MH_Elementor_Widgets_Loader::instance();
}

/**
 * Wait for Elementor, then register widgets.
 * Themes load after plugins_loaded, so call this immediately.
 *
 * @return void
 */
function mh_elementor_widgets_maybe_boot(): void {
	if ( did_action( 'elementor/loaded' ) ) {
		mh_elementor_widgets_boot();
		return;
	}

	add_action( 'elementor/loaded', 'mh_elementor_widgets_boot' );
}
mh_elementor_widgets_maybe_boot();
