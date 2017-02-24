<?php

/**
 * Manage Dashboard Directory Size plugin
 */
class Dashboard_Directory_Size_Command extends Dashboard_Directory_Size_Base_Command  {

	/**
	 * List directory sizes
	 *
	 * ## OPTIONS
	 *
	 * --format
	 * table, csv, json
	 * 
	 * ## EXAMPLES
	 *
	 *     wp dashboard-directory-size list
	 *
	 * @subcommand list
	 *
	 * @synopsis
	 */
	function list_sizes( $positional_args, $assoc_args = array() ) {

		$format = ! empty( $assoc_args['format'] ) ? $assoc_args['format'] : 'table';

		$columns     = array( 
			__( 'Name', 'dashboard-directory-size' ),
			__( 'Path', 'dashboard-directory-size' ),
			__( 'Size', 'dashboard-directory-size' ),
			__( 'Bytes', 'dashboard-directory-size' ),
			);
		$rows        = array();

		$directories = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get-directories', array() );

		foreach ( $directories as $directory ) {
			$row = array();

			$row[ __( 'Name', 'dashboard-directory-size' ) ] = $directory['name'];
			$row[ __( 'Path', 'dashboard-directory-size' ) ] = $directory['path'];

			$bytes = '';

			if ( -2 === $directory['size'] ) {
				$directory['size'] = Dashboard_Directory_Size_Common::get_directory_size( $directory['path'] );
				$directory['size_friendly'] = size_format( $directory['size'], Dashboard_Directory_Size_Common::get_decimal_places() );
			}

			switch ( intval( $directory['size'] ) ) {
				case -1:
					$size = __( 'Error', 'dashboard-directory-size' );
					break;
				case 0;
					$size = __( 'Empty', 'dashboard-directory-size' );
					break;
				default:
					$size = __( $directory['size_friendly'] );
					$bytes = __( $directory['size'] );
				break;
			}

			$row[ __( 'Size', 'dashboard-directory-size' ) ] = $size;
			$row[ __( 'Bytes', 'dashboard-directory-size' ) ] = $bytes;

			$rows[] = $row;
		}

		// add total sum
		if ( apply_filters( 'dashboard-directory-size-setting-is-enabled', false, 'dashboard-directory-size-settings-general', 'show-sum' ) ) {
	
			// Create the "Sum" directory.
			$row = array();

			$row[ __( 'Name', 'dashboard-directory-size' ) ] = __( 'Total Size', 'dashboard-directory-size' );
			$row[ __( 'Path', 'dashboard-directory-size' ) ] = '';

			// Sum up the sizes.
			$bytes = array_reduce( $rows, function( $carry, $r ) {
				$carry += $r[ __( 'Bytes', 'dashboard-directory-size' ) ];
				return $carry;
			}, 0 );

			$row[ __( 'Bytes', 'dashboard-directory-size' ) ] = $bytes;
			$row[ __( 'Size', 'dashboard-directory-size' ) ] = size_format( $bytes, Dashboard_Directory_Size_Common::get_decimal_places() );

			// Add the "Sum" directory.
			$rows[] = $row;
		}

		$args = array( 'format' => $format );

		$formatter = new \WP_CLI\Formatter(
			$args,
			$columns
		);

		$formatter->display_items( $rows );

	}

	/**
	 * Refresh the directory sizes transient
	 *
	 * ## OPTIONS
	 *
	 * ## EXAMPLES
	 *
	 *     wp dashboard-directory-size refresh
	 *
	 * @subcommand refresh
	 *
	 * @synopsis
	 */
	function refresh( $positional_args, $assoc_args = array() ) {

		WP_CLI::Line( __( 'Refreshing directory sizes...' ), 'dashboard-directory-size' );

		do_action( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-flush-sizes-transient' );

		$directories = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get-directories', array() );

		WP_CLI::Success( __( 'Done' ), 'dashboard-directory-size' );

	}

}

WP_CLI::add_command( 'dashboard-directory-size', 'Dashboard_Directory_Size_Command' );
