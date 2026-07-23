<?php
// Prevent direct access for security
if (! defined('ABSPATH')) exit;

// Create radio button on product variations option 
if (!function_exists('radio_variations')) {
    add_filter('woocommerce_dropdown_variation_attribute_options_html', 'radio_variations', 20, 2);

    function radio_variations($html, $args)
    {

        // in wc_dropdown_variation_attribute_options() they also extract all the array elements into variables
        $options   = $args['options'];
        $product   = $args['product'];
        $attribute = $args['attribute'];
        $name      = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title($attribute);

        if (empty($options) || ! $product) {
            return $html;
        }

        // HTML for our radio buttons
        $radios = '<div class="variation-radios">';

        // taxonomy-based attributes
        if (taxonomy_exists($attribute)) {

            $terms = wc_get_product_terms(
                $product->get_id(),
                $attribute,
                array(
                    'fields' => 'all',
                )
            );

            foreach ($terms as $term) {
                if (in_array($term->slug, $options, true)) {
                    $checked = checked($args['selected'], $term->slug, false);
                    $radios .= "<label for=\"{$name}-{$term->slug}\">
                            <input type=\"radio\" id=\"{$name}-{$term->slug}\" name=\"{$name}\" value=\"{$term->slug}\" {$checked}>
                            <span>{$term->name}</span>
                            </label>";
                }
            }
            // individual product attributes
        } else {
            foreach ($options as $option) {
                $checked = sanitize_title($args['selected']) === $args['selected'] ? checked($args['selected'], sanitize_title($option), false) : checked($args['selected'], $option, false);
                $radios .= "<label for=\"{$name}-{$option}\">
                            <input type=\"radio\" id=\"{$name}-{$option}\" name=\"{$name}\" value=\"{$option}\" {$checked}>
                            <span>{$option}</span>
                        </label>";
            }
        }

        $radios .= '</div>';

        return $html . $radios;
    }
}