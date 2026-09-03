<?php
/**
 * Expandable content shortcode — auto-rotating accordion with progress bar.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the CTA arrow icon SVG.
 *
 * @return string
 */
function sb_expandable_cta_arrow_icon(): string {
	$icon_path = get_stylesheet_directory() . '/assets/icons/ArrowRight.svg';

	if ( ! is_readable( $icon_path ) ) {
		return '';
	}

	$svg = file_get_contents( $icon_path );

	return is_string( $svg ) ? $svg : '';
}

/**
 * Shortcode: [singh_expandable_content duration="6000"]
 *
 * Reads ACF fields from the current post:
 * caption, title, description, expandable_content (repeater), cta.
 *
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function sb_expandable_content_shortcode( $atts ): string {
	$atts = shortcode_atts(
		[
			'duration' => 6000,
		],
		$atts,
		'singh_expandable_content'
	);

	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}

	$rows = get_field( 'expandable_content' );

	if ( ! is_array( $rows ) || empty( $rows ) ) {
		return '';
	}

	$valid_rows = [];
	foreach ( $rows as $index => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$heading  = isset( $row['heading'] ) && is_string( $row['heading'] ) ? $row['heading'] : '';
		$content  = isset( $row['content'] ) && is_string( $row['content'] ) ? $row['content'] : '';
		$image    = isset( $row['image'] ) && is_array( $row['image'] ) ? $row['image'] : null;
		$image_id = ( $image && ! empty( $image['ID'] ) ) ? (int) $image['ID'] : 0;

		if ( $heading === '' && $content === '' && ! $image_id ) {
			continue;
		}

		$valid_rows[] = [
			'index'    => (int) $index,
			'heading'  => $heading,
			'content'  => $content,
			'image_id' => $image_id,
		];
	}

	if ( empty( $valid_rows ) ) {
		return '';
	}

	$caption     = get_field( 'caption' );
	$title       = get_field( 'title' );
	$description = get_field( 'description' );
	$cta         = get_field( 'cta' );

	$duration = absint( $atts['duration'] );
	if ( $duration < 1000 ) {
		$duration = 6000;
	}

	static $instance = 0;
	$instance++;
	$uid = (string) $instance;

	$GLOBALS['sb_expandable_used'] = true;

	ob_start();
	?>
	<section
		class="sb-expandable"
		data-sb-expandable
		data-duration="<?php echo esc_attr( (string) $duration ); ?>"
	>
		<div class="sb-expandable__layout">
			<div class="sb-expandable__copy">
				<?php if ( is_string( $caption ) && $caption !== '' ) : ?>
					<p class="sb-expandable__caption"><?php echo esc_html( $caption ); ?></p>
				<?php endif; ?>

				<?php if ( is_string( $title ) && $title !== '' ) : ?>
					<h2 class="sb-expandable__title"><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>

				<?php if ( is_string( $description ) && $description !== '' ) : ?>
					<div class="sb-expandable__description"><?php echo wp_kses_post( $description ); ?></div>
				<?php endif; ?>

				<div class="sb-expandable__body">
					<ul class="sb-expandable__list">
						<?php foreach ( $valid_rows as $rendered => $row ) : ?>
							<?php
							$is_first   = 0 === (int) $rendered;
							$tab_id     = 'sb-tab-' . $uid . '-' . $row['index'];
							$panel_id   = 'sb-panel-' . $uid . '-' . $row['index'];
							$item_class = 'sb-expandable__item' . ( $is_first ? ' is-active' : '' );
							?>
							<li class="<?php echo esc_attr( $item_class ); ?>">
								<h3>
									<button
										type="button"
										class="sb-expandable__trigger"
										aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>"
										aria-controls="<?php echo esc_attr( $panel_id ); ?>"
										id="<?php echo esc_attr( $tab_id ); ?>"
									>
										<span class="sb-expandable__trigger-label"><?php echo esc_html( $row['heading'] ); ?></span>
										<span class="sb-expandable__icon" aria-hidden="true"></span>
									</button>
								</h3>

								<div
									class="sb-expandable__panel"
									id="<?php echo esc_attr( $panel_id ); ?>"
									role="region"
									aria-labelledby="<?php echo esc_attr( $tab_id ); ?>"
								>
									<div class="sb-expandable__panel-inner">
										<?php if ( $row['content'] !== '' ) : ?>
											<div class="sb-expandable__content"><?php echo wp_kses_post( $row['content'] ); ?></div>
										<?php endif; ?>

										<?php if ( $row['image_id'] ) : ?>
											<figure class="sb-expandable__media">
												<?php
												echo wp_get_attachment_image(
													$row['image_id'],
													'large',
													false,
													[
														'loading'  => $is_first ? 'eager' : 'lazy',
														'decoding' => $is_first ? 'sync' : 'async',
														'width'    => 600,
														'height'   => 600,
														'sizes'    => '(min-width: 1100px) 600px, (min-width: 768px) 42vw, 100vw',
													]
												);
												?>
											</figure>
										<?php endif; ?>
									</div>
								</div>

								<span class="sb-expandable__progress" aria-hidden="true"></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<?php if ( is_array( $cta ) && ! empty( $cta['url'] ) ) : ?>
					<?php
					$cta_url    = $cta['url'];
					$cta_title  = ! empty( $cta['title'] ) ? $cta['title'] : __( 'Learn more', 'hello-elementor-child' );
					$cta_target = ! empty( $cta['target'] ) ? $cta['target'] : '';
					$cta_icon   = sb_expandable_cta_arrow_icon();
					?>
					<a
						class="sb-expandable__cta"
						href="<?php echo esc_url( $cta_url ); ?>"
						<?php if ( $cta_target ) : ?>
							target="<?php echo esc_attr( $cta_target ); ?>"
							<?php if ( '_blank' === $cta_target ) : ?>
								rel="noopener noreferrer"
							<?php endif; ?>
						<?php endif; ?>
					>
						<span class="sb-expandable__cta-label"><?php echo esc_html( $cta_title ); ?></span>
						<?php if ( $cta_icon ) : ?>
							<span class="sb-expandable__cta-icon" aria-hidden="true"><?php echo $cta_icon; ?></span>
						<?php endif; ?>
					</a>
				<?php endif; ?>
			</div>

			<div class="sb-expandable__stage" aria-hidden="true"></div>
		</div>
	</section>
	<?php

	return (string) ob_get_clean();
}

add_shortcode( 'singh_expandable_content', 'sb_expandable_content_shortcode' );
