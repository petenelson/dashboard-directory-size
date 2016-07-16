<?php
/*
Plugin Name: Dashboard Directory Size
Description: Dashboard widget to display directory sizes
Author: Pete Nelson <a href="https://twitter.com/GunGeekATX">(@GunGeekATX)</a>
Version: 1.5.0
Text Domain: dashboard-directory-size
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! defined( 'DASHBOARD_DIRECOTRY_SIZE_ROOT' ) ) {
	define( 'DASHBOARD_DIRECOTRY_SIZE_ROOT', trailingslashit( dirname( __FILE__ ) ) );
}

require_once DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/class-dashboard-directory-size-common.php';
require_once DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/class-dashboard-directory-size-i18n.php';
require_once DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/class-dashboard-directory-size-settings.php';
require_once DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/class-dashboard-directory-size-dashboard-widget.php';
require_once DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/class-dashboard-directory-size-rest-api.php';

add_action( 'plugins_loaded', 'Dashboard_Directory_Size_i18n::plugins_loaded' );
add_action( 'plugins_loaded', 'Dashboard_Directory_Size_Common::plugins_loaded' );
add_action( 'plugins_loaded', 'Dashboard_Directory_Size_Dashboard_Widget::plugins_loaded' );

// add_action( 'plugins_loaded', 'Dashboard_Directory_Size_Common::plugins_loaded' );

// handler for activation
if ( class_exists( 'Dashboard_Directory_Size_Settings' ) ) {
	$dds_settings = new Dashboard_Directory_Size_Settings();
	register_activation_hook( __FILE__, array( $dds_settings, 'activation_hook' ) );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/wp-cli/setup.php';
}
