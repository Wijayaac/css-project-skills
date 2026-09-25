<?php
/**
 * Elementor widget: ACF Floorplan Gallery.
 *
 * Main stage + thumbnails from an ACF gallery field.
 * Maximize opens fullscreen lightbox; minimize closes it.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;

/**
 * ACF Floorplan Gallery widget.
 */
class CFS_ACF_Floorplan_Gallery_Widget extends Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'cfs_acf_floorplan_gallery';
	}

	/**
	 * Widget title in the panel.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'ACF Floorplan Gallery', 'hello-elementor-child' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-gallery-grid';
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
		return array( 'floorplan', 'gallery', 'acf', 'images', 'lightbox', 'zoom', 'maximize' );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends(): array {
		return array( 'cfs-floorplan-gallery' );
	}

	/**
	 * Script dependencies.
	 *
	 * @return string[]
	 */
	public function get_script_depends(): array {
		return array( 'cfs-floorplan-gallery' );
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
			'gallery_field',
			array(
				'label'       => esc_html__( 'Gallery field', 'hello-elementor-child' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'floorplan_images',
				'placeholder' => 'floorplan_images',
				'description' => esc_html__( 'ACF gallery field name (return format: array).', 'hello-elementor-child' ),
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
				'description' => esc_html__( 'Post that holds the ACF gallery.', 'hello-elementor-child' ),
			)
		);

		$this->add_control(
			'image_size',
			array(
				'label'   => esc_html__( 'Main image size', 'hello-elementor-child' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'large',
				'options' => array(
					'medium'       => esc_html__( 'Medium', 'hello-elementor-child' ),
					'large'        => esc_html__( 'Large', 'hello-elementor-child' ),
					'full'         => esc_html__( 'Full', 'hello-elementor-child' ),
					'medium_large' => esc_html__( 'Medium Large', 'hello-elementor-child' ),
				),
			)
		);

		$this->add_control(
			'thumb_size',
			array(
				'label'   => esc_html__( 'Thumbnail size', 'hello-elementor-child' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'medium',
				'options' => array(
					'thumbnail' => esc_html__( 'Thumbnail', 'hello-elementor-child' ),
					'medium'    => esc_html__( 'Medium', 'hello-elementor-child' ),
					'large'     => esc_html__( 'Large', 'hello-elementor-child' ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_zoom',
			array(
				'label' => esc_html__( 'Maximize / Minimize', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_zoom',
			array(
				'label'        => esc_html__( 'Show maximize button', 'hello-elementor-child' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'hello-elementor-child' ),
				'label_off'    => esc_html__( 'No', 'hello-elementor-child' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'maximize_icon',
			array(
				'label'            => esc_html__( 'Maximize icon', 'hello-elementor-child' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => array(
					'value'   => 'fas fa-search-plus',
					'library' => 'fa-solid',
				),
				'condition'        => array(
					'show_zoom' => 'yes',
				),
			)
		);

		$this->add_control(
			'minimize_icon',
			array(
				'label'            => esc_html__( 'Minimize icon', 'hello-elementor-child' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon_minimize',
				'default'          => array(
					'value'   => 'fas fa-search-minus',
					'library' => 'fa-solid',
				),
				'condition'        => array(
					'show_zoom' => 'yes',
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
		$this->start_controls_section(
			'section_style_stage',
			array(
				'label' => esc_html__( 'Stage', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'stage_bg',
			array(
				'label'     => esc_html__( 'Background', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F2F4F8',
				'selectors' => array(
					'{{WRAPPER}} .cfs-floorplan-gallery__stage' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'stage_padding',
			array(
				'label'      => esc_html__( 'Padding', 'hello-elementor-child' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'      => '24',
					'right'    => '24',
					'bottom'   => '24',
					'left'     => '24',
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .cfs-floorplan-gallery__stage' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'stage_min_height',
			array(
				'label'      => esc_html__( 'Min height', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 200,
						'max' => 900,
					),
				),
				'default'    => array(
					'size' => 420,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .cfs-floorplan-gallery__main' => 'min-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_thumbs',
			array(
				'label' => esc_html__( 'Thumbnails', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'thumb_gap',
			array(
				'label'      => esc_html__( 'Gap', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
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
					'{{WRAPPER}} .cfs-floorplan-gallery__thumbs' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'thumb_active_border',
			array(
				'label'     => esc_html__( 'Active border color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#C36645',
				'selectors' => array(
					'{{WRAPPER}} .cfs-floorplan-gallery__thumb.is-active' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'thumb_border_width',
			array(
				'label'      => esc_html__( 'Border width', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 8,
					),
				),
				'default'    => array(
					'size' => 2,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .cfs-floorplan-gallery__thumb' => 'border-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_zoom',
			array(
				'label'     => esc_html__( 'Maximize Button', 'hello-elementor-child' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_zoom' => 'yes',
				),
			)
		);

		$this->add_control(
			'zoom_bg',
			array(
				'label'     => esc_html__( 'Background', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#C36645',
				'selectors' => array(
					'{{WRAPPER}} .cfs-floorplan-gallery__zoom' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'zoom_color',
			array(
				'label'     => esc_html__( 'Icon color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .cfs-floorplan-gallery__zoom' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .cfs-floorplan-gallery__zoom svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'zoom_size',
			array(
				'label'      => esc_html__( 'Button size', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 32,
						'max' => 80,
					),
				),
				'default'    => array(
					'size' => 48,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .cfs-floorplan-gallery__zoom' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'zoom_icon_size',
			array(
				'label'      => esc_html__( 'Icon size', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 12,
						'max' => 40,
					),
				),
				'default'    => array(
					'size' => 18,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .cfs-floorplan-gallery__zoom i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .cfs-floorplan-gallery__zoom svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_lightbox',
			array(
				'label'     => esc_html__( 'Lightbox', 'hello-elementor-child' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_zoom' => 'yes',
				),
			)
		);

		$this->add_control(
			'lightbox_bg',
			array(
				'label'     => esc_html__( 'Overlay background', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(20, 28, 40, 0.92)',
				'selectors' => array(
					'{{WRAPPER}} .cfs-floorplan-gallery__lightbox' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'minimize_bg',
			array(
				'label'     => esc_html__( 'Minimize button background', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#C36645',
				'selectors' => array(
					'{{WRAPPER}} .cfs-floorplan-gallery__minimize' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'minimize_color',
			array(
				'label'     => esc_html__( 'Minimize icon color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .cfs-floorplan-gallery__minimize' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .cfs-floorplan-gallery__minimize svg' => 'fill: {{VALUE}};',
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
	 * Pull gallery images from ACF.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function get_gallery_images(): array {
		if ( ! function_exists( 'get_field' ) ) {
			return array();
		}

		$settings      = $this->get_settings_for_display();
		$gallery_field = sanitize_key( (string) ( $settings['gallery_field'] ?? 'floorplan_images' ) );

		if ( '' === $gallery_field ) {
			return array();
		}

		$post_id = $this->get_source_post_id();
		$images  = get_field( $gallery_field, $post_id );

		if ( ! is_array( $images ) || empty( $images ) ) {
			return array();
		}

		$normalized = array();

		foreach ( $images as $image ) {
			if ( ! is_array( $image ) || empty( $image['ID'] ) ) {
				continue;
			}

			$normalized[] = $image;
		}

		return $normalized;
	}

	/**
	 * Pick a sized URL from an ACF image array.
	 *
	 * @param array<string, mixed> $image ACF image array.
	 * @param string               $size  Registered image size.
	 * @return string
	 */
	private function get_image_url( array $image, string $size ): string {
		if ( ! empty( $image['sizes'][ $size ] ) ) {
			return (string) $image['sizes'][ $size ];
		}

		if ( ! empty( $image['url'] ) ) {
			return (string) $image['url'];
		}

		$id = absint( $image['ID'] ?? 0 );

		if ( $id ) {
			$url = wp_get_attachment_image_url( $id, $size );
			return $url ? $url : '';
		}

		return '';
	}

	/**
	 * Frontend / editor render.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$images   = $this->get_gallery_images();

		if ( empty( $images ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">' . esc_html__( 'No floorplan images found. Check ACF gallery field "floorplan_images" on this post.', 'hello-elementor-child' ) . '</div>';
			}
			return;
		}

		$image_size = sanitize_key( (string) ( $settings['image_size'] ?? 'large' ) );
		$thumb_size = sanitize_key( (string) ( $settings['thumb_size'] ?? 'medium' ) );
		$show_zoom  = 'yes' === ( $settings['show_zoom'] ?? 'yes' );
		$uid        = 'cfs-fpg-' . $this->get_id();
		$count      = count( $images );
		$multi      = $count > 1;
		?>
		<div
			class="cfs-floorplan-gallery"
			data-cfs-floorplan-gallery
			data-cfs-count="<?php echo esc_attr( (string) $count ); ?>"
			id="<?php echo esc_attr( $uid ); ?>"
		>
			<div class="cfs-floorplan-gallery__stage">
				<div class="cfs-floorplan-gallery__main-wrap">
					<div
						class="cfs-floorplan-gallery__main"
						data-cfs-main-stage
						role="button"
						tabindex="0"
						aria-label="<?php echo esc_attr__( 'Maximize floorplan', 'hello-elementor-child' ); ?>"
					>
						<div class="cfs-floorplan-gallery__viewport" data-cfs-main-viewport>
							<div class="cfs-floorplan-gallery__track" data-cfs-main-track>
								<?php foreach ( $images as $index => $image ) : ?>
									<div
										class="cfs-floorplan-gallery__slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
										data-cfs-main-slide
										data-index="<?php echo esc_attr( (string) $index ); ?>"
									>
										<img
											class="cfs-floorplan-gallery__image"
											src="<?php echo esc_url( $this->get_image_url( $image, $image_size ) ); ?>"
											alt="<?php echo esc_attr( (string) ( $image['alt'] ?? '' ) ); ?>"
											<?php echo 0 === $index ? 'data-cfs-main-image' : ''; ?>
											decoding="async"
											<?php echo 0 === $index ? '' : 'loading="lazy"'; ?>
										/>
									</div>
								<?php endforeach; ?>
							</div>
						</div>

						<?php if ( $multi ) : ?>
							<button
								type="button"
								class="cfs-floorplan-gallery__nav cfs-floorplan-gallery__nav--prev cfs-floorplan-gallery__nav--main"
								data-cfs-nav="prev"
								aria-label="<?php echo esc_attr__( 'Previous floorplan', 'hello-elementor-child' ); ?>"
							>
								<span aria-hidden="true">&#10094;</span>
							</button>
							<button
								type="button"
								class="cfs-floorplan-gallery__nav cfs-floorplan-gallery__nav--next cfs-floorplan-gallery__nav--main"
								data-cfs-nav="next"
								aria-label="<?php echo esc_attr__( 'Next floorplan', 'hello-elementor-child' ); ?>"
							>
								<span aria-hidden="true">&#10095;</span>
							</button>
						<?php endif; ?>

						<?php if ( $show_zoom ) : ?>
							<button
								type="button"
								class="cfs-floorplan-gallery__zoom"
								data-cfs-maximize
								aria-label="<?php echo esc_attr__( 'Maximize floorplan', 'hello-elementor-child' ); ?>"
							>
								<?php Icons_Manager::render_icon( $settings['maximize_icon'], array( 'aria-hidden' => 'true' ) ); ?>
							</button>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( $multi ) : ?>
					<div
						class="cfs-floorplan-gallery__thumbs"
						data-cfs-thumbs
						role="tablist"
						aria-label="<?php echo esc_attr__( 'Floorplan thumbnails', 'hello-elementor-child' ); ?>"
					>
						<?php foreach ( $images as $index => $image ) : ?>
							<button
								type="button"
								class="cfs-floorplan-gallery__thumb<?php echo 0 === $index ? ' is-active' : ''; ?>"
								role="tab"
								aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
								data-cfs-thumb
								data-index="<?php echo esc_attr( (string) $index ); ?>"
								data-main-src="<?php echo esc_url( $this->get_image_url( $image, $image_size ) ); ?>"
								data-full-src="<?php echo esc_url( $this->get_image_url( $image, 'full' ) ); ?>"
								aria-label="<?php echo esc_attr( sprintf( /* translators: %d: image number */ __( 'Floorplan image %d', 'hello-elementor-child' ), $index + 1 ) ); ?>"
							>
								<img
									src="<?php echo esc_url( $this->get_image_url( $image, $thumb_size ) ); ?>"
									alt=""
									decoding="async"
								/>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div
				class="cfs-floorplan-gallery__lightbox"
				data-cfs-lightbox
				hidden
				aria-hidden="true"
				role="dialog"
				aria-modal="true"
				aria-label="<?php echo esc_attr__( 'Floorplan maximized view', 'hello-elementor-child' ); ?>"
			>
				<button
					type="button"
					class="cfs-floorplan-gallery__minimize"
					data-cfs-minimize
					aria-label="<?php echo esc_attr__( 'Minimize floorplan', 'hello-elementor-child' ); ?>"
				>
					<?php Icons_Manager::render_icon( $settings['minimize_icon'], array( 'aria-hidden' => 'true' ) ); ?>
				</button>

				<?php if ( $multi ) : ?>
					<button
						type="button"
						class="cfs-floorplan-gallery__nav cfs-floorplan-gallery__nav--prev cfs-floorplan-gallery__nav--lightbox"
						data-cfs-lightbox-nav="prev"
						aria-label="<?php echo esc_attr__( 'Previous floorplan', 'hello-elementor-child' ); ?>"
					>
						<span aria-hidden="true">&#10094;</span>
					</button>
				<?php endif; ?>

				<div class="cfs-floorplan-gallery__lightbox-viewport" data-cfs-lightbox-viewport>
					<div class="cfs-floorplan-gallery__lightbox-track" data-cfs-lightbox-track>
						<?php foreach ( $images as $index => $image ) : ?>
							<div
								class="cfs-floorplan-gallery__lightbox-slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
								data-cfs-lightbox-slide
								data-index="<?php echo esc_attr( (string) $index ); ?>"
							>
								<img
									class="cfs-floorplan-gallery__lightbox-image"
									src="<?php echo esc_url( $this->get_image_url( $image, 'full' ) ); ?>"
									alt="<?php echo esc_attr( (string) ( $image['alt'] ?? '' ) ); ?>"
									<?php echo 0 === $index ? 'data-cfs-lightbox-image' : ''; ?>
									decoding="async"
									<?php echo 0 === $index ? '' : 'loading="lazy"'; ?>
								/>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<?php if ( $multi ) : ?>
					<button
						type="button"
						class="cfs-floorplan-gallery__nav cfs-floorplan-gallery__nav--next cfs-floorplan-gallery__nav--lightbox"
						data-cfs-lightbox-nav="next"
						aria-label="<?php echo esc_attr__( 'Next floorplan', 'hello-elementor-child' ); ?>"
					>
						<span aria-hidden="true">&#10095;</span>
					</button>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
