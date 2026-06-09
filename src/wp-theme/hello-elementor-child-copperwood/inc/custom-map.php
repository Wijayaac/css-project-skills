<?php

/**
 * Custom Google Map shortcode.
 *
 * @package Copperwood
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Resolve the Google Maps API key from Elementor settings.
 */
function copperwood_get_google_maps_api_key()
{
	$key = apply_filters('copperwood_google_maps_api_key', '');

	if ($key) {
		return $key;
	}

	return (string) get_option('elementor_google_maps_api_key', '');
}

/**
 * Enqueue Google Maps assets when the shortcode is used.
 */
function copperwood_custom_map_enqueue_assets()
{
	static $enqueued = false;

	if ($enqueued) {
		return;
	}

	$enqueued = true;

	$api_key = copperwood_get_google_maps_api_key();

	if (! $api_key) {
		return;
	}

	wp_enqueue_script(
		'copperwood-google-maps',
		add_query_arg(
			array(
				'key' => $api_key,
			),
			'https://maps.googleapis.com/maps/api/js'
		),
		array(),
		null,
		true
	);

	wp_localize_script(
		'copperwood_custom',
		'copperwoodMapDefaults',
		array(
			'markerIcon'   => trailingslashit(get_stylesheet_directory_uri()) . 'map-pin.png',
			'markerWidth'  => 77,
			'markerHeight' => 89,
		)
	);
}

/**
 * Custom map shortcode.
 *
 * Usage: [get_the_map_shortcode lat="53.4880694" lng="-113.6870252"]
 */
function get_the_map($atts)
{
	$atts = shortcode_atts(
		array(
			'lat'      => '',
			'lng'      => '',
			'icon'     => '',
			'address'  => '',
			'title'    => '',
			'zoom'     => 13,
			'map_type' => 'hybrid',
			'class'    => '',
		),
		$atts,
		'get_the_map_shortcode'
	);

	$lat     = trim((string) $atts['lat']);
	$lng     = trim((string) $atts['lng']);
	$address = trim((string) $atts['address']);
	$icon    = trim((string) $atts['icon']);

	if ($icon === '') {
		$icon = trailingslashit(get_stylesheet_directory_uri()) . 'map-pin.png';
	}

	if (($lat === '' || $lng === '') && $address === '') {
		return '';
	}

	$api_key = copperwood_get_google_maps_api_key();

	if (! $api_key) {
		if (current_user_can('edit_posts')) {
			return '<p>' . esc_html__('Google Maps API key is missing. Add it in Elementor → Settings → Integrations.', 'copperwood') . '</p>';
		}

		return '';
	}

	copperwood_custom_map_enqueue_assets();

	$instance  = function_exists('wp_unique_id') ? wp_unique_id('custom-map-') : uniqid('custom-map-');
	$add_class = trim((string) $atts['class']);
	$title     = trim((string) $atts['title']);

	if ($title === '') {
		$title = $address ? $address : __('Location', 'copperwood');
	}

	$zoom = max(1, min(20, (int) $atts['zoom']));

	$map_type          = strtolower(trim((string) $atts['map_type']));
	$allowed_map_types = array('roadmap', 'satellite', 'hybrid', 'terrain');

	if (! in_array($map_type, $allowed_map_types, true)) {
		$map_type = 'hybrid';
	}

	ob_start();
	?>
	<section
		class="the_custom_map<?php echo $add_class ? ' ' . esc_attr($add_class) : ''; ?>"
		data-custom-map
		id="<?php echo esc_attr($instance); ?>">
		<div class="map_container">
			<div
				class="map_canvas"
				data-address="<?php echo esc_attr($address); ?>"
				data-title="<?php echo esc_attr($title); ?>"
				data-zoom="<?php echo esc_attr((string) $zoom); ?>"
				data-map-type="<?php echo esc_attr($map_type); ?>"
				data-icon="<?php echo esc_url($icon); ?>"
				<?php if ($lat !== '' && $lng !== '') : ?>
					data-lat="<?php echo esc_attr($lat); ?>"
					data-lng="<?php echo esc_attr($lng); ?>"
				<?php endif; ?>></div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

add_shortcode('get_the_map_shortcode', 'get_the_map');
add_shortcode('copperwood_map', 'get_the_map');
