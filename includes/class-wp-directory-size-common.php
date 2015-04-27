<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'WP_Directory_Size_Common' ) ) {

	class WP_Directory_Size_Common {

		static $plugin_name         = 'wp-directory-size';

		public function plugins_loaded() {

			add_filter( self::$plugin_name . '-get', array( $this, 'filter_get_directory_size' ), 10, 2 );

		}


		public function filter_get_directory_size( $size, $directory ) {

			require_once ABSPATH . 'wp-includes/ms-functions.php';

			// TODO verify access to directory
			// TODO verify directory exists
			// TODO get/set transient

			$size = recurse_dirsize( $directory );

			return $size;
		}

	} // end class

}