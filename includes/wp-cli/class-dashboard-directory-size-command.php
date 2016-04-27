<?php

/**
 * Mamane Dashboard Directory Size plugin
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

		$args = array( 'format' => $format );

		$formatter = new \WP_CLI\Formatter(
			$args,
			$columns
		);

		$formatter->display_items( $rows );

	}
}

WP_CLI::add_command( 'dashboard-directory-size', 'Dashboard_Directory_Size_Command' );
