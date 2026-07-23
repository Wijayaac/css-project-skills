<?php
// Prevent direct access for security
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Normalize attribute key/value for tolerant matching (slug vs label).
 *
 * @param string $value Raw attribute key or value.
 * @return string
 */
function sagiper_normalize_attr_token($value)
{
    $value = strtolower(wp_strip_all_tags((string) $value));
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Collapse punctuation/spaces so "6\" Channeled" ≈ "6-channeled" ≈ "6\"-channeled"
    $value = preg_replace('/[^a-z0-9]+/', '', $value);

    return $value ?: '';
}

/**
 * Find a variation attribute value whose key matches any of the given needles.
 *
 * @param array $attributes Variation attributes (keys like attribute_product-line).
 * @param array $key_needles Substrings that identify the attribute (e.g. line, profile).
 * @return string
 */
function sagiper_find_attr_value(array $attributes, array $key_needles)
{
    foreach ($attributes as $key => $value) {
        $norm_key = sagiper_normalize_attr_token($key);
        foreach ($key_needles as $needle) {
            if ($needle !== '' && strpos($norm_key, $needle) !== false) {
                return (string) $value;
            }
        }
    }

    return '';
}

/**
 * Whether a value looks like SAGIREV - SOFFIT/CEILING (slug or label).
 *
 * @param string $value Attribute value.
 * @return bool
 */
function sagiper_is_sagirev_value($value)
{
    $norm = sagiper_normalize_attr_token($value);

    return $norm !== '' && strpos($norm, 'sagirev') !== false;
}

/**
 * Whether a value looks like 6" Channeled (slug or label).
 *
 * @param string $value Attribute value.
 * @return bool
 */
function sagiper_is_channeled_value($value)
{
    $norm = sagiper_normalize_attr_token($value);

    return $norm !== '' && strpos($norm, 'channeled') !== false;
}

/**
 * Detect SAGIREV + Channeled combination on a variation's attributes array.
 *
 * @param array $variation_attributes Keys are attribute_* names.
 * @return bool
 */
function sagiper_is_sagirev_channeled_variation(array $variation_attributes)
{
    $line    = sagiper_find_attr_value($variation_attributes, ['productline', 'line']);
    $profile = sagiper_find_attr_value($variation_attributes, ['productprofile', 'profile']);

    if ($line === '' || $profile === '') {
        return false;
    }

    return sagiper_is_sagirev_value($line) && sagiper_is_channeled_value($profile);
}

/**
 * Strip SAGIREV + Channeled combos from quick view variation JSON.
 * Woo data may still contain them; quick view must not resolve them.
 *
 * @param array $variations Available variations from WC.
 * @return array
 */
function sagiper_filter_quickview_variations(array $variations)
{
    $filtered = array_values(array_filter($variations, function ($variation) {
        $attrs = isset($variation['attributes']) && is_array($variation['attributes'])
            ? $variation['attributes']
            : [];

        return ! sagiper_is_sagirev_channeled_variation($attrs);
    }));

    return $filtered;
}
