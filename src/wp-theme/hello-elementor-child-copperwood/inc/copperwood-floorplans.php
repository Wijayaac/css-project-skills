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
 * Normalize floorplan name for display (subtitle).
 */
function copperwood_floorplan_subtitle($name)
{
	$name = trim((string) $name);

	if ($name === '') {
		return '';
	}

	return strtoupper($name);
}

/**
 * Build a short tab label from the repeater name field.
 * Strips square footage and text after / - – — ‒ delimiters.
 */
function copperwood_floorplan_tab_label($name)
{
	$name = trim((string) $name);

	if ($name === '') {
		return '';
	}

	// Keep only the floor name before common separators.
	$parts = preg_split('/\s*[\/\-–—‒]\s*/u', $name, 2);
	$label = trim($parts[0]);

	// Remove trailing square footage (565 SQ.FT., 660 SQ. FT., etc.).
	$label = preg_replace(
		'/\s*\d[\d,]*\s*(?:SQ\.?\s*FT\.?|SQUARE\s+FEET)\.?\s*$/iu',
		'',
		$label
	);

	$label = trim($label, " \t\n\r\0\x0B-/–—‒");

	return $label !== '' ? strtoupper($label) : strtoupper(trim($name));
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
 * Home type slugs that suppress floorplan output (e.g. Show Home listings).
 */
function copperwood_floorplan_excluded_home_type_slugs()
{
	return apply_filters(
		'copperwood_floorplan_excluded_home_type_slugs',
		array('show-home')
	);
}

/**
 * Assigned home-type terms for a post.
 */
function copperwood_get_home_type_terms($post_id)
{
	$terms = get_the_terms($post_id, 'home-type');

	if (is_wp_error($terms) || empty($terms)) {
		return array();
	}

	return $terms;
}

/**
 * Whether the post is tagged with a home type that hides floorplans.
 */
function copperwood_post_hides_floorplans($post_id)
{
	$excluded = array_map('sanitize_title', copperwood_floorplan_excluded_home_type_slugs());

	foreach (copperwood_get_home_type_terms($post_id) as $term) {
		if (in_array($term->slug, $excluded, true)) {
			return true;
		}
	}

	return false;
}

/**
 * Display name for the model home type (e.g. "The Aspen").
 * Skips excluded slugs such as Show Home when multiple terms are assigned.
 */
function copperwood_get_model_home_type_name($post_id)
{
	$excluded = array_map('sanitize_title', copperwood_floorplan_excluded_home_type_slugs());

	foreach (copperwood_get_home_type_terms($post_id) as $term) {
		if (! in_array($term->slug, $excluded, true)) {
			return $term->name;
		}
	}

	return '';
}

/**
 * Resolve the floorplan section heading from home-type, then post title.
 */
function copperwood_floorplan_heading($post_id)
{
	$home_type = copperwood_get_model_home_type_name($post_id);

	if ($home_type !== '') {
		return $home_type;
	}

	return get_the_title($post_id);
}

/**
 * Post types that support floorplan tab data.
 */
function copperwood_floorplan_post_types()
{
	return apply_filters('copperwood_floorplan_post_types', array('model'));
}

/**
 * Whether a post can render floorplan tabs.
 */
function copperwood_post_supports_floorplans($post_id)
{
	$post = get_post($post_id);

	if (! $post) {
		return false;
	}

	return in_array($post->post_type, copperwood_floorplan_post_types(), true);
}

/**
 * Repeater field key for floorplan rows.
 */
function copperwood_floorplan_field_key()
{
	return 'field_6a22c21773560';
}

/**
 * Normalize a floorplan image to an array with a valid URL.
 */
function copperwood_floorplan_normalize_image($image)
{
	if (is_array($image)) {
		if (empty($image['url'])) {
			return null;
		}

		return $image;
	}

	if (is_numeric($image)) {
		$image_id = (int) $image;

		if (function_exists('acf_get_attachment')) {
			$attachment = acf_get_attachment($image_id);
		} else {
			$image_url = wp_get_attachment_image_url($image_id, 'full');
			$attachment = $image_url ? array(
				'url' => $image_url,
				'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
			) : null;
		}

		if (! is_array($attachment) || empty($attachment['url'])) {
			return null;
		}

		return $attachment;
	}

	return null;
}

/**
 * Read repeater rows from post meta using the ACF field key count only.
 */
function copperwood_get_floorplan_rows_from_meta($post_id)
{
	$rows  = array();
	$count = (int) get_post_meta($post_id, copperwood_floorplan_field_key(), true);

	if ($count <= 0) {
		return $rows;
	}

	for ($i = 0; $i < $count; $i++) {
		$name  = get_post_meta($post_id, "floorplan_{$i}_name", true);
		$image = copperwood_floorplan_normalize_image(
			get_post_meta($post_id, "floorplan_{$i}_image", true)
		);

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
 * Build floorplan items from repeater rows.
 */
function copperwood_build_floorplan_items_from_rows($rows)
{
	$items = array();

	if (! is_array($rows)) {
		return $items;
	}

	foreach ($rows as $row) {
		$name  = isset($row['name']) ? $row['name'] : '';
		$image = copperwood_floorplan_normalize_image(
			isset($row['image']) ? $row['image'] : null
		);

		if (! $image) {
			continue;
		}

		$label    = copperwood_floorplan_tab_label($name);
		$subtitle = copperwood_floorplan_subtitle($name);

		$items[] = array(
			'name'     => $name ? $name : $label,
			'label'    => $label ? $label : ('FLOOR ' . (count($items) + 1)),
			'image'    => $image,
			'subtitle' => $subtitle ? $subtitle : $label,
		);
	}

	return $items;
}

/**
 * Load floorplan repeater rows from the model post.
 */
function copperwood_get_floorplan_items($post_id)
{
	if (! function_exists('get_field')) {
		return array();
	}

	$field_key = copperwood_floorplan_field_key();
	$rows      = get_field($field_key, $post_id);

	// Empty repeater from ACF — do not read orphaned post meta.
	if (is_array($rows)) {
		return copperwood_build_floorplan_items_from_rows($rows);
	}

	// Fallback only when ACF lookup fails but the field key count exists.
	if (metadata_exists('post', $post_id, $field_key)) {
		$rows = copperwood_get_floorplan_rows_from_meta($post_id);

		return copperwood_build_floorplan_items_from_rows($rows);
	}

	return array();
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

	if (! copperwood_post_supports_floorplans($post_id)) {
		return '';
	}

	if (copperwood_post_hides_floorplans($post_id)) {
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
					<?php echo esc_html($item['label']); ?>
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
		'copperwood_floorplans_v2'
	);

	$post_id = (int) $atts['post_id'];

	return copperwood_get_floorplans_markup($post_id ? $post_id : null);
}

add_shortcode('copperwood_floorplans_v2', 'copperwood_floorplans_shortcode');
add_shortcode('copperwood_floorplans', 'copperwood_floorplans_shortcode');
