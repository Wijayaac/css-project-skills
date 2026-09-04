<?php
/**
 * Elementor widget: ACF FAQ Accordion.
 *
 * Renders an Elementor Nested Accordion–compatible markup from an ACF repeater.
 * Sub-field names are fixed for portability across themes:
 * - question (text)
 * - content  (wysiwyg)
 *
 * Only the repeater field name is configurable in the editor.
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
 * ACF FAQ Accordion widget.
 */
class MH_ACF_FAQ_Accordion_Widget extends Widget_Base {

	/**
	 * Fixed ACF sub-field name for the question.
	 */
	public const SUB_QUESTION = 'question';

	/**
	 * Fixed ACF sub-field name for the answer body.
	 */
	public const SUB_CONTENT = 'content';

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'mh_acf_faq_accordion';
	}

	/**
	 * Widget title in the panel.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'ACF FAQ Accordion', 'hello-elementor-child' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-accordion';
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
		return array( 'faq', 'accordion', 'acf', 'repeater', 'meyer' );
	}

	/**
	 * Frontend script dependencies.
	 *
	 * @return string[]
	 */
	public function get_script_depends(): array {
		return array( 'mh-acf-faq-accordion' );
	}

	/**
	 * Frontend style dependencies.
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
			'repeater_field',
			array(
				'label'       => esc_html__( 'Repeater field name', 'hello-elementor-child' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'faq_content',
				'placeholder' => 'faq_content',
				'description' => esc_html__( 'ACF repeater field name. Sub-fields must be named "question" (text) and "content" (WYSIWYG).', 'hello-elementor-child' ),
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
				'description' => esc_html__( 'Post that holds the ACF repeater.', 'hello-elementor-child' ),
			)
		);

		$this->add_control(
			'accordion_class',
			array(
				'label'       => esc_html__( 'Accordion CSS class', 'hello-elementor-child' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => 'container-accordion-align',
				'description' => esc_html__( 'Extra class on the accordion wrapper (e.g. container-accordion-align).', 'hello-elementor-child' ),
			)
		);

		$this->add_control(
			'show_numbers',
			array(
				'label'        => esc_html__( 'Show item numbers', 'hello-elementor-child' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'hello-elementor-child' ),
				'label_off'    => esc_html__( 'No', 'hello-elementor-child' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'faq_schema',
			array(
				'label'        => esc_html__( 'Output FAQ schema', 'hello-elementor-child' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'hello-elementor-child' ),
				'label_off'    => esc_html__( 'No', 'hello-elementor-child' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab controls (typography + colors).
	 *
	 * @return void
	 */
	private function register_style_controls(): void {
		$this->start_controls_section(
			'section_style_question',
			array(
				'label' => esc_html__( 'Question', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'question_typography',
				'selector' => '{{WRAPPER}} .e-n-accordion-item-title-text',
			)
		);

		$this->add_control(
			'question_color',
			array(
				'label'     => esc_html__( 'Color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .e-n-accordion-item-title-text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_answer',
			array(
				'label' => esc_html__( 'Answer', 'hello-elementor-child' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'answer_typography',
				'selector' => '{{WRAPPER}} .mh-faq-accordion__answer',
			)
		);

		$this->add_control(
			'answer_color',
			array(
				'label'     => esc_html__( 'Color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mh-faq-accordion__answer' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

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
				'label'     => esc_html__( 'Color', 'hello-elementor-child' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .e-n-accordion-item-title-icon svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => esc_html__( 'Size', 'hello-elementor-child' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 8,
						'max' => 48,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .e-n-accordion-item-title-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
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
	 * Load FAQ rows from ACF.
	 *
	 * @return array<int, array{question: string, content: string}>
	 */
	private function get_faq_rows(): array {
		if ( ! function_exists( 'get_field' ) ) {
			return array();
		}

		$settings       = $this->get_settings_for_display();
		$repeater_field = sanitize_key( (string) ( $settings['repeater_field'] ?? 'faq_content' ) );

		if ( '' === $repeater_field ) {
			return array();
		}

		$post_id = $this->get_source_post_id();
		$rows    = get_field( $repeater_field, $post_id );

		if ( ! is_array( $rows ) || empty( $rows ) ) {
			return array();
		}

		$normalized = array();

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$question = isset( $row[ self::SUB_QUESTION ] ) ? trim( (string) $row[ self::SUB_QUESTION ] ) : '';
			$content  = isset( $row[ self::SUB_CONTENT ] ) ? (string) $row[ self::SUB_CONTENT ] : '';

			if ( '' === $question && '' === trim( wp_strip_all_tags( $content ) ) ) {
				continue;
			}

			$normalized[] = array(
				'question' => $question,
				'content'  => $content,
			);
		}

		return $normalized;
	}

	/**
	 * Chevron SVG icons (opened / closed).
	 *
	 * @return string
	 */
	private function render_icons(): string {
		$chevron_up = '<svg aria-hidden="true" class="e-font-icon-svg e-fas-chevron-up" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M240.971 130.524l194.343 194.343c9.373 9.373 9.373 24.569 0 33.941l-22.667 22.667c-9.357 9.357-24.522 9.375-33.901.04L224 227.495 69.255 381.516c-9.379 9.335-24.544 9.317-33.901-.04l-22.667-22.667c-9.373-9.373-9.373-24.569 0-33.941L207.03 130.525c9.372-9.373 24.568-9.373 33.941-.001z"></path></svg>';

		$chevron_down = '<svg aria-hidden="true" class="e-font-icon-svg e-fas-chevron-down" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M207.029 381.476L12.686 187.132c-9.373-9.373-9.373-24.569 0-33.941l22.667-22.667c9.357-9.357 24.522-9.375 33.901-.04L224 284.505l154.745-154.021c9.379-9.335 24.544-9.317 33.901.04l22.667 22.667c9.373 9.373 9.373 24.569 0 33.941L240.971 381.476c-9.373 9.372-24.569 9.372-33.942 0z"></path></svg>';

		return sprintf(
			'<span class="e-n-accordion-item-title-icon"><span class="e-opened">%1$s</span><span class="e-closed">%2$s</span></span>',
			$chevron_up,
			$chevron_down
		);
	}

	/**
	 * FAQPage JSON-LD schema.
	 *
	 * @param array<int, array{question: string, content: string}> $rows FAQ rows.
	 * @return void
	 */
	private function render_schema( array $rows ): void {
		$entities = array();

		foreach ( $rows as $row ) {
			$answer_text = trim( wp_strip_all_tags( $row['content'] ) );

			if ( '' === $row['question'] || '' === $answer_text ) {
				continue;
			}

			$entities[] = array(
				'@type'          => 'Question',
				'name'           => $row['question'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $answer_text,
				),
			);
		}

		if ( empty( $entities ) ) {
			return;
		}

		$schema = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $entities,
		);

		printf(
			'<script type="application/ld+json">%s</script>',
			wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
		);
	}

	/**
	 * Frontend / editor render.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$rows     = $this->get_faq_rows();

		if ( empty( $rows ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">' . esc_html__( 'No FAQ rows found. Check the repeater field name and ACF data on this post.', 'hello-elementor-child' ) . '</div>';
			}
			return;
		}

		$uid             = 'mh-faq-' . $this->get_id();
		$accordion_class = trim( (string) ( $settings['accordion_class'] ?? '' ) );
		$show_numbers    = 'yes' === ( $settings['show_numbers'] ?? '' );
		$output_schema   = 'yes' === ( $settings['faq_schema'] ?? '' );
		$wrapper_classes = array( 'e-n-accordion', 'mh-faq-accordion' );

		if ( '' !== $accordion_class ) {
			foreach ( preg_split( '/\s+/', $accordion_class ) as $class ) {
				$class = sanitize_html_class( $class );
				if ( '' !== $class ) {
					$wrapper_classes[] = $class;
				}
			}
		}

		$icons = $this->render_icons();
		?>
		<div
			class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
			data-mh-faq-accordion
			aria-label="<?php echo esc_attr__( 'Accordion. Open links with Enter or Space, close with Escape, and navigate with Arrow Keys', 'hello-elementor-child' ); ?>"
		>
			<?php foreach ( $rows as $index => $row ) : ?>
				<?php
				$item_id    = $uid . '-item-' . ( $index + 1 );
				$panel_id   = $item_id . '-panel';
				$button_id  = $item_id . '-trigger';
				$number     = str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT );
				$question   = $row['question'];
				$content    = $row['content'];
				?>
				<div class="e-n-accordion-item mh-faq-accordion__item" data-mh-faq-item>
					<button
						type="button"
						id="<?php echo esc_attr( $button_id ); ?>"
						class="e-n-accordion-item-title mh-faq-accordion__trigger"
						data-accordion-index="<?php echo esc_attr( (string) ( $index + 1 ) ); ?>"
						aria-expanded="false"
						aria-controls="<?php echo esc_attr( $panel_id ); ?>"
					>
						<span class="e-n-accordion-item-title-header">
							<span class="e-n-accordion-item-title-text">
								<?php if ( $show_numbers ) : ?>
									<span class="accordion-num"><?php echo esc_html( $number ); ?></span>
								<?php endif; ?>
								<?php echo esc_html( $question ); ?>
							</span>
						</span>
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup.
						echo $icons;
						?>
					</button>
					<div
						id="<?php echo esc_attr( $panel_id ); ?>"
						role="region"
						aria-labelledby="<?php echo esc_attr( $button_id ); ?>"
						class="mh-faq-accordion__panel"
						aria-hidden="true"
					>
						<div class="mh-faq-accordion__panel-inner e-con-inner">
							<div class="mh-faq-accordion__answer elementor-widget-text-editor">
								<?php echo wp_kses_post( $content ); ?>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php

		if ( $output_schema ) {
			$this->render_schema( $rows );
		}
	}
}
