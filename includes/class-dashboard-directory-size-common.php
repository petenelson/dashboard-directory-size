<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'Dashboard_Directory_Size_Common' ) ) {

	class Dashboard_Directory_Size_Common {

		static $plugin_name         = 'dashboard-directory-size';


		public function plugins_loaded() {

			add_filter( Dashboard_Directory_Size_Common::$plugin_name . '-get', array( $this, 'filter_get_directory_size' ), 10, 2 );
			add_filter( Dashboard_Directory_Size_Common::$plugin_name . '-get-directories', array( $this, 'filter_get_directories' ), 10, 1 );

		}


		public function filter_get_directories( $directories ) {

			$new_dirs = array();

			// add common directories
			$common = apply_filters( Dashboard_Directory_Size_Common::$plugin_name . '-setting-get', array(), Dashboard_Directory_Size_Common::$plugin_name . '-settings-general', 'common-directories' );

			if ( ! empty( $common ) && is_array( $common ) ) {

				foreach ( $common as $common_dir ) {

					$path = $this->get_path_for_common_dir( $common_dir );
					$new_dir = $this->create_directory_info( $common_dir, $path );

					if ( ! empty( $new_dir ) ) {
						$new_dirs[] = $new_dir;
					}
				}

			}

			// add custom directories
			$custom_dirs = $this->get_custom_dirs();
			if ( ! empty( $custom_dirs) ) {
				$new_dirs = array_merge( $new_dirs, $custom_dirs );
			}

			return array_merge( $directories, $new_dirs );

		}


		private function get_custom_dirs() {

			$dir_list = array();

			$custom = apply_filters( Dashboard_Directory_Size_Common::$plugin_name . '-setting-get', array(), Dashboard_Directory_Size_Common::$plugin_name . '-settings-general', 'custom-directories' );

			if ( ! empty( $custom ) ) {
				$custom_dir_list = explode( "\n", $custom );
				if ( ! empty( $custom_dir_list ) ) {

					foreach ( $custom_dir_list as $row ) {
						$custom_dir = $this->get_custom_dir( $row );
						if ( ! empty( $custom_dir ) ) {
							$dir_list[] = $custom_dir;
						}
					}

				}
			}

			return $dir_list;

		}


		private function get_custom_dir( $row ) {

			$parts = explode( '|', $row );
			if ( ! empty( $parts ) && count( $parts ) == 2) {
				$path = trim( $parts[1] );
				if ( stripos( $path, '~' ) === 0 ) {
					$path = ABSPATH . substr( $path, 2 );
				}

				return $this->create_directory_info( trim( $parts[0] ), $path );

			}

			return null;

		}


		private function create_directory_info( $name, $path ) {

			if ( ! empty( $path ) ) {
				$new_dir['path'] = $path;
				$new_dir['name'] = $name;
				$new_dir['size'] = $this->filter_get_directory_size( -1, $path );
				return $new_dir;
			} else {
				return null;
			}

		}


		private function get_path_for_common_dir( $common_dir ) {

			switch ( $common_dir ) {
				case 'uploads':
					$upload_dir = wp_upload_dir();
					if ( ! empty( $upload_dir ) ) {
						return $upload_dir['basedir'];
					}

				case 'themes':
					return get_theme_root( );

				case 'plugins':
					return WP_PLUGIN_DIR;

				case 'mu-plugins':
					return WPMU_PLUGIN_DIR;

				default:
					return '';
			}

		}


		public function filter_get_directory_size( $size, $path ) {

			require_once ABSPATH . 'wp-includes/ms-functions.php';

			if ( ! is_dir( $path ) ) {
				$size = -1;
			} else {
				$size = recurse_dirsize( $path );
			}

			return $size;

		}

	} // end class

}