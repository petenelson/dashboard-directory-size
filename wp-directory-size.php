<?php
/*
Plugin Name: WP Directory Size
Description: Dashboard widget to display directory sizes
Author: Pete Nelson
Version: 1.0
*/

if ( !defined( 'ABSPATH' ) ) exit( 'restricted access' );

// include plugin files
$include_files = array( 'common', 'settings', 'dashboard-widget' );
foreach ( $include_files as $include_file ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-directory-size-' . $include_file . '.php';
}


// hook up our classes
if ( class_exists( 'WP_Directory_Size_Common' ) ) {
	$wpds_common = new WP_Directory_Size_Common();
	add_action( 'plugins_loaded', array( $wpds_common, 'plugins_loaded' ) );
}


if ( class_exists( 'WP_Directory_Size_Settings' ) ) {
	$wpds_settings = new WP_Directory_Size_Settings();
	add_action( 'plugins_loaded', array( $wpds_settings, 'plugins_loaded' ) );
}


if ( class_exists( 'WP_Directory_Size_Dashboard_Widget' ) ) {
	$wpds_dash_widget = new WP_Directory_Size_Dashboard_Widget();
	$wpds_dash_widget->plugin_dir_url = plugin_dir_url( __FILE__ );
	add_action( 'plugins_loaded', array( $wpds_dash_widget, 'plugins_loaded' ) );
}
