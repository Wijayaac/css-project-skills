<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// this code to create custom map 
if (!function_exists("get_the_project_map")) {
    add_shortcode("get-project-map", "get_the_project_map");
    function get_the_project_map($atts)
    {
        $atts = shortcode_atts([
            'neighbourhood' => ''
        ], $atts);
        $category = $atts['neighbourhood'];
        $lat = 53.555058463701315;
        $lng = -113.67226721027832; //default lat

        if (!empty($atts['neighbourhood'])) {
            $term = get_term_by('slug',  $category, 'neighbourhood');

            if ($term && !is_wp_error($term)) {
                $location = get_field('location', $term); // ACF map field

                if (!empty($location['lat']) && !empty($location['lng'])) {
                    $lat = $location['lat'];
                    $lng = $location['lng'];
                }
            }
        }
        
        $snazzyy = THEME_URL_ASSETS . '/js/mystylesnazzy.js';
        $locationList = get_first_location( $category);
        ob_start();
?>
<section class="the_custom_map">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAAcR8xj9zBAlsXFYscwp78Sd4UkuTCEh8"></script>
    <script src="<?= $snazzyy; ?>"></script>
    <div class="map_container">
        <div id="map-list"></div>
        <div id="map" style="height: 500px;"></div>
        <ul id="location-list"></ul>
        <input type="hidden" class="lrt_ast" value="">
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        loadMaps();
    });

    let markers = [];
    let directionsService;
    const isMobile = window.innerWidth < 768;

    function loadMaps() {
        var defaultLat = <?= $lat; ?>;
        var defaultLng = <?= $lng; ?>;

        const termList = <?= $locationList; ?>;
        const infoWindow = new google.maps.InfoWindow();

        const map = new google.maps.Map(document.getElementById("map"), {
            center: {
                lat: defaultLat,
                lng: defaultLng
            },
            zoom: 14,
            styles: snazyystyle,
        });

        directionsService = new google.maps.DirectionsService();
        const defaultMarker = new google.maps.Marker({
            position: {
                lat: defaultLat,
                lng: defaultLng
            },
            map: map,
            animation: google.maps.Animation.DROP,
            // icon: {
            //     url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
            // }
            icon: {
                path: "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z",
                fillColor: '#D90429',
                fillOpacity: 1,
                strokeColor: "#ffffff",
                strokeWeight: 2,
                scale: 1.6,
                anchor: new google.maps.Point(12, 22)
            }
        });

        const defaultInfo = new google.maps.InfoWindow({
            content: "<strong>Our Location</strong>"
        });

        defaultMarker.addListener("click", () => {
            defaultInfo.open(map, defaultMarker);
        });

        markers.forEach(m => m.setMap(null));
        markers = [];

        const flatLocations = termList.flatMap(term =>
            term.items.map(item => ({
                ...item,
                color: term.color
            }))
        );

        addMarkers(flatLocations);

        function addMarkers(data) {
            let totalLat = 0;
            let totalLng = 0;
            let count = 0;

            const listContainer = document.getElementById('map-list');

            if (!isMobile && listContainer) {
                listContainer.innerHTML = '';
            }

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

                const marker = new google.maps.Marker({
                    position: latLng,
                    map: map,
                    icon: {
                        path: "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z",
                        fillColor: '#D90429',
												fillOpacity: 1,
												strokeColor: "#ffffff",
                        strokeWeight: 2,
                        scale: 1.6,
                        anchor: new google.maps.Point(12, 22)
                    }
                });

                let listItem = null;

                if (!isMobile && listContainer) {
                    listItem = document.createElement('div');
                    listItem.className = 'map-list-item';

                    listItem.innerHTML = `
                    <div class="category">${location.category || 'Unknown'}</div>
                    <div class="name">${location.name}</div>
                    <div class="meta">Calculating...</div>
                `;

                    listItem.addEventListener('click', () => {
                        google.maps.event.trigger(marker, 'click');
                        map.panTo(marker.getPosition());
                        map.setZoom(15);
                    });

                    listContainer.appendChild(listItem);

                    directionsService.route({
                        origin: {
                            lat: defaultLat,
                            lng: defaultLng
                        },
                        destination: latLng,
                        travelMode: google.maps.TravelMode.DRIVING
                    }, (result, status) => {

                        let durationText = 'Unavailable';

                        if (status === 'OK') {
                            durationText = result.routes[0].legs[0].duration.text;
                        }

                        if (listItem) {
                            listItem.querySelector('.meta').innerText = durationText + ' Drive';
                        }
                    });
                }

                // Marker click (works on both)
                marker.addListener('click', () => {
                    const imageHtml = location.image ?
                        `<div class="map-thumb">
                        <img src="${location.image}" alt="${location.name}" />
                    </div>` :
                        '';

                    infoWindow.setContent(`
                    <div class="map-infowindow">
                        ${imageHtml}
                        <strong>${location.name}</strong><br>
                        <span>Calculating driving time...</span>
                    </div>
                `);

                    infoWindow.open(map, marker);

                    directionsService.route({
                        origin: {
                            lat: defaultLat,
                            lng: defaultLng
                        },
                        destination: latLng,
                        travelMode: google.maps.TravelMode.DRIVING
                    }, (result, status) => {

                        let durationText = 'Unavailable';

                        if (status === 'OK') {
                            durationText = result.routes[0].legs[0].duration.text;
                        }

                        infoWindow.setContent(`
                        <div class="map-infowindow">
                            ${imageHtml}
                            <strong>${location.name}</strong><br>
                            <span>Driving time: ${durationText}</span>
                        </div>
                    `);
                    });
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

                filterForm.querySelectorAll('input[type="radio"]:checked')
                    .forEach(input => {
                        try {
                            filtered = filtered.concat(JSON.parse(input.value));
                        } catch (e) {
                            console.error('Invalid JSON', e);
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

    function get_location_list($term = '', $color = '', $categoryName='', $selectedCat="alces")
    {
        $result = array();

        $tax_query = [
            'relation' => 'AND',
        ];

        if (!empty($term)) {
            $tax_query[] = [
                'taxonomy' => 'amenity-type',
                'field'    => 'slug',
                'terms'    => $term,
            ];
        }

        if (!empty($selectedCat)) {
            $tax_query[] = [
                'taxonomy' => 'neighbourhood',
                'field'    => 'slug',
                'terms'    => $selectedCat,
            ];
        }

        $args = [
            'post_type'      => 'amenity',
            'posts_per_page' => -1,
            'tax_query'      => $tax_query,
        ];


        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $location = get_field('location'); // Google Map field
                $url = "#";
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
                        'category' => $categoryName,
                    ];

                    $result[] = $item;
                }
            }

            wp_reset_postdata();
            return $result;
        } else {
            return 'No locations found under the selected category.';
        }
    }

    function get_first_location($selectedCat = "")
    {
        $terms = get_terms(array(
            'taxonomy' => 'amenity-type',
            'hide_empty' => true,
        ));
        // Check if terms were returned
        if (!empty($terms) && !is_wp_error($terms)) {

            $termList = [];

            foreach ($terms as $term) {
                $slug = $term->slug;
                $name = $term->name;
                $color = get_field('color', $term);

                $termList[] = [
                    'slug' => $slug,
                    'color' => $color,
                    'items' => get_location_list($slug, $color, $name, $selectedCat),
                ];

                break; // only the first term
            }

            return json_encode($termList);
        }
    }

    add_shortcode("get-project-map-option", 'get_map_filter');
    function get_map_filter($atts)
    {
        $atts = shortcode_atts([
            'neighbourhood' => ''
        ], $atts);
        $selectedCat = $atts['neighbourhood'];

        $terms = get_terms(array(
            'taxonomy' => 'amenity-type',
            'hide_empty' => true,
        ));

        // Check if terms were returned
        if (!empty($terms) && !is_wp_error($terms)) {
            $termList = '<form id="map-filter" >';

            // Loop through each term and build the list
            $count = 0;
            foreach ($terms as $term) {
                $status = ($count == 0) ? 'checked' : '';
                $slug = esc_attr($term->slug);
                $name = esc_html($term->name);
                $color = get_field("color", $term);
                $dataLocations = get_location_list($slug, $color, $name,  $selectedCat);
                if (!empty($dataLocations)) {
                    $dataLocations = json_encode($dataLocations);
                    $termList .= "<label class='flex map-input'>
                                    <input type='radio' value='$dataLocations' name='map-filter' data-color='$color' $status>
                                    <span class='label-text'>$name</span>
                                    </label>
                                    ";
                }
                $count++;
            }

            $termList .= '</form>';
            return $termList;
        }
    }
}