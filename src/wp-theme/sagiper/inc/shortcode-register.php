<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

if (!function_exists("get_file_details")) {
    add_shortcode("get_file_details", "get_file_details");
    function get_file_details()
    {
        $html = "";
        $id = get_the_ID();
        $pdfUrl = get_field('project_file_download', $id);
        $linkFile = get_field('project_file_url_download', $id);
        $pdfIcon = THEME_URL_ASSETS . '/images/pdf.png';
        $dwgIcon = THEME_URL_ASSETS . '/images/dwg.svg';
        $zipIcon = THEME_URL_ASSETS . '/images/zip.svg';
        $youtubeIcon = THEME_URL_ASSETS . '/images/youtube.svg';
        $documentIcon = THEME_URL_ASSETS . '/images/document.svg';

        $fileName = get_the_title();

        $btnClass = '';

        // Get attachment ID from URL
        $attachment_id = attachment_url_to_postid($pdfUrl);
        $pdfPath = get_attached_file($attachment_id);

        $pdfSize = filesize($pdfPath);
        $pdfSize = size_format($pdfSize);

        $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M7.50046 16.25H5.62546C5.00512 16.2492 4.39203 16.1166 3.82688 15.8608C3.26172 15.605 2.75743 15.2319 2.34746 14.7663C1.93748 14.3008 1.63121 13.7534 1.44895 13.1604C1.26669 12.5674 1.21261 11.9425 1.29031 11.327C1.36801 10.7116 1.57571 10.1197 1.89962 9.5906C2.22354 9.06153 2.65626 8.6074 3.16907 8.25834C3.68189 7.90928 4.26308 7.67326 4.87407 7.56595C5.48506 7.45864 6.11189 7.4825 6.71296 7.63593" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.25 10C6.25 9.00968 6.48533 8.03353 6.9366 7.152C7.38788 6.27047 8.04217 5.50879 8.84556 4.92974C9.64895 4.35068 10.5784 3.97083 11.5574 3.82148C12.5364 3.67213 13.5369 3.75756 14.4764 4.07073C15.4159 4.3839 16.2676 4.91584 16.9612 5.62272C17.6547 6.3296 18.1704 7.19118 18.4657 8.13645C18.761 9.08173 18.8274 10.0836 18.6595 11.0596C18.4916 12.0356 18.0942 12.9577 17.5 13.75" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9.375 13.75L11.875 16.25L14.375 13.75" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M11.875 10V16.25" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>	';

        if ($pdfSize || $linkFile) {

            $iconSrc = $pdfSize ? $pdfIcon : $documentIcon;
            $fileUrl = $pdfSize ? $pdfUrl : $linkFile;
            $size = $pdfSize ?: "";

            if (!empty($fileUrl)) {
                $ext = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION));
                $popTypes = ['dwg', 'bim', 'cad', 'zip'];
                if (in_array($ext, $popTypes)) {
                    $btnClass = 'btn-pop-technical';
                    $fileUrl = "#";
                }

                if ($ext === "dwg") {
                    $iconSrc = $dwgIcon;
                }
                if ($ext === "zip") {
                    $iconSrc = $zipIcon;
                }
            }

            if (strpos($fileUrl, 'youtube.com') !== false || strpos($fileUrl, 'youtu.be') !== false) {
                $iconSrc = $youtubeIcon;
            }


            $html = "
                    <div class='file-item flex'>
                        <div class='file-title flex'>
                            <img src='{$iconSrc}' alt='icon' height='40'>
                            <p>
                                <span class='file-name'>{$fileName}</span><br>
                                <span class='blue file-size'>{$size}</span>
                            </p>
                        </div>
                        <a class='elementor-button elementor-size-sm $btnClass' role='button' href='$fileUrl' data-item='$id'>
                            <span class='wrapper'>
                                <span class='elementor-button-text'>Download</span>
                                <span class='elementor-button-icon'>{$icon}</span>
                            </span>
                        </a>
                    </div>
                ";
        } else {
            $html = "<p class='file-not-found'>No file available.</p>";
        }

        // Output
        ob_start();
        echo $html;
        return ob_get_clean();
    }
}

if (!function_exists("title_stories")) {
    add_shortcode("title_stories", "title_stories");
    function title_stories()
    {
        $result = "";
        $args = [
            'post_type' => 'our-story',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ];

        $query = new WP_Query($args);


        if ($query->have_posts()):
            while ($query->have_posts()):
                $query->the_post();
                $id = get_the_ID();
                $title = get_the_title();
                $result .= "<a data-id='post-$id' class='flex'>$title</a>";
            endwhile;
            wp_reset_postdata();
        endif;

        if ($result) {
            $result = "<div class='title-list-container flex'><div class='title-lists flex'>$result</div></div>";
        }

        return $result;
    }
}

// this code to create custom map 
if (!function_exists("get_the_map")) {
    add_shortcode("get-project-map", "get_the_map");
    function get_the_map()
    {

        $lat = 53.555058463701315;
        $lng = -113.67226721027832; //default lat
        $snazzyy = THEME_URL_ASSETS . '/js/mystylesnazzy.js';
        $locationList = get_all_locations();
        ob_start();
?>
        <section class="the_custom_map">
            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAAcR8xj9zBAlsXFYscwp78Sd4UkuTCEh8"></script>
            <script src="<?= $snazzyy; ?>"></script>
            <div class="map_container">
                <div id="map" style="height: 700px;"></div>
                <ul id="location-list"></ul>
                <input type="hidden" class="lrt_ast" value="">
            </div>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    loadMaps();
                });


                let markers = [];

                function loadMaps() {
                    var defaultLat = <?= $lat; ?>;
                    var defaultLng = <?= $lng; ?>;

                    const termList = <?= $locationList; ?>;
                    const infoWindow = new google.maps.InfoWindow();

                    // Initialize map
                    const map = new google.maps.Map(document.getElementById("map"), {
                        center: {
                            lat: defaultLat,
                            lng: defaultLng
                        },
                        zoom: 14,
                        styles: snazyystyle,
                    });

                    markers.forEach(m => m.setMap(null));
                    markers = [];

                    //  FLATTEN grouped termList to be flat locations
                    const flatLocations = termList.flatMap(term =>
                        term.items.map(item => ({
                            ...item,
                            color: term.color
                        }))
                    );

                    // Initial markers
                    addMarkers(flatLocations);

                    function addMarkers(data) {
                        let totalLat = 0;
                        let totalLng = 0;
                        let count = 0;

                        markers.forEach(marker => marker.setMap(null));
                        markers = [];

                        data.forEach(location => {
                            const latLng = {
                                lat: parseFloat(location.lat),
                                lng: parseFloat(location.lng),
                            };

                            if (isNaN(latLng.lat) || isNaN(latLng.lng)) return;

                            totalLat += latLng.lat;
                            totalLng += latLng.lng;
                            count++;

                            // const marker = new google.maps.Marker({
                            //     position: latLng,
                            //     map: map,
                            //     icon: {
                            //         path: google.maps.SymbolPath.CIRCLE,
                            //         scale: 7,
                            //         fillColor: location.color || '#000',
                            //         fillOpacity: 1,
                            //         strokeWeight: 0,
                            //     }
                            // });

                            const marker = new google.maps.Marker({
                                position: latLng,
                                map: map,
                                icon: {
                                    path: "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z",
                                    fillColor: location.color || '#000',
                                    fillOpacity: 1,
                                    strokeColor: "#ffffff",
                                    strokeWeight: 2,
                                    scale: 1.6,
                                    anchor: new google.maps.Point(12, 22)
                                }
                            });


                            marker.addListener('click', () => {
                                const imageHtml = location.image ?
                                    `<div class="map-thumb">
                                <img src="${location.image}" alt="${location.name}" />
                        </div>` :
                                    '';

                                infoWindow.setContent(`
                                    <div class="map-infowindow">
                                        ${imageHtml}
                                        <strong>${location.name}</strong>
                                    </div>
                                `);
                                infoWindow.open(map, marker);
                            });

                            markers.push(marker);
                        });

                        if (count > 0) {
                            const bounds = new google.maps.LatLngBounds();
                            markers.forEach(m => bounds.extend(m.getPosition()));
                            map.fitBounds(bounds);
                        }
                    }

                    const filterForm = document.getElementById('map-filter');
                    if (filterForm) {
                        filterForm.addEventListener('change', () => {
                            let filtered = [];

                            filterForm.querySelectorAll('input[type="checkbox"]:checked')
                                .forEach(input => {
                                    try {
                                        filtered = filtered.concat(JSON.parse(input.value));
                                    } catch (e) {
                                        console.error('Invalid JSON in checkbox value', e);
                                    }
                                });

                            addMarkers(filtered);
                        });
                    }
                }
            </script>

        </section>


    <?php
        return ob_get_clean();
    }

    function get_location_list($term = '', $color = '')
    {
        $result = array();

        $tax_query = [
            'taxonomy' => 'sector',
            'operator' => 'EXISTS',
        ];

        if (!empty($term)) {
            $tax_query = [
                'taxonomy' => 'sector',
                'field' => 'slug',
                'terms' => $term,
            ];
        }

        $args = [
            'post_type' => 'projects',
            'posts_per_page' => -1,
            'tax_query' => [$tax_query],
        ];

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $location = get_field('project_location'); // Google Map field
                $url = get_field('url');
                $name = get_the_title();
                $image = get_the_post_thumbnail_url(get_the_ID(), 'medium');

                if (!empty($location) && !empty($location['lat']) && !empty($location['lng'])) {
                    $item = [
                        'name' => $name,
                        'lat' => $location['lat'],
                        'lng' => $location['lng'],
                        'color' => $color,
                        'image' => $image,
                        'address' => $location['address'],
                        'url' => $url,
                    ];

                    $result[] = $item;
                }
            }

            wp_reset_postdata();
            return $result;
        } else {
            return 'No locations found under the dining category.';
        }
    }

    function get_all_locations()
    {
        $terms = get_terms(array(
            'taxonomy' => 'sector',
            'hide_empty' => true,
        ));
        // Check if terms were returned
        if (!empty($terms) && !is_wp_error($terms)) {

            $termList = [];

            foreach ($terms as $term) {
                $slug = $term->slug;
                $color = get_field('color', $term);

                $termList[] = [
                    'slug' => $slug,
                    'color' => $color,
                    'items' => get_location_list($slug, $color),
                ];
            }

            return json_encode($termList);
        }
    }

    add_shortcode("get-project-map-option", 'get_map_filter');
    function get_map_filter()
    {
        $terms = get_terms(array(
            'taxonomy' => 'sector',
            'hide_empty' => true,
        ));

        // Check if terms were returned
        if (!empty($terms) && !is_wp_error($terms)) {
            $termList = '<form id="map-filter" >';

            // Loop through each term and build the list
            foreach ($terms as $term) {
                $slug = esc_attr($term->slug);
                $name = esc_html($term->name);
                $color = get_field("color", $term);
                $dataLocations = get_location_list($slug, $color);
                if (!empty($dataLocations)) {
                    $dataLocations = json_encode($dataLocations);
                    $termList .= "<label class='flex map-input'>
                                    <input type='checkbox' value='$dataLocations' name='$slug' data-color='$color' checked>
                                    <span class='color' style='background-color:$color; width:15px; height:15px; border-radius:50%; display:block; margin: 0 6px 0 20px;'></span>
                                    $name
                                    </label>
                                    ";
                }
            }

            $termList .= '</form>';
            return $termList;
        }
    }
}

if (!function_exists("project_gallery_slider")) {
    add_shortcode('project_gallery_slider', 'project_gallery_jquery_shortcode');
    function project_gallery_jquery_shortcode($atts)
    {
        wp_enqueue_style(
            'slick-css',
            'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css',
            array(),
            '1.8.1'
        );

        wp_enqueue_script(
            'slick-js',
            'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
            array('jquery'),
            '1.8.1',
            true
        );

        if (!function_exists('get_field'))
            return '';
        $id = get_the_ID();
        $images = get_field('project_gallery', $id);
        if (!$images)
            return '';
        $uid = uniqid('pg_');

        ob_start();
    ?>
        <div class="project-gallery">
            <button class="pg-nav pg-prev" type="button" aria-label="Previous image">
                <svg width="54" height="55" viewBox="0 0 54 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M54 8C54 3.58172 50.4183 0 46 0H8C3.58172 0 0 3.58172 0 8V47C0 51.4183 3.58172 55 8 55H46C50.4183 55 54 51.4183 54 47V8Z"
                        fill="#121212" />
                    <path d="M33.875 27.5H20.125" stroke="white" stroke-width="1.25" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M25.75 21.875L20.125 27.5L25.75 33.125" stroke="white" stroke-width="1.25" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>

            </button>
            <div class="pg-main">

                <?php foreach ($images as $i => $image): ?>
                    <a href="<?php echo esc_url(untrailingslashit($image['url'])); ?>" data-elementor-open-lightbox="yes"
                        data-elementor-lightbox-slideshow="<?php echo esc_attr($uid); ?>">
                        <img src="<?php echo esc_url(untrailingslashit($image['url'])); ?>"
                            alt="<?php echo esc_attr($image['alt']); ?>"
                            class="pg-slide no-smush skip-lazy <?php echo $i === 0 ? 'active' : ''; ?>"
                            data-index="<?php echo $i; ?>" loading="eager" decoding="sync">
                    </a>

                <?php endforeach; ?>
            </div>

            <button class="pg-nav pg-next" type="button" aria-label="Next image">
                <svg width="54" height="55" viewBox="0 0 54 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M0 8C0 3.58172 3.58172 0 8 0H46C50.4183 0 54 3.58172 54 8V47C54 51.4183 50.4183 55 46 55H8C3.58172 55 0 51.4183 0 47V8Z"
                        fill="#121212" />
                    <path d="M20.125 27.5H33.875" stroke="white" stroke-width="1.25" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M28.25 21.875L33.875 27.5L28.25 33.125" stroke="white" stroke-width="1.25" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
            <div class="pg-thumbs-container">
                <div class="pg-thumbs">
                    <?php foreach ($images as $i => $image): ?>
                        <img src="<?php echo esc_url($image['sizes']['thumbnail']); ?>" alt="<?php echo esc_attr($image['alt']); ?>"
                            class="pg-thumb no-smush skip-lazy" loading="eager" decoding="sync">
                    <?php
                    endforeach; ?>
                </div>
            </div>

        </div>
<?php
        return ob_get_clean();
    }
}

if (!function_exists("get_product_used")) {
    add_shortcode('get_product_used', 'get_product_used');
    function get_product_used()
    {

        $id = get_the_ID();
        $html = "";
        $siteUrl = get_site_url();
        $learnBtn = "<a href='$siteUrl/finishes' aria-label='go to resources page' class='black-btn'>LEARN MORE</a>";



        if (have_rows("products_used_", $id)) {
            while (have_rows("products_used_", $id)) {
                the_row();
                $picture = get_sub_field("picture");
                $sku = get_sub_field("sku");
                $name = get_sub_field("name");
                $profile = get_sub_field("profile");
                $productWooID = get_sub_field("product_select");
                $selectedFamily = get_sub_field("family_select");
                $result = "";
                $familyName = "";
                if ($picture) {
                    $picture = $picture['url'];
                    $result .= "<img src='$picture' alt='$name' load='lazy'>";
                }

                $requestUrl = "$siteUrl/request-samples";
                if ($productWooID) {
                    $requestUrl .= "/?product-sample=$productWooID";
                }

                if ($selectedFamily) {
                    $familyData = json_decode($selectedFamily, true);
                    $familyUrl = $familyData['url'];
                    $familyName = $familyData['name'];
                    $learnBtn = "<a href='$familyUrl' aria-label='go to resources page' class='black-btn'>LEARN MORE</a>";
                }

                $result .= "<div class='product-used-title'>";
                $result .= "<p class='product-used-name'>$name</p>";

                if (!empty($familyName)) {
                    $result .= "<p class='product-used-family'><strong>$familyName</strong></p>";
                }

                $result .= "<p class='product-used-profile'>$profile</p>";
                $result .= "<p class='product-used-sku'>$sku</p>";
                $result .= "</div>";
                $result .= "<div class='product-used-btn flex'> 
							$learnBtn
                            <a href='$requestUrl' aria-label='go to request samples page' class='blue-btn' target='_blank'>REQUEST SAMPLES</a>
                            </div>";
                $html .= "<div class='product-used-item'>$result</div>";
            }

            $html = "<div class='product-used-container flex'>$html</div>";
        } else {
            $html = "<p>Sorry, we still didn't update the product used.</p>";
        }

        ob_start();

        echo $html;
        return ob_get_clean();
    }
}


// [get-stars-review]
if (!function_exists("get_stars_review")) {
    add_shortcode('get_stars_review', 'get_stars_review');
    function get_stars_review()
    {

        $id = get_the_ID();
        $html = "";
        $starCount = get_field("star", $id);
        $i = 0;
        $starIcon = THEME_URL_ASSETS . '/images/Star.svg';
        while ($i < $starCount) {
            $html .= "<img src='$starIcon' alt='star' width=20>";
            $i++;
        }

        ob_start();
        $html = "<div class='stars-container'>$html</div>";
        echo $html;
        return ob_get_clean();
    }
}

if (!function_exists('get_the_project_phase')) {
    add_shortcode('get_the_project_phase', 'get_the_project_phase');

    function get_the_project_phase()
    {
        $id = get_the_ID();
        $count = 0;
        $html = '';

        if (have_rows('timeline', $id)) {
            while (have_rows('timeline', $id)) {
                the_row();
                $count++;
                $countText = str_pad($count, 2, '0', STR_PAD_LEFT);
                $title = get_sub_field('title');
                $description = get_sub_field('description');
                $html .= "
                    <li>
                        <p class='counter'>$countText</p>
                        <div class='phase-description'>
                            <h3>{$title}</h3>
                            <div>{$description}</div>
                        </div>
                    </li>
                ";
            }
        }

        if (!$html) {
            return '';
        }

        return "<ul class='star-review-container'>{$html}</ul>";
    }
}

if (!function_exists("get_custom_menu_shortcode")) {
    function get_custom_menu_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'id' => '',
            'pages-black-header' => '',
            'class' => 'custom-menu'
        ), $atts, 'get_custom_menu');

        $cartHtml = do_shortcode("[show_custom_drawer]");
        $currentPageID = get_queried_object_id();
        $currentUrl = home_url(add_query_arg(array(), $_SERVER['REQUEST_URI']));
        $blackHeader = "";
        $post_type = get_post_type($currentPageID);

        if (empty($atts['id'])) {
            return '<p>No menu ID provided.</p>';
        }

        $menu_items = wp_get_nav_menu_items((int) $atts['id']);
        if (empty($menu_items)) {
            return '<p>No menu found.</p>';
        }

        $blackHeaderPages = array_map(
            'intval',
            array_map('trim', explode(',', $atts['pages-black-header']))
        );

        if (in_array($currentPageID, $blackHeaderPages, true) || $post_type === 'post' || $post_type === 'career') {
            $blackHeader = 'black-header -default';
        }

        // Build menu tree
        $menu_tree = [];
        foreach ($menu_items as $item) {
            $menu_tree[$item->menu_item_parent][] = $item;
        }

        $output = "<ul class='{$atts['class']} menu-level-1'>";

        // Render top-level
        if (isset($menu_tree[0])) {
            foreach ($menu_tree[0] as $item) {
                $has_child = isset($menu_tree[$item->ID]);

                // Detect current page
                $is_current_page = (
                    intval($item->object_id) === intval($currentPageID) ||
                    untrailingslashit($item->url) === untrailingslashit($currentUrl)
                );

                // Detect current parent
                $is_current_parent = false;
                if ($has_child) {
                    foreach ($menu_tree[$item->ID] as $child) {
                        if (
                            intval($child->object_id) === intval($currentPageID) ||
                            untrailingslashit($child->url) === untrailingslashit($currentUrl)
                        ) {
                            $is_current_parent = true;
                            break;
                        }
                    }
                }

                $title = esc_html($item->title);
                $href = $item->url !== '#' ? " href='" . esc_url($item->url) . "'" : "";
                $classes = "menu-item level-1";
                if ($has_child)
                    $classes .= " has-child";
                if ($has_child)
                    $title .= " <span class='caret'></span>";
                if ($is_current_page)
                    $classes .= " current-page";
                if ($is_current_parent)
                    $classes .= " current-parent";

                $output .= "<a id='menu-{$item->ID}' class='{$classes}' $href>$title</a>";
            }
        }

        $output .= "
        <div class='buttons-menu flex center'>
            $cartHtml
            <div class='hamburger'>
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        ";

        $output .= "</ul>";

        // Render submenus
        if (isset($menu_tree[0])) {
            $levelResult = "";
            foreach ($menu_tree[0] as $parent) {
                $bottomMenuList = "";
                if (isset($menu_tree[$parent->ID])) {
                    $count = 0;
                    foreach ($menu_tree[$parent->ID] as $child) {
                        $count++;
                        $menuID = $child->object_id;
                        $menuPicture = get_the_post_thumbnail_url($menuID, 'full');
                        $title = esc_html($child->title);
                        $subTitle = esc_html($child->attr_title);
                        $menuUrl = $child->url;

                        // Detect current child page
                        $is_current_child = (
                            intval($menuID) === intval($currentPageID) ||
                            untrailingslashit($menuUrl) === untrailingslashit($currentUrl)
                        );

                        $childClass = "submenu-item img-zoom cursor-link";
                        if ($is_current_child)
                            $childClass .= " current-page";

                        $imgHtml = '';
                        if (!empty($menuPicture)) {
                            $imgHtml = "<img src='" . esc_url($menuPicture) . "' alt='" . esc_attr($title) . "' loading='lazy'>";
                        }

                        $bottomMenuList .= "
                            <a href='" . esc_url($menuUrl) . "' class='{$childClass}'>
                                <div class='submenu_picture'>$imgHtml</div>
                                <div>
                                    <p class='title'>
                                        <span class='menu-title'>$title</span>
                                        <span class='menu-subtitle'>$subTitle</span>
                                    </p>
                                </div>
                            </a>
                        ";
                    }

                    $addLogo = $count > 4 ? "with-logo" : "";

                    $levelResult .= "<div data-menu='menu-{$parent->ID}' class='submenu-items $addLogo'>
                        <div class='custom-animate elementor-invisible _flex-space menu_list_container'>
                            {$bottomMenuList}
                            <div class='menu-close'>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                    </div>";
                }
            }

            if ($levelResult) {
                $levelResult = "<div class='submenu-container'>$levelResult</div>";
            }

            $siteUrl = get_site_url();
            $output = "<div class='custom-menu-container $blackHeader'>
                <div class='top_menu'>
                    <a href='$siteUrl' class='site-logo' aria-label='Go to homepage'></a>
                    $output
                </div>
                <div class='bottom_menu '>$levelResult</div>
            </div>";
        }

        return $output;
    }
    add_shortcode('get_custom_menu', 'get_custom_menu_shortcode');
}

if (!function_exists("get_title_story")) {
    add_shortcode('get_title_story', 'get_title_story');
    function get_title_story()
    {
        $title = get_the_title();
        if ($title) {
            $title = "<span class='blue title-story'>$title: </span>";
        }
        return $title;
    }
}

if (!function_exists("status_projects")) {
    add_shortcode('status_projects', 'status_projects');
    function status_projects()
    {
        $currentSlug = get_post_field('post_name', get_post());

        if (empty($currentSlug)) {
            return '';
        }

        $args = [
            'post_type' => 'projects',
            'posts_per_page' => -1,
            'tax_query' => [
                [
                    'taxonomy' => 'product-family',
                    'field' => 'slug',
                    'terms' => $currentSlug,
                ],
            ],
        ];

        $query = new WP_Query($args);
        if ($query->have_posts()) {
            if (in_array($currentSlug, ['sagibatten', 'sagibond', 'sagiscreen'])) {
                return 'remove_margin_top';
            }

            return '';
        } else {
            return "hidden";
        }
    }
}

// Code to handle ajax for architect toolbox and the shortcode
if (!function_exists("get_architect_toolbox")) {
    add_shortcode("get_architect_toolbox", "get_architect_toolbox");
    function get_architect_toolbox()
    {
        $slug = "architect-toolbox";
        $filterToolbox = get_catalogs_children($slug);
        $dataToolbox = get_catalogs_data($slug);
        $result = "<div class='toolbox-container'>
                        {$filterToolbox}
                        <div class='toolbox-result' id='architect-results'>
                        {$dataToolbox}
                        </div>            
            </div>";

        return $result;
    }

    function get_catalogs_children($slug)
    {
        $parent = get_term_by('slug', $slug, 'resource');
        $ariaPressed = "aria-pressed='false'";
        $count = 0;

        if (!$parent) {
            return 'Parent term not found';
        }

        $defaultFilter = [
            'parent' => $slug,
            'term' => ''
        ];

        $defaultData = base64_encode(wp_json_encode($defaultFilter));

        $baseUrl = get_permalink();
        $terms = get_terms([
            'taxonomy' => 'resource',
            'hide_empty' => false,
            'parent' => $parent->term_id
        ]);

        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        $output = "<search class='e-filter architect-filter' role='search' data-base-url='$baseUrl' >";

        if ($slug === "architect-toolbox") {
            $output .= "<button class='e-filter-item' aria-pressed='true' data-filter='$defaultData'>ALL</button>";
        } else {
            $ariaPressed = "aria-pressed='true'";
        }

        foreach ($terms as $term) {
            if ($count > 0) {
                $ariaPressed = "aria-pressed='false'";
            }

            $name = $term->name;
            $termSlug = $term->slug;

            $filter_data = [
                'parent' => $slug,
                'term' => $termSlug
            ];

            $encoded = base64_encode(wp_json_encode($filter_data));

            $smallTitle = get_field('small_title', 'resource_' . $term->term_id);

            if ($smallTitle) {
                $name .= '<br><span class="small-title">' . $smallTitle . '</span';
            }

            $output .= "<button class='e-filter-item' data-filter='$encoded' $ariaPressed>$name</button>";
            $count++;
        }

        $output .= '</search>';

        return $output;
    }

    function get_catalogs_data($slug)
    {
        $parent = get_term_by('slug', $slug, 'resource');

        if (!$parent) {
            return 'Parent term not found';
        }

        $terms = get_terms([
            'taxonomy' => 'resource',
            'hide_empty' => false,
            'parent' => $parent->term_id
        ]);

        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        $term_ids = wp_list_pluck($terms, 'term_id');

        // architect toolbox = all children
        $terms_to_use = ($slug === "architect-toolbox")
            ? $term_ids
            : [$term_ids[0]];

        $query = new WP_Query([
            'post_type' => 'catalogo',
            'posts_per_page' => -1,
            'tax_query' => [
                [
                    'taxonomy' => 'resource',
                    'field' => 'term_id',
                    'terms' => $terms_to_use
                ]
            ]
        ]);

        $posts = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $posts[] = get_post();
            }
            wp_reset_postdata();
        }

        if (empty($posts)) {
            return 'No posts found';
        }

        return ($slug === "architect-toolbox")
            ? collectArchitectData($posts)
            : collectToolboxData($posts);
    }


    add_action('wp_ajax_load_architect_filter', 'load_architect_filter');
    add_action('wp_ajax_nopriv_load_architect_filter', 'load_architect_filter');

    add_action('wp_ajax_load_architect_filter', 'load_architect_filter');
    add_action('wp_ajax_nopriv_load_architect_filter', 'load_architect_filter');

    function load_architect_filter()
    {
        $slug = sanitize_text_field($_POST['slug'] ?? '');

        $encoded = sanitize_text_field($_POST['slug'] ?? '');

        $data = json_decode(base64_decode($encoded), true);

        $parentSlug = $data['parent'] ?? '';
        $termSlug = $data['term'] ?? '';


        $args = [
            'post_type' => 'catalogo',
            'posts_per_page' => -1,
        ];

        if (!empty($slug) & !empty($termSlug)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'resource',
                    'field' => 'slug',
                    'terms' => $termSlug
                ]
            ];
        } else {
            $parent = get_term_by('slug', $parentSlug, 'resource');

            if (!$parent) {
                echo 'Parent term not found';
                wp_die();
            }

            $terms = get_terms([
                'taxonomy' => 'resource',
                'hide_empty' => false,
                'parent' => $parent->term_id
            ]);

            if (empty($terms) || is_wp_error($terms)) {
                echo '';
                wp_die();
            }

            $term_ids = wp_list_pluck($terms, 'term_id');

            $args['tax_query'] = [
                [
                    'taxonomy' => 'resource',
                    'field' => 'term_id',
                    'terms' => $term_ids
                ]
            ];
        }

        $posts = get_posts($args);

        if (!$posts) {
            echo '<p>No data found</p>';
            wp_die();
        }

        if ($parentSlug === "architect-toolbox") {
            $result = collectArchitectData($posts);
        } else if ($parentSlug === "technical-info") {
            $result = collectToolboxData($posts);
        } else {
            $result = '<p>No data found</p>';
        }
        echo $result;

        wp_die();
    }


    function collectArchitectData($posts)
    {
        if (empty($posts)) return '';

        $grouped = [];

        foreach ($posts as $post) {

            $post_id = $post->ID;

            // Get file types safely
            $file_types = wp_get_object_terms($post_id, 'file-type');

            if (empty($file_types) || is_wp_error($file_types)) {

                $grouped['uncategorized']['label'] = 'Uncategorized';
                $grouped['uncategorized']['items'][] = $post;
                continue;
            }

            foreach ($file_types as $type) {

                $iconField = get_field('icon', 'file-type_' . $type->term_id);

                $icon = is_array($iconField)
                    ? ($iconField['url'] ?? '')
                    : $iconField;

                $grouped[$type->slug]['label'] = $type->name;
                $grouped[$type->slug]['description'] = $type->description;
                $grouped[$type->slug]['icon'] = $icon;
                $grouped[$type->slug]['items'][] = $post;
            }
        }

        // Order by menu_order
        $ordered_terms = get_terms([
            'taxonomy' => 'file-type',
            'hide_empty' => false,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ]);

        $order_map = [];
        foreach ($ordered_terms as $index => $term) {
            $order_map[$term->slug] = $index;
        }

        uksort($grouped, function ($a, $b) use ($order_map) {
            return ($order_map[$a] ?? 999) <=> ($order_map[$b] ?? 999);
        });

        return buildCatalogOutput($grouped);
    }


    function collectToolboxData($posts)
    {
        if (empty($posts)) return '';
        $grouped = [];

        foreach ($posts as $post) {
            $post_id = $post->ID;
            $families = wp_get_object_terms($post_id, 'product-family');
            $file_types = wp_get_object_terms($post_id, 'file-type');
            $icon = '';

            if (!empty($file_types) && !is_wp_error($file_types)) {
                $icon = get_field('icon', 'file-type_' . $file_types[0]->term_id);
            }

            // attach icon to post itself
            $post->toolbox_icon = $icon;

            if (empty($families) || is_wp_error($families)) {
                $grouped['no-family']['label'] = ' ';
                $grouped['no-family']['description'] = '';
                $grouped['no-family']['items'][] = $post;
                continue;
            }

            foreach ($families as $family) {
                $slug = $family->slug;
                if (!isset($grouped[$slug])) {
                    $technicalLibrary = get_field('technical_library', 'product-family_' . $family->term_id);
                    $grouped[$slug]['label'] = $technicalLibrary ?: $family->name;
                    $grouped[$slug]['description'] = '';
                    $grouped[$slug]['items'] = [];
                }
                $grouped[$slug]['items'][] = $post;
            }
        }

        // ordering remains same
        $ordered_terms = get_terms([
            'taxonomy' => 'product-family',
            'hide_empty' => false,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ]);

        $order_map = [];

        foreach ($ordered_terms as $index => $term) {
            $order_map[$term->slug] = $index;
        }

        $order_map['no-family'] = 999;

        uksort($grouped, function ($a, $b) use ($order_map) {
            return ($order_map[$a] ?? 999) <=> ($order_map[$b] ?? 999);
        });

        return buildCatalogOutput($grouped);
    }


    function buildCatalogOutput($grouped)
    {
        $output = '';

        foreach ($grouped as $slug => $data) {

            $title = esc_html($data['label'] ?? 'Uncategorized');
            $description = esc_html($data['description'] ?? '');
            $icon = esc_url($data['icon'] ?? '');

            $categoryItems = '';

            foreach ($data['items'] as $item) {

                $id = $item->ID;
                $fileName = esc_html($item->post_title);

                $pdfUrl = get_field('project_file_download', $id);
                $linkFile = get_field('project_file_url_download', $id);
                $confirmation = get_field('is_confirmation_needed_before_downloading', $id);

                $btnClass = $confirmation ? 'btn-pop-technical' : '';
                $target = $confirmation ? '' : '_blank';

                $fileUrl = $pdfUrl ?: $linkFile;

                $size = '';

                if ($pdfUrl) {

                    $attachmentId = attachment_url_to_postid($pdfUrl);
                    $path = get_attached_file($attachmentId);

                    if ($path && file_exists($path)) {
                        $size = size_format(filesize($path));
                    }
                }

                if ($confirmation) {
                    $fileUrl = '#';
                }

                $iconUrl = $icon ? $icon : $item->toolbox_icon;

                $image = $iconUrl ? "<img src='{$iconUrl}' alt='file icon'>" : '';

                $categoryItems .= "
            <div class='file-item flex'>
                <div class='file-title flex'>
                    {$image}
                    <p>
                        <span class='file-name'>{$fileName}</span><br>
                        <span class='blue file-size'>{$size}</span>
                    </p>
                </div>

                <a class='elementor-button elementor-size-sm {$btnClass}'
                   href='{$fileUrl}'
                   data-item='{$id}'
                   target='{$target}'>

                    <span class='wrapper'>
                        <span class='elementor-button-text'>Download</span>
                    </span>

                </a>
            </div>";
            }

            if ($categoryItems) {

                $output .= "
            <div class='category-file-items'>

                <div class='category-file-title'>
                    <h3 class='file-type'>{$title}</h3>
                    <p>{$description}</p>
                </div>

                <div class='file-items-container'>
                    {$categoryItems}
                </div>

            </div>";
            }
        }

        return $output;
    }


    // get technical info
    add_shortcode("get_technical_info", "get_technical_info");
    function get_technical_info()
    {
        $slug = "technical-info";
        $filterToolbox = get_catalogs_children($slug);
        $dataToolbox = get_catalogs_data($slug);
        $result = "<div class='toolbox-container'>
                        {$filterToolbox}
                        <div class='toolbox-result' id='architect-results'>
                        {$dataToolbox}
                        </div>            
            </div>";
        return $result;
    }
}


// get challenges shortcode
if (!function_exists("get_challenges_case_study")) {
    add_shortcode("get_challenges_case_study", "get_challenges_case_study");
    function get_challenges_case_study()
    {
        $id = get_the_ID();
        $result = "";
        $count = 0;
        $checkMarkIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 19 19" fill="none"><g clip-path="url(#clip0_1191_5539)"><path d="M0.610258 14.3594C0.362242 14.1019 0.161434 13.8471 0.0395394 13.5384C-0.235956 12.8392 0.999192 12.1173 1.54736 11.8805C1.91657 11.7206 2.29493 11.7497 2.66837 11.8937C4.16421 12.5056 4.98506 13.8374 5.82775 15.1748C7.91757 10.5965 10.2448 6.52345 13.3958 2.58819C13.9623 1.88005 14.5731 1.25498 15.2735 0.687359C15.4821 0.518457 15.7167 0.417394 15.9837 0.363401C16.8807 0.182039 17.7649 0.0692079 18.6837 0.000678362C18.7739 -0.00624382 18.9416 0.0359815 18.974 0.104511C19.0452 0.257491 18.9578 0.443006 18.8303 0.554453C16.7996 2.33138 15.1833 4.41772 13.6424 6.62244C11.4581 9.74711 9.64382 13.0704 8.21139 16.5855C7.95914 17.2044 7.68858 17.7775 7.39547 18.3652C7.25737 18.6414 7.04458 18.8159 6.72611 18.8782C5.97713 19.0256 5.20208 19.0332 4.44182 18.9273C4.24736 18.9003 4.07544 18.7958 3.96763 18.6511C3.72596 18.3258 3.50472 18.0274 3.28136 17.6862C2.49715 16.4907 1.61923 15.4032 0.610963 14.3587L0.610258 14.3594Z" fill="#0F6D7F"/></g><defs><clipPath id="clip0_1191_5539"><rect width="19" height="19" fill="white"/></clipPath></defs></svg>';

        if (have_rows('challenges_and_solutions', $id)) {
            while (have_rows('challenges_and_solutions', $id)) {
                the_row();
                $count++;
                $position = ($count % 2 == 0) ? 'second' : '';
                $title       = get_sub_field('title', $id);
                $description = get_sub_field('description', $id);
                $challenge     = get_sub_field('challenges', $id);
                $solution     = get_sub_field('solution', $id);
                $quote      = get_sub_field('quote', $id);
                $picture     = get_sub_field('picture', $id);
                $picture_url = $picture['url'] ?? '';
                $hideQuote = $quote ? '' : 'hidden';
                $result      .= "
                            <div class='challenges_content_outer $position'>
                                <div class='challenges_content_container' >
                                    <div class='challenges_content'>
                                        <p class='challenges_subtitle'>The Challenge $count</p>
                                        <h2 class='challenges_title'>$title</h2>
                                        <p>$challenge</p>
                                        <div class='challenges_break'></div>
                                        <p class='challenges_subtitle'> <span>$checkMarkIcon</span> <span>The Solution</span></p>
                                        <p>$solution</p>
                                    </div>
                                    <div class='challenges_background' style='background-image:url($picture_url);'>
                                        <div class='challenges_quote $hideQuote'><p>$quote</p></div>
                                    </div>
                                </div>
                            </div>
                            ";
            }
        }

        return $result;
    }
}

// get finished product on case study 
if (!function_exists("get_study_case_finished_product")) {
    add_shortcode("get_study_case_finished_product", "get_study_case_finished_product");
    function get_study_case_finished_product()
    {
        $id = get_the_ID();
        $result = "";

        if (have_rows('finish', $id)) {
            while (have_rows('finish', $id)) {
                the_row();
                $name      = get_sub_field('name', $id);
                $picture     = get_sub_field('picture', $id);
                $pictureUrl = $picture['url'] ?? '';
                if ($picture) {
                    $picture = "<a href='$pictureUrl' data-elementor-open-lightbox='yes' data-elementor-lightbox-title='$name'><img src='$pictureUrl' alt='$name'></a>";
                }

                $result .= "<div class='finished-item-case flex add-icon-zoom'>
                            $picture
                            <p class='name'>$name</p>
                            </div>";
            }
        }

        return $result;
    }
}

if (!function_exists('get_product_family_title')) {
    add_shortcode("get_product_family_title", "get_product_family_title");
    function get_product_family_title()
    {
        $id = get_the_ID();
        $selectedFamily = get_field("family_select");
        $result = "";
        if ($selectedFamily) {
            $familyData = json_decode($selectedFamily, true);
            $result = "VIEW ";
            $result .= $familyData['name'];
        }

        return $result;
    }
}

if (!function_exists('get_product_family_url')) {
    add_shortcode("get_product_family_url", "get_product_family_url");
    function get_product_family_url()
    {
        $id = get_the_ID();
        $selectedFamily = get_field("family_select");
        $result = "";
        if ($selectedFamily) {
            $familyData = json_decode($selectedFamily, true);
            $result = $familyData['url'];
        }

        return $result;
    }
}

function star_rating_shortcode()
{
    $rating = get_field('star', get_the_ID()); // ACF field
    if (!$rating) {
        $rating = 0;
    }
    return display_star_rating($rating);
}
add_shortcode('star_rating', 'star_rating_shortcode');

function display_star_rating($rating, $max = 5)
{
    $output = '';
    for ($i = 1; $i <= $max; $i++) {
        if ($i <= $rating) {
            $output .= '<span class="star filled">★</span>';
        } else {
            $output .= '<span class="star empty">☆</span>';
        }
    }
    return $output;
}

if (!function_exists('get_the_inventory')) {
    add_shortcode('get_the_inventory', 'get_the_inventory');
    function get_the_inventory()
    {
        wp_enqueue_script(
            'w3js',
            'https://www.w3schools.com/lib/w3.js',
            array(),
            null,
            false
        );

        $arrows = '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.625 12.375L9 15.75L12.375 12.375" stroke="#343330" stroke-width="1.125" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5.625 5.625L9 2.25L12.375 5.625" stroke="#343330" stroke-width="1.125" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    ';

        $args = array(
            'post_type' => 'inventory',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        );

        $query = new WP_Query($args);
        $content = '';
        $locations = array();
        $lengths = array();
        $categories = array();
        $brands     = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $id = get_the_ID();
                $title = get_the_title();
                $profile = get_field('profile', $id);
                $profile = strtoupper($profile);
                $length = get_field('length', $id);
                $panelCount = get_field('panel_count', $id);
                $calcFormula = get_field('calculation_formula', $id);
                $location = get_field('location', $id);
                $status = get_field('status', $id);
                $image = get_the_post_thumbnail_url($id, 'full');
                $images = get_field('second_images', $id);
                $category = get_the_terms($id, 'product-family')[0]->name ?? '';
                $category = strtoupper($category);
                $category .= "™";
                $brand = get_the_terms($id, 'product_brand')[0]->name ?? '';

                // save the variable for filter
                if (!empty($location)) {
                    $locations[$location] = $location;
                }

                if (!empty($length)) {
                    $lengths[$length] = $length;
                }

                if (!empty($category)) {
                    $catId = get_the_terms($id, 'product-family')[0]->term_id ?? '';
                    $catSlug = get_the_terms($id, 'product-family')[0]->slug ?? '';
                    $categories[$catId] = array(
                        'id'   => $catId,
                        'name' => $category,
                        'slug' => $catSlug,
                    );
                }

                if (!empty($brand)) {
                    $brandId = get_the_terms($id, 'product_brand')[0]->term_id ?? '';
                    $brandSlug = get_the_terms($id, 'product_brand')[0]->slug ?? '';
                    $brands[$brandId] = array(
                        'id'   => $brandId,
                        'name' => $brand,
                        'slug' => $brandSlug,
                    );
                }

                $secondImageHtml = '';
                if (!empty($images)) {
                    foreach ($images as $img) {
                        $imgUrl = $img['url'];
                        $secondImageHtml .= "<a href='$imgUrl' data-elementor-open-lightbox='yes' data-elementor-lightbox-slideshow='inventory-{$id}' data-elementor-lightbox-title='$title' style='display:none;'></a>";
                    }
                }

                $content .= get_inventory_html($category, $image, $title, $profile, $length, $panelCount, $location, $status, $id, $secondImageHtml, $calcFormula);
            }
            wp_reset_postdata();
        } else {
            $content .= '<p>No inventory items found.</p>';
        }


        // get the filter html
        $getInventoryFilter = inventoryFilter($locations, $lengths, $categories, $brands);

        $html = "<div class='inventory-container'>
                    {$getInventoryFilter}
                    <div class='inventory-table-container'>
                        <div class='inventory-loading'>
                            Loading...
                        </div>
                        <table id='inventory-table'>
                            <thead>
                                <tr>
                                    <th onclick=\"w3.sortHTML('#inventory-table', '.item', 'td:nth-child(1)')\"><span class='inventory-title-container'><span class='inventory-title'>PRODUCTS</span> <span class='sort-icon'>$arrows</span></span></th>
                                    <th onclick=\"w3.sortHTML('#inventory-table', '.item', 'td:nth-child(2)')\"><span class='inventory-title-container'><span class='inventory-title'>FINISH</span> <span class='sort-icon'>$arrows</span></span></th>
                                    <th onclick=\"w3.sortHTML('#inventory-table', '.item', 'td:nth-child(3)')\"><span class='inventory-title-container'><span class='inventory-title'>PROFILE</span> <span class='sort-icon'>$arrows</span></span></th>
                                    <th onclick=\"w3.sortHTML('#inventory-table', '.item', 'td:nth-child(4)')\"><span class='inventory-title-container'><span class='inventory-title'>LENGTH</span> <span class='sort-icon'>$arrows</span></span></th>
                                    <th onclick=\"w3.sortHTML('#inventory-table', '.item', 'td:nth-child(5)')\"><span class='inventory-title-container'><span class='inventory-title'>PANEL COUNT</span> <span class='sort-icon'>$arrows</span></span></th>
                                    <th onclick=\"w3.sortHTML('#inventory-table', '.item', 'td:nth-child(6)')\"><span class='inventory-title-container'><span class='inventory-title'>TOTAL SQ FT <br> AVAILABLE</span> <span class='sort-icon'>$arrows</span></span></th>
                                    <th onclick=\"w3.sortHTML('#inventory-table', '.item', 'td:nth-child(7)')\"><span class='inventory-title-container'><span class='inventory-title'>LOCATION</span> <span class='sort-icon'>$arrows</span></span></th>
                                    <th onclick=\"w3.sortHTML('#inventory-table', '.item', 'td:nth-child(8)')\"><span class='inventory-title-container'><span class='inventory-title'>STATUS</span> <span class='sort-icon'>$arrows</span></span></th>
                                </tr>
                            </thead>
                            <tbody>
                                $content
                            </tbody>
                        </table>
                    </div>
                </div>";

        return $html;
    }


    function inventoryFilter($locations = array(), $lengths = array(), $categories = array(), $brands = array())
    {
        $locationsOptions = '';
        foreach ($locations as $location) {
            $locationsOptions .= "<option value='$location'>$location</option>";
        }

        $lengthsOptions = '';
        foreach ($lengths as $length) {
            $lengthsOptions .= "<option value='$length'>{$length}FT</option>";
        }

        $categoriesOptions = '';
        foreach ($categories as $category) {
            $categoriesOptions .= "<option value='{$category['slug']}'>{$category['name']}</option>";
        }

        $brandsOptions = '';
        foreach ($brands as $brand) {
            $brandsOptions .= "<option value='{$brand['slug']}'>{$brand['name']}</option>";
        }

        return "
        <form class='inventory-filter-form' id='inventory-filter-form'>
            <div class='inventory-filters'>
                <label for='category-filter' name='category-filter'>Product Type -  
                    <select id='category-filter'>
                        <option value=''>All Types</option>
                        $categoriesOptions
                    </select>
                </label>

                <label for='profile-filter' name='profile-filter'>Finishes -  
                    <select id='profile-filter'>
                        <option value=''>All Finishes</option>
                        $brandsOptions
                    </select>
                </label>

                <label for='length-filter' name='length-filter'>Length -  
                    <select id='length-filter'>
                        <option value=''>All Lengths</option>
                        $lengthsOptions
                    </select>
                </label>

                <label for='location-filter' name='location-filter'>Location -  
                    <select id='location-filter'>
                        <option value=''>All Locations</option>
                        $locationsOptions
                    </select>
                </label>
            </div>
        </form>";
    }

    function get_inventory_html($category = "", $image = "", $title = "", $profile = "", $length = 0, $panelCount = 0, $location = "", $status = "", $id = "", $secondImageHtml = "", $calcFormula = 0)
    {
        if ($calcFormula == '2') {
            $total = floor((8 / 12) * $length * $panelCount);
        } else {
            $total = floor(($length / 2) * $panelCount);
        }
        $class = strtolower(str_replace(' ', '-', $status));
        $html = "<tr class='item'>
                    <td>$category</td>
                    <td>
                        <a href='$image' data-elementor-open-lightbox='yes' data-elementor-lightbox-slideshow='inventory-{$id}' data-elementor-lightbox-title='$title' class='inventory-image' style='background-image:url($image);'>
                            <span class='image-title'>$title</span>
                        </a>
                        $secondImageHtml
                    </td>
                    <td>$profile</td>
                    <td>{$length}FT</td>
                    <td>{$panelCount} Panels</td>
                    <td>{$total} sq ft</td>
                    <td>{$location}</td>
                    <td><span class='inventory-status {$class}'>{$status}</span></td>
                </tr>";
        return $html;
    }


    /**
     * AJAX
     */
    add_action('wp_ajax_inventory_filter', 'inventory_filter_ajax');
    add_action('wp_ajax_nopriv_inventory_filter', 'inventory_filter_ajax');

    function inventory_filter_ajax()
    {

        $category = $_POST['category'] ?? '';
        $profile  = $_POST['profile'] ?? '';
        $length   = $_POST['length'] ?? '';
        $location = $_POST['location'] ?? '';

        $tax_query  = array();
        $meta_query = array();

        /** Category */
        if (!empty($category)) {
            $tax_query[] = array(
                'taxonomy' => 'product-family',
                'field'    => 'slug',
                'terms'    => $category,
            );
        }

        /** Profile */
        if (!empty($profile)) {
            $tax_query[] = array(
                'taxonomy' => 'product_brand',
                'field'    => 'slug',
                'terms'    => $profile,
            );
        }

        /** Location */
        if (!empty($length)) {
            $meta_query[] = array(
                'key'     => 'length',
                'value'   => $length,
                'compare' => '=',
            );
        }

        /** Location */
        if (!empty($location)) {
            $meta_query[] = array(
                'key'     => 'location',
                'value'   => $location,
                'compare' => '=',
            );
        }


        $args = array(
            'post_type'      => 'inventory',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'ID',
            'order'          => 'DESC',
            'tax_query'      => $tax_query,
            'meta_query'     => $meta_query,
        );


        $query = new WP_Query($args);
        $html = '';


        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $id = get_the_ID();
                $title = get_the_title();
                $profile = get_field('profile', $id);
                $profile = strtoupper($profile);
                $length = get_field('length', $id);
                $panelCount = get_field('panel_count', $id);
                $calcFormula = get_field('calculation_formula', $id);
                $location = get_field('location', $id);
                $status = get_field('status', $id);
                $image = get_the_post_thumbnail_url($id, 'full');
                $images = get_field('second_images', $id);
                $category = get_the_terms($id, 'product-family')[0]->name ?? '';
                $category = strtoupper($category);
                $category .= "™";

                $secondImageHtml = '';
                if (!empty($images)) {
                    foreach ($images as $img) {
                        $imgUrl = $img['url'];
                        $secondImageHtml .= "<a href='$imgUrl' data-elementor-open-lightbox='yes' data-elementor-lightbox-slideshow='inventory-{$id}' data-elementor-lightbox-title='$title' style='display:none;'>";
                    }
                }

                $html .= get_inventory_html($category, $image, $title, $profile, $length, $panelCount, $location, $status, $id, $secondImageHtml, $calcFormula);
            }

            wp_reset_postdata();
        } else {

            $html = "<tr>
                    <td colspan='10'>
                        No inventory found
                    </td>
                </tr> ";
        }


        wp_send_json(array(
            'html' => $html,
        ));
    }
}
