<?php
/*
Plugin Name: Dashboard Directory Size
Description: Dashboard widget to display directory sizes
Author: Pete Nelson <a href="https://twitter.com/CodeGeekATX">(@CodeGeekATX)</a>
Version: 1.6.1
Text Domain: dashboard-directory-size
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! defined( 'DASHBOARD_DIRECOTRY_SIZE_ROOT' ) ) {
	define( 'DASHBOARD_DIRECOTRY_SIZE_ROOT', trailingslashit( dirname( __FILE__ ) ) );
}

if ( ! defined( 'DASHBOARD_DIRECOTRY_SIZE_INC' ) ) {
	define( 'DASHBOARD_DIRECOTRY_SIZE_INC', DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/' );
}

$files = [
	'class-dashboard-directory-size-common.php',
	'class-dashboard-directory-size-i18n.php',
	'class-dashboard-directory-size-settings.php',
	'class-dashboard-directory-size-dashboard-widget.php',
	'class-dashboard-directory-size-rest-api.php',
	'class-dashboard-directory-size-rest-api.php',
	'sanitizers.php',
];

foreach( $files as $file ) {
	require_once DASHBOARD_DIRECOTRY_SIZE_INC . $file;
}

add_action( 'plugins_loaded', 'Dashboard_Directory_Size_i18n::plugins_loaded' );
add_action( 'plugins_loaded', 'Dashboard_Directory_Size_Common::plugins_loaded' );
add_action( 'plugins_loaded', 'Dashboard_Directory_Size_Dashboard_Widget::plugins_loaded' );
add_action( 'plugins_loaded', 'Dashboard_Directory_Size_Settings::plugins_loaded' );
add_action( 'plugins_loaded', 'Dashboard_Directory_Size_REST_API::plugins_loaded' );

// handler for activation
if ( class_exists( 'Dashboard_Directory_Size_Settings' ) ) {
	$dds_settings = new Dashboard_Directory_Size_Settings();
	register_activation_hook( __FILE__, array( $dds_settings, 'activation_hook' ) );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/wp-cli/setup.php';
}
