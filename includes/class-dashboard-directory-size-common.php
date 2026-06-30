<?php
/**
 * Common class
 *
 * @package Dashboard_Directory_Size
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'restricted access' );
}

if ( ! class_exists( 'Dashboard_Directory_Size_Common' ) ) {

	/**
	 * Common functionality for the Dashboard Directory Size plugin.
	 */
	class Dashboard_Directory_Size_Common {

		const VERSION     = '2026-06-30';
		const PLUGIN_NAME = 'dashboard-directory-size';

		/**
		 * Setup WordPress hooks and filters.
		 *
		 * @return void
		 */
		public static function plugins_loaded() {

			add_filter( self::PLUGIN_NAME . '-get', 'Dashboard_Directory_Size_Common::filter_get_directory_size', 10, 2 );
			add_filter( self::PLUGIN_NAME . '-get-directories', 'Dashboard_Directory_Size_Common::filter_get_directories', 10, 1 );

			// Hook to allow purging of the transient.
			add_action( self::PLUGIN_NAME . '-flush-sizes-transient', 'Dashboard_Directory_Size_Common::flush_sizes_transient' );

			self::add_transient_flushers();
		}

		/**
		 * Adds actions and filters to allow purging of the transients.
		 *
		 * @return void
		 */
		public static function add_transient_flushers() {

			// Hooks and filters to allow us to purge the transient.
			foreach ( array( 'add_attachment', 'edit_attachment', 'upgrader_process_complete', 'deleted_plugin' ) as $action ) {
				add_action( $action, 'Dashboard_Directory_Size_Common::flush_sizes_transient' );
			}

			foreach( array( 'wp_update_attachment_metadata', 'wp_handle_upload' ) as $filter ) {
				add_filter( $filter, 'Dashboard_Directory_Size_Common::flush_sizes_transient_filter' );
			}

			// this passes the specific option or transient affected
			foreach ( array( 'update_option', 'deleted_site_transient' ) as $action ) {
				add_action( $action, 'Dashboard_Directory_Size_Common::flush_sizes_on_item_match' );
			}
		}

		/**
		 * Filter to get directories.
		 *
		 * @param array $directories List of directories.
		 * @return array
		 */
		public static function filter_get_directories( $directories ) {

			$cli = defined( 'WP_CLI' ) && WP_CLI;

			$new_dirs = array();

			// add common directories
			$common_dirs = self::get_common_dirs();
			if ( ! empty( $common_dirs) ) {
				$new_dirs = array_merge( $new_dirs, $common_dirs );
			}

			// add custom directories
			$custom_dirs = self::get_custom_dirs();
			if ( ! empty( $custom_dirs) ) {
				$new_dirs = array_merge( $new_dirs, $custom_dirs );
			}

			// add database size
			if ( apply_filters( 'dashboard-directory-size-setting-is-enabled', false, 'dashboard-directory-size-settings-general', 'show-database-size' ) ) {
				$new_dirs = array_merge( $new_dirs, self::get_database_size() );
			}

			// merge all the directories
			$results = array_merge( $directories, $new_dirs );

			// add total sum
			if ( ! $cli && apply_filters( 'dashboard-directory-size-setting-is-enabled', false, 'dashboard-directory-size-settings-general', 'show-sum' ) ) {

				// Create the "Sum" directory.
				$sum_dir = self::create_directory_info( __( 'Total Size', 'dashboard-directory-size' ), '.' );
				$sum_dir['sum'] = true;
				$sum_dir['path'] = '';

				// Add the "Sum" directory.
				$results[] = $sum_dir;
			}

			$results = self::apply_friendly_sizes( $results );

			// allow filtering of the results
			$results = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-sizes-generated', $results );

			return $results;
		}

		/**
		 * Gets a list of common directories.
		 *
		 * @return array
		 */
		public static function get_common_dirs() {

			$dir_list = array();

			$common = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get', array(), Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'common-directories' );

			if ( ! empty( $common ) && is_array( $common ) ) {

				foreach ( $common as $common_dir ) {

					$path = self::get_path_for_common_dir( $common_dir );
					$new_dir = self::create_directory_info( $common_dir, $path );

					if ( ! empty( $new_dir ) ) {
						$dir_list[] = $new_dir;
					}
				}

			}

			return $dir_list;
		}

		/**
		 * Gets a list of custom directories.
		 *
		 * @return array
		 */
		public static function get_custom_dirs() {

			$dir_list = array();

			$custom = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get', array(), Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'custom-directories' );

			if ( ! empty( $custom ) ) {
				$custom_dir_list = explode( "\n", $custom );
				foreach ( $custom_dir_list as $row ) {
					$custom_dir = self::get_custom_dir( $row );
					if ( ! empty( $custom_dir ) ) {
						$dir_list[] = $custom_dir;
					}
				}
			}

			return $dir_list;
		}

		/**
		 * Converts a custom row entry from settings into a directory
		 * info array.
		 *
		 * @param  string $row Entry from settings ( name | path )
		 * @return array|null  Results from create_directory_info()
		 */
		public static function get_custom_dir( $row ) {

			$parts = explode( '|', $row );
			if ( count( $parts ) === 2 ) {
				$path = trim( $parts[1] );
				if ( stripos( $path, '~' ) === 0 ) {
					$path = ABSPATH . substr( $path, 2 );
				}

				return self::create_directory_info( trim( $parts[0] ), $path );

			}

			return null;
		}

		/**
		 * Gets the database size and info.
		 *
		 * @return array
		 */
		public static function get_database_size() {

			global $wpdb;

			$database = array();
			$database['name'] = 'WP ' . __( 'Database' );
			$database['path'] = DB_NAME;
			$database['size'] = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(data_length + index_length) FROM information_schema.TABLES where table_schema = '%s' GROUP BY table_schema;", DB_NAME ) );
			$database['database'] = true;

			return array( $database );
		}

		/**
		 * Creates a directory info array.
		 *
		 * @param string $name The name of the directory.
		 * @param string $path The path of the directory.
		 * @return array|null
		 */
		public static function create_directory_info( $name, $path ) {

			if ( ! empty( $path ) ) {
				$new_dir['path'] = $path;
				$new_dir['name'] = $name;
				$new_dir['size'] = -2;
				return $new_dir;
			} else {
				return null;
			}
		}

		/**
		 * Gets the path for a common directory.
		 *
		 * @param string $common_dir The common dir.
		 * @return string
		 */
		public static function get_path_for_common_dir( $common_dir ) {

			switch ( $common_dir ) {
				case 'uploads':
					$upload_dir = wp_upload_dir();
					return $upload_dir['basedir'];

				case 'themes':
					return get_theme_root();

				case 'plugins':
					return WP_PLUGIN_DIR;

				case 'mu-plugins':
					return WPMU_PLUGIN_DIR;

				default:
					return '';
			}
		}

		/**
		 * Filter hook to get the directory size.
		 *
		 * @param int    $size The size of the directory.
		 * @param string $path The path of the directory.
		 * @return int
		 */
		public static function filter_get_directory_size( $size, $path ) {
			$size = self::get_directory_size( $path );
			return $size;
		}

		/**
		 * Gets the size of a directory.
		 *
		 * @param string  $path    The path of the directory.
		 * @param boolean $refresh Whether to refresh the transient.
		 * @return int
		 */
		public static function get_directory_size( $path, $refresh = false ) {

			$transient_time = self::get_transient_time();

			if ( $refresh ) {
				self::flush_size_transient( $path );
			}

			if ( $transient_time > 0 ) {
				$size = get_transient( self::transient_path_key( $path ) );
				if ( false !== $size ) {
					return $size;
				}
			}

			if ( file_exists( ABSPATH . 'wp-includes/ms-functions.php' ) ) {
				require_once ABSPATH . 'wp-includes/ms-functions.php';
			}

			if ( ! is_dir( $path ) ) {
				$size = -1;
			} else {
				$size = recurse_dirsize( $path );
			}

			if ( $transient_time > 0 ) {
				set_transient( self::transient_path_key( $path ), $size, MINUTE_IN_SECONDS * $transient_time );
			}

			return $size;
		}

		/**
		 * Gets the time for the transient.
		 *
		 * @return int
		 */
		public static function get_transient_time() {
			return intval( apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get', 60, Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'transient-time-minutes' ) );
		}

		/**
		 * Gets the number of decimal places to use.
		 *
		 * @return int
		 */
		public static function get_decimal_places() {
			return intval( apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get', 0, Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'decimal-places' ) );
		}

		/**
		 * Flushes the sizes transient when a specific item is matched.
		 *
		 * @param string $item The item to match.
		 * @return void
		 */
		public static function flush_sizes_on_item_match( $item ) {
			// hook for deleted plugins and deleted themes
			$flushable_items = array( 'active_plugins', 'uninstall_plugins', 'update_themes' );
			if ( in_array( $item, $flushable_items ) ) {
				self::flush_sizes_transient();
			}
		}

		/**
		 * Filter hook to flush the sizes transient.
		 *
		 * @param mixed $data The data passed through the filter.
		 * @return mixed The data passed through the filter.
		 */
		public static function flush_sizes_transient_filter( $data ) {
			self::flush_sizes_transient();
			return $data;
		}

		/**
		 * Flushes the sizes transient.
		 *
		 * @return void
		 */
		public static function flush_sizes_transient() {

			$directories = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get-directories', array() );
			foreach( $directories as $directory ) {
				self::flush_size_transient( $directory['path'] );
			}
		}

		/**
		 * Flushes a specific size transient.
		 *
		 * @param string $path The path of the directory.
		 * @return void
		 */
		public static function flush_size_transient( $path ) {
			delete_transient( self::transient_path_key( $path ) );
		}

		/**
		 * The meta key for the transient.
		 *
		 * @param string $path The path of the directory.
		 * @return string
		 */
		public static function transient_path_key( $path ) {
			return 'DD-Path-Size-' . md5( $path );
		}

		/**
		 * Adds human-readable sizes to the results.
		 *
		 * @param array $results The results.
		 * @return array
		 */
		public static function apply_friendly_sizes( array $results ) {
			for ( $i = 0; $i < count( $results ); $i++ ) {
				if ( ! empty( $results[ $i ]['size'] ) ) {
					$results[ $i ]['size_friendly'] = size_format( $results[ $i ]['size'], self::get_decimal_places() );
				} else {
					$results[ $i ]['size_friendly'] = __( 'Empty', 'dashboard-directory-size' );
				}
			}

			return $results;
		}

		/**
		 * Trims a path to a predetermined length
		 *
		 * @param  string $path Full path.
		 * @return array        Trimmed path and boolean to indicate if
		 *                      it was trimmed.
		 */
		public static function trim_path( $path ) {

			$trim_size = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-trimmed-path-length', 25 );
			$trimmed = false;

			// if this is part of the install, remove the start to show relative path
			if ( stripos( $path , ABSPATH ) !== false ) {
				$path = substr( $path, strlen( ABSPATH ) );
			}

			$full_path = $path;

			// trim directory name
			if ( ! empty( $path ) && strlen( $path ) > $trim_size ) {
				$path = substr( $path, 0, $trim_size );
				$trimmed = true;
			}

			return compact( 'path', 'full_path', 'trimmed' );
		}
	}
}
