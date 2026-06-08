<?php

/**
 * Google Map shortcode with custom marker.
 *
 * @package Copperwood
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Resolve the Google Maps API key.
 */
function copperwood_get_google_maps_api_key()
{
	$key = apply_filters('copperwood_google_maps_api_key', '');

	if ($key) {
		return $key;
	}

	if (class_exists('\Elementor\Plugin')) {
		$key = \Elementor\Plugin::$instance->get_google_maps_api_key();

		if ($key) {
			return $key;
		}
	}

	return (string) get_option('elementor_google_maps_api_key', '');
}

/**
 * Enqueue Google Maps assets when the shortcode is used.
 */
function copperwood_map_enqueue_assets()
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
			'markerIcon' => trailingslashit(get_stylesheet_directory_uri()) . 'map-pin.png',
			'markerWidth' => 77,
			'markerHeight' => 89,
		)
	);
}

/**
 * Google Map shortcode.
 *
 * Usage: [copperwood_map address="Copperwood Close, Edmonton, AB"]
 */
function copperwood_map_shortcode($atts)
{
	$atts = shortcode_atts(
		array(
			'address'  => '',
			'title'    => '',
			'zoom'     => 13,
			'lat'      => '',
			'lng'      => '',
			'map_type' => 'hybrid',
		),
		$atts,
		'copperwood_map'
	);

	$address = trim((string) $atts['address']);
	$lat     = trim((string) $atts['lat']);
	$lng     = trim((string) $atts['lng']);

	if ($address === '' && ($lat === '' || $lng === '')) {
		return '';
	}

	$api_key = copperwood_get_google_maps_api_key();

	if (! $api_key) {
		if (current_user_can('edit_posts')) {
			return '<p>' . esc_html__('Google Maps API key is missing. Add it in Elementor → Settings → Integrations.', 'copperwood') . '</p>';
		}

		return '';
	}

	copperwood_map_enqueue_assets();

	$instance = function_exists('wp_unique_id') ? wp_unique_id('cw-map-') : uniqid('cw-map-');
	$title    = trim((string) $atts['title']);

	if ($title === '') {
		$title = $address;
	}

	$zoom = max(1, min(20, (int) $atts['zoom']));

	$map_type = strtolower(trim((string) $atts['map_type']));
	$allowed_map_types = array('roadmap', 'satellite', 'hybrid', 'terrain');

	if (! in_array($map_type, $allowed_map_types, true)) {
		$map_type = 'hybrid';
	}

	ob_start();
	?>
	<div class="cw-map" data-cw-map id="<?php echo esc_attr($instance); ?>">
		<div class="cw-map__inner">
			<div
				class="cw-map__canvas"
				data-address="<?php echo esc_attr($address); ?>"
				data-title="<?php echo esc_attr($title); ?>"
				data-zoom="<?php echo esc_attr((string) $zoom); ?>"
				data-map-type="<?php echo esc_attr($map_type); ?>"
				<?php if ($lat !== '' && $lng !== '') : ?>
					data-lat="<?php echo esc_attr($lat); ?>"
					data-lng="<?php echo esc_attr($lng); ?>"
				<?php endif; ?>></div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

add_shortcode('copperwood_map', 'copperwood_map_shortcode');
