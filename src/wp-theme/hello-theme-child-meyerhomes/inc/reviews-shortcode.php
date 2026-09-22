<?php
/**
 * Reviews masonry shortcode.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default relative speed weights per column (kept for markup hooks).
 *
 * @return float[]
 */
function mh_reviews_default_speeds() {
	return array( 1, 1, 1, 1 );
}

/**
 * Read an ACF/meta string field safely (no fatal if ACF missing / wrong type).
 *
 * @param string $key     Field name.
 * @param int    $post_id Post ID.
 * @return string
 */
function mh_reviews_get_string_field( $key, $post_id ) {
	$value = null;

	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $key, $post_id );
	}

	if ( null === $value || false === $value || '' === $value ) {
		$value = get_post_meta( $post_id, $key, true );
	}

	if ( is_array( $value ) ) {
		return '';
	}

	return is_string( $value ) ? $value : ( is_scalar( $value ) ? (string) $value : '' );
}

/**
 * Render a single testimonial card.
 *
 * Staging ACF fields: name, position, content (WYSIWYG).
 * Post title is used as the quoted headline.
 *
 * @param WP_Post $post Testimonial post object.
 * @return string
 */
function mh_reviews_render_card( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	$headline = get_the_title( $post );
	$content  = mh_reviews_get_string_field( 'content', (int) $post->ID );
	$name     = mh_reviews_get_string_field( 'name', (int) $post->ID );
	$position = mh_reviews_get_string_field( 'position', (int) $post->ID );

	if ( '' === $headline && '' === $content && '' === $name ) {
		return '';
	}

	$attribution = $name;
	if ( '' !== $name && '' !== $position ) {
		$attribution = $name . ', ' . $position;
	}

	ob_start();
	?>
	<article class="mh-reviews__card">
		<?php if ( '' !== $headline ) : ?>
			<h3 class="mh-reviews__headline">&ldquo;<?php echo esc_html( $headline ); ?>&rdquo;</h3>
		<?php endif; ?>

		<?php if ( '' !== $content ) : ?>
			<div class="mh-reviews__body"><?php echo wp_kses_post( $content ); ?></div>
		<?php endif; ?>

		<?php if ( '' !== $attribution ) : ?>
			<p class="mh-reviews__author">&mdash; <?php echo esc_html( $attribution ); ?></p>
		<?php endif; ?>
	</article>
	<?php
	return (string) ob_get_clean();
}

/**
 * Distribute review posts into columns round-robin.
 *
 * @param WP_Post[] $posts   Review posts.
 * @param int       $columns Number of columns.
 * @return WP_Post[][]
 */
function mh_reviews_split_into_columns( $posts, $columns ) {
	$columns = max( 1, (int) $columns );
	$groups  = array_fill( 0, $columns, array() );

	foreach ( $posts as $index => $post ) {
		$groups[ $index % $columns ][] = $post;
	}

	return $groups;
}

/**
 * Shortcode callback: [meyer_reviews].
 *
 * @param array<string, string> $atts Shortcode attributes.
 * @return string
 */
function mh_reviews_shortcode( $atts ) {
	try {
		return mh_reviews_shortcode_render( $atts );
	} catch ( \Throwable $e ) {
		// Keep Elementor save alive; surface real cause in PHP error log.
		error_log(
			sprintf(
				'[meyer_reviews] %s in %s:%d',
				$e->getMessage(),
				$e->getFile(),
				$e->getLine()
			)
		);
		return '';
	}
}

/**
 * Internal render for [meyer_reviews].
 *
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function mh_reviews_shortcode_render( $atts ) {
	$atts = shortcode_atts(
		array(
			'columns' => '4',
			'limit'   => '-1',
			'orderby' => 'menu_order',
			'order'   => 'ASC',
		),
		$atts,
		'meyer_reviews'
	);

	$columns = max( 1, (int) $atts['columns'] );
	$limit   = (int) $atts['limit'];

	$query = new WP_Query(
		array(
			'post_type'      => 'testimonial',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => sanitize_key( $atts['orderby'] ),
			'order'          => strtoupper( $atts['order'] ) === 'DESC' ? 'DESC' : 'ASC',
			'no_found_rows'  => true,
		)
	);

	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		return '';
	}

	$GLOBALS['mh_reviews_shortcode_used'] = true;

	$column_groups = mh_reviews_split_into_columns( $query->posts, $columns );
	$speeds        = mh_reviews_default_speeds();

	wp_reset_postdata();

	ob_start();
	?>
	<section class="mh-reviews" data-mh-reviews>
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
	return (string) ob_get_clean();
}
add_shortcode( 'meyer_reviews', 'mh_reviews_shortcode' );

/**
 * Seed sample reviews once when the theme is activated and no reviews exist.
 *
 * @return void
 */
function mh_reviews_seed_sample_data() {
	if ( get_option( 'mh_testimonials_seeded' ) ) {
		return;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'testimonial',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	if ( ! empty( $existing ) ) {
		update_option( 'mh_testimonials_seeded', 1, false );
		return;
	}

	$samples = array(
		array(
			'title'    => 'Attention to detail that exceeds expectations.',
			'content'  => '<p>From the first meeting to the final walkthrough, the team was responsive, organized, and genuinely invested in our vision. Every finish feels intentional.</p>',
			'name'     => 'Janie & Robin',
			'position' => '',
		),
		array(
			'title'    => 'A seamless build experience.',
			'content'  => '<p>Communication was clear at every stage. We always knew what was happening next, and the craftsmanship in our home speaks for itself.</p>',
			'name'     => 'Michael T.',
			'position' => '',
		),
		array(
			'title'    => 'They listened and delivered.',
			'content'  => '<p>We had a long list of must-haves and they found thoughtful solutions for each one without compromising the design. We could not be happier with the result.</p>',
			'name'     => 'Sarah & David',
			'position' => '',
		),
		array(
			'title'    => 'Quality you can see and feel.',
			'content'  => '<p>The materials, the layout, the lighting — everything was considered. Friends comment on how warm and polished the space feels the moment they walk in.</p>',
			'name'     => 'Elena R.',
			'position' => '',
		),
		array(
			'title'    => 'Professional from start to finish.',
			'content'  => '<p>Timelines were realistic, updates were consistent, and the site was always well managed. It made a stressful process feel manageable.</p>',
			'name'     => 'Chris & Amanda',
			'position' => '',
		),
		array(
			'title'    => 'Exceeded what we imagined.',
			'content'  => '<p>We came in with inspiration photos and left with a home that feels uniquely ours. The team balanced creativity with practical living beautifully.</p>',
			'name'     => 'Patricia L.',
			'position' => '',
		),
		array(
			'title'    => 'Craftsmanship at every turn.',
			'content'  => '<p>Cabinetry, trim, tile work — the details matter and they nailed them. This is the kind of build quality you notice years later.</p>',
			'name'     => 'James W.',
			'position' => '',
		),
		array(
			'title'    => 'We would choose them again.',
			'content'  => '<p>Transparent pricing, strong design guidance, and a finished home we are proud to show off. Highly recommend to anyone building custom.</p>',
			'name'     => 'Nicole & Greg',
			'position' => '',
		),
	);

	foreach ( $samples as $sample ) {
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'testimonial',
				'post_title'  => $sample['title'],
				'post_status' => 'publish',
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		if ( function_exists( 'update_field' ) ) {
			update_field( 'content', $sample['content'], $post_id );
			update_field( 'name', $sample['name'], $post_id );
			update_field( 'position', $sample['position'], $post_id );
		} else {
			update_post_meta( $post_id, 'content', $sample['content'] );
			update_post_meta( $post_id, 'name', $sample['name'] );
			update_post_meta( $post_id, 'position', $sample['position'] );
		}
	}

	update_option( 'mh_testimonials_seeded', 1, false );
}
add_action( 'after_switch_theme', 'mh_reviews_seed_sample_data' );


