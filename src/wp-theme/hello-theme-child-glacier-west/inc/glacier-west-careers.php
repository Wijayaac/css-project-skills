<?php
/**
 * Glacier West – Careers (gw_career): open-job query, [gw_careers] list, application form hooks, assets.
 *
 * The post type gw_career, the gw_job_location taxonomy and the "Job details" field group load from acf-json/
 * (post_type_gw_career.json, taxonomy_gw_job_location.json, group_gw_career.json). This file only adds behaviour.
 * Loaded from inc/glacier-west-cpt.php (so it only runs when ACF is active). Works with ACF free (no repeaters).
 *
 * Contents
 *   1. Default location terms (Alberta, British Columbia), created once
 *   2. Open jobs: switch "Accepting applications" on and close date empty or today/later
 *   3. [gw_careers] – location filter buttons + accordion rows (details/summary) + empty states
 *   4. Application form (Elementor Pro Form named "Glacier West job application"): Position and Preferred location
 *      options from open jobs / location terms; ?position={job-slug} preselects
 *   5. Admin: Status column, Order sorting
 *   6. Assets: glacier-west-careers.css / .js, only on pages that use them
 *   7. Application form hardening: rate limits + strict server-side resume checks
 */

defined( 'ABSPATH' ) || exit;

const GW_CAREER_FORM_NAME = 'Glacier West job application';
const GW_JOB_LOCATIONS    = array(
	// slug => [ name, order ]
	'alberta'          => array( 'Alberta', 1 ),
	'british-columbia' => array( 'British Columbia', 2 ),
);
const GW_EMPLOYMENT_TYPES = array(
	'full-time' => 'Full-time',
	'part-time' => 'Part-time',
	'contract'  => 'Contract',
	'temporary' => 'Temporary',
);

/* ---------------------------------------------------------------------------------------------
 * 1. Location terms – ACF registers the taxonomy on init (priority 5); terms are added once on 20.
 *    Re-run by deleting the option 'gw_job_location_terms_v1'. Renaming / adding terms later is safe.
 * ------------------------------------------------------------------------------------------- */
add_action( 'init', function () {
	if ( get_option( 'gw_job_location_terms_v1' ) || ! taxonomy_exists( 'gw_job_location' ) ) {
		return;
	}
	$ok = true;
	foreach ( GW_JOB_LOCATIONS as $slug => $def ) {
		$term = term_exists( $slug, 'gw_job_location' );
		if ( ! $term ) {
			$term = wp_insert_term( $def[0], 'gw_job_location', array( 'slug' => $slug ) );
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
		update_option( 'gw_job_location_terms_v1', 1, false );
	}
}, 20 );

function gw_job_location_terms() {
	$terms = get_terms( array( 'taxonomy' => 'gw_job_location', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	usort( $terms, function ( $a, $b ) {
		$oa = get_term_meta( $a->term_id, 'gw_order', true );
		$ob = get_term_meta( $b->term_id, 'gw_order', true );
		$oa = '' === $oa ? 999 : (int) $oa;
		$ob = '' === $ob ? 999 : (int) $ob;
		return $oa === $ob ? strcasecmp( $a->name, $b->name ) : $oa - $ob;
	} );
	return $terms;
}

/* ---------------------------------------------------------------------------------------------
 * 2. Open jobs. "Open" = gw_is_open not switched off AND (no close date OR close date >= today, site time zone).
 *    Also available to a Loop Grid: Query → Query ID "gw_open_jobs".
 * ------------------------------------------------------------------------------------------- */
function gw_open_jobs_meta_query() {
	return array(
		'relation' => 'AND',
		array(
			'relation' => 'OR',
			array( 'key' => 'gw_is_open', 'value' => '0', 'compare' => '!=' ),
			array( 'key' => 'gw_is_open', 'compare' => 'NOT EXISTS' ),
		),
		array(
			'relation' => 'OR',
			array( 'key' => 'gw_close_date', 'compare' => 'NOT EXISTS' ),
			array( 'key' => 'gw_close_date', 'value' => '', 'compare' => '=' ),
			array( 'key' => 'gw_close_date', 'value' => wp_date( 'Ymd' ), 'compare' => '>=', 'type' => 'NUMERIC' ),
		),
	);
}

function gw_open_jobs() {
	static $jobs = null;
	if ( null === $jobs ) {
		$jobs = get_posts( array(
			'post_type'        => 'gw_career',
			'post_status'      => 'publish',
			'posts_per_page'   => 100,
			'orderby'          => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'meta_query'       => gw_open_jobs_meta_query(), // phpcs:ignore WordPress.DB.SlowDBQuery
			'no_found_rows'    => true,
			'suppress_filters' => false,
		) );
	}
	return $jobs;
}

add_action( 'elementor/query/gw_open_jobs', function ( $query ) {
	$query->set( 'meta_query', gw_open_jobs_meta_query() );
} );

function gw_job_data( $post ) {
	$id    = $post->ID;
	$terms = get_the_terms( $id, 'gw_job_location' );
	$terms = ( $terms && ! is_wp_error( $terms ) ) ? $terms : array();
	$type  = (string) get_field( 'gw_employment_type', $id );
	$place = trim( (string) get_field( 'gw_location_text', $id ) );
	return array(
		'id'       => $id,
		'title'    => get_the_title( $id ),
		'slug'     => $post->post_name,
		'loc_slug' => wp_list_pluck( $terms, 'slug' ),
		'loc_name' => wp_list_pluck( $terms, 'name' ),
		'place'    => '' !== $place ? $place : implode( ' / ', wp_list_pluck( $terms, 'name' ) ),
		'type'     => GW_EMPLOYMENT_TYPES[ $type ] ?? '',
		'overview' => trim( (string) get_field( 'gw_overview', $id ) ),
		'more'     => array_filter( array(
			''                 => (string) get_field( 'gw_description', $id ),
			'Responsibilities' => (string) get_field( 'gw_responsibilities', $id ),
			'Requirements'     => (string) get_field( 'gw_requirements', $id ),
		), function ( $html ) {
			return '' !== trim( wp_strip_all_tags( $html ) );
		} ),
		'url'      => trim( (string) get_field( 'gw_apply_url', $id ) ),
	);
}

/* ---------------------------------------------------------------------------------------------
 * 3. [gw_careers form="#apply" all="All locations"]
 *    Server-rendered, so it works without JavaScript (all rows shown, native details/summary accordion).
 *    The JS file turns the location buttons into a client-side filter (aria-pressed) and makes
 *    Apply Now scroll to the form with the position preselected; without JS the link reloads with
 *    ?position={slug}#apply and the form hook below preselects it.
 * ------------------------------------------------------------------------------------------- */
function gw_career_arrow_svg( $color ) {
	// Same 11×10 arrow as /wp-content/uploads/2026/11/Vector.svg (inline, so the hover sweep can recolour it).
	return '<svg xmlns="http://www.w3.org/2000/svg" width="11" height="10" viewBox="0 0 11 10" fill="none" aria-hidden="true" focusable="false"><path d="M0.75 4.75H9.75M5.75 8.75L9.75 4.75L5.75 0.75" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke="' . esc_attr( $color ) . '"></path></svg>';
}

function gw_career_apply_link( $label, $form, $slug, $title, $location = '', $external = '' ) {
	$attrs = $external
		? sprintf( 'href="%s" target="_blank" rel="noopener"', esc_url( $external ) )
		: sprintf(
			'href="%s" data-gw-apply="%s" data-gw-slug="%s" data-gw-position="%s" data-gw-location="%s"',
			esc_attr( '?position=' . rawurlencode( $slug ) . $form ),
			esc_attr( $form ),
			esc_attr( $slug ),
			esc_attr( $title ),
			esc_attr( $location )
		);
	$sr = $external ? '<span class="screen-reader-text"> (opens in a new tab)</span>' : '';
	if ( $title && 'general' !== $slug ) {
		$sr .= '<span class="screen-reader-text"> – ' . esc_html( $title ) . '</span>';
	}
	return '<span class="gw-job__action gw-btn-sweep gw-pill gw-pill--primary"><a class="elementor-button gw-job__apply" ' . $attrs . '>'
		. '<span class="elementor-button-content-wrapper"><span class="elementor-button-icon">' . gw_career_arrow_svg( '#0074BE' ) . '</span>'
		. '<span class="elementor-button-text">' . esc_html( $label ) . $sr . '</span></span></a></span>';
}

add_shortcode( 'gw_careers', function ( $atts ) {
	$atts = shortcode_atts( array(
		'form'  => '#apply',
		'all'   => 'All locations',
		'apply' => 'Apply Now',
	), $atts, 'gw_careers' );
	$form = '#' . ltrim( sanitize_html_class( ltrim( (string) $atts['form'], '#' ) ), '#' );
	gw_careers_enqueue();

	$jobs = array_map( 'gw_job_data', gw_open_jobs() );
	if ( ! $jobs ) {
		return '<div class="gw-careers gw-careers--none"><div class="gw-careers__card gw-careers__none">'
			. '<h3 class="gw-careers__none-title">No open positions right now</h3>'
			. '<p class="gw-careers__none-text">We&#8217;re always interested in hearing from great people. Send a general application and we&#8217;ll keep it on file for future roles.</p>'
			. gw_career_apply_link( 'Send a General Application', $form, 'general', 'General application' )
			. '</div></div>';
	}

	$out   = '<div class="gw-careers" data-gw-careers>';
	$terms = gw_job_location_terms();
	if ( count( $terms ) > 1 ) {
		$out .= '<div class="gw-careers__tabs" role="group" aria-label="' . esc_attr__( 'Filter positions by location', 'hello-elementor-child' ) . '">';
		$out .= '<button type="button" class="gw-careers__tab is-active" aria-pressed="true" data-filter="">' . esc_html( $atts['all'] ) . '</button>';
		foreach ( $terms as $t ) {
			$out .= '<button type="button" class="gw-careers__tab" aria-pressed="false" data-filter="' . esc_attr( $t->slug ) . '">' . esc_html( $t->name ) . '</button>';
		}
		$out .= '</div>';
	}
	$out .= '<div class="gw-careers__card"><div class="gw-careers__list">';
	foreach ( $jobs as $i => $j ) {
		$meta = implode( ' &middot; ', array_filter( array( esc_html( $j['place'] ), esc_html( $j['type'] ) ) ) );
		$out .= '<details class="gw-job" data-locations="' . esc_attr( implode( ' ', $j['loc_slug'] ) ) . '"' . ( 0 === $i ? ' open' : '' ) . '>';
		$out .= '<summary class="gw-job__summary"><span class="gw-job__head"><h3 class="gw-job__title">' . esc_html( $j['title'] ) . '</h3>';
		$out .= $meta ? '<span class="gw-job__meta">' . $meta . '</span>' : '';
		$out .= '</span><span class="gw-job__toggle" aria-hidden="true"></span></summary>';
		$out .= '<div class="gw-job__panel">';
		if ( $j['overview'] ) {
			$out .= '<p class="gw-job__overview">' . nl2br( esc_html( $j['overview'] ) ) . '</p>';
		}
		if ( $j['more'] ) {
			$out .= '<details class="gw-job__more"><summary>' . esc_html__( 'View full job description', 'hello-elementor-child' ) . '</summary><div class="gw-job__more-body">';
			foreach ( $j['more'] as $heading => $html ) {
				$out .= ( $heading ? '<h4>' . esc_html( $heading ) . '</h4>' : '' ) . wp_kses_post( $html );
			}
			$out .= '</div></details>';
		}
		$single_loc = 1 === count( $j['loc_name'] ) ? $j['loc_name'][0] : '';
		$out       .= gw_career_apply_link( $atts['apply'], $form, $j['slug'], $j['title'], $single_loc, $j['url'] );
		$out       .= '</div></details>';
	}
	$out .= '</div><p class="gw-careers__empty" hidden>' . sprintf(
		/* translators: %s: location name */
		esc_html__( 'No open positions in %s right now.', 'hello-elementor-child' ),
		'<span data-gw-empty-name></span>'
	) . ' <a href="' . esc_attr( '?position=general' . $form ) . '" data-gw-apply="' . esc_attr( $form ) . '" data-gw-slug="general" data-gw-position="General application">'
		. esc_html__( 'Send a general application', 'hello-elementor-child' ) . '</a></p>';
	return $out . '</div></div>';
} );

/* ---------------------------------------------------------------------------------------------
 * 4. Application form – Elementor Pro Form with Form Name "Glacier West job application".
 *    Select field ID "position": "Select a position" + open job titles + "General application".
 *    Select field ID "location": placeholder + location terms + "Either location".
 *    ?position={job-slug} (or "general") preselects the position, and the location when the job has one.
 * ------------------------------------------------------------------------------------------- */
function gw_career_requested_job() {
	$slug = isset( $_GET['position'] ) ? sanitize_title( wp_unslash( $_GET['position'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	if ( '' === $slug ) {
		return null;
	}
	if ( 'general' === $slug ) {
		return array( 'title' => 'General application', 'loc_name' => array() );
	}
	foreach ( gw_open_jobs() as $post ) {
		if ( $post->post_name === $slug ) {
			return gw_job_data( $post );
		}
	}
	return null;
}

function gw_career_option( $label ) {
	$label = trim( str_replace( array( '|', "\r", "\n" ), ' ', wp_strip_all_tags( $label ) ) );
	return $label . '|' . $label;
}

add_filter( 'elementor_pro/forms/render/item/select', function ( $item, $index, $form ) {
	if ( ! is_object( $form ) || ! method_exists( $form, 'get_settings_for_display' ) || GW_CAREER_FORM_NAME !== $form->get_settings_for_display( 'form_name' ) ) {
		return $item;
	}
	$id  = $item['custom_id'] ?? '';
	$job = gw_career_requested_job();
	if ( 'position' === $id ) {
		$opts = array( 'Select a position|' );
		foreach ( gw_open_jobs() as $post ) {
			$opts[] = gw_career_option( get_the_title( $post ) );
		}
		$opts[]                = 'General application|General application';
		$item['field_options'] = implode( "\n", $opts );
		if ( $job ) {
			$item['field_value'] = $job['title'];
		}
	} elseif ( 'location' === $id ) {
		$terms = gw_job_location_terms();
		if ( $terms ) {
			$names = wp_list_pluck( $terms, 'name' );
			$opts  = array( implode( ' or ', $names ) . '|' );
			foreach ( $names as $n ) {
				$opts[] = gw_career_option( $n );
			}
			$opts[]                = 'Either location|Either location';
			$item['field_options'] = implode( "\n", $opts );
		}
		if ( $job && 1 === count( $job['loc_name'] ) ) {
			$item['field_value'] = $job['loc_name'][0];
		}
	}
	return $item;
}, 10, 3 );

/* ---------------------------------------------------------------------------------------------
 * 5. Admin: Status column (Open / Closed / Closes …) and Order sorting (Page Attributes → Order).
 * ------------------------------------------------------------------------------------------- */
add_filter( 'manage_gw_career_posts_columns', function ( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['gw_status'] = 'Status';
			$new['gw_type']   = 'Type';
		}
	}
	return $new;
} );
add_action( 'manage_gw_career_posts_custom_column', function ( $col, $post_id ) {
	if ( 'gw_type' === $col ) {
		echo esc_html( GW_EMPLOYMENT_TYPES[ (string) get_field( 'gw_employment_type', $post_id ) ] ?? '–' );
	} elseif ( 'gw_status' === $col ) {
		$open  = '0' !== (string) get_post_meta( $post_id, 'gw_is_open', true );
		$close = (string) get_post_meta( $post_id, 'gw_close_date', true );
		if ( ! $open ) {
			echo 'Closed';
		} elseif ( $close && $close < wp_date( 'Ymd' ) ) {
			echo 'Closed (date passed)';
		} elseif ( $close ) {
			$d = DateTime::createFromFormat( 'Ymd', $close, wp_timezone() );
			echo esc_html( 'Open · closes ' . ( $d ? wp_date( 'M j, Y', $d->getTimestamp() ) : $close ) );
		} else {
			echo 'Open';
		}
	}
}, 10, 2 );
add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() && $query->is_main_query() && 'gw_career' === $query->get( 'post_type' ) && ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
} );

/* ---------------------------------------------------------------------------------------------
 * 6. Assets – only where needed: pages whose Elementor data contains [gw_careers] or the
 *    gw-apply-form class (checked in wp_enqueue_scripts, so the CSS is in <head>), plus a late
 *    fallback from the shortcode itself.
 * ------------------------------------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
	$base = get_stylesheet_directory();
	$uri  = get_stylesheet_directory_uri();
	if ( file_exists( $base . '/assets/css/glacier-west-careers.css' ) ) {
		wp_register_style( 'hello-elementor-child-glacier-west-careers', $uri . '/assets/css/glacier-west-careers.css', array( 'hello-elementor-child-style' ), (string) filemtime( $base . '/assets/css/glacier-west-careers.css' ) );
	}
	if ( file_exists( $base . '/assets/js/glacier-west-careers.js' ) ) {
		wp_register_script( 'hello-elementor-child-glacier-west-careers', $uri . '/assets/js/glacier-west-careers.js', array(), (string) filemtime( $base . '/assets/js/glacier-west-careers.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_localize_script( 'hello-elementor-child-glacier-west-careers', 'gwCareers', array(
			'uploadTitle' => __( 'Upload your resume', 'hello-elementor-child' ),
			'uploadHint'  => __( 'PDF or DOC, up to 10 MB', 'hello-elementor-child' ),
		) );
	}
	if ( is_singular() ) {
		$data = (string) get_post_meta( get_queried_object_id(), '_elementor_data', true );
		if ( false !== strpos( $data, 'gw_careers' ) || false !== strpos( $data, 'gw-apply-form' ) ) {
			gw_careers_enqueue();
		}
	}
}, 20 );

function gw_careers_enqueue() {
	if ( wp_style_is( 'hello-elementor-child-glacier-west-careers', 'registered' ) ) {
		wp_enqueue_style( 'hello-elementor-child-glacier-west-careers' );
	}
	if ( wp_script_is( 'hello-elementor-child-glacier-west-careers', 'registered' ) ) {
		wp_enqueue_script( 'hello-elementor-child-glacier-west-careers' );
	}
}

/* ---------------------------------------------------------------------------------------------
 * 7. Application form hardening (server side; the browser-side file limits are easy to bypass).
 *    Runs on Elementor Pro's validation step, BEFORE any uploaded file is moved into wp-content/uploads,
 *    so a rejected submission never leaves a file on the server.
 *    - Rate limit: per IP (3 per 10 min, 10 per day) and site-wide (60 per hour), counted on every attempt.
 *    - Resume: exactly one file, required, max 10 MB, extension pdf/doc/docx only, a single safe file name
 *      (no "cv.php.pdf"), and the file content must really be PDF / Word (magic bytes + finfo MIME),
 *      with no embedded PHP or script tags in the first 64 KB.
 *    - Any other upload field on this form is refused.
 *    Tune the limits with the constants below.
 * ------------------------------------------------------------------------------------------- */
const GW_APPLY_FILE_FIELD   = 'resume';
const GW_APPLY_MAX_BYTES    = 10 * MB_IN_BYTES;
const GW_APPLY_IP_SHORT     = array( 3, 10 * MINUTE_IN_SECONDS );
const GW_APPLY_IP_DAY       = array( 10, DAY_IN_SECONDS );
const GW_APPLY_SITE_HOURLY  = array( 60, HOUR_IN_SECONDS );

function gw_apply_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
	// Behind Cloudways' own proxy REMOTE_ADDR can be a private address; then trust only the LAST
	// X-Forwarded-For hop (added by that proxy), never the first one, which the visitor can forge.
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) && ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$hops = array_map( 'trim', explode( ',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		$last = end( $hops );
		if ( filter_var( $last, FILTER_VALIDATE_IP ) ) {
			$ip = $last;
		}
	}
	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : 'unknown';
}

/** Increments a fixed-window counter and returns false once it is over its limit. */
function gw_apply_hit( $key, $limit_window ) {
	list( $limit, $window ) = $limit_window;
	$key  = 'gw_apply_' . substr( md5( $key . '|' . $window ), 0, 20 );
	$data = get_transient( $key );
	$now  = time();
	if ( ! is_array( $data ) || $data['until'] <= $now ) {
		$data = array( 'n' => 0, 'until' => $now + $window );
	}
	$data['n']++;
	set_transient( $key, $data, max( 1, $data['until'] - $now ) );
	return $data['n'] <= $limit;
}

/** Normalises $_FILES['form_fields'][...][ $id ] (single or multiple input) into a list of files. */
function gw_apply_files( $id ) {
	if ( empty( $_FILES['form_fields'] ) || ! is_array( $_FILES['form_fields'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return array();
	}
	$f   = $_FILES['form_fields']; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
	$out = array();
	if ( ! isset( $f['name'][ $id ] ) ) {
		return $out;
	}
	foreach ( (array) $f['name'][ $id ] as $i => $name ) {
		$pick  = function ( $k ) use ( $f, $id, $i ) {
			$v = $f[ $k ][ $id ] ?? null;
			return is_array( $v ) ? ( $v[ $i ] ?? null ) : $v;
		};
		$error = (int) $pick( 'error' );
		if ( UPLOAD_ERR_NO_FILE === $error && '' === (string) $name ) {
			continue;
		}
		$out[] = array(
			'name'     => (string) $name,
			'tmp_name' => (string) $pick( 'tmp_name' ),
			'size'     => (int) $pick( 'size' ),
			'error'    => $error,
		);
	}
	return $out;
}

function gw_apply_check_file( $file ) {
	if ( UPLOAD_ERR_OK !== $file['error'] ) {
		return UPLOAD_ERR_INI_SIZE === $file['error'] || UPLOAD_ERR_FORM_SIZE === $file['error']
			? 'Your resume is larger than 10 MB.'
			: 'The upload failed. Please try again.';
	}
	if ( ! is_uploaded_file( $file['tmp_name'] ) ) {
		return 'The upload failed. Please try again.';
	}
	$size = (int) filesize( $file['tmp_name'] );
	if ( $size <= 0 || $size > GW_APPLY_MAX_BYTES ) {
		return 'Your resume must be a PDF or Word file up to 10 MB.';
	}
	$name = strtolower( wp_basename( $file['name'] ) );
	// One extension only, from the allow-list, and nothing executable hiding in the name.
	if ( ! preg_match( '/^[^.\/\\\\]{1,150}\.(pdf|docx?)$/', $name, $m ) ) {
		return 'Your resume must be a single PDF, DOC or DOCX file (one dot, no special file name).';
	}
	$ext = $m[1];
	$fh  = fopen( $file['tmp_name'], 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! $fh ) {
		return 'The upload failed. Please try again.';
	}
	$head = (string) fread( $fh, 65536 ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$magic = array(
		'pdf'  => "%PDF-",
		'doc'  => "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1",
		'docx' => "PK\x03\x04",
	);
	if ( 0 !== strpos( $head, $magic[ $ext ] ) ) {
		return 'That file does not look like a real ' . strtoupper( $ext ) . ' document.';
	}
	if ( preg_match( '/<\?php|<\?=|<script\b/i', $head ) ) {
		return 'That file could not be accepted.';
	}
	if ( function_exists( 'finfo_open' ) ) {
		$fi   = finfo_open( FILEINFO_MIME_TYPE );
		$mime = $fi ? (string) finfo_file( $fi, $file['tmp_name'] ) : '';
		if ( $fi ) {
			finfo_close( $fi );
		}
		$ok = array(
			'pdf'  => array( 'application/pdf' ),
			'doc'  => array( 'application/msword', 'application/x-ole-storage', 'application/CDFV2', 'application/vnd.ms-office' ),
			'docx' => array( 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream' ),
		);
		if ( '' !== $mime && ! in_array( $mime, $ok[ $ext ], true ) ) {
			return 'That file does not look like a real ' . strtoupper( $ext ) . ' document.';
		}
	}
	if ( 'docx' === $ext && class_exists( 'ZipArchive' ) ) {
		$zip = new ZipArchive();
		if ( true !== $zip->open( $file['tmp_name'] ) || false === $zip->locateName( 'word/document.xml' ) ) {
			return 'That file does not look like a real DOCX document.';
		}
		// Refuse macro-enabled or oversized ("zip bomb") archives.
		$total = 0;
		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$st     = $zip->statIndex( $i );
			$total += (int) ( $st['size'] ?? 0 );
			if ( preg_match( '/vbaProject\.bin$/i', (string) ( $st['name'] ?? '' ) ) || $total > 100 * MB_IN_BYTES || $i > 2000 ) {
				$zip->close();
				return 'That file could not be accepted.';
			}
		}
		$zip->close();
	}
	return '';
}

add_action( 'elementor_pro/forms/validation', function ( $record, $ajax_handler ) {
	if ( GW_CAREER_FORM_NAME !== $record->get_form_settings( 'form_name' ) ) {
		return;
	}
	$ip = gw_apply_client_ip();
	if ( ! gw_apply_hit( 'site', GW_APPLY_SITE_HOURLY ) || ! gw_apply_hit( 'ip-short|' . $ip, GW_APPLY_IP_SHORT ) || ! gw_apply_hit( 'ip-day|' . $ip, GW_APPLY_IP_DAY ) ) {
		$ajax_handler->add_error_message( 'Too many applications from your connection. Please wait a few minutes and try again, or email us your resume.' );
		$ajax_handler->add_error( GW_APPLY_FILE_FIELD, 'Please try again later.' );
		return;
	}
	foreach ( (array) $record->get_field( array( 'type' => 'upload' ) ) as $id => $field ) {
		$files = gw_apply_files( $id );
		if ( GW_APPLY_FILE_FIELD !== $id ) {
			if ( $files ) {
				$ajax_handler->add_error( $id, 'This upload is not accepted.' );
			}
			continue;
		}
		if ( 1 !== count( $files ) ) {
			$ajax_handler->add_error( $id, $files ? 'Please upload one file only.' : 'Please upload your resume (PDF, DOC or DOCX, up to 10 MB).' );
			continue;
		}
		$err = gw_apply_check_file( $files[0] );
		if ( '' !== $err ) {
			$ajax_handler->add_error( $id, $err );
		}
	}
}, 10, 2 );
