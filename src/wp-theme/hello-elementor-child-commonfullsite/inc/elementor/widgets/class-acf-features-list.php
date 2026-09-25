<?php
/**
 * Elementor widget: ACF Features List.
 *
 * Two-column checklist from an ACF repeater.
 * Sub-field name is fixed for portability:
 * - name (text)
 *
 * Repeater field name is configurable in the editor.
 * Icon + typography are controlled in Elementor.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;

/**
 * ACF Features List widget.
 */
class CFS_ACF_Features_List_Widget extends Widget_Base {

	/**
	 * Fixed ACF sub-field name for feature text.
	 */
	public const SUB_NAME = 'name';

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'cfs_acf_features_list';
	}

	/**
	 * Widget title in the panel.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'ACF Features List', 'hello-elementor-child' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-check-circle';
	}

	/**
	 * Widget categories.
	 *
	 * @return string[]
	 */
	public function get_categories(): array {
		return array( 'commonfullsite', 'general' );
	}

	/**
	 * Keywords for the widget search.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'features', 'list', 'checklist', 'acf', 'repeater', 'floorplan' );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends(): array {
		return array( 'cfs-features-list' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	/**
	 * Content tab controls.
	 *
	 * @return void
	 */
	private function register_content_controls(): void {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'ACF Source', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'list_field',
			array(
				'label'       => esc_html__( 'List repeater field', 'hello-elementor-child' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'features_items',
				'placeholder' => 'features_items',
				'description' => esc_html__( 'ACF repeater field name. Sub-field must be named "name" (text).', 'hello-elementor-child' ),
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => esc_html__( 'Data source', 'hello-elementor-child' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'current',
				'options' => array(
					'current' => esc_html__( 'Current post', 'hello-elementor-child' ),
					'custom'  => esc_html__( 'Custom post ID', 'hello-elementor-child' ),
				),
			)
		);

		$this->add_control(
			'post_id',
			array(
				'label'       => esc_html__( 'Post ID', 'hello-elementor-child' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 1,
				'condition'   => array(
					'source' => 'custom',
				),
				'description' => esc_html__( 'Post that holds the ACF fields.', 'hello-elementor-child' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_icon',
			array(
				'label' => esc_html__( 'Icon', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'item_icon',
			array(
				'label'            => esc_html__( 'Icon', 'hello-elementor-child' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => array(
					'value'   => 'fas fa-check',
					'library' => 'fa-solid',
				),
				'recommended'      => array(
					'fa-solid' => array(
						'check',
						'check-circle',
						'check-double',
						'star',
						'circle',
					),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab controls.
	 *
	 * @return void
	 */
	private function register_style_controls(): void {
		$this->register_layout_style_controls();
		$this->register_item_style_controls();
		$this->register_icon_style_controls();
	}

	/**
	 * Layout style controls.
	 *
	 * @return void
	 */
	private function register_layout_style_controls(): void {
		$this->start_controls_section(
			'section_style_layout',
			array(
				'label' => esc_html__( 'Layout', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => esc_html__( 'Columns', 'hello-elementor-child' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '2',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
				),
				'selectors'      => array(
					'{{WRAPPER}} .cfs-features-list' => '--cfs-features-columns: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'column_gap',
			array(
				'label'      => esc_html__( 'Column gap', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 120,
					),
				),
				'default'    => array(
					'size' => 48,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .cfs-features-list' => '--cfs-features-column-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'row_gap',
			array(
				'label'      => esc_html__( 'Row gap', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'default'    => array(
					'size' => 20,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .cfs-features-list' => '--cfs-features-row-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Item / text style controls.
	 *
	 * @return void
	 */
	private function register_item_style_controls(): void {
		$this->start_controls_section(
			'section_style_item',
			array(
				'label' => esc_html__( 'Item Text', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4A4A4A',
				'selectors' => array(
					'{{WRAPPER}} .cfs-features-list__label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'item_typography',
				'selector' => '{{WRAPPER}} .cfs-features-list__label',
			)
		);

		$this->add_responsive_control(
			'item_gap',
			array(
				'label'      => esc_html__( 'Icon / text gap', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'default'    => array(
					'size' => 12,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .cfs-features-list__item' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'item_align',
			array(
				'label'     => esc_html__( 'Alignment', 'hello-elementor-child' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => esc_html__( 'Left', 'hello-elementor-child' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => esc_html__( 'Center', 'hello-elementor-child' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => esc_html__( 'Right', 'hello-elementor-child' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'flex-start',
				'selectors' => array(
					'{{WRAPPER}} .cfs-features-list__item' => 'justify-content: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Icon style controls.
	 *
	 * @return void
	 */
	private function register_icon_style_controls(): void {
		$this->start_controls_section(
			'section_style_icon',
			array(
				'label' => esc_html__( 'Icon', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => esc_html__( 'Icon color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .cfs-features-list__icon' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .cfs-features-list__icon svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_bg_color',
			array(
				'label'     => esc_html__( 'Circle background', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#C36645',
				'selectors' => array(
					'{{WRAPPER}} .cfs-features-list__icon' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => esc_html__( 'Icon size', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 8,
						'max' => 40,
					),
				),
				'default'    => array(
					'size' => 11,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .cfs-features-list__icon i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .cfs-features-list__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_box_size',
			array(
				'label'      => esc_html__( 'Circle size', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 16,
						'max' => 64,
					),
				),
				'default'    => array(
					'size' => 24,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .cfs-features-list__icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Resolve post ID for ACF reads.
	 *
	 * @return int
	 */
	private function get_source_post_id(): int {
		$settings = $this->get_settings_for_display();

		if ( 'custom' === ( $settings['source'] ?? 'current' ) && ! empty( $settings['post_id'] ) ) {
			return absint( $settings['post_id'] );
		}

		return get_the_ID() ? (int) get_the_ID() : 0;
	}

	/**
	 * Pull feature labels from ACF repeater.
	 *
	 * @return string[]
	 */
	private function get_feature_labels(): array {
		if ( ! function_exists( 'get_field' ) ) {
			return array();
		}

		$settings   = $this->get_settings_for_display();
		$list_field = sanitize_key( (string) ( $settings['list_field'] ?? 'features_items' ) );

		if ( '' === $list_field ) {
			return array();
		}

		$post_id = $this->get_source_post_id();
		$rows    = get_field( $list_field, $post_id );

		if ( ! is_array( $rows ) || empty( $rows ) ) {
			return array();
		}

		$labels = array();

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$label = isset( $row[ self::SUB_NAME ] ) ? trim( (string) $row[ self::SUB_NAME ] ) : '';

			if ( '' === $label ) {
				continue;
			}

			$labels[] = $label;
		}

		return $labels;
	}

	/**
	 * Render a single feature row.
	 *
	 * @param string               $label    Feature text.
	 * @param array<string, mixed> $settings Widget settings.
	 * @return void
	 */
	private function render_item( string $label, array $settings ): void {
		?>
		<li class="cfs-features-list__item">
			<span class="cfs-features-list__icon" aria-hidden="true">
				<?php Icons_Manager::render_icon( $settings['item_icon'], array( 'aria-hidden' => 'true' ) ); ?>
			</span>
			<span class="cfs-features-list__label"><?php echo esc_html( $label ); ?></span>
		</li>
		<?php
	}

	/**
	 * Frontend / editor render.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$labels   = $this->get_feature_labels();

		if ( empty( $labels ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">' . esc_html__( 'No features found. Check ACF field "features_items" and "name" sub-field on this post.', 'hello-elementor-child' ) . '</div>';
			}
			return;
		}
		?>
		<ul class="cfs-features-list" data-cfs-features-list>
			<?php foreach ( $labels as $label ) : ?>
				<?php $this->render_item( $label, $settings ); ?>
			<?php endforeach; ?>
		</ul>
		<?php
	}
}
