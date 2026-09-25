<?php
/**
 * Register custom Elementor widgets for Common Full Site child theme.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bootstrap Elementor widget registration.
 */
final class CFS_Elementor_Widgets_Loader {

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
			'commonfullsite',
			array(
				'title' => esc_html__( 'Common Full Site', 'hello-elementor-child' ),
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
		require_once get_stylesheet_directory() . '/inc/elementor/widgets/class-acf-features-list.php';
		require_once get_stylesheet_directory() . '/inc/elementor/widgets/class-acf-floorplan-gallery.php';
		require_once get_stylesheet_directory() . '/inc/elementor/widgets/class-acf-room-dimensions.php';

		$widgets_manager->register( new CFS_ACF_Features_List_Widget() );
		$widgets_manager->register( new CFS_ACF_Floorplan_Gallery_Widget() );
		$widgets_manager->register( new CFS_ACF_Room_Dimensions_Widget() );
	}
}

/**
 * Boot the loader once Elementor is available.
 *
 * @return void
 */
function cfs_elementor_widgets_boot(): void {
	CFS_Elementor_Widgets_Loader::instance();
}

/**
 * Wait for Elementor, then register widgets.
 *
 * @return void
 */
function cfs_elementor_widgets_maybe_boot(): void {
	if ( did_action( 'elementor/loaded' ) ) {
		cfs_elementor_widgets_boot();
		return;
	}

	add_action( 'elementor/loaded', 'cfs_elementor_widgets_boot' );
}
cfs_elementor_widgets_maybe_boot();
