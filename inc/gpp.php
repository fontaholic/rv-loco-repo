<?php

defined( 'ABSPATH' ) or die;

//include menu in header
add_action( 'generate_after_secondary_menu', function() {
	if ( function_exists( 'generate_wc_do_cart_menu_item' ) )
		echo generate_wc_do_cart_menu_item();
} );

// gp mobile nav
add_action( 'init', function() {
	register_nav_menu( 'mobile-menu', __( 'Mobile Menu' ) );
} );

add_filter( 'generate_mobile_header_theme_location', function() {
	return 'mobile-menu';
} );

// Enqueue Font Awesome
add_action( 'wp_enqueue_scripts', 'tu_load_font_awesome' );
function tu_load_font_awesome() {
	wp_enqueue_style( 'font-awesome', 'https://use.fontawesome.com/releases/v5.5.0/css/all.css', [], '5.5.0' );
}

// gp move top-bar to bottom of header
add_action( 'after_setup_theme', 'tu_move_top_bar' );
function tu_move_top_bar() {
	remove_action( 'generate_before_header', 'generate_top_bar', 5 );
	add_action( 'generate_after_header', 'generate_top_bar', 6 );
}

add_action( 'init', function() {
	register_nav_menu( 'mobile-menu', __( 'Mobile Menu' ) );
} );

add_filter( 'generate_mobile_header_theme_location', function() {
	return 'mobile-menu';
} );

// change sidebar on one page
add_filter( 'generate_sidebar_layout', function( $layout ) {
	global $post;

	if ( is_page() && 140 === $post->post_parent )
		return 'right-sidebar';

	return $layout;
} );


function current_year_shortcode() {
	// Get the current year
	$current_year = date('Y');

	// Return the year inside a span
	return '<span class="current-year">' . $current_year . '</span>';
}
add_shortcode('current_year', 'current_year_shortcode');


