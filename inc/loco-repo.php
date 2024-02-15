<?php

defined( 'ABSPATH' ) or die;

add_shortcode( 'city_list_by_state', 'city_list_by_state_shortcode' );
/**
 * @shortcode city_list_by_state
 * some comment
 */
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
				} elseif ($location_name === 'Philadelphia, PA') {
					$city_name = 'Philadelphia';					
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
		$items_per_column = ceil($total_items / $column_count); //not used now
		$items_in_first_column = 2; //change this to put more states in first column.

		$columns = array();


		for ($i = 0; $i < $column_count; $i++) {
			$start = $i * $items_in_first_column;
			if($i == 0) {
				$column_items = array_slice($output, $start, $items_in_first_column);
			} else
			{
				$column_items = array_slice($output, $items_in_first_column, $total_items);
			}

			$columns[] = "<div class='column'>" . implode('', $column_items) . "</div>";
		}
		// Wrap the columns in a container div
		$output = "<div id='new-rv-city-footer'>" . implode('', $columns) . "</div";

		return $output;
	}

	return 'No locations found.';
}


// [location_start_map] //////////////////////////////////////// rehearsal city, location and date
function location_start_map_shortcode() {
	ob_start();

	$args = array(
		'post_type'      => 'choir_location',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'     => 'rehearsal_day_and_start_time',
				'value'   => date('Y-m-d H:i:s'), // Current date and time
				'compare' => '>=', // Show only dates in the future
				'type'    => 'DATETIME',
			),
		),
	);

	$locations = new WP_Query($args);

	if ($locations->have_posts()) {
		echo '<div class="location-start-map">';
		while ($locations->have_posts()) {
			$locations->the_post();
			$location_title = get_the_title();
			$rehearsal_day_and_start_time = get_field('rehearsal_day_and_start_time');
			$rehearsal_location = get_field('rehearsal_location');
			$google_map_link = get_field('google_map_link');

			echo '<div class="location-entry">';
			//echo '<span class="location-title"><a href="' . get_permalink() . '">' . esc_html($location_title) . '</a></span>';
			echo '<span class="location-title">' . esc_html($location_title) . '</span>';
			echo '<span class="location-start">';
			echo esc_html($rehearsal_day_and_start_time) . '</span>';
			echo '<span class="location-link"><a href="' . esc_url($google_map_link) . '">' . esc_html($rehearsal_location) . '</a>';
			echo '</span>';
			echo '</div>';
		}
		echo '</div>';
	}

	wp_reset_postdata();

	return ob_get_clean();
}

add_shortcode('location_start_map', 'location_start_map_shortcode');



// [concert_links] //////////////////////////////////////// concert ticket popup
function concert_links_shortcode() {
	ob_start();

	$args = array(
		'post_type'      => 'choir_location',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	);

	$locations = new WP_Query($args);

	if ($locations->have_posts()) {
		echo '<div id="concert-time">';
		echo '<ul class="locations-list">';
		while ($locations->have_posts()) {
			$locations->the_post();

			$location_title      = get_the_title();
			$concert_date        = get_field('concert_date');
			$concert_ticket_link = get_field('concert_ticket_link');

			// Check if concert date has not passed
			if (strtotime($concert_date) >= strtotime(date('Y-m-d'))) {
				// Format the date
				$formatted_date = date('l, F j, Y - g:i A', strtotime($concert_date));

				echo '<li class="location-entry">';
				echo '<span class="location-details">';
				echo '<strong><a target="_blank"href="' . esc_url($concert_ticket_link) . '">' . esc_html($location_title) . '</a></strong>';
				echo ' - ' . esc_html($formatted_date);
				echo '</span>';
				echo '</li>';
			}
		}
		echo '</ul>';
		echo '</div>';
	}

	wp_reset_postdata();

	return ob_get_clean();
}

add_shortcode('concert_links', 'concert_links_shortcode');


// [all-concert-posters] //////////////////////////////////////// concert posters
function all_concert_posters_shortcode() {
	ob_start();

	$args = array(
		'post_type'      => 'choir_location',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	);

	$locations = new WP_Query($args);

	if ($locations->have_posts()) {
		echo '<div id="all-concert-posters">';
		echo '<ul class="locations-list">';
		while ($locations->have_posts()) {
			$locations->the_post();

			$location_title   = get_the_title();
			$concert_date     = get_field('concert_date');
			$printable_poster = get_field('printable_poster');
			$digital_poster   = get_field('digital_poster');

			// Check if concert date has not passed
			if (strtotime($concert_date) >= strtotime(date('Y-m-d'))) {
				// Format the date
				$formatted_date = date('M j, Y - g:i A', strtotime($concert_date));

				echo '<li class="location-entry">';
				echo '<span class="location-details">';
				echo '<strong>' . esc_html($location_title) . '</strong>';
				echo ' - ' . esc_html($formatted_date);
				echo ' </span><span class="poster-white"> <i aria-hidden="true" class="fas fa-print"></i> <a href="' . esc_url($printable_poster) . '" target="_blank" download="">Printable Poster</a>';
				echo ' </span><span class="poster-black"> <i aria-hidden="true" class="fas fa-share-alt-square"></i> <a href="' . esc_url($digital_poster) . '" download="">Digital Poster</a>';
				echo '</span>';
				echo '</li>';
			}
		}
		echo '</ul>';
		echo '</div>';
	}

	wp_reset_postdata();

	return ob_get_clean();
}

add_shortcode('all-concert-posters', 'all_concert_posters_shortcode');

////////////////////////////////////////// add custom column configurations on left column ADMIN COLUMNS PRO
class AdminColumnsMenu
{

	private $list_id;

	private $label;

	private $list_screen;

	public function __construct(string $list_id, string $label = null)
	{
		$this->list_id = $list_id;
		$this->label = $label;
		$this->list_screen = AC()->get_storage()->find(new AC\Type\ListScreenId($list_id));

		if ($this->list_screen) {
			if ($label === null) {
				$this->label = $this->list_screen->get_title();
			}
			add_action('admin_menu', [$this, 'register_menu']);
		}
	}

	private function get_parent_url(): string
	{
		if (!$this->list_screen instanceof AC\ListScreen\Post) {
			return '';
		}

		if ($this->list_screen->get_post_type() === 'post') {
			return 'edit.php';
		}

		return 'edit.php?post_type=' . $this->list_screen->get_post_type();
	}

	public function register_menu()
	{
		add_submenu_page(
			$this->get_parent_url(),
			$this->label,
			$this->label,
			'manage_options',
			$this->list_screen->get_table_url()
		);
	}
}

add_action('ac/ready', function () {
	add_action('init', function () {
		// Provide a valid ListID and the rest is done automatically, It is possible to overwrite the label
		new AdminColumnsMenu('650c803ad4a96', '- Locations');
		new AdminColumnsMenu('6582dd74d48e3', '- Registration');
		new AdminColumnsMenu('6582fb33e7608', '- Concert Details');
	});
});


// [register-today-by-city] //////////////////////////////////////// register today by city

function register_today_by_city_shortcode() {
	ob_start();

	$args = array(
		'post_type'      => 'choir_location',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'     => 'commit_date', // Update the key to 'commit_date'
				'value'   => date('Y-m-d H:i:s'), // Current date and time
				'compare' => '>=', // Show only dates in the future
				'type'    => 'DATETIME',
			),
		),
	);

	$locations = new WP_Query( $args );

	if ( $locations->have_posts() ) {
		echo '<div class="register-today-by-city">';
		while ( $locations->have_posts() ) {
			$locations->the_post();
			$location_title             = get_the_title();
			$rehearsal_day_and_start_time = get_field( 'rehearsal_day_and_start_time' );
			$rehearsal_location         = get_field( 'rehearsal_location' );
			$google_map_link            = get_field( 'google_map_link' );
			$registration_link          = get_field( 'registration_link' );

			// Check if registration_link is not empty
			if ( ! empty( $registration_link ) ) {
				echo '<div class="city-entry">';
				echo '<span class="location-title">' . esc_html( $location_title ) . '</span>';
				echo '<span class="location-link"><a target="_blank" href="' . esc_url( $google_map_link ) . '">' . esc_html( $rehearsal_location ) . '</a></span>';
				echo '<span class="location-start">' . esc_html( $rehearsal_day_and_start_time ) . '</span>';
				echo '<a target="_blank" class="donate-button" href="' . esc_url( $registration_link ) . '">Join ' . esc_html( $location_title ) . ' Rock Voices</a>';
				echo '</div>';
			}
		}
		echo '</div>';
	}

	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'register-today-by-city', 'register_today_by_city_shortcode' );


// [all-flyers] //////////////////////////////////////// seasonal flyers
function all_season_flyers_shortcode( ) {
	ob_start( );

	$args = array(
		'post_type'      => 'choir_location',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	);

	$locations = new WP_Query( $args );

	if ( $locations->have_posts( ) ) {
		echo '<div id="all-flyers">';
		echo '<ul class="locations-list">';
		while ($locations->have_posts( ) ) {
			$locations->the_post( );

			$location_title   = get_the_title( );
			$commit_date     = get_field( 'commit_date' );
			$printable_flyer = get_field( 'printable_flyer' );
			$digital_flyer   = get_field( 'digital_flyer' );
			$permalink       = get_permalink(); // Get the permalink

			// Check if concert date has not passed
			if (strtotime( $commit_date ) >= strtotime(date( 'Y-m-d' ) ) ) {
				// Format the date
				$formatted_date = date( 'M j, Y', strtotime( $commit_date ) );

				echo '<li class="location-entry">';
				echo '<span class="location-details">';
				echo '<strong><a href="' . esc_url( $permalink ) . '">' . esc_html( $location_title ) . '</a></strong>'; // Use the permalink as the href
				echo ' -  payment due by ' . esc_html( $formatted_date) ;
				echo ' </span><span class="flyer-white"> <i aria-hidden="true" class="fas fa-print"></i> <a href="' . esc_url( $printable_flyer ) . '" target="_blank" download="">Printable flyer</a>';
				echo ' </span><span class="flyer-black"> <i aria-hidden="true" class="fas fa-share-alt-square"></i> <a href="' . esc_url( $digital_flyer ) . '" target="_blank" download="">Digital flyer</a>';
				echo '</span>';
				echo '</li>';
			}
		}
		echo '</ul>';
		echo '</div>';
	}

	wp_reset_postdata( );

	return ob_get_clean( );
}

add_shortcode( 'all-flyers', 'all_season_flyers_shortcode' );



// [auto-playlist] //////////////////////////////////////// register today by city
function auto_playlist_shortcode( $atts ) {

	$playlist_id = $atts['id'] ?? 0;

	// If no ID is specified in the shortcode, get the latest published playlist
	if ( empty( $playlist_id ) ) {
		if ( is_singular( 'playlist' ) ) {
			$playlist_id = get_queried_object_id();
		} else {
			$atts = [];

			$query = new WP_Query( [
				'posts_per_page'   => 1,
				'post_type'        => 'playlists',
				'orderby'          => 'date',
				'order'            => 'DESC',
				'post_status'      => 'publish',
				'fields'           => 'ids',
				'cache_results'    => false,
				'suppress_filters' => true,
				'no_found_rows'    => true,
			] );

			$playlist_id = reset( $query->posts );
		}
	}

	if ( empty( $playlist_id ) ) return ''; // No playlist found.

	// Get the playlist repeater field
	$playlist = function_exists( 'get_field' ) ? get_field( 'song', $playlist_id ) : null;

	if ( $playlist ) {

		$video_ids = [];

		foreach ( $playlist as $song ) {
			// Accept all links. Sybre-sama (and GPT).
			preg_match(
				'/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|(\S*?[?&]v=))|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
				$song['youtube_link'],
				$matches,
			);

			$video_ids[] = $matches[2] ?? '';
		}

		// Format the playlist HTML using sprintf
		$playlist_html = sprintf(
			'<div class="auto-playlist-container"><div class="auto-playlist"><iframe width="720" height="405" src="%s" frameborder="0" allowfullscreen></iframe></div></div>',
			esc_url( sprintf(
				'https://www.youtube.com/embed/%s?playlist=%s',
				reset( $video_ids ),
				implode( ',', $video_ids ),
			) )
		);
	}

	return $playlist_html ?? '';
}
add_shortcode('auto-playlist', 'auto_playlist_shortcode');

// [part_recordings] ////////////////////////////////////////  part=alto cybr
function part_recordings_shortcode( $atts ) {

	$songs = function_exists( 'get_field' ) ? get_field( 'song' ) : null;

	if ( empty( $songs ) ) return '';

	switch ( $atts['part'] ?? '' ) {
		case 'soprano':
			$get = [
				'part_recording_soprano'  => '',
				'part_recording_soprano1' => 'Soprano 1',
				'part_recording_soprano2' => 'Soprano 2',
			];
			break;
		case 'alto':
			$get = [
				'part_recording_alto'  => '',
				'part_recording_alto1' => 'Alto 1',
				'part_recording_alto2' => 'Alto 2',
			];
			break;
		case 'tenor':
			$get = [
				'part_recording_tenor'    => '',
				'part_recording_tenor_hi' => 'Tenor Hi',
				'part_recording_tenor_lo' => 'Tenor Lo',
			];
			break;
		case 'bass':
			$get = [
				'part_recording_bass' => '',
			];
			break;
		case 'solo':
			$get = [
				'part_recording_solo' => '',
			];
			break;
			case 'choral_mix':
			$get = [
				'part_recording_choral_mix' => '',
			];
			break;
	}

	if ( empty( $get ) ) return '';

	$links = '';

	foreach ( $songs as $song ) {
		foreach ( $get as $field_name => $affix ) {
			if ( empty( $song[ $field_name ] ) ) continue;

			$links .= sprintf(
				'<li><a href="%s" target="_blank" download>%s</a></li>',
				esc_url( $song[ $field_name ] ),
				esc_html(
					$song['song_title']
						. ( $affix ? " - $affix" : '' )
				),
			);
		}
	}

	return $links ? "<ul>$links</ul>" : '';
}
add_shortcode( 'part-recordings', 'part_recordings_shortcode' );


// force mp3 downloads cybr
add_action(
	'wp_footer',
	function () {
		if ( ! is_singular( 'playlist' ) ) return;
		echo <<<'HTML'
			<script>document.addEventListener( 'DOMContentLoaded', () => {
				const downloadBlob = ( blobUrl, filename ) => {
					const anchor = document.createElement( 'a' );

					anchor.href = blobUrl;
					anchor.download = filename;
					anchor.click();

					URL.revokeObjectURL( blobUrl );
				}

				// Function to force download
				const forceDownload = e => {
					e.preventDefault();
					const domain = new URL( window.location.href ).origin;
					const linkie = new URL( e.target.href.replace( /^(https?:\/\/)[^/]+/, domain ) );
					linkie.searchParams.append( 'cacheBust', new Date().getTime() );

					fetch( linkie, { mode: 'same-origin', cache: 'no-cache', } )
						.then( res => res.blob() )
						.then( blob => {
							downloadBlob( 
								window.URL.createObjectURL( blob ), 
								e.target.href.split( '\\' ).pop().split( '/' ).pop(), 
							);
							URL.revokeObjectURL( blob );
						} )
						.catch( e => console.error( e ) );
			}

			// Trigger download for each mp3 link
			document.querySelectorAll( 'a[href$=".mp3"][download]' )
				.forEach( el => el.addEventListener( 'click', forceDownload ) );
		} );</script>
		HTML;
	},
);



function auto_playlist_txt_shortcode( $atts ) {
	$playlist_id = $atts['id'] ?? 0;

	// If no ID is specified in the shortcode, get the latest published playlist
	if ( empty( $playlist_id ) ) {
		if ( is_singular( 'playlist' ) ) {
			$playlist_id = get_queried_object_id();
		} else {
			$atts = [];

			$query = new WP_Query( [
				'posts_per_page'   => 1,
				'post_type'        => 'playlist', // Corrected post type name
				'orderby'          => 'date',
				'order'            => 'DESC',
				'post_status'      => 'publish',
				'fields'           => 'ids',
				'cache_results'    => false,
				'suppress_filters' => true,
				'no_found_rows'    => true,
			] );

			$playlist_id = reset( $query->posts );
		}
	}

	if ( empty( $playlist_id ) ) return ''; // No playlist found.

	// Get the playlist repeater field
	$playlist = function_exists( 'get_field' ) ? get_field( 'song', $playlist_id ) : null;

	if ( $playlist ) {

		$video_ids = '';

		foreach ( $playlist as $song ) {
			preg_match(
				'/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|(\S*?[?&]v=))|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
				$song['youtube_link'],
				$matches,
			);

			$video_ids .= $matches[2] . ',';

			// Output each song title and artist
			echo '<div class="song-details">';
			echo '<span class="song-title">' . esc_html( $song['song_title'] ) . '</span>';
			echo ' by ';
			echo '<span class="artist">' . esc_html( $song['artist'] ) . '</span>';
			echo '</div>';
		}

		// Remove the trailing comma from video_ids
		$video_ids = rtrim( $video_ids, ',' );

		// Format the playlist HTML using sprintf
		$playlist_html = sprintf(
			'<div class="auto-playlist-container"><div class="auto-playlist"><iframe width="720" height="405" src="%s" frameborder="0" allowfullscreen></iframe></div></div>',
			esc_url( sprintf(
				'https://www.youtube.com/embed/%s?playlist=%s',
				reset( explode( ',', $video_ids ) ),
				$video_ids,
			) )
		);
	}

	return $playlist_html ?? '';
}
add_shortcode( 'auto-playlist-txt', 'auto_playlist_txt_shortcode' );



// [part_recordings] ////////////////////////////////////////  part=alto

function playlist_text_shortcode( $atts ) {
	$playlist_id = $atts['id'] ?? 0;

	// If no ID is specified in the shortcode, get the latest published playlist
	if ( empty( $playlist_id ) ) {
		if ( is_singular( 'playlist' ) ) {
			$playlist_id = get_queried_object_id();
		} else {
			$atts = [];

			$query = new WP_Query( [
				'posts_per_page'   => 1,
				'post_type'        => 'playlist', // Corrected post type name
				'orderby'          => 'date',
				'order'            => 'DESC',
				'post_status'      => 'publish',
				'fields'           => 'ids',
				'cache_results'    => false,
				'suppress_filters' => true,
				'no_found_rows'    => true,
			] );

			$playlist_id = reset( $query->posts );
		}
	}

	if ( empty( $playlist_id ) ) return ''; // No playlist found.

	// Get the playlist post data
	$playlist_post = get_post( $playlist_id );
	$playlist_title = esc_html( $playlist_post->post_title );
	$season_field = get_field( 'season', $playlist_id );

	// Get the playlist repeater field
	$playlist = function_exists( 'get_field' ) ? get_field( 'song', $playlist_id ) : null;

	if ( $playlist ) {
		// Output playlist name and season
		echo '<div class="playlist-head">';
		echo '<strong>' . $playlist_title . ' ' . $season_field . ' Song List</strong>';
		echo '</div>';
		// Output each song title linked to the YouTube URL and the artist name
		echo '<div class="song-list">';
		foreach ( $playlist as $song ) {
			$youtube_url = esc_url( $song['youtube_link'] );
			$song_title = esc_html( $song['song_title'] );
			$artist_name = esc_html( $song['artist'] );

			echo '<div class="song-details">';
			echo '<a href="' . $youtube_url . '" target="_blank" rel="noopener">' . $song_title . '</a>';
			echo '<p class="artist-name">' . $artist_name . '</p>';
			echo '</div>';
		}
		echo '</div>';
	}
}
add_shortcode( 'playlist-text', 'playlist_text_shortcode' );


// collapses ACF repeater fields for song list in admin
function rdsn_acf_repeater_collapse() {
?>
<style id="rdsn-acf-repeater-collapse">
	.acf-repeater .acf-row:not(.-clone) {display:none;}
	.acf-repeater .acf-row.-active {background-color: lightgreen;}
</style>
<script type="text/javascript">
  jQuery(function($) {
	$(document).on('click', '.acf-pagination-button', function() {
	  setTimeout(function() {
		$('.acf-repeater .acf-row:not(.-clone)').addClass('-collapsed');
	  }, 200);
	});
	$('.acf-repeater .acf-row:not(.-clone)').addClass('-collapsed');
	$('#rdsn-acf-repeater-collapse').detach();
  });
</script>
<?php
}
add_action('acf/input/admin_head', 'rdsn_acf_repeater_collapse');



function choir_location_buttons_shortcode($atts) {
	ob_start();

	$atts = shortcode_atts(array(), $atts, 'choir_location_buttons');
	
	// Get the current page's director term (assuming the director taxonomy is associated with the choir_location post type)
	$terms = get_the_terms(get_the_ID(), 'director');

	if ($terms && !is_wp_error($terms)) {
		$director_slug = $terms[0]->slug;
		
		// Query to get choir locations based on the director
		$args = array(
			'post_type' => 'choir_location',
			'tax_query' => array(
				array(
					'taxonomy' => 'director',
					'field'    => 'slug',
					'terms'    => $director_slug,
				),
			),
		);

		$locations = new WP_Query($args);

		if ($locations->have_posts()) {
			while ($locations->have_posts()) {
				$locations->the_post();
				$location_title = get_the_title();
				$location_permalink = get_permalink();

				// Output button for each location
				echo '<a href="' . esc_url($location_permalink) . '" class="elementor-button elementor-button-link elementor-size-sm">' . esc_html($location_title) . '</a>';
			}

			wp_reset_postdata();
		} else {
			echo '<p>No choir locations found for the director.</p>';
		}
	} else {
		echo '<p>No director term found for the current page.</p>';
	}

	return ob_get_clean();
}
add_shortcode('choir_location_buttons', 'choir_location_buttons_shortcode');

