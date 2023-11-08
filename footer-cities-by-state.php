<?php


// cities by state list for wp_footer

function city_list_by_state_shortcode() {
	$args = array(
		'post_type' => 'choir_location', // Replace with your custom post type name
		'posts_per_page' => -1,
	);

	$locations_query = new WP_Query($args);

	if ($locations_query->have_posts()) {
		$cities_by_state = array();

		while ($locations_query->have_posts()) {
			$locations_query->the_post();
			$location_state = wp_get_post_terms(get_the_ID(), 'state');

			if (!empty($location_state)) {
				$state_name = esc_html($location_state[0]->name);
				$location_name = esc_html(get_the_title()); // Get the location name
				$city_name = esc_html(get_field('city')); // Replace with your ACF city field name
				$city_link = esc_url(get_permalink()); // Get the location post's permalink

				// Check if a specific location should use a different city name
				if ($location_name === 'Albany, NY') {
					$city_name = 'Albany';
				} elseif ($location_name === 'Buffalo, NY') {
					$city_name = 'Buffalo';
				} elseif ($location_name === 'Newton, MA') {
					$city_name = 'Newton'; 
					// Add more elseif conditions as needed for other cities
				} elseif ($location_name === 'North Shore, MA') {
					$city_name = 'North Shore';
				}
				// End Add more elseif conditions as needed for other cities

				if (!isset($cities_by_state[$state_name])) {
					$cities_by_state[$state_name] = array();
				}

				$cities_by_state[$state_name][] = "<a href='$city_link'>$city_name</a>";
			}
		}

		wp_reset_postdata();

		// Sort states by the number of cities (posts)
		arsort($cities_by_state);

		$output = array(); // Use an array to accumulate HTML content

		foreach ($cities_by_state as $state => $cities) {
			// Sort cities alphabetically
			asort($cities);

			$city_list = implode(',&nbsp; ', $cities);
			$output[] = "<span class='state-row'><span class='footer-states'>$state:</span> $city_list </span>"; // style bits
		}

		$column_count = 2; // Number of columns
		$total_items = count($output);
		$items_per_column = ceil($total_items / $column_count);

		$columns = array();

		for ($i = 0; $i < $column_count; $i++) {
			$start = $i * $items_per_column;
			$column_items = array_slice($output, $start, $items_per_column);
			$columns[] = "<div class='column'>" . implode('', $column_items) . "</div>";
		}

		// Wrap the columns in a container div
		$output = "<div id='new-rv-city-footer'>" . implode('', $columns) . "</div";

		return $output;
	}

	return 'No locations found.';
}
add_shortcode('city_list_by_state', 'city_list_by_state_shortcode');
