<?php

defined( 'ABSPATH' ) or die;

// Change link to display published items only
function wcs_change_admin_link( $post_type, $menu_slug ) {
	global $submenu;
	$submenu["edit.php?post_type=$post_type"][5][2] = "edit.php?post_status=publish&post_type=$post_type";
}
add_action('admin_menu', 'wcs_change_admin_links');

// Change choir_location link to display published locations only
function wcs_change_admin_links( ) {
	wcs_change_admin_link( 'page', 'page' );
	wcs_change_admin_link( 'post', 'edit' );
	wcs_change_admin_link( 'product', 'product' );
	wcs_change_admin_link( 'elementor_library', 'elementor_library' );
	wcs_change_admin_link( 'choir_location', 'choir_location' );
	wcs_change_admin_link( 'playlist', 'playlist' );
}
