<?php
/*
Plugin Name: Dashboard Directory Size
Description: Dashboard widget to display directory sizes
Author: Pete Nelson
Version: 1.0
*/

if ( !defined( 'ABSPATH' ) ) exit( 'restricted access' );

// include plugin files
$include_files = array( 'common', 'settings', 'dashboard-widget' );
foreach ( $include_files as $include_file ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-dashboard-directory-size-' . $include_file . '.php';
}


// hook up our classes
if ( class_exists( 'Dashboard_Directory_Size_Common' ) ) {
	$dds_common = new Dashboard_Directory_Size_Common();
	add_action( 'plugins_loaded', array( $dds_common, 'plugins_loaded' ) );
}


if ( class_exists( 'Dashboard_Directory_Size_Settings' ) ) {
	$dds_settings = new Dashboard_Directory_Size_Settings();
	add_action( 'plugins_loaded', array( $dds_settings, 'plugins_loaded' ) );
	register_activation_hook( __FILE__, array( $dds_settings, 'activation_hook' ) );
}


if ( class_exists( 'Dashboard_Directory_Size_Dashboard_Widget' ) ) {
	$dds_dash_widget = new Dashboard_Directory_Size_Dashboard_Widget();
	$dds_dash_widget->plugin_dir_url = plugin_dir_url( __FILE__ );
	add_action( 'plugins_loaded', array( $dds_dash_widget, 'plugins_loaded' ) );
}
