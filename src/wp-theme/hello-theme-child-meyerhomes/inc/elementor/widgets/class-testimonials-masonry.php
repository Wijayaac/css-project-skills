<?php
/**
 * Elementor widget: Testimonials Masonry.
 *
 * Queries the `testimonial` CPT (ACF: name, position, content).
 * Post title = quoted headline. Horizontal marquee (~3.8 visible) with scroll speed boost.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

/**
 * Testimonials masonry widget.
 */
class MH_Testimonials_Masonry_Widget extends Widget_Base {

	/**
	 * CPT slug.
	 */
	public const POST_TYPE = 'testimonial';

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'mh_testimonials_masonry';
	}

	/**
	 * Widget title in the panel.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Testimonials Masonry', 'hello-elementor-child' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-testimonial-carousel';
	}

	/**
	 * Widget categories.
	 *
	 * @return string[]
	 */
	public function get_categories(): array {
		return array( 'meyer-homes', 'general' );
	}

	/**
	 * Keywords for the widget search.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'testimonial', 'review', 'masonry', 'marquee', 'quote', 'meyer' );
	}

	/**
	 * Frontend script dependencies.
	 *
	 * @return string[]
	 */
	public function get_script_depends(): array {
		return array( 'mh-reviews-scroll' );
	}

	/**
	 * Frontend style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends(): array {
		return array( 'mh-reviews' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->register_query_controls();
		$this->register_layout_controls();
		$this->register_card_style_controls();
		$this->register_typography_style_controls();
	}

	/**
	 * Query controls.
	 *
	 * @return void
	 */
	private function register_query_controls(): void {
		$this->start_controls_section(
			'section_query',
			array(
				'label' => esc_html__( 'Query', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'posts_per_page',
			array(
				'label'       => esc_html__( 'Testimonials to show', 'hello-elementor-child' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => -1,
				'min'         => -1,
				'description' => esc_html__( 'Use -1 for all testimonials.', 'hello-elementor-child' ),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => esc_html__( 'Order by', 'hello-elementor-child' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'menu_order',
				'options' => array(
					'menu_order' => esc_html__( 'Menu order', 'hello-elementor-child' ),
					'date'       => esc_html__( 'Date', 'hello-elementor-child' ),
					'title'      => esc_html__( 'Title', 'hello-elementor-child' ),
					'rand'       => esc_html__( 'Random', 'hello-elementor-child' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => esc_html__( 'Order', 'hello-elementor-child' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'ASC',
				'options' => array(
					'ASC'  => esc_html__( 'Ascending', 'hello-elementor-child' ),
					'DESC' => esc_html__( 'Descending', 'hello-elementor-child' ),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Layout controls.
	 *
	 * @return void
	 */
	private function register_layout_controls(): void {
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'Layout', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'       => esc_html__( 'Column stacks', 'hello-elementor-child' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 4,
				'min'         => 2,
				'max'         => 8,
				'description' => esc_html__( 'How many vertical stacks to distribute reviews into before the marquee loops.', 'hello-elementor-child' ),
			)
		);

		$this->add_control(
			'visible_columns',
			array(
				'label'       => esc_html__( 'Visible columns', 'hello-elementor-child' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 3.8,
				'min'         => 1.2,
				'max'         => 5,
				'step'        => 0.1,
				'description' => esc_html__( 'How many stacks fit in the viewport (e.g. 3.8 = three full + partial bleed on the edges).', 'hello-elementor-child' ),
				'selectors'   => array(
					'{{WRAPPER}} .mh-reviews' => '--mh-reviews-visible: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'enable_marquee',
			array(
				'label'        => esc_html__( 'Marquee animation', 'hello-elementor-child' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'hello-elementor-child' ),
				'label_off'    => esc_html__( 'Off', 'hello-elementor-child' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Continuous horizontal scroll. Speeds up slightly while the page is scrolled. Disabled when reduced motion is preferred.', 'hello-elementor-child' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Card shell style controls.
	 *
	 * @return void
	 */
	private function register_card_style_controls(): void {
		$this->start_controls_section(
			'section_style_card',
			array(
				'label' => esc_html__( 'Card', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'card_background',
			array(
				'label'     => esc_html__( 'Background', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f2f2f2',
				'selectors' => array(
					'{{WRAPPER}} .mh-reviews__card' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => esc_html__( 'Padding', 'hello-elementor-child' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .mh-reviews__card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'columns_gap',
			array(
				'label'      => esc_html__( 'Gap', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 64,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-reviews' => '--mh-reviews-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'section_padding',
			array(
				'label'      => esc_html__( 'Section padding (block)', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
					'vh' => array(
						'min' => 0,
						'max' => 30,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-reviews' => 'padding-block: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Typography style controls.
	 *
	 * @return void
	 */
	private function register_typography_style_controls(): void {
		$this->start_controls_section(
			'section_style_typography',
			array(
				'label' => esc_html__( 'Typography', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'heading_headline',
			array(
				'label' => esc_html__( 'Headline', 'hello-elementor-child' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'headline_typography',
				'selector' => '{{WRAPPER}} .mh-reviews__headline',
			)
		);

		$this->add_control(
			'headline_color',
			array(
				'label'     => esc_html__( 'Color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mh-reviews__headline' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'heading_body',
			array(
				'label'     => esc_html__( 'Body', 'hello-elementor-child' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'body_typography',
				'selector' => '{{WRAPPER}} .mh-reviews__body',
			)
		);

		$this->add_control(
			'body_color',
			array(
				'label'     => esc_html__( 'Color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mh-reviews__body' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'heading_author',
			array(
				'label'     => esc_html__( 'Author', 'hello-elementor-child' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'author_typography',
				'selector' => '{{WRAPPER}} .mh-reviews__author',
			)
		);

		$this->add_control(
			'author_color',
			array(
				'label'     => esc_html__( 'Color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mh-reviews__author' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Query testimonials.
	 *
	 * @return WP_Post[]
	 */
	private function get_testimonials(): array {
		$settings = $this->get_settings_for_display();
		$limit    = isset( $settings['posts_per_page'] ) ? (int) $settings['posts_per_page'] : -1;
		$orderby  = sanitize_key( (string) ( $settings['orderby'] ?? 'menu_order' ) );
		$order    = strtoupper( (string) ( $settings['order'] ?? 'ASC' ) ) === 'DESC' ? 'DESC' : 'ASC';

		$allowed_orderby = array( 'menu_order', 'date', 'title', 'rand' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'menu_order';
		}

		$query = new WP_Query(
			array(
				'post_type'              => self::POST_TYPE,
				'post_status'            => 'publish',
				'posts_per_page'         => $limit,
				'orderby'                => $orderby,
				'order'                  => $order,
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			)
		);

		$posts = $query->posts;
		wp_reset_postdata();

		return is_array( $posts ) ? $posts : array();
	}

	/**
	 * Resolve desktop column count for PHP distribution.
	 *
	 * @return int
	 */
	private function get_column_count(): int {
		$settings = $this->get_settings_for_display();
		$columns  = isset( $settings['columns'] ) ? (int) $settings['columns'] : 4;

		return max( 2, min( 8, $columns ) );
	}

	/**
	 * Frontend / editor render.
	 *
	 * @return void
	 */
	protected function render(): void {
		$posts = $this->get_testimonials();

		if ( empty( $posts ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">' . esc_html__( 'No testimonials found. Add items under Testimonials in the admin.', 'hello-elementor-child' ) . '</div>';
			}
			return;
		}

		if ( ! function_exists( 'mh_reviews_split_into_columns' ) || ! function_exists( 'mh_reviews_render_card' ) || ! function_exists( 'mh_reviews_default_speeds' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-warning">' . esc_html__( 'Reviews helpers are missing. Check that reviews-shortcode.php is loaded.', 'hello-elementor-child' ) . '</div>';
			}
			return;
		}

		$settings      = $this->get_settings_for_display();
		$columns       = $this->get_column_count();
		$column_groups = mh_reviews_split_into_columns( $posts, $columns );
		$speeds        = mh_reviews_default_speeds();
		$marquee       = ( $settings['enable_marquee'] ?? 'yes' ) === 'yes';
		$visible       = isset( $settings['visible_columns'] ) ? (float) $settings['visible_columns'] : 3.8;
		$visible       = max( 1.2, min( 5.0, $visible ) );

		$GLOBALS['mh_reviews_shortcode_used'] = true;
		?>
		<section
			class="mh-reviews"
			data-mh-reviews
			style="--mh-reviews-visible: <?php echo esc_attr( (string) $visible ); ?>"
			<?php if ( ! $marquee ) : ?>
				data-marquee="off"
			<?php endif; ?>
		>
			<div class="mh-reviews__viewport">
				<div class="mh-reviews__track">
					<?php foreach ( $column_groups as $column_index => $column_posts ) : ?>
						<?php
						$speed = $speeds[ $column_index % count( $speeds ) ];
						?>
						<div class="mh-reviews__col" data-speed="<?php echo esc_attr( (string) $speed ); ?>">
							<?php foreach ( $column_posts as $post ) : ?>
								<?php echo mh_reviews_render_card( $post ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
