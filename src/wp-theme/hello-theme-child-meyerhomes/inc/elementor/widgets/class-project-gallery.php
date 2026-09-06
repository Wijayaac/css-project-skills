<?php
/**
 * Elementor widget: Project Gallery.
 *
 * Taxonomy filter + CSS grid masonry shell. Loop item markup comes from an
 * Elementor Theme Builder Loop Item template (drag-and-drop editable).
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
 * Project Gallery widget.
 */
class MH_Project_Gallery_Widget extends Widget_Base {

	/**
	 * CPT slug.
	 */
	public const POST_TYPE = 'project';

	/**
	 * Taxonomy slug for filters.
	 */
	public const TAXONOMY = 'project-type';

	/**
	 * ACF field that controls grid span (`listing_image_size`).
	 */
	public const SIZE_FIELD = 'listing_image_size';

	/**
	 * Allowed listing image sizes.
	 *
	 * @var string[]
	 */
	private const SIZES = array( 'regular', 'medium', 'large' );

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'mh_project_gallery';
	}

	/**
	 * Widget title in the panel.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Project Gallery', 'hello-elementor-child' );
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
		return array( 'meyer-homes', 'general' );
	}

	/**
	 * Keywords for the widget search.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'project', 'gallery', 'masonry', 'filter', 'taxonomy', 'loop', 'meyer' );
	}

	/**
	 * Frontend script dependencies.
	 *
	 * @return string[]
	 */
	public function get_script_depends(): array {
		return array( 'mh-project-gallery' );
	}

	/**
	 * Frontend style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends(): array {
		return array( 'mh-project-gallery' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->register_query_controls();
		$this->register_filter_controls();
		$this->register_layout_controls();
		$this->register_filter_style_controls();
	}

	/**
	 * Query + Loop Item template controls.
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
			'loop_item_template',
			array(
				'label'       => esc_html__( 'Loop Item template', 'hello-elementor-child' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $this->get_loop_item_template_options(),
				'default'     => '',
				'description' => esc_html__( 'Theme Builder → Loop Item. Card design stays drag-and-drop editable.', 'hello-elementor-child' ),
			)
		);

		$this->add_control(
			'posts_per_page',
			array(
				'label'   => esc_html__( 'Projects to show', 'hello-elementor-child' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => -1,
				'min'     => -1,
				'description' => esc_html__( 'Use -1 for all projects.', 'hello-elementor-child' ),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => esc_html__( 'Order by', 'hello-elementor-child' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'date'       => esc_html__( 'Date', 'hello-elementor-child' ),
					'title'      => esc_html__( 'Title', 'hello-elementor-child' ),
					'menu_order' => esc_html__( 'Menu order', 'hello-elementor-child' ),
					'rand'       => esc_html__( 'Random', 'hello-elementor-child' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => esc_html__( 'Order', 'hello-elementor-child' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => array(
					'DESC' => esc_html__( 'Descending', 'hello-elementor-child' ),
					'ASC'  => esc_html__( 'Ascending', 'hello-elementor-child' ),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Filter bar controls.
	 *
	 * @return void
	 */
	private function register_filter_controls(): void {
		$this->start_controls_section(
			'section_filter',
			array(
				'label' => esc_html__( 'Filter', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_filter',
			array(
				'label'        => esc_html__( 'Show taxonomy filter', 'hello-elementor-child' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'hello-elementor-child' ),
				'label_off'    => esc_html__( 'No', 'hello-elementor-child' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'all_label',
			array(
				'label'     => esc_html__( '“All” label', 'hello-elementor-child' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'All', 'hello-elementor-child' ),
				'condition' => array(
					'show_filter' => 'yes',
				),
			)
		);

		$this->add_control(
			'hide_empty_terms',
			array(
				'label'        => esc_html__( 'Hide empty terms', 'hello-elementor-child' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'hello-elementor-child' ),
				'label_off'    => esc_html__( 'No', 'hello-elementor-child' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'show_filter' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Layout controls (gap / row height).
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

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => esc_html__( 'Grid columns', 'hello-elementor-child' ),
				'type'           => Controls_Manager::NUMBER,
				'default'        => 12,
				'tablet_default' => 6,
				'mobile_default' => 4,
				'min'            => 4,
				'max'            => 12,
				'selectors'      => array(
					'{{WRAPPER}} .mh-project-gallery__grid' => '--mh-gallery-cols: {{VALUE}};',
				),
				'description'    => esc_html__( 'Base track count. Regular/Medium/Large map to spans of this grid.', 'hello-elementor-child' ),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => esc_html__( 'Gap', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 48,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 12,
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-project-gallery__grid' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'row_height',
			array(
				'label'      => esc_html__( 'Row height', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 160,
						'max' => 560,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 320,
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-project-gallery__grid' => '--mh-gallery-row-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Filter pill styles.
	 *
	 * @return void
	 */
	private function register_filter_style_controls(): void {
		$this->start_controls_section(
			'section_style_filter',
			array(
				'label'     => esc_html__( 'Filter', 'hello-elementor-child' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_filter' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'filter_typography',
				'selector' => '{{WRAPPER}} .mh-project-gallery__filter-btn',
			)
		);

		$this->add_responsive_control(
			'filter_gap',
			array(
				'label'      => esc_html__( 'Gap', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 32,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 10,
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-project-gallery__filters' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'filter_spacing',
			array(
				'label'      => esc_html__( 'Bottom spacing', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 28,
				),
				'selectors'  => array(
					'{{WRAPPER}} .mh-project-gallery__filters' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'filter_tabs' );

		$this->start_controls_tab(
			'filter_tab_normal',
			array(
				'label' => esc_html__( 'Normal', 'hello-elementor-child' ),
			)
		);

		$this->add_control(
			'filter_color',
			array(
				'label'     => esc_html__( 'Text color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mh-project-gallery__filter-btn' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'filter_bg',
			array(
				'label'     => esc_html__( 'Background', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mh-project-gallery__filter-btn' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'filter_border_color',
			array(
				'label'     => esc_html__( 'Border color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mh-project-gallery__filter-btn' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'filter_tab_active',
			array(
				'label' => esc_html__( 'Active', 'hello-elementor-child' ),
			)
		);

		$this->add_control(
			'filter_color_active',
			array(
				'label'     => esc_html__( 'Text color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mh-project-gallery__filter-btn.is-active' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'filter_bg_active',
			array(
				'label'     => esc_html__( 'Background', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#00243D',
				'selectors' => array(
					'{{WRAPPER}} .mh-project-gallery__filter-btn.is-active' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'filter_border_color_active',
			array(
				'label'     => esc_html__( 'Border color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mh-project-gallery__filter-btn.is-active' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Loop Item templates as select options.
	 *
	 * @return array<int|string, string>
	 */
	private function get_loop_item_template_options(): array {
		$options = array(
			'' => esc_html__( '— Select template —', 'hello-elementor-child' ),
		);

		if ( ! did_action( 'elementor/loaded' ) ) {
			return $options;
		}

		$posts = get_posts(
			array(
				'post_type'      => 'elementor_library',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => '_elementor_template_type',
						'value' => 'loop-item',
					),
				),
			)
		);

		foreach ( $posts as $post ) {
			$options[ (string) $post->ID ] = $post->post_title;
		}

		return $options;
	}

	/**
	 * Frontend render.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings    = $this->get_settings_for_display();
		$template_id = absint( $settings['loop_item_template'] ?? 0 );

		if ( ! $template_id ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p class="mh-project-gallery__notice">' . esc_html__( 'Select a Loop Item template to render project cards.', 'hello-elementor-child' ) . '</p>';
			}
			return;
		}

		$per_page = isset( $settings['posts_per_page'] ) ? (int) $settings['posts_per_page'] : -1;
		$orderby  = sanitize_key( $settings['orderby'] ?? 'date' );
		$order    = strtoupper( (string) ( $settings['order'] ?? 'DESC' ) ) === 'ASC' ? 'ASC' : 'DESC';

		$query = new \WP_Query(
			array(
				'post_type'              => self::POST_TYPE,
				'post_status'            => 'publish',
				'posts_per_page'         => $per_page,
				'orderby'                => $orderby,
				'order'                  => $order,
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => true,
			)
		);

		$uid = 'mh-project-gallery-' . $this->get_id();

		echo '<div class="mh-project-gallery" id="' . esc_attr( $uid ) . '" data-mh-project-gallery>';

		if ( ( $settings['show_filter'] ?? '' ) === 'yes' ) {
			$this->render_filters( $settings, $uid );
		}

		echo '<div class="mh-project-gallery__grid" role="list">';

		if ( $query->have_posts() ) {
			// Same bootstrap as Elementor Pro Loop Grid skin.
			$this->enqueue_loop_document_css_meta( $template_id );

			while ( $query->have_posts() ) {
				$query->the_post();
				$this->render_item( $template_id );
			}
			wp_reset_postdata();
		} elseif ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			echo '<p class="mh-project-gallery__notice">' . esc_html__( 'No projects found.', 'hello-elementor-child' ) . '</p>';
		}

		echo '</div></div>';
	}

	/**
	 * Taxonomy filter bar.
	 *
	 * @param array<string, mixed> $settings Widget settings.
	 * @param string               $uid      Unique gallery id for aria controls.
	 * @return void
	 */
	private function render_filters( array $settings, string $uid ): void {
		$hide_empty = ( $settings['hide_empty_terms'] ?? '' ) === 'yes';
		$all_label  = $settings['all_label'] ?? __( 'All', 'hello-elementor-child' );

		$terms = get_terms(
			array(
				'taxonomy'   => self::TAXONOMY,
				'hide_empty' => $hide_empty,
			)
		);

		if ( is_wp_error( $terms ) ) {
			$terms = array();
		}

		echo '<div class="mh-project-gallery__filters" role="tablist" aria-label="' . esc_attr__( 'Filter projects by type', 'hello-elementor-child' ) . '">';

		printf(
			'<button type="button" class="mh-project-gallery__filter-btn is-active" role="tab" aria-selected="true" data-filter="all" aria-controls="%1$s">%2$s</button>',
			esc_attr( $uid ),
			esc_html( $all_label )
		);

		foreach ( $terms as $term ) {
			printf(
				'<button type="button" class="mh-project-gallery__filter-btn" role="tab" aria-selected="false" data-filter="%1$s" aria-controls="%2$s">%3$s</button>',
				esc_attr( $term->slug ),
				esc_attr( $uid ),
				esc_html( $term->name )
			);
		}

		echo '</div>';
	}

	/**
	 * Single grid item wrapper + Loop Item template.
	 *
	 * @param int $template_id Loop Item template ID.
	 * @return void
	 */
	private function render_item( int $template_id ): void {
		$post_id = get_the_ID();
		$size    = $this->get_listing_image_size( $post_id );
		$slugs   = wp_get_post_terms( $post_id, self::TAXONOMY, array( 'fields' => 'slugs' ) );

		if ( is_wp_error( $slugs ) ) {
			$slugs = array();
		}

		$classes = array(
			'mh-project-gallery__item',
			'mh-project-gallery__item--' . $size,
		);

		printf(
			'<article class="%1$s" role="listitem" data-size="%2$s" data-types="%3$s">',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $size ),
			esc_attr( implode( ' ', $slugs ) )
		);

		echo '<div class="mh-project-gallery__item-inner">';
		$this->print_loop_item( $template_id );
		echo '</div></article>';
	}

	/**
	 * Resolve ACF listing image size with fallback.
	 *
	 * @param int $post_id Project ID.
	 * @return string
	 */
	private function get_listing_image_size( int $post_id ): string {
		$size = 'regular';

		if ( function_exists( 'get_field' ) ) {
			$value = get_field( self::SIZE_FIELD, $post_id );
			if ( is_string( $value ) && $value !== '' ) {
				$size = $value;
			}
		} else {
			$meta = get_post_meta( $post_id, self::SIZE_FIELD, true );
			if ( is_string( $meta ) && $meta !== '' ) {
				$size = $meta;
			}
		}

		$size = sanitize_key( $size );

		return in_array( $size, self::SIZES, true ) ? $size : 'regular';
	}

	/**
	 * Print Elementor Loop Item the same way Loop Grid does.
	 *
	 * Loop Grid path:
	 *   print_dynamic_css( $post_id, $template_id );
	 *   $document->print_content();
	 *
	 * Avoid get_builder_content_for_display( ..., true ) — it dumps Post CSS
	 * (`.elementor-{id}`) instead of Loop CSS and breaks card/hover styles.
	 *
	 * @param int $template_id Template ID.
	 * @return void
	 */
	private function print_loop_item( int $template_id ): void {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		$post_id   = (int) get_the_ID();
		$document  = \Elementor\Plugin::$instance->documents->get( $template_id );

		if ( ! $document ) {
			return;
		}

		$this->print_loop_dynamic_css( $post_id, $template_id );

		// Same call as ElementorPro Loop Grid skin.
		$document->print_content();
	}

	/**
	 * Ensure Loop Item CSS meta exists (Elementor Pro Loop Grid does this once per render).
	 *
	 * @param int $template_id Loop Item template ID.
	 * @return void
	 */
	private function enqueue_loop_document_css_meta( int $template_id ): void {
		if ( $template_id < 1 ) {
			return;
		}

		$meta_key = class_exists( '\Elementor\Core\Files\CSS\Post' )
			? \Elementor\Core\Files\CSS\Post::META_KEY
			: '_elementor_css';

		if ( ! empty( get_post_meta( $template_id, $meta_key, true ) ) ) {
			return;
		}

		if ( ! class_exists( '\ElementorPro\Modules\LoopBuilder\Files\Css\Loop' ) ) {
			return;
		}

		$css_file = \ElementorPro\Modules\LoopBuilder\Files\Css\Loop::create( $template_id );
		if ( $css_file && method_exists( $css_file, 'update' ) ) {
			$css_file->update();
		}
	}

	/**
	 * Print per-post Loop Dynamic CSS (featured-image backgrounds, etc.).
	 *
	 * Mirrors ElementorPro\Modules\LoopBuilder\Files\Css\Loop_Css_Trait::print_dynamic_css().
	 *
	 * @param int $post_id     Current project post ID.
	 * @param int $template_id Loop Item template ID.
	 * @return void
	 */
	private function print_loop_dynamic_css( int $post_id, int $template_id ): void {
		if ( $post_id < 1 || $template_id < 1 ) {
			return;
		}

		if ( class_exists( '\ElementorPro\Modules\LoopBuilder\Files\Css\Loop_Dynamic_CSS' ) && class_exists( '\Elementor\Plugin' ) ) {
			$documents = \Elementor\Plugin::$instance->documents;
			$document  = method_exists( $documents, 'get_doc_for_frontend' )
				? $documents->get_doc_for_frontend( $template_id )
				: $documents->get( $template_id );

			if ( $document ) {
				$documents->switch_to_document( $document );

				$css_file = \ElementorPro\Modules\LoopBuilder\Files\Css\Loop_Dynamic_CSS::create( $post_id, $template_id );
				$post_css = $css_file ? $css_file->get_content() : '';

				if ( is_string( $post_css ) && $post_css !== '' ) {
					// Dynamic_CSS emits `.elementor-{post_id}`; Loop Grid rewrites to `.e-loop-item-{post_id}`.
					$css = str_replace( '.elementor-' . $post_id, '.e-loop-item-' . $post_id, $post_css );

					printf(
						'<style id="mh-loop-dynamic-%1$d-%2$d">%3$s</style>',
						$template_id,
						$post_id,
						$css // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor CSS.
					);
				}

				$documents->restore_document();
			}
		}

		// Always ensure featured image paints the card.
		$this->print_featured_image_fallback_css( $post_id );
	}

	/**
	 * Inject featured image as background on the Loop Item card.
	 *
	 * Targets `.card-project` (class on the Loop Item container) and the root e-con.
	 *
	 * @param int $post_id Project ID.
	 * @return void
	 */
	private function print_featured_image_fallback_css( int $post_id ): void {
		$url = get_the_post_thumbnail_url( $post_id, 'large' );

		if ( ! is_string( $url ) || $url === '' ) {
			return;
		}

		$selector = sprintf( '.e-loop-item-%d', $post_id );

		printf(
			'<style id="mh-gallery-bg-%1$d">%2$s .card-project,%2$s > .e-con,%2$s > .elementor-element{background-image:url(%3$s)!important;background-size:cover;background-position:center center;background-repeat:no-repeat;}</style>',
			$post_id,
			$selector,
			esc_url( $url )
		);
	}
}
