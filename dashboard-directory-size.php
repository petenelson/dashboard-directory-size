<?php
/*
Plugin Name: Dashboard Directory Size
Description: Dashboard widget to display directory sizes
Author: Pete Nelson <a href="https://twitter.com/GunGeekATX">(@GunGeekATX)</a>
Version: 1.2.0
Text Domain: dashboard-directory-size
Domain Path: /languages
*/

if ( !defined( 'ABSPATH' ) ) die( 'restricted access' );

// include plugin files
$include_files = array( 'common', 'i18n', 'settings', 'dashboard-widget', 'rest-api' );
foreach ( $include_files as $include_file ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-dashboard-directory-size-' . $include_file . '.php';
}

$classes = array();

$class_names = array(
	'Dashboard_Directory_Size_Common',
	'Dashboard_Directory_Size_i18n',
	'Dashboard_Directory_Size_Settings',
	'Dashboard_Directory_Size_Dashboard_Widget',
	'Dashboard_Directory_Size_REST_API',
	);

// instantiate our classes
foreach ( $class_names as $class_name ) {
	if ( class_exists( $class_name ) ) {
		$classes[] = new $class_name;
	}
}

// hook our classes into WordPress
foreach ( $classes as $class ) {
	add_action( 'plugins_loaded', array( $class, 'plugins_loaded' ) );
}


// handler for activation
if ( class_exists( 'Dashboard_Directory_Size_Settings' ) ) {
	$dds_settings = new Dashboard_Directory_Size_Settings();
	register_activation_hook( __FILE__, array( $dds_settings, 'activation_hook' ) );
}

