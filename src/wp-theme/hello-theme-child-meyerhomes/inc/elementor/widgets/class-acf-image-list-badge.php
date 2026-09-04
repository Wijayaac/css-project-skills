<?php
/**
 * Elementor widget: ACF Image + List Badge.
 *
 * Central image with floating checklist badges from an ACF repeater.
 * Sub-field name is fixed for portability:
 * - label (text)
 *
 * Image and repeater field names are configurable in the editor.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;

/**
 * ACF Image + List Badge widget.
 */
class MH_ACF_Image_List_Badge_Widget extends Widget_Base {

	/**
	 * Fixed ACF sub-field name for badge text.
	 */
	public const SUB_LABEL = 'label';

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'mh_acf_image_list_badge';
	}

	/**
	 * Widget title in the panel.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'ACF Image List Badge', 'hello-elementor-child' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-bullet-list';
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
		return array( 'image', 'badge', 'list', 'planning', 'acf', 'checklist', 'meyer' );
	}

	/**
	 * Style dependencies (theme stylesheet already loaded globally).
	 *
	 * @return string[]
	 */
	public function get_style_depends(): array {
		return array( 'mh-service-details' );
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
				'default'     => 'planning_list',
				'placeholder' => 'planning_list',
				'description' => esc_html__( 'ACF repeater field name. Sub-field must be named "label" (text).', 'hello-elementor-child' ),
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'image_field',
			array(
				'label'       => esc_html__( 'Image field', 'hello-elementor-child' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'planning_image',
				'placeholder' => 'planning_image',
				'description' => esc_html__( 'ACF image field name (return format: array).', 'hello-elementor-child' ),
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
				'label' => esc_html__( 'Badge Icon', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'badge_icon',
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
		$this->register_stage_style_controls();
		$this->register_image_style_controls();
		$this->register_badge_style_controls();
		$this->register_icon_style_controls();
	}

	/**
	 * Stage / layout style controls.
	 *
	 * @return void
	 */
	private function register_stage_style_controls(): void {
		$this->start_controls_section(
			'section_style_stage',
			array(
				'label' => esc_html__( 'Layout', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'stage_max_width',
			array(
				'label'      => esc_html__( 'Max width', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 320,
						'max' => 1400,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-image-list-badge' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_overlap',
			array(
				'label'      => esc_html__( 'Badge overlap', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
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
					'{{WRAPPER}} .mh-image-list-badge' => '--mh-badge-overlap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_column_gap',
			array(
				'label'      => esc_html__( 'Badge vertical gap', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-image-list-badge' => '--mh-badge-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Image style controls.
	 *
	 * @return void
	 */
	private function register_image_style_controls(): void {
		$this->start_controls_section(
			'section_style_image',
			array(
				'label' => esc_html__( 'Image', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'image_width',
			array(
				'label'      => esc_html__( 'Width', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 160,
						'max' => 640,
					),
				),
				'default'    => array(
					'size' => 340,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-image-list-badge' => '--mh-image-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'image_border_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .mh-image-list-badge__media img' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'image_box_shadow',
				'selector' => '{{WRAPPER}} .mh-image-list-badge__media img',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Badge container + text style controls.
	 *
	 * @return void
	 */
	private function register_badge_style_controls(): void {
		$this->start_controls_section(
			'section_style_badge',
			array(
				'label' => esc_html__( 'Badge', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'badge_bg_color',
			array(
				'label'     => esc_html__( 'Background color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .mh-image-list-badge__badge' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'badge_text_color',
			array(
				'label'     => esc_html__( 'Text color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mh-image-list-badge__label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'badge_typography',
				'selector' => '{{WRAPPER}} .mh-image-list-badge__label',
			)
		);

		$this->add_responsive_control(
			'badge_padding',
			array(
				'label'      => esc_html__( 'Padding', 'hello-elementor-child' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .mh-image-list-badge__badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_border_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'size' => 999,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-image-list-badge__badge' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'badge_border',
				'selector' => '{{WRAPPER}} .mh-image-list-badge__badge',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'badge_box_shadow',
				'selector' => '{{WRAPPER}} .mh-image-list-badge__badge',
			)
		);

		$this->add_responsive_control(
			'badge_gap',
			array(
				'label'      => esc_html__( 'Icon / text gap', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-image-list-badge__badge' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Checklist icon style controls (single place for all badges).
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
					'{{WRAPPER}} .mh-image-list-badge__icon' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .mh-image-list-badge__icon svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_bg_color',
			array(
				'label'     => esc_html__( 'Circle background', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#00243D',
				'selectors' => array(
					'{{WRAPPER}} .mh-image-list-badge__icon' => 'background-color: {{VALUE}};',
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
					'size' => 12,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-image-list-badge__icon i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .mh-image-list-badge__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
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
					'size' => 28,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-image-list-badge__icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Resolve the post ID used for ACF reads.
	 *
	 * @return int
	 */
	private function get_source_post_id(): int {
		$settings = $this->get_settings_for_display();

		if ( 'custom' === ( $settings['source'] ?? '' ) && ! empty( $settings['post_id'] ) ) {
			return absint( $settings['post_id'] );
		}

		return get_the_ID() ? (int) get_the_ID() : 0;
	}

	/**
	 * Load planning image from ACF.
	 *
	 * @return array{url: string, alt: string, width: int, height: int}|null
	 */
	private function get_planning_image(): ?array {
		if ( ! function_exists( 'get_field' ) ) {
			return null;
		}

		$settings    = $this->get_settings_for_display();
		$image_field = sanitize_key( (string) ( $settings['image_field'] ?? 'planning_image' ) );

		if ( '' === $image_field ) {
			return null;
		}

		$post_id = $this->get_source_post_id();
		$image   = get_field( $image_field, $post_id );

		if ( is_numeric( $image ) ) {
			$attachment_id = absint( $image );
			$url           = wp_get_attachment_image_url( $attachment_id, 'large' );
			if ( ! $url ) {
				return null;
			}

			$meta = wp_get_attachment_image_src( $attachment_id, 'large' );

			return array(
				'url'    => $url,
				'alt'    => (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
				'width'  => isset( $meta[1] ) ? (int) $meta[1] : 0,
				'height' => isset( $meta[2] ) ? (int) $meta[2] : 0,
			);
		}

		if ( ! is_array( $image ) || empty( $image['url'] ) ) {
			return null;
		}

		return array(
			'url'    => (string) $image['url'],
			'alt'    => isset( $image['alt'] ) ? (string) $image['alt'] : '',
			'width'  => isset( $image['width'] ) ? (int) $image['width'] : 0,
			'height' => isset( $image['height'] ) ? (int) $image['height'] : 0,
		);
	}

	/**
	 * Load badge labels from ACF repeater.
	 *
	 * @return string[]
	 */
	private function get_badge_labels(): array {
		if ( ! function_exists( 'get_field' ) ) {
			return array();
		}

		$settings   = $this->get_settings_for_display();
		$list_field = sanitize_key( (string) ( $settings['list_field'] ?? 'planning_list' ) );

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

			$label = isset( $row[ self::SUB_LABEL ] ) ? trim( (string) $row[ self::SUB_LABEL ] ) : '';

			if ( '' === $label ) {
				continue;
			}

			$labels[] = $label;
		}

		return $labels;
	}

	/**
	 * Split labels into left / right columns (first half left, second half right).
	 *
	 * @param string[] $labels Badge labels.
	 * @return array{left: string[], right: string[]}
	 */
	private function split_columns( array $labels ): array {
		$count = count( $labels );

		if ( 0 === $count ) {
			return array(
				'left'  => array(),
				'right' => array(),
			);
		}

		$mid = (int) ceil( $count / 2 );

		return array(
			'left'  => array_slice( $labels, 0, $mid ),
			'right' => array_slice( $labels, $mid ),
		);
	}

	/**
	 * Render a single badge.
	 *
	 * @param string               $label    Badge text.
	 * @param array<string, mixed> $settings Widget settings.
	 * @return void
	 */
	private function render_badge( string $label, array $settings ): void {
		?>
		<li class="mh-image-list-badge__item">
			<span class="mh-image-list-badge__badge">
				<span class="mh-image-list-badge__icon" aria-hidden="true">
					<?php Icons_Manager::render_icon( $settings['badge_icon'], array( 'aria-hidden' => 'true' ) ); ?>
				</span>
				<span class="mh-image-list-badge__label"><?php echo esc_html( $label ); ?></span>
			</span>
		</li>
		<?php
	}

	/**
	 * Render a badge column list.
	 *
	 * @param string               $side     left|right.
	 * @param string[]             $labels   Labels for this column.
	 * @param array<string, mixed> $settings Widget settings.
	 * @return void
	 */
	private function render_column( string $side, array $labels, array $settings ): void {
		if ( empty( $labels ) ) {
			return;
		}

		$side_class = 'left' === $side ? 'mh-image-list-badge__list--left' : 'mh-image-list-badge__list--right';
		?>
		<ul class="mh-image-list-badge__list <?php echo esc_attr( $side_class ); ?>">
			<?php foreach ( $labels as $label ) : ?>
				<?php $this->render_badge( $label, $settings ); ?>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Frontend / editor render.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$labels   = $this->get_badge_labels();
		$image    = $this->get_planning_image();

		if ( empty( $labels ) && null === $image ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">' . esc_html__( 'No planning image or list found. Check ACF field names and data on this post.', 'hello-elementor-child' ) . '</div>';
			}
			return;
		}

		$columns = $this->split_columns( $labels );
		?>
		<div class="mh-image-list-badge" data-mh-image-list-badge>
			<div class="mh-image-list-badge__stage">
				<?php $this->render_column( 'left', $columns['left'], $settings ); ?>

				<div class="mh-image-list-badge__media">
					<?php if ( null !== $image ) : ?>
						<img
							src="<?php echo esc_url( $image['url'] ); ?>"
							alt="<?php echo esc_attr( $image['alt'] ); ?>"
							<?php if ( $image['width'] ) : ?>
								width="<?php echo esc_attr( (string) $image['width'] ); ?>"
							<?php endif; ?>
							<?php if ( $image['height'] ) : ?>
								height="<?php echo esc_attr( (string) $image['height'] ); ?>"
							<?php endif; ?>
							loading="lazy"
							decoding="async"
						/>
					<?php elseif ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) : ?>
						<div class="mh-image-list-badge__media-placeholder">
							<?php echo esc_html__( 'Set planning_image on this post.', 'hello-elementor-child' ); ?>
						</div>
					<?php endif; ?>
				</div>

				<?php $this->render_column( 'right', $columns['right'], $settings ); ?>
			</div>
		</div>
		<?php
	}
}
