<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'WP_Directory_Size_Common' ) ) {

	class WP_Directory_Size_Common {

		static $plugin_name         = 'wp-directory-size';

		public function plugins_loaded() {

			add_filter( self::$plugin_name . '-get', array( $this, 'filter_get_directory_size' ), 10, 2 );
			add_filter( self::$plugin_name . '-get-directories', array( $this, 'filter_get_directories' ), 10, 1 );

		}

		public function filter_get_directories( $directories ) {

			$common = apply_filters( WP_Directory_Size_Common::$plugin_name . '-setting-get', array(), 'wp-directory-size-settings-general', 'common-directories' );
			$common_dirs = array();

			$upload_dir = wp_upload_dir();
			if ( ! empty( $upload_dir ) ) {
				$upload_dir = $upload_dir['basename'];
			}

			// TODO upload dir not showing

			if ( ! empty( $common ) ) {
				for ( $i=0 ; $i < count( $common ); $i++) {

					$new_dir = array( 'path' => $common[ $i ] );

					switch ( $new_dir['path'] ) {
						case WP_PLUGIN_DIR;
							$new_dir['name'] = 'plugins';
							break;
						case WPMU_PLUGIN_DIR;
							$new_dir['name'] = 'mu-plugins';
							break;
					}

					if ( $new_dir['path'] === $upload_dir ) {
						$new_dir['name'] = 'uploads';
					}

					$new_dir['size'] = $this->filter_get_directory_size( -1, $new_dir['path'] );

					$common_dirs[] = $new_dir;
				}
			}


			return array_merge( $directories, $common_dirs );
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