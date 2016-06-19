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

class Dashboard_Directory_Size_Plugin {

	function define_constants() {
		if ( ! defined( 'DASHBOARD_DIRECOTRY_SIZE_ROOT' ) ) {
			define( 'DASHBOARD_DIRECOTRY_SIZE_ROOT', trailingslashit( dirname( __FILE__ ) ) );
		}
	}

	function get_required_files() {
		$include_files = array( 'common', 'i18n', 'settings', 'dashboard-widget', 'rest-api' );
		$files = array();
		foreach ( $include_files as $include_file ) {
			$files[] = DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/class-dashboard-directory-size-' . $include_file . '.php';
		}
		return $files;
	}

	function get_class_names() {
		return array(
			'Dashboard_Directory_Size_Common',
			'Dashboard_Directory_Size_i18n',
			'Dashboard_Directory_Size_Settings',
			'Dashboard_Directory_Size_Dashboard_Widget',
			'Dashboard_Directory_Size_REST_API',
			);
	}

	function require_files( $files ) {
		foreach( $files as $file ) {
			require_once $file;
		}
	}

}

$plugin = new Dashboard_Directory_Size_Plugin();
$plugin->define_constants();
$plugin->require_files( $plugin->get_required_files() );

// load plugin classes
foreach( $plugin->get_class_names() as $class_name ) {
	$classes = array();
	if ( class_exists( $class_name ) ) {
		$classes[] = new $class_name;
	}

	foreach ( $classes as $class ) {
		add_action( 'plugins_loaded', array( $class, 'plugins_loaded' ) );
	}
}


// handler for activation
if ( class_exists( 'Dashboard_Directory_Size_Settings' ) ) {
	$dds_settings = new Dashboard_Directory_Size_Settings();
	register_activation_hook( __FILE__, array( $dds_settings, 'activation_hook' ) );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/wp-cli/setup.php';
}
