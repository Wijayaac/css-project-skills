<?php

/**
 * Floorplan tabs shortcode for model posts.
 *
 * @package Granville
 */

if (! defined('ABSPATH')) {
	exit;
}

function granville_floorplan_display_title($name)
{
	$name = trim((string) $name);

	if ($name === '') {
		return '';
	}

	return ucwords(strtolower($name));
}

function granville_floorplan_tab_label($name)
{
	$name = trim((string) $name);

	if ($name === '') {
		return '';
	}

	$parts = preg_split('/\s*[\/\-–—‒]\s*/u', $name, 2);
	$label = trim($parts[0]);

	$label = preg_replace(
		'/\s*\d[\d,]*\s*(?:SQ\.?\s*FT\.?|SQUARE\s+FEET)\.?\s*$/iu',
		'',
		$label
	);

	$label = trim($label, " \t\n\r\0\x0B-/–—‒");

	return $label !== '' ? strtoupper($label) : strtoupper(trim($name));
}

function granville_floorplan_stats_line($post_id)
{
	if (! function_exists('get_field')) {
		return '';
	}

	$parts       = array();
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

function granville_floorplan_excluded_home_type_slugs()
{
	return apply_filters(
		'granville_floorplan_excluded_home_type_slugs',
		array('show-home')
	);
}

function granville_get_home_type_terms($post_id)
{
	$terms = get_the_terms($post_id, 'home-type');

	if (is_wp_error($terms) || empty($terms)) {
		return array();
	}

	return $terms;
}

function granville_post_hides_floorplans($post_id)
{
	$excluded = array_map('sanitize_title', granville_floorplan_excluded_home_type_slugs());

	foreach (granville_get_home_type_terms($post_id) as $term) {
		if (in_array($term->slug, $excluded, true)) {
			return true;
		}
	}

	return false;
}

function granville_get_model_home_type_name($post_id)
{
	$excluded = array_map('sanitize_title', granville_floorplan_excluded_home_type_slugs());

	foreach (granville_get_home_type_terms($post_id) as $term) {
		if (! in_array($term->slug, $excluded, true)) {
			return $term->name;
		}
	}

	return '';
}

function granville_floorplan_heading($post_id)
{
	$home_type = granville_get_model_home_type_name($post_id);

	return $home_type !== '' ? $home_type : get_the_title($post_id);
}

function granville_floorplan_post_types()
{
	return apply_filters('granville_floorplan_post_types', array('model'));
}

function granville_post_supports_floorplans($post_id)
{
	$post = get_post($post_id);

	if (! $post) {
		return false;
	}

	return in_array($post->post_type, granville_floorplan_post_types(), true);
}

function granville_floorplan_field_key()
{
	return 'field_6a22c21773560';
}

function granville_floorplan_normalize_image($image)
{
	if (is_array($image)) {
		return empty($image['url']) ? null : $image;
	}

	if (is_numeric($image)) {
		$image_id = (int) $image;

		if (function_exists('acf_get_attachment')) {
			$attachment = acf_get_attachment($image_id);
		} else {
			$image_url  = wp_get_attachment_image_url($image_id, 'full');
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

function granville_get_floorplan_rows_from_meta($post_id)
{
	$rows      = array();
	$field_key = granville_floorplan_field_key();
	$count     = (int) get_post_meta($post_id, $field_key, true);

	if ($count <= 0) {
		return $rows;
	}

	for ($i = 0; $i < $count; $i++) {
		$name  = get_post_meta($post_id, "floorplan_{$i}_name", true);
		$image = granville_floorplan_normalize_image(
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

function granville_build_floorplan_items_from_rows($rows)
{
	$items = array();

	if (! is_array($rows)) {
		return $items;
	}

	foreach ($rows as $row) {
		$name  = isset($row['name']) ? $row['name'] : '';
		$image = granville_floorplan_normalize_image(
			isset($row['image']) ? $row['image'] : null
		);

		if (! $image) {
			continue;
		}

		$label    = granville_floorplan_tab_label($name);
		$subtitle = granville_floorplan_display_title($name);

		$items[] = array(
			'name'     => $name ? $name : $label,
			'label'    => $label ? $label : ('FLOOR ' . (count($items) + 1)),
			'image'    => $image,
			'subtitle' => $subtitle ? $subtitle : $label,
		);
	}

	return $items;
}

function granville_get_floorplan_items($post_id)
{
	if (! function_exists('get_field')) {
		return array();
	}

	$field_key = granville_floorplan_field_key();
	$rows      = get_field('floorplan', $post_id);

	if (! is_array($rows)) {
		$rows = get_field($field_key, $post_id);
	}

	if (is_array($rows)) {
		return granville_build_floorplan_items_from_rows($rows);
	}

	if (metadata_exists('post', $post_id, $field_key)) {
		return granville_build_floorplan_items_from_rows(
			granville_get_floorplan_rows_from_meta($post_id)
		);
	}

	return array();
}

function granville_get_floorplans_markup($post_id = null)
{
	if (! function_exists('get_field')) {
		return '';
	}

	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if (! $post_id || ! granville_post_supports_floorplans($post_id) || granville_post_hides_floorplans($post_id)) {
		return '';
	}

	$items = granville_get_floorplan_items($post_id);

	if (empty($items)) {
		return '';
	}

	$instance = function_exists('wp_unique_id') ? wp_unique_id('gv-floorplans-') : uniqid('gv-floorplans-');

	ob_start();
?>
	<section class="gv-floorplans" data-gv-floorplans id="<?php echo esc_attr($instance); ?>">
		<div class="gv-floorplans__viewer">
				<?php foreach ($items as $index => $item) : ?>
					<?php
					$is_active = $index === 0;
					$tab_id    = $instance . '-tab-' . $index;
					$panel_id  = $instance . '-panel-' . $index;
					$alt       = ! empty($item['image']['alt']) ? $item['image']['alt'] : $item['label'];
					?>
					<div
						id="<?php echo esc_attr($panel_id); ?>"
						class="gv-floorplans__panel<?php echo $is_active ? ' is-active' : ''; ?>"
						role="tabpanel"
						aria-labelledby="<?php echo esc_attr($tab_id); ?>"
						data-subtitle="<?php echo esc_attr($item['subtitle']); ?>"
						<?php echo $is_active ? '' : ' hidden'; ?>>
						<img
							class="gv-floorplans__image"
							src="<?php echo esc_url($item['image']['url']); ?>"
							alt="<?php echo esc_attr($alt); ?>" />
					</div>
				<?php endforeach; ?>
		</div>

		<div class="gv-floorplans__tabs" role="tablist" aria-label="<?php esc_attr_e('Floorplan levels', 'granville'); ?>">
			<?php foreach ($items as $index => $item) : ?>
				<?php
				$is_active = $index === 0;
				$tab_id    = $instance . '-tab-' . $index;
				$panel_id  = $instance . '-panel-' . $index;
				?>
				<button
					type="button"
					id="<?php echo esc_attr($tab_id); ?>"
					class="gv-floorplans__tab<?php echo $is_active ? ' is-active' : ''; ?>"
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

function granville_floorplans_shortcode($atts)
{
	if (! function_exists('get_field')) {
		return '';
	}

	$atts = shortcode_atts(
		array(
			'post_id' => 0,
		),
		$atts,
		'granville_floorplans_v2'
	);

	$post_id = (int) $atts['post_id'];

	return granville_get_floorplans_markup($post_id ? $post_id : null);
}

add_shortcode('granville_floorplans_v2', 'granville_floorplans_shortcode');
add_shortcode('granville_floorplans', 'granville_floorplans_shortcode');
