<?php

defined( 'ABSPATH' ) or die;

// change page link to display published pages only
// https://wordpress.stackexchange.com/questions/91299/how-to-display-by-default-only-published-posts-pages-in-the-admin-area
function wcs_change_admin_page_link() {
	global $submenu;
	$submenu['edit.php?post_type=page'][5][2] = 'edit.php?post_type=page&post_status=publish';
}
add_action( 'admin_menu', 'wcs_change_admin_page_link' );

// change post link to display published posts only
function wcs_change_admin_post_link() {
	global $submenu;
	$submenu['edit.php'][5][2] = 'edit.php?post_status=publish';
}
add_action( 'admin_menu', 'wcs_change_admin_post_link' );

// change post link to display published products only (i made this one!)
function wcs_change_admin_product_link() {
	global $submenu;
	$submenu['edit.php?post_type=product'][5][2] = 'edit.php?post_type=product&post_status=publish';
}
add_action( 'admin_menu', 'wcs_change_admin_product_link' );

// change elementor templates link to display published templates only (i made this one!)
function wcs_change_admin_elementor_templates_link() {
	global $submenu;
	$submenu['edit.php?post_type=elementor_library'][5][2] = 'edit.php?post_status=publish&post_type=elementor_library';
}
add_action( 'admin_menu', 'wcs_change_admin_elementor_templates_link' );
