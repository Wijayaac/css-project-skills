<?php

/**
 * Floorplan tabs shortcode for model posts.
 *
 * @package Copperwood
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Build a short tab label from the repeater name field.
 */
function copperwood_floorplan_tab_label($name)
{
	$name = trim((string) $name);

	if ($name === '') {
		return '';
	}

	if (strpos($name, '/') !== false) {
		return trim(strtok($name, '/'));
	}

	return $name;
}

/**
 * Build the stats line under the floorplan title.
 */
function copperwood_floorplan_stats_line($post_id)
{
	$parts = array();
	$square_feet = get_field('square_feet', $post_id);
	$bedrooms    = get_field('bedrooms', $post_id);
	$bathrooms   = get_field('bathrooms', $post_id);

	if ($square_feet) {
		$parts[] = strtoupper((string) $square_feet) . ' SQ. FT.';
	}

	if ($bedrooms) {
		$parts[] = strtoupper((string) $bedrooms) . ' BEDROOMS';
	}

	if ($bathrooms) {
		$parts[] = strtoupper((string) $bathrooms) . ' BATHROOMS';
	}

	if (empty($parts)) {
		return '';
	}

	return implode(' | ', $parts) . ' |';
}

/**
 * Resolve the model heading from the home-type taxonomy.
 */
function copperwood_floorplan_heading($post_id)
{
	$terms = get_the_terms($post_id, 'home-type');

	if ($terms && ! is_wp_error($terms)) {
		return $terms[0]->name;
	}

	return get_the_title($post_id);
}

/**
 * Read repeater rows directly from post meta when ACF name lookup fails.
 */
function copperwood_get_floorplan_rows_from_meta($post_id)
{
	$rows  = array();
	$count = (int) get_post_meta($post_id, 'floorplan', true);

	if ($count <= 0) {
		$i = 0;

		while ($i < 20 && metadata_exists('post', $post_id, "floorplan_{$i}_image")) {
			$i++;
		}

		$count = $i;
	}

	for ($i = 0; $i < $count; $i++) {
		$name     = get_post_meta($post_id, "floorplan_{$i}_name", true);
		$image_id = get_post_meta($post_id, "floorplan_{$i}_image", true);

		if (! $image_id) {
			continue;
		}

		if (function_exists('acf_get_attachment')) {
			$image = acf_get_attachment($image_id);
		} else {
			$image_url = wp_get_attachment_image_url($image_id, 'full');
			$image     = $image_url ? array(
				'url' => $image_url,
				'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
			) : null;
		}

		if (! $image) {
			continue;
		}

		$rows[] = array(
			'name'  => $name,
			'image' => $image,
		);
	}

	return $rows;
}

/**
 * Load floorplan repeater rows from the model post.
 */
function copperwood_get_floorplan_items($post_id)
{
	$items = array();

	if (! function_exists('get_field')) {
		return $items;
	}

	$rows = get_field('field_6a22c21773560', $post_id);

	if (! is_array($rows) || empty($rows)) {
		$rows = copperwood_get_floorplan_rows_from_meta($post_id);
	}

	if (is_array($rows) && ! empty($rows)) {
		foreach ($rows as $row) {
			$name  = isset($row['name']) ? $row['name'] : '';
			$image = isset($row['image']) ? $row['image'] : null;

			if (! $image) {
				continue;
			}

			$label = copperwood_floorplan_tab_label($name);

			$items[] = array(
				'name'     => $name ? $name : $label,
				'label'    => $label ? $label : ('Floor ' . (count($items) + 1)),
				'image'    => $image,
				'subtitle' => strtoupper((string) ($name ? $name : $label)),
			);
		}

		return $items;
	}

	if (function_exists('have_rows') && have_rows('field_6a22c21773560', $post_id)) {
		while (have_rows('field_6a22c21773560', $post_id)) {
			the_row();

			$name  = get_sub_field('name');
			$image = get_sub_field('image');

			if (! $image) {
				continue;
			}

			$label = copperwood_floorplan_tab_label($name);

			$items[] = array(
				'name'     => $name ? $name : $label,
				'label'    => $label ? $label : ('Floor ' . (count($items) + 1)),
				'image'    => $image,
				'subtitle' => strtoupper((string) ($name ? $name : $label)),
			);
		}
	}

	return $items;
}

/**
 * Render floorplan tabs markup.
 */
function copperwood_get_floorplans_markup($post_id = null)
{
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if (! $post_id) {
		return '';
	}

	$items = copperwood_get_floorplan_items($post_id);

	if (empty($items)) {
		return '';
	}

	$instance   = function_exists('wp_unique_id') ? wp_unique_id('cw-floorplans-') : uniqid('cw-floorplans-');
	$heading    = copperwood_floorplan_heading($post_id);
	$stats_line = copperwood_floorplan_stats_line($post_id);
	$download = get_field('floorplan_url', $post_id);

	if (! is_array($download) || empty($download['url'])) {
		$download = get_field('field_6a22c07d1bcbd', $post_id);
	}
	$download_url = '';

	if (is_array($download) && ! empty($download['url'])) {
		$download_url = $download['url'];
	}

	ob_start();
?>
	<section class="cw-floorplans" data-cw-floorplans id="<?php echo esc_attr($instance); ?>">
		<div class="cw-floorplans__main">
			<div class="cw-floorplans__viewer">
				<?php foreach ($items as $index => $item) : ?>
					<?php
					$is_active = $index === 0;
					$tab_id    = $instance . '-tab-' . $index;
					$panel_id  = $instance . '-panel-' . $index;
					$alt       = ! empty($item['image']['alt']) ? $item['image']['alt'] : $item['label'];
					?>
					<div
						id="<?php echo esc_attr($panel_id); ?>"
						class="cw-floorplans__panel<?php echo $is_active ? ' is-active' : ''; ?>"
						role="tabpanel"
						aria-labelledby="<?php echo esc_attr($tab_id); ?>"
						data-subtitle="<?php echo esc_attr($item['subtitle']); ?>"
						<?php echo $is_active ? '' : ' hidden'; ?>>
						<img
							class="cw-floorplans__image"
							src="<?php echo esc_url($item['image']['url']); ?>"
							alt="<?php echo esc_attr($alt); ?>" />
					</div>
				<?php endforeach; ?>
			</div>

			<div class="cw-floorplans__details">
				<h2 class="cw-floorplans__title"><?php echo esc_html($heading); ?></h2>
				<p class="cw-floorplans__subtitle"><?php echo esc_html($items[0]['subtitle']); ?></p>

				<?php if ($stats_line) : ?>
					<p class="cw-floorplans__stats"><?php echo esc_html($stats_line); ?></p>
				<?php endif; ?>

				<?php if ($download_url) : ?>
					<a class="cw-floorplans__download" href="<?php echo esc_url($download_url); ?>" download>
						<?php esc_html_e('Download Floorplan', 'copperwood'); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="cw-floorplans__tabs" role="tablist" aria-label="<?php esc_attr_e('Floorplan levels', 'copperwood'); ?>">
			<?php foreach ($items as $index => $item) : ?>
				<?php
				$is_active = $index === 0;
				$tab_id    = $instance . '-tab-' . $index;
				$panel_id  = $instance . '-panel-' . $index;
				?>
				<button
					type="button"
					id="<?php echo esc_attr($tab_id); ?>"
					class="cw-floorplans__tab<?php echo $is_active ? ' is-active' : ''; ?>"
					role="tab"
					aria-controls="<?php echo esc_attr($panel_id); ?>"
					aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
					data-floorplan-index="<?php echo esc_attr((string) $index); ?>">
					<?php echo esc_html(strtoupper($item['label'])); ?>
				</button>
			<?php endforeach; ?>
		</div>
	</section>
<?php
	return ob_get_clean();
}

/**
 * Floorplan tabs shortcode.
 */
function copperwood_floorplans_shortcode($atts)
{
	$atts = shortcode_atts(
		array(
			'post_id' => 0,
		),
		$atts,
		'copperwood_floorplans'
	);

	$post_id = (int) $atts['post_id'];

	return copperwood_get_floorplans_markup($post_id ? $post_id : null);
}

add_shortcode('copperwood_floorplans', 'copperwood_floorplans_shortcode');
