<?php
/**
 * Glacier West – runtime code for Services, Projects, Testimonials, Team Members, Careers and the blog single.
 *
 * Post types (gw_service, gw_project, gw_testimonial, gw_team, gw_career), the gw_sector and gw_job_location taxonomies and all field groups are
 * registered by ACF from the theme's acf-json/ folder (ACF 6.1+, local JSON). This file only adds behaviour.
 *
 * Theme: hello-theme-child-glacier-west  (loaded from functions.php when ACF is active)
 * Needs: ACF PRO 6.1+ (repeater + gallery fields), Elementor Pro 3.8+ (Loop Grid). Tested syntax with PHP 8.3 (php -l).
 *
 * Contents
 *   1. Guard (does nothing without ACF)
 *   2. Default sector terms (created once, after ACF registers the taxonomy) + term ordering
 *   3. Elementor Loop Grid query hooks: gw_related_projects, gw_related_testimonials
 *   4. Conditional rendering: CSS classes gw-if-{field} / gw-ifnot-{field} hide sections when a field is empty
 *   5. Featured testimonial class on loop items; Elementor source list + admin order for non-public types
 *   6. Shortcodes for repeaters and computed labels (incl. [gw_takeaways] for blog posts)
 *   7. Front-end CSS: shortcode markup + blog single (Gutenberg content, also in the block editor)
 *   8. Careers (inc/glacier-west-careers.php): open jobs, [gw_careers], application form hooks
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------------------------
 * 1. Guard – plugins load before the theme, so class_exists() is reliable here (ACF free + PRO).
 * ------------------------------------------------------------------------------------------- */
if ( ! class_exists( 'ACF' ) ) {
	return;
}

const GW_SECTORS = array(
	// slug => [ name, order ]
	'cold-storage'          => array( 'Cold Storage', 1 ),
	'food-processing'       => array( 'Food Processing', 2 ),
	'community-medical-lab' => array( 'Community, Medical & Lab', 3 ),
	'industrial'            => array( 'Industrial', 4 ),
	'institutional'         => array( 'Institutional', 5 ),
);

/* ---------------------------------------------------------------------------------------------
 * 2. Sector terms. ACF registers gw_sector from acf-json/ on init (priority 5); ACF JSON cannot
 *    hold terms, so the 5 defaults are created here once, on a later init priority. Nothing runs
 *    if the taxonomy is missing (ACF post type / taxonomy JSON not loaded) – it retries next load.
 *    Re-run by deleting the option 'gw_sector_terms_v1'. Renaming / deleting terms later is safe.
 * ------------------------------------------------------------------------------------------- */
add_action( 'init', function () {
	if ( get_option( 'gw_sector_terms_v1' ) || ! taxonomy_exists( 'gw_sector' ) ) {
		return;
	}
	$ok = true;
	foreach ( GW_SECTORS as $slug => $def ) {
		$term = term_exists( $slug, 'gw_sector' );
		if ( ! $term ) {
			$term = wp_insert_term( $def[0], 'gw_sector', array( 'slug' => $slug ) );
		}
		if ( is_wp_error( $term ) ) {
			$ok = false;
			continue;
		}
		$term_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
		if ( '' === get_term_meta( $term_id, 'gw_order', true ) ) {
			update_term_meta( $term_id, 'gw_order', $def[1] );
		}
	}
	if ( $ok ) {
		update_option( 'gw_sector_terms_v1', 1, false );
	}
}, 20 );

// Sector terms sort by their "Order" field (Cold Storage → Institutional) on the front end, incl. the Taxonomy Filter.
add_filter( 'get_terms_args', function ( $args, $taxonomies ) {
	if ( is_admin() || array( 'gw_sector' ) !== array_values( (array) $taxonomies ) ) {
		return $args;
	}
	if ( empty( $args['orderby'] ) || in_array( $args['orderby'], array( 'name', 'term_order' ), true ) ) {
		$args['meta_query'] = array(
			'relation'      => 'OR',
			'gw_order_set'  => array( 'key' => 'gw_order', 'type' => 'NUMERIC' ),
			'gw_order_none' => array( 'key' => 'gw_order', 'compare' => 'NOT EXISTS' ),
		);
		$args['orderby']    = 'gw_order_set';
		$args['order']      = 'ASC';
	}
	return $args;
}, 10, 2 );

/* ---------------------------------------------------------------------------------------------
 * 3. Elementor Loop Grid query hooks (Loop Grid → Query → Query ID)
 * ------------------------------------------------------------------------------------------- */
function gw_current_sector_ids( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_queried_object_id();
	if ( ! $post_id ) {
		return array();
	}
	$ids = wp_get_post_terms( $post_id, 'gw_sector', array( 'fields' => 'ids' ) );
	return is_wp_error( $ids ) ? array() : array_map( 'intval', $ids );
}

function gw_apply_sector_query( $query, $post_type ) {
	$current = get_queried_object_id();
	$terms   = gw_current_sector_ids( $current );
	$query->set( 'post_type', $post_type );
	if ( ! $terms ) {
		$query->set( 'post__in', array( 0 ) ); // nothing related → empty grid (section is hidden by gw-if-related_*)
		return;
	}
	$query->set( 'tax_query', array( array( 'taxonomy' => 'gw_sector', 'field' => 'term_id', 'terms' => $terms ) ) );
	if ( $current && get_post_type( $current ) === $post_type ) {
		$query->set( 'post__not_in', array( $current ) );
	}
	$query->set( 'ignore_sticky_posts', true );
}
// Service page "Case studies" + project page "Related projects".
add_action( 'elementor/query/gw_related_projects', function ( $query ) {
	gw_apply_sector_query( $query, 'gw_project' );
} );
// Testimonials that share the current page's sector (optional, not used by the current designs).
add_action( 'elementor/query/gw_related_testimonials', function ( $query ) {
	gw_apply_sector_query( $query, 'gw_testimonial' );
} );

function gw_related_count( $post_type ) {
	static $cache = array();
	$current = get_queried_object_id();
	$key     = $post_type . ':' . $current;
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}
	$terms = gw_current_sector_ids( $current );
	if ( ! $terms ) {
		return $cache[ $key ] = 0;
	}
	$q = new WP_Query( array(
		'post_type'      => $post_type,
		'post_status'    => 'publish',
		'fields'         => 'ids',
		'posts_per_page' => 1,
		'no_found_rows'  => false,
		'post__not_in'   => array( $current ),
		'tax_query'      => array( array( 'taxonomy' => 'gw_sector', 'field' => 'term_id', 'terms' => $terms ) ),
	) );
	return $cache[ $key ] = (int) $q->found_posts;
}

/* ---------------------------------------------------------------------------------------------
 * 4. Conditional rendering. Add to a container (Advanced → CSS Classes) or widget:
 *      gw-if-{field}       render only when the ACF field has a value (repeaters: at least one row)
 *      gw-ifnot-{field}    render only when the field is empty / false
 *      gw-if-related_projects / gw-if-related_testimonials   only when the Loop Grid would have items
 *    Skipped in the Elementor editor so every block stays editable.
 *    Note: turn Element Caching off for these templates (Elementor → Settings → Performance) or per widget.
 * ------------------------------------------------------------------------------------------- */
function gw_field_has_value( $name ) {
	if ( 'related_projects' === $name ) {
		return gw_related_count( 'gw_project' ) > 0;
	}
	if ( 'related_testimonials' === $name ) {
		return gw_related_count( 'gw_testimonial' ) > 0;
	}
	$field = function_exists( 'acf_get_field' ) ? acf_get_field( $name ) : null;
	if ( $field && in_array( $field['type'], array( 'repeater', 'gallery' ), true ) ) {
		return count( gw_rows( $name, get_the_ID() ) ) > 0;
	}
	$v = get_field( $name, get_the_ID() );
	if ( is_array( $v ) ) {
		return count( array_filter( $v ) ) > 0;
	}
	return ! ( null === $v || false === $v || '' === trim( wp_strip_all_tags( (string) $v ) ) );
}

function gw_should_render( $should_render, $element ) {
	if ( ! $should_render ) {
		return false;
	}
	if ( class_exists( '\Elementor\Plugin' ) && ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) ) {
		return true;
	}
	$classes = (string) $element->get_settings( 'css_classes' ) . ' ' . (string) $element->get_settings( '_css_classes' );
	if ( false === strpos( $classes, 'gw-if' ) ) {
		return true;
	}
	if ( preg_match_all( '/(?:^|\s)gw-if(not)?-([a-z0-9_]+)/', $classes, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $c ) {
			$has = gw_field_has_value( $c[2] );
			if ( ( '' === $c[1] && ! $has ) || ( 'not' === $c[1] && $has ) ) {
				return false;
			}
		}
	}
	return true;
}
add_filter( 'elementor/frontend/container/should_render', 'gw_should_render', 10, 2 );
add_filter( 'elementor/frontend/widget/should_render', 'gw_should_render', 10, 2 );

/* ---------------------------------------------------------------------------------------------
 * 5. Featured testimonial → class on the Loop Item wrapper (styled in the CSS file); Elementor + admin
 *    helpers for the non-public types (Testimonials, Team Members).
 * ------------------------------------------------------------------------------------------- */
add_filter( 'post_class', function ( $classes, $class, $post_id ) {
	if ( 'gw_testimonial' === get_post_type( $post_id ) && get_field( 'gw_featured', $post_id ) ) {
		$classes[] = 'gw-featured';
	}
	return $classes;
}, 10, 3 );

// Testimonials, Team Members and Careers have no public URL (show_in_nav_menus = false), which also hides them from
// Elementor Pro's post type lists (Loop Grid → Query → Source, Loop Item preview). Add them back.
add_filter( 'elementor_pro/utils/get_public_post_types', function ( $post_types ) {
	foreach ( array( 'gw_testimonial', 'gw_team', 'gw_career' ) as $type ) {
		$object = get_post_type_object( $type );
		if ( $object && ! isset( $post_types[ $type ] ) ) {
			$post_types[ $type ] = $object->labels->name;
		}
	}
	return $post_types;
} );

// Admin lists for manually ordered types follow Order (Page Attributes), lowest first, unless a column is clicked.
add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() && $query->is_main_query() && in_array( $query->get( 'post_type' ), array( 'gw_team', 'gw_testimonial' ), true ) && ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
} );

// Disable the block/content editor (Gutenberg) and per-post Elementor editing on all custom post types.
// Only standard blog posts ('post') use the Gutenberg editor.
const GW_CPT_TYPES = array( 'gw_service', 'gw_project', 'gw_testimonial', 'gw_team', 'gw_career' );

add_filter( 'use_block_editor_for_post_type', function ( $use_block_editor, $post_type ) {
	if ( in_array( $post_type, GW_CPT_TYPES, true ) ) {
		return false;
	}
	return $use_block_editor;
}, 100, 2 );

add_action( 'init', function () {
	foreach ( GW_CPT_TYPES as $post_type ) {
		if ( post_type_exists( $post_type ) ) {
			remove_post_type_support( $post_type, 'editor' );
			remove_post_type_support( $post_type, 'elementor' );
		}
	}
}, 100 );

/* ---------------------------------------------------------------------------------------------
 * 6. Shortcodes (Elementor's ACF dynamic tags cannot output repeater rows, so repeaters render here).
 *    All accept post_id="" (defaults to the current post in the loop / single).
 * ------------------------------------------------------------------------------------------- */
function gw_sc_post_id( $atts ) {
	return ! empty( $atts['post_id'] ) ? (int) $atts['post_id'] : get_the_ID();
}

// Repeater / gallery rows as an array of arrays. Anything else (e.g. the raw row count ACF free
// returns for a repeater it cannot load) counts as "no rows".
function gw_rows( $name, $post_id ) {
	$v = get_field( $name, $post_id );
	return is_array( $v ) ? array_values( array_filter( $v, 'is_array' ) ) : array();
}

// [gw_list field="gw_includes" cols="2"] – bullet list from a one-column repeater (sub field "item").
add_shortcode( 'gw_list', function ( $atts ) {
	$atts  = shortcode_atts( array( 'field' => 'gw_includes', 'sub' => 'item', 'cols' => '1', 'size' => '', 'post_id' => '' ), $atts );
	$rows  = gw_rows( $atts['field'], gw_sc_post_id( $atts ) );
	$items = array();
	foreach ( $rows as $r ) {
		$v = $r[ $atts['sub'] ] ?? '';
		if ( '' !== trim( (string) $v ) ) {
			$items[] = $v;
		}
	}
	if ( ! $items ) {
		return '';
	}
	$cols   = max( 1, min( 2, (int) $atts['cols'] ) );
	$chunks = 2 === $cols ? array_chunk( $items, (int) ceil( count( $items ) / 2 ) ) : array( $items ); // column-major like the design
	$out    = '<div class="gw-list gw-list--cols-' . $cols . ( 'sm' === $atts['size'] ? ' gw-list--compact' : '' ) . '">';
	foreach ( $chunks as $chunk ) {
		$out .= '<ul>';
		foreach ( $chunk as $item ) {
			$out .= '<li>' . esc_html( $item ) . '</li>';
		}
		$out .= '</ul>';
	}
	return $out . '</div>';
} );

// [gw_stats] – stats band (label / value / unit / text).
add_shortcode( 'gw_stats', function ( $atts ) {
	$atts = shortcode_atts( array( 'field' => 'gw_stats', 'post_id' => '' ), $atts );
	$rows = gw_rows( $atts['field'], gw_sc_post_id( $atts ) );
	if ( ! $rows ) {
		return '';
	}
	$out = '<div class="gw-stats gw-stats--' . count( $rows ) . '">';
	$i   = 0;
	foreach ( $rows as $r ) {
		$i++;
		$label = trim( (string) ( $r['label'] ?? '' ) );
		$out  .= '<div class="gw-stat">';
		$out  .= '<div class="gw-stat__label">' . sprintf( '%02d', $i ) . ( '' !== $label ? ' · ' . esc_html( $label ) : '' ) . '</div>';
		$out  .= '<div class="gw-stat__value">' . esc_html( $r['value'] ?? '' ) . ( ! empty( $r['unit'] ) ? ' <span class="gw-stat__unit">' . esc_html( $r['unit'] ) . '</span>' : '' ) . '</div>';
		if ( ! empty( $r['text'] ) ) {
			$out .= '<div class="gw-stat__text">' . esc_html( $r['text'] ) . '</div>';
		}
		$out .= '</div>';
	}
	return $out . '</div>';
} );

// [gw_timeline] – project construction timeline.
add_shortcode( 'gw_timeline', function ( $atts ) {
	$atts = shortcode_atts( array( 'field' => 'gw_timeline', 'post_id' => '', 'label' => 'Construction timeline' ), $atts );
	$rows = gw_rows( $atts['field'], gw_sc_post_id( $atts ) );
	if ( ! $rows ) {
		return '';
	}
	$out = '<div class="gw-timeline"><div class="gw-timeline__label">' . esc_html( $atts['label'] ) . '</div><ol>';
	foreach ( $rows as $r ) {
		$out .= '<li class="' . ( ! empty( $r['current'] ) ? 'is-current' : '' ) . '"><span class="gw-timeline__year">' . esc_html( $r['year'] ?? '' ) . '</span><span class="gw-timeline__text">' . esc_html( $r['text'] ?? '' ) . '</span></li>';
	}
	return $out . '</ol></div>';
} );

// [gw_faqs] – FAQ accordion in two columns (first item open), with FAQPage schema.
add_shortcode( 'gw_faqs', function ( $atts ) {
	$atts = shortcode_atts( array( 'field' => 'gw_faqs', 'post_id' => '', 'schema' => 'yes' ), $atts );
	$rows = array_values( array_filter( gw_rows( $atts['field'], gw_sc_post_id( $atts ) ), function ( $r ) {
		return ! empty( $r['question'] );
	} ) );
	if ( ! $rows ) {
		return '';
	}
	$half   = (int) ceil( count( $rows ) / 2 );
	$cols   = array( array_slice( $rows, 0, $half ), array_slice( $rows, $half ) );
	$out    = '<div class="gw-faqs">';
	$schema = array();
	$n      = 0;
	foreach ( $cols as $col ) {
		$out .= '<div class="gw-faqs__col">';
		foreach ( $col as $r ) {
			$out .= '<details class="gw-faq"' . ( 0 === $n ? ' open' : '' ) . '><summary><span>' . esc_html( $r['question'] ) . '</span><i aria-hidden="true"></i></summary>';
			$out .= '<div class="gw-faq__a">' . wpautop( esc_html( $r['answer'] ?? '' ) ) . '</div></details>';
			$schema[] = array( '@type' => 'Question', 'name' => $r['question'], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => (string) ( $r['answer'] ?? '' ) ) );
			$n++;
		}
		$out .= '</div>';
	}
	$out .= '</div>';
	if ( 'yes' === $atts['schema'] ) {
		$out .= '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $schema ) ) . '</script>';
	}
	return $out;
} );

// [gw_project_details] – the "Project details" card rows (empty rows are skipped).
add_shortcode( 'gw_project_details', function ( $atts ) {
	$atts = shortcode_atts( array( 'post_id' => '', 'label' => 'Project details' ), $atts );
	$id   = gw_sc_post_id( $atts );
	$rows = array(
		'Client'              => get_field( 'gw_client', $id ),
		'Original owner'      => get_field( 'gw_original_owner', $id ),
		'Location'            => get_field( 'gw_location', $id ),
		'Size'                => get_field( 'gw_size', $id ),
		'Cost'                => get_field( 'gw_cost', $id ),
		'Original completion' => get_field( 'gw_completion', $id ),
		'Sector'              => gw_sector_names( $id ),
	);
	$out = '<div class="gw-details"><div class="gw-details__label">' . esc_html( $atts['label'] ) . '</div><dl>';
	foreach ( $rows as $k => $v ) {
		if ( '' !== trim( (string) $v ) ) {
			$out .= '<div><dt>' . esc_html( $k ) . '</dt><dd>' . esc_html( $v ) . '</dd></div>';
		}
	}
	return $out . '</dl></div>';
} );

function gw_sector_names( $post_id ) {
	$terms = get_the_terms( $post_id, 'gw_sector' );
	return ( $terms && ! is_wp_error( $terms ) ) ? implode( ', ', wp_list_pluck( $terms, 'name' ) ) : '';
}

// [gw_sector before="" after=" Projects"] – sector name(s) of the current post (project hero tag, related tag).
add_shortcode( 'gw_sector', function ( $atts ) {
	$atts  = shortcode_atts( array( 'before' => '', 'after' => '', 'post_id' => '', 'fallback' => '' ), $atts );
	$names = gw_sector_names( gw_sc_post_id( $atts ) );
	return '' === $names ? esc_html( $atts['fallback'] ) : esc_html( $atts['before'] . $names . $atts['after'] );
} );

// [gw_project_eyebrow mode="sector|type"] – "Cold Storage · Surrey, BC" (sector) or "Egg Processing · British Columbia" (type).
add_shortcode( 'gw_project_eyebrow', function ( $atts ) {
	$atts  = shortcode_atts( array( 'mode' => 'sector', 'post_id' => '' ), $atts );
	$id    = gw_sc_post_id( $atts );
	$first = 'type' === $atts['mode'] ? get_field( 'gw_project_type', $id ) : '';
	if ( ! $first ) {
		$first = gw_sector_names( $id );
	}
	$loc = get_field( 'gw_location', $id );
	return esc_html( trim( $first . ' · ' . ( $loc ? $loc : 'Location TBC' ), ' ·' ) );
} );

// [gw_service_tag] – "01 / 05 · Cold Storage" from the service's position (Order) among published services.
add_shortcode( 'gw_service_tag', function ( $atts ) {
	$atts = shortcode_atts( array( 'post_id' => '' ), $atts );
	$id   = gw_sc_post_id( $atts );
	static $ids = null;
	if ( null === $ids ) {
		$ids = get_posts( array( 'post_type' => 'gw_service', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ), 'fields' => 'ids' ) );
	}
	$pos  = array_search( $id, $ids, true );
	$name = get_field( 'gw_short_name', $id );
	$num  = false === $pos ? '' : sprintf( '%02d / %02d', $pos + 1, count( $ids ) );
	return esc_html( trim( $num . ( $name ? ' · ' . $name : '' ), ' ·' ) );
} );

// [gw_split field="gw_intro_title" muted="gw_intro_title_muted"] – statement heading, second part grey.
add_shortcode( 'gw_split', function ( $atts ) {
	$atts  = shortcode_atts( array( 'field' => 'gw_intro_title', 'muted' => 'gw_intro_title_muted', 'post_id' => '' ), $atts );
	$id    = gw_sc_post_id( $atts );
	$black = (string) get_field( $atts['field'], $id );
	$grey  = (string) get_field( $atts['muted'], $id );
	return esc_html( $black ) . ( '' !== $grey ? ' <span class="gw-muted">' . esc_html( $grey ) . '</span>' : '' );
} );

// [gw_count post_type="gw_project" singular="project" plural="projects"] – "17 projects".
add_shortcode( 'gw_count', function ( $atts ) {
	$atts = shortcode_atts( array( 'post_type' => 'gw_project', 'singular' => 'project', 'plural' => 'projects' ), $atts );
	$n    = (int) ( wp_count_posts( $atts['post_type'] )->publish ?? 0 );
	return esc_html( $n . ' ' . ( 1 === $n ? $atts['singular'] : $atts['plural'] ) );
} );

// [gw_takeaways] – blog post "Key takeaways" box (ACF repeater gw_takeaways → gw_takeaway). Nothing when empty.
// The title is an H2 so the "On this page" list picks it up; it is styled as the small blue label.
add_shortcode( 'gw_takeaways', function ( $atts ) {
	$atts  = shortcode_atts( array( 'title' => 'Key takeaways', 'post_id' => '' ), $atts );
	$items = array();
	foreach ( gw_rows( 'gw_takeaways', gw_sc_post_id( $atts ) ) as $r ) {
		$t = trim( (string) ( $r['gw_takeaway'] ?? '' ) );
		if ( '' !== $t ) {
			$items[] = $t;
		}
	}
	if ( ! $items ) {
		return '';
	}
	$out = '<section class="gw-takeaways"><h2 class="gw-takeaways__title">' . esc_html( $atts['title'] ) . '</h2><ul class="gw-takeaways__list">';
	foreach ( $items as $t ) {
		$out .= '<li>' . esc_html( $t ) . '</li>';
	}
	return $out . '</ul></section>';
} );

// [gw_featured_testimonial] – Featured Client block (featured gw_testimonial, or first by Order).
// Uses optional gw_lead ACF field if set; otherwise promotes the first paragraph (or sentence) of gw_quote as the lead quote.
add_shortcode( 'gw_featured_testimonial', function ( $atts ) {
	$atts = shortcode_atts( array( 'post_id' => '' ), $atts );
	$id   = (int) $atts['post_id'];
	if ( ! $id ) {
		$ids = get_posts( array(
			'post_type'      => 'gw_testimonial',
			'post_status'    => 'publish',
			'numberposts'    => 1,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'meta_key'       => 'gw_featured',
			'meta_value'     => '1',
			'fields'         => 'ids',
		) );
		if ( ! $ids ) {
			$ids = get_posts( array(
				'post_type'   => 'gw_testimonial',
				'post_status' => 'publish',
				'numberposts' => 1,
				'orderby'     => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
				'fields'      => 'ids',
			) );
		}
		$id = $ids ? (int) $ids[0] : 0;
	}
	if ( ! $id ) {
		return '';
	}
	$raw  = trim( wp_strip_all_tags( (string) get_field( 'gw_quote', $id ) ) );
	$lead = trim( (string) get_field( 'gw_lead', $id ) );
	$body = $raw;
	if ( '' === $lead && '' !== $raw ) {
		$paras = preg_split( '/\r?\n\s*\r?\n/', $raw );
		if ( count( $paras ) > 1 ) {
			$lead = trim( array_shift( $paras ) );
			$body = implode( "\n\n", $paras );
		} elseif ( preg_match( '/^(.+?[.!?])\s+(.+)$/s', $raw, $m ) ) {
			$lead = trim( $m[1] );
			$body = trim( $m[2] );
		} else {
			$lead = $raw;
			$body = '';
		}
	}
	$icon = '<svg class="gw-tfeat__icon" aria-hidden="true" viewBox="0 0 24 19" fill="currentColor"><path d="M0 19V11.2C0 5.1 3.4 1.2 9.4 0l1.1 2.9C6.8 4.1 5 6.4 4.8 9.6H9.6V19H0zm13.4 0V11.2c0-6.1 3.4-10 9.4-11.2l1.1 2.9c-3.7 1.2-5.5 3.5-5.7 6.7H23V19h-9.6z"/></svg>';
	$out  = '<div class="gw-tfeat">' . $icon;
	if ( '' !== $lead ) {
		$out .= '<p class="gw-tfeat__lead">' . esc_html( $lead ) . '</p>';
	}
	if ( '' !== $body ) {
		$out .= '<div class="gw-tfeat__body">' . wpautop( esc_html( $body ) ) . '</div>';
	}
	$out .= '<p class="gw-tfeat__name">' . esc_html( get_the_title( $id ) ) . '</p></div>';
	return $out;
} );

// [gw_testimonials_nav] (alias [gw_testimonial_nav]) – Prev/Next circular controls for the Client Stories carousel.
$gw_testimonials_nav_cb = function () {
	wp_enqueue_script( 'hello-elementor-child-glacier-west-testimonials' );
	$arrow = '<svg aria-hidden="true" viewBox="0 0 12 10" fill="none"><path d="M1.25 5H10.75M6.75 9L10.75 5L6.75 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	$prev  = '<svg aria-hidden="true" viewBox="0 0 12 10" fill="none" style="transform:rotate(180deg)"><path d="M1.25 5H10.75M6.75 9L10.75 5L6.75 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	return '<div class="gw-tcarousel__nav">'
		. '<button type="button" class="gw-tcarousel__btn is-disabled" data-gw-t-prev aria-label="Previous testimonial" disabled>' . $prev . '</button>'
		. '<button type="button" class="gw-tcarousel__btn" data-gw-t-next aria-label="Next testimonial">' . $arrow . '</button>'
		. '</div>';
};
add_shortcode( 'gw_testimonials_nav', $gw_testimonials_nav_cb );
add_shortcode( 'gw_testimonial_nav', $gw_testimonials_nav_cb );

// [gw_testimonials] – Client Stories horizontal carousel (gw_testimonial CPT ordered by menu_order).
add_shortcode( 'gw_testimonials', function ( $atts ) {
	$atts  = shortcode_atts( array( 'limit' => -1 ), $atts );
	$posts = get_posts( array(
		'post_type'      => 'gw_testimonial',
		'post_status'    => 'publish',
		'numberposts'    => (int) $atts['limit'],
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) );
	if ( ! $posts ) {
		return '<p class="gw-tcarousel__empty">Client testimonials coming soon.</p>';
	}
	wp_enqueue_script( 'hello-elementor-child-glacier-west-testimonials' );
	$qsvg  = '<svg class="gw-tcard-slide__q" aria-hidden="true" viewBox="0 0 24 19" fill="currentColor"><path d="M0 19V11.2C0 5.1 3.4 1.2 9.4 0l1.1 2.9C6.8 4.1 5 6.4 4.8 9.6H9.6V19H0zm13.4 0V11.2c0-6.1 3.4-10 9.4-11.2l1.1 2.9c-3.7 1.2-5.5 3.5-5.7 6.7H23V19h-9.6z"/></svg>';
	$total = count( $posts );
	$out   = '<div class="gw-tcarousel" data-gw-tcarousel><div class="gw-tcarousel__track">';
	foreach ( $posts as $i => $p ) {
		$mod   = 0 === $i ? ' gw-tcard-slide--navy' : ( 1 === $i ? ' gw-tcard-slide--blue' : '' );
		$quote = wpautop( esc_html( trim( wp_strip_all_tags( (string) get_field( 'gw_quote', $p->ID ) ) ) ) );
		$out  .= '<article class="gw-tcard-slide' . $mod . '">'
			. $qsvg
			. '<div class="gw-tcard-slide__body">' . $quote . '</div>'
			. '<h3 class="gw-tcard-slide__name">' . esc_html( get_the_title( $p ) ) . '</h3>'
			. '</article>';
	}
	$pct = number_format( 100 / max( 1, $total ), 2, '.', '' );
	$out .= '</div><div class="gw-tcarousel__foot">'
		. '<span class="gw-tcarousel__count" aria-live="polite">01 / ' . sprintf( '%02d', $total ) . '</span>'
		. '<div class="gw-tcarousel__bar"><span class="gw-tcarousel__fill" style="width:' . $pct . '%"></span></div>'
		. '</div></div>';
	return $out;
} );

/* ---------------------------------------------------------------------------------------------
 * 7. CSS for the shortcode markup + card states; blog single CSS (front end + block editor).
 * ------------------------------------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
	$rel  = '/assets/css/glacier-west-cpt.css';
	$path = get_stylesheet_directory() . $rel;
	if ( file_exists( $path ) ) {
		wp_enqueue_style(
			'hello-elementor-child-glacier-west-cpt',
			get_stylesheet_directory_uri() . $rel,
			[
				'hello-elementor-child-style',
			],
			(string) filemtime( $path )
		);
	}
	// Scroll text fill for two-tone headings, site-wide.
	$fx_rel  = '/assets/js/glacier-west-interactions.js';
	$fx_path = get_stylesheet_directory() . $fx_rel;
	if ( file_exists( $fx_path ) ) {
		wp_enqueue_script( 'hello-elementor-child-glacier-west-interactions', get_stylesheet_directory_uri() . $fx_rel, array(), (string) filemtime( $fx_path ), true );
	}
	$js_rel  = '/assets/js/glacier-west-testimonials.js';
	$js_path = get_stylesheet_directory() . $js_rel;
	if ( file_exists( $js_path ) ) {
		wp_register_script(
			'hello-elementor-child-glacier-west-testimonials',
			get_stylesheet_directory_uri() . $js_rel,
			array(),
			(string) filemtime( $js_path ),
			true
		);
	}
}, 20 );

// Blog single: styles for Gutenberg content in the Post Content widget (.gw-post-content) + Key takeaways.
// Front end: single posts and Elementor template previews. Block editor: the same file so writers see the design.
function gw_enqueue_blog_css() {
	$rel  = '/assets/css/glacier-west-blog.css';
	$path = get_stylesheet_directory() . $rel;
	if ( ! file_exists( $path ) ) {
		return;
	}
	$deps = array();
	foreach ( array( 'wp-block-library', 'hello-elementor-child-style' ) as $dep ) {
		if ( wp_style_is( $dep, 'registered' ) ) {
			$deps[] = $dep;
		}
	}
	wp_enqueue_style( 'hello-elementor-child-glacier-west-blog', get_stylesheet_directory_uri() . $rel, $deps, (string) filemtime( $path ) );
}
add_action( 'wp_enqueue_scripts', function () {
	if ( is_singular( array( 'post', 'elementor_library' ) ) ) {
		gw_enqueue_blog_css();
	}
}, 20 );
add_action( 'enqueue_block_assets', function () {
	if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'post' === $screen->post_type && $screen->is_block_editor() ) {
		gw_enqueue_blog_css();
	}
} );

/* ---------------------------------------------------------------------------------------------
 * 8. Careers – see inc/glacier-west-careers.php.
 * ------------------------------------------------------------------------------------------- */
if ( file_exists( __DIR__ . '/glacier-west-careers.php' ) ) {
	require_once __DIR__ . '/glacier-west-careers.php';
}
