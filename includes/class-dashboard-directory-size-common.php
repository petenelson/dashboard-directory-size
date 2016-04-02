<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'Dashboard_Directory_Size_Common' ) ) {

	class Dashboard_Directory_Size_Common {

		const VERSION         = '2016-03-30-01';
		const PLUGIN_NAME     = 'dashboard-directory-size';
		const TEXT_DOMAIN     = 'dashboard-directory-size';


		public function plugins_loaded() {

			add_filter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get', array( $this, 'filter_get_directory_size' ), 10, 2 );
			add_filter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get-directories', array( $this, 'filter_get_directories' ), 10, 1 );

			// hook to allow purging of the transient
			add_action( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-flush-sizes-transient', array( $this, 'flush_sizes_transient' ) );

			$this->add_transient_flushers();

		}


		public function add_transient_flushers() {

			// hooks and filters to allow us to purge the transient
			foreach ( array( 'add_attachment', 'edit_attachment', 'upgrader_process_complete', 'deleted_plugin' ) as $action ) {
				add_action( $action, array( $this, 'flush_sizes_transient' ) );
			}

			foreach( array( 'wp_update_attachment_metadata', 'wp_handle_upload' ) as $filter ) {
				add_filter( $filter, array( $this, 'flush_sizes_transient' ) );
			}

			// this passes the specific option or transient affected
			foreach ( array( 'update_option', 'deleted_site_transient' ) as $action ) {
				add_action( $action, array( $this, 'flush_sizes_on_item_match' ) );
			}

		}


		public function filter_get_directories( $directories ) {

			// time in minutes
			$transient_time = intval( apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get', 60, Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'transient-time-minutes' ) );

			if ( $transient_time > 0 ) {
				$transient = get_transient( $this->sizes_transient_name() );
				if ( ! empty( $transient ) ) {
					return $transient;
				}
			}

			$new_dirs = array();

			// add common directories
			$common_dirs = $this->get_common_dirs();
			if ( ! empty( $common_dirs) ) {
				$new_dirs = array_merge( $new_dirs, $common_dirs );
			}

			// add custom directories
			$custom_dirs = $this->get_custom_dirs();
			if ( ! empty( $custom_dirs) ) {
				$new_dirs = array_merge( $new_dirs, $custom_dirs );
			}

			// add database size
			if ( apply_filters( 'dashboard-directory-size-setting-is-enabled', false, 'dashboard-directory-size-settings-general', 'show-database-size' ) ) {
				$new_dirs = array_merge( $new_dirs, $this->get_database_size() );
			}

			// merge all the directories
			$results = array_merge( $directories, $new_dirs );

			$results = $this->apply_friendly_sizes( $results );

			// allow filtering of the results
			$results = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-sizes-generated', $results );

			// set transient
			if( $transient_time > 0 && ! empty( $results ) ) {
				set_transient( $this->sizes_transient_name(), $results, $transient_time * MINUTE_IN_SECONDS );
			}

			return $results;

		}


		private function get_common_dirs() {

			$dir_list = array();

			$common = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get', array(), Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'common-directories' );

			if ( ! empty( $common ) && is_array( $common ) ) {

				foreach ( $common as $common_dir ) {

					$path = $this->get_path_for_common_dir( $common_dir );
					$new_dir = $this->create_directory_info( $common_dir, $path );

					if ( ! empty( $new_dir ) ) {
						$dir_list[] = $new_dir;
					}
				}

			}

			return $dir_list;

		}


		private function get_custom_dirs() {

			$dir_list = array();

			$custom = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get', array(), Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'custom-directories' );

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


		private function get_database_size( ) {

			$database = array();
			$database['name'] = 'WP ' . __( 'Database' );
			$database['path'] = DB_NAME;

			global $wpdb;
			$database['size'] = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(data_length + index_length) FROM information_schema.TABLES where table_schema = '%s' GROUP BY table_schema;", DB_NAME ) );

			return array( $database );

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


		public function flush_sizes_on_item_match( $item ) {
			// hook for deleted plugins and deleted themes
			$flushable_items = array( 'active_plugins', 'uninstall_plugins', 'update_themes' );
			if ( in_array( $item, $flushable_items ) ) {
				$this->flush_sizes_transient();
			}
		}


		public function flush_sizes_transient( $data = null ) {

			delete_transient( $this->sizes_transient_name() );

			// catch-all for actions and filters, we're not modifying anything, so return whatever was passed to us
			return $data;
		}


		private function sizes_transient_name() {
			return Dashboard_Directory_Size_Common::PLUGIN_NAME . '-sizes';
		}


		private function apply_friendly_sizes( $results ) {
			if ( is_array( $results ) ) {
				for( $i = 0; $i < count( $results ); $i++ ) {
					if ( ! empty( $results[ $i ]['size'] ) ) {
						$results[ $i ]['size_friendly'] = size_format( $results[ $i ]['size'] );
					} else {
						$results[ $i ]['size_friendly'] = __( 'Empty', 'dashboard-directory-size' );
					}
				}
			}
			return $results;
		}


	} // end class

}