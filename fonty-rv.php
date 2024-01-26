<?php
/**
 * Plugin Name: fonty-rv
 * Description: customizations for rock voices
 * Author: fontaholic design
 * Author URI: https://fontaholic.biz
 * Version: 1.0.1
 **/

defined( 'ABSPATH' ) or die;

define( 'FONTY_RV_PLUGIN_BASE_DIR', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );

require FONTY_RV_PLUGIN_BASE_DIR . 'inc/admin.php';
require FONTY_RV_PLUGIN_BASE_DIR . 'inc/woo.php';
require FONTY_RV_PLUGIN_BASE_DIR . 'inc/gpp.php';
require FONTY_RV_PLUGIN_BASE_DIR . 'inc/loco-repo.php';
