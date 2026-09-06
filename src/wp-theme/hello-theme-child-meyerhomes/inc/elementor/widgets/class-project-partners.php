<?php
/**
 * Elementor widget: Project Partners.
 *
 * Pill badges from an ACF gallery (`partners_logo` by default).
 * Logo = gallery image; label = attachment title.
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
 * Project Partners widget.
 */
class MH_Project_Partners_Widget extends Widget_Base {

	/**
	 * Default ACF gallery field name.
	 */
	public const GALLERY_FIELD = 'partners_logo';

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'mh_project_partners';
	}

	/**
	 * Widget title in the panel.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Project Partners', 'hello-elementor-child' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-tags';
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
		return array( 'partners', 'logo', 'badge', 'gallery', 'acf', 'materials', 'meyer' );
	}

	/**
	 * Frontend style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends(): array {
		return array( 'mh-project-partners' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->register_content_controls();
		$this->register_badge_style_controls();
		$this->register_title_style_controls();
	}

	/**
	 * Content tab: ACF source.
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
				'label'       => esc_html__( 'Gallery field name', 'hello-elementor-child' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => self::GALLERY_FIELD,
				'placeholder' => self::GALLERY_FIELD,
				'description' => esc_html__( 'ACF gallery field. Image title is used as the badge label.', 'hello-elementor-child' ),
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
				'description' => esc_html__( 'Project post that holds the partners gallery.', 'hello-elementor-child' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: badge shell (background, spacing, logo size).
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
			'badge_background',
			array(
				'label'     => esc_html__( 'Background', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#efefef',
				'selectors' => array(
					'{{WRAPPER}} .mh-project-partners__badge' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_gap',
			array(
				'label'      => esc_html__( 'Gap between badges', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem' ),
				'range'      => array(
					'px'  => array(
						'min' => 0,
						'max' => 48,
					),
					'rem' => array(
						'min' => 0,
						'max' => 3,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 12,
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-project-partners__list' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_padding',
			array(
				'label'      => esc_html__( 'Padding', 'hello-elementor-child' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'rem' ),
				'default'    => array(
					'top'      => '10',
					'right'    => '18',
					'bottom'   => '10',
					'left'     => '12',
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-project-partners__badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'logo_size',
			array(
				'label'      => esc_html__( 'Logo size', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 16,
						'max' => 64,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 28,
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-project-partners__logo' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'logo_gap',
			array(
				'label'      => esc_html__( 'Logo / title gap', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 24,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 10,
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-project-partners__badge' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: logo title typography + color.
	 *
	 * @return void
	 */
	private function register_title_style_controls(): void {
		$this->start_controls_section(
			'section_style_title',
			array(
				'label' => esc_html__( 'Logo title', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .mh-project-partners__title',
			)
		);

		$this->add_responsive_control(
			'title_font_size',
			array(
				'label'      => esc_html__( 'Font size', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem', 'em' ),
				'range'      => array(
					'px'  => array(
						'min' => 10,
						'max' => 32,
					),
					'rem' => array(
						'min'  => 0.5,
						'max'  => 2,
						'step' => 0.05,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 14,
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-project-partners__title' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1a1a1a',
				'selectors' => array(
					'{{WRAPPER}} .mh-project-partners__title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Resolve the post ID for ACF lookups.
	 *
	 * @return int
	 */
	private function resolve_post_id(): int {
		$settings = $this->get_settings_for_display();
		$source   = $settings['source'] ?? 'current';

		if ( 'custom' === $source ) {
			$post_id = isset( $settings['post_id'] ) ? absint( $settings['post_id'] ) : 0;
			return $post_id > 0 ? $post_id : 0;
		}

		return (int) get_the_ID();
	}

	/**
	 * Normalize ACF gallery items into logo + title pairs.
	 *
	 * @return array<int, array{url: string, alt: string, title: string, width: int, height: int}>
	 */
	private function get_partners(): array {
		if ( ! function_exists( 'get_field' ) ) {
			return array();
		}

		$settings = $this->get_settings_for_display();
		$field    = sanitize_key( (string) ( $settings['gallery_field'] ?? self::GALLERY_FIELD ) );
		$post_id  = $this->resolve_post_id();

		if ( '' === $field || $post_id < 1 ) {
			return array();
		}

		$gallery = get_field( $field, $post_id );
		if ( ! is_array( $gallery ) || empty( $gallery ) ) {
			return array();
		}

		$partners = array();

		foreach ( $gallery as $item ) {
			$partner = $this->normalize_gallery_item( $item );
			if ( null !== $partner ) {
				$partners[] = $partner;
			}
		}

		return $partners;
	}

	/**
	 * Normalize one ACF gallery entry (array or attachment ID).
	 *
	 * @param mixed $item Gallery item.
	 * @return array{url: string, alt: string, title: string, width: int, height: int}|null
	 */
	private function normalize_gallery_item( $item ): ?array {
		$attachment_id = 0;
		$url           = '';
		$alt           = '';
		$title         = '';
		$width         = 0;
		$height        = 0;

		if ( is_numeric( $item ) ) {
			$attachment_id = absint( $item );
		} elseif ( is_array( $item ) ) {
			$attachment_id = isset( $item['ID'] ) ? absint( $item['ID'] ) : 0;
			$url           = isset( $item['url'] ) ? (string) $item['url'] : '';
			$alt           = isset( $item['alt'] ) ? (string) $item['alt'] : '';
			$title         = isset( $item['title'] ) ? (string) $item['title'] : '';
			$width         = isset( $item['width'] ) ? absint( $item['width'] ) : 0;
			$height        = isset( $item['height'] ) ? absint( $item['height'] ) : 0;

			if ( '' === $url && isset( $item['sizes']['thumbnail'] ) ) {
				$url = (string) $item['sizes']['thumbnail'];
			}
		} else {
			return null;
		}

		if ( $attachment_id > 0 ) {
			if ( '' === $url ) {
				$url = (string) wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
			}
			if ( '' === $alt ) {
				$alt = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
			}
			if ( '' === $title ) {
				$title = (string) get_the_title( $attachment_id );
			}
			if ( ! $width || ! $height ) {
				$meta = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
				if ( is_array( $meta ) ) {
					$width  = absint( $meta[1] ?? 0 );
					$height = absint( $meta[2] ?? 0 );
				}
			}
		}

		$title = trim( $title );
		$url   = trim( $url );

		if ( '' === $url && '' === $title ) {
			return null;
		}

		if ( '' === $title ) {
			$title = '' !== $alt ? $alt : esc_html__( 'Partner', 'hello-elementor-child' );
		}

		return array(
			'url'    => $url,
			'alt'    => '' !== $alt ? $alt : $title,
			'title'  => $title,
			'width'  => $width,
			'height' => $height,
		);
	}

	/**
	 * Frontend / editor render.
	 *
	 * @return void
	 */
	protected function render(): void {
		$partners = $this->get_partners();

		if ( empty( $partners ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">' . esc_html__( 'No partner logos found. Check the gallery field name and ACF data on this project.', 'hello-elementor-child' ) . '</div>';
			}
			return;
		}
		?>
		<div class="mh-project-partners" data-mh-project-partners>
			<ul class="mh-project-partners__list" role="list">
				<?php foreach ( $partners as $partner ) : ?>
					<li class="mh-project-partners__item">
						<span class="mh-project-partners__badge">
							<?php if ( '' !== $partner['url'] ) : ?>
								<img
									class="mh-project-partners__logo"
									src="<?php echo esc_url( $partner['url'] ); ?>"
									alt="<?php echo esc_attr( $partner['alt'] ); ?>"
									<?php if ( $partner['width'] ) : ?>
										width="<?php echo esc_attr( (string) $partner['width'] ); ?>"
									<?php endif; ?>
									<?php if ( $partner['height'] ) : ?>
										height="<?php echo esc_attr( (string) $partner['height'] ); ?>"
									<?php endif; ?>
									loading="lazy"
									decoding="async"
								/>
							<?php endif; ?>
							<span class="mh-project-partners__title"><?php echo esc_html( $partner['title'] ); ?></span>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}
