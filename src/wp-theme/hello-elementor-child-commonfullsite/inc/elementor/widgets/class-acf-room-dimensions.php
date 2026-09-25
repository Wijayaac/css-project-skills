<?php
/**
 * Elementor widget: ACF Room Dimensions.
 *
 * Label / value rows from an ACF repeater.
 * Sub-field names are fixed for portability:
 * - name (text) — room label
 * - dimension (text) — measurement value
 *
 * Empty name or dimension rows are skipped.
 * Repeater field name is configurable in the editor.
 * Typography / colors / divider are controlled in Elementor.
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
 * ACF Room Dimensions widget.
 */
class CFS_ACF_Room_Dimensions_Widget extends Widget_Base {

	/**
	 * Fixed ACF sub-field name for room label.
	 */
	public const SUB_NAME = 'name';

	/**
	 * Fixed ACF sub-field name for dimension value.
	 */
	public const SUB_DIMENSION = 'dimension';

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'cfs_acf_room_dimensions';
	}

	/**
	 * Widget title in the panel.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'ACF Room Dimensions', 'hello-elementor-child' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-form-horizontal';
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
		return array( 'room', 'dimensions', 'measurements', 'acf', 'repeater', 'floorplan' );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends(): array {
		return array( 'cfs-room-dimensions' );
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
				'label'       => esc_html__( 'Dimensions repeater field', 'hello-elementor-child' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'room_dimensions',
				'placeholder' => 'room_dimensions',
				'description' => esc_html__( 'ACF repeater field name. Sub-fields must be named "name" and "dimension".', 'hello-elementor-child' ),
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => esc_html__( 'Source', 'hello-elementor-child' ),
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
				'description' => esc_html__( 'Leave source as Current post when using a single floorplan template.', 'hello-elementor-child' ),
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
		$this->start_controls_section(
			'section_style_list',
			array(
				'label' => esc_html__( 'List', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'row_padding',
			array(
				'label'      => esc_html__( 'Row padding', 'hello-elementor-child' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'default'    => array(
					'top'      => '18',
					'right'    => '0',
					'bottom'   => '18',
					'left'     => '0',
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'  => array(
					'{{WRAPPER}} .cfs-room-dimensions__row' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'divider_color',
			array(
				'label'     => esc_html__( 'Divider color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#E5E5E5',
				'selectors' => array(
					'{{WRAPPER}} .cfs-room-dimensions__row' => 'border-bottom-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'divider_width',
			array(
				'label'      => esc_html__( 'Divider thickness', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 4,
					),
				),
				'default'    => array(
					'size' => 1,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .cfs-room-dimensions__row' => 'border-bottom-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'hide_last_divider',
			array(
				'label'        => esc_html__( 'Hide last divider', 'hello-elementor-child' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'hello-elementor-child' ),
				'label_off'    => esc_html__( 'No', 'hello-elementor-child' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_label',
			array(
				'label' => esc_html__( 'Room label', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'label_color',
			array(
				'label'     => esc_html__( 'Color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4A4A4A',
				'selectors' => array(
					'{{WRAPPER}} .cfs-room-dimensions__label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'label_typography',
				'selector' => '{{WRAPPER}} .cfs-room-dimensions__label',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_value',
			array(
				'label' => esc_html__( 'Dimension value', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'value_color',
			array(
				'label'     => esc_html__( 'Color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#7A7A7A',
				'selectors' => array(
					'{{WRAPPER}} .cfs-room-dimensions__value' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'value_typography',
				'selector' => '{{WRAPPER}} .cfs-room-dimensions__value',
			)
		);

		$this->add_responsive_control(
			'value_gap',
			array(
				'label'      => esc_html__( 'Gap from label', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 8,
						'max' => 80,
					),
				),
				'default'    => array(
					'size' => 24,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .cfs-room-dimensions__row' => 'gap: {{SIZE}}{{UNIT}};',
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
	 * Pull room / dimension pairs from ACF repeater.
	 * Skips rows missing name or dimension.
	 *
	 * @return array<int, array{name: string, dimension: string}>
	 */
	private function get_dimension_rows(): array {
		if ( ! function_exists( 'get_field' ) ) {
			return array();
		}

		$settings   = $this->get_settings_for_display();
		$list_field = sanitize_key( (string) ( $settings['list_field'] ?? 'room_dimensions' ) );

		if ( '' === $list_field ) {
			return array();
		}

		$post_id = $this->get_source_post_id();
		$rows    = get_field( $list_field, $post_id );

		if ( ! is_array( $rows ) || empty( $rows ) ) {
			return array();
		}

		$items = array();

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$name      = isset( $row[ self::SUB_NAME ] ) ? trim( (string) $row[ self::SUB_NAME ] ) : '';
			$dimension = isset( $row[ self::SUB_DIMENSION ] ) ? trim( (string) $row[ self::SUB_DIMENSION ] ) : '';

			if ( '' === $name || '' === $dimension ) {
				continue;
			}

			$items[] = array(
				'name'      => $name,
				'dimension' => $dimension,
			);
		}

		return $items;
	}

	/**
	 * Render a single dimension row.
	 *
	 * @param array{name: string, dimension: string} $item Row data.
	 * @return void
	 */
	private function render_item( array $item ): void {
		?>
		<li class="cfs-room-dimensions__row">
			<span class="cfs-room-dimensions__label"><?php echo esc_html( $item['name'] ); ?></span>
			<span class="cfs-room-dimensions__value"><?php echo esc_html( $item['dimension'] ); ?></span>
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
		$items    = $this->get_dimension_rows();

		if ( empty( $items ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">' . esc_html__( 'No room dimensions found. Check ACF repeater "room_dimensions" with "name" + "dimension" sub-fields on this post.', 'hello-elementor-child' ) . '</div>';
			}
			return;
		}

		$classes = array( 'cfs-room-dimensions' );

		if ( 'yes' === ( $settings['hide_last_divider'] ?? 'yes' ) ) {
			$classes[] = 'cfs-room-dimensions--hide-last-divider';
		}
		?>
		<ul class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-cfs-room-dimensions>
			<?php foreach ( $items as $item ) : ?>
				<?php $this->render_item( $item ); ?>
			<?php endforeach; ?>
		</ul>
		<?php
	}
}
