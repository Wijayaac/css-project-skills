<?php
/**
 * Featured services shortcode — hover preview + linked service list.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the arrow icon SVG for service list items.
 *
 * @return string
 */
function mh_featured_services_arrow_icon(): string {
	$icon_path = get_stylesheet_directory() . '/assets/icons/arrow-up-right.svg';

	if ( ! is_readable( $icon_path ) ) {
		return '';
	}

	$svg = file_get_contents( $icon_path );

	return is_string( $svg ) ? $svg : '';
}

/**
 * Shortcode: [meyer_featured_services]
 *
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function mh_featured_services_shortcode( $atts ): string {
	$atts = shortcode_atts(
		[
			'heading' => 'Our work includes:',
			'limit'   => -1,
			'orderby' => 'menu_order',
			'order'   => 'ASC',
		],
		$atts,
		'meyer_featured_services'
	);

	$query = new WP_Query(
		[
			'post_type'      => 'service',
			'posts_per_page' => (int) $atts['limit'],
			'orderby'        => $atts['orderby'],
			'order'          => $atts['order'],
			'post_status'    => 'publish',
			'meta_query'     => [
				[
					'key'     => 'featured_service',
					'value'   => '1',
					'compare' => '=',
				],
			],
		]
	);

	if ( ! $query->have_posts() ) {
		return '';
	}

	$services = [];

	while ( $query->have_posts() ) {
		$query->the_post();

		$post_id   = get_the_ID();
		$image_url = get_the_post_thumbnail_url( $post_id, 'large' );

		if ( ! $image_url ) {
			continue;
		}

		$thumbnail_id = get_post_thumbnail_id( $post_id );
		$image_alt    = $thumbnail_id ? get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ) : '';

		$services[] = [
			'title' => get_the_title(),
			'url'   => get_permalink(),
			'image' => $image_url,
			'alt'   => is_string( $image_alt ) ? $image_alt : '',
		];
	}

	wp_reset_postdata();

	if ( empty( $services ) ) {
		return '';
	}

	$GLOBALS['mh_featured_services_used'] = true;

	$icon          = mh_featured_services_arrow_icon();
	$default_image = $services[0]['image'];
	$default_alt   = $services[0]['alt'];

	ob_start();
	?>
	<section class="mh-featured-services" data-mh-featured-services>
		<div class="mh-featured-services__preview">
			<img
				class="mh-featured-services__image"
				src="<?php echo esc_url( $default_image ); ?>"
				alt="<?php echo esc_attr( $default_alt ); ?>"
				data-mh-preview-img
			/>
		</div>

		<div class="mh-featured-services__content">
			<?php if ( ! empty( $atts['heading'] ) ) : ?>
				<p class="mh-featured-services__heading"><?php echo esc_html( $atts['heading'] ); ?></p>
			<?php endif; ?>

			<ul class="mh-featured-services__list">
				<?php foreach ( $services as $index => $service ) : ?>
					<li class="mh-featured-services__item-wrap">
						<a
							href="<?php echo esc_url( $service['url'] ); ?>"
							class="mh-featured-services__item<?php echo $index === 0 ? ' is-active' : ''; ?>"
							data-image="<?php echo esc_url( $service['image'] ); ?>"
							data-alt="<?php echo esc_attr( $service['alt'] ); ?>"
						>
							<span class="mh-featured-services__number"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
							<span class="mh-featured-services__title"><?php echo esc_html( $service['title'] ); ?></span>
							<span class="mh-featured-services__icon"><?php echo $icon; ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php

	return (string) ob_get_clean();
}

add_shortcode( 'meyer_featured_services', 'mh_featured_services_shortcode' );
