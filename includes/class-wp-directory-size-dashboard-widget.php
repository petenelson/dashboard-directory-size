<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'WP_Directory_Size_Dashboard_Widget' ) ) {

	class WP_Directory_Size_Dashboard_Widget {

		static $plugin_name         = 'wp-directory-size';
		static $version             = '2015-04-27-01';

		var $plugin_dir_url         = '';


		public function plugins_loaded( ) {

			add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widgets' ) );
		}


		public function register_dashboard_widgets() {
			if ( current_user_can( 'manage_options' ) ) {
				wp_add_dashboard_widget( $this->plugin_name . '-dashboard-widget',
					__('WP Directory Size', 'wp-directory-size' ),
					array( $this, 'dashboard_widget' )
				);
			}
		}


		public function dashboard_widget() {

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( self::$plugin_name . '-dashboard-widget', $this->plugin_dir_url. '/admin/js/wp-directory-size-dashboard-widget.js', array( 'jquery' ), self::$version, true );
			wp_enqueue_style( self::$plugin_name . '-dashboard-widget', $this->plugin_dir_url. '/admin/css/wp-directory-size-dashboard-widget.css', array( ), self::$version );

			?>
				<div class="inside">

					<?php $this->display_sizes_table(); ?>

				</div>

			<?php
		}


		private function display_sizes_table() {

			// TODO get via filters
			$uploads_dir = wp_upload_dir()['basedir'];
			$size = apply_filters( 'wp-directory-size-get', 0, $uploads_dir );

			$directories = array(
				array( 'name' => 'nginx cache', 'path' => '/var/run/nginx-cache/', 'size' => apply_filters( 'wp-directory-size-get', 0, '/var/run/nginx-cache/'  ) ),
				array( 'name' => 'wp-uploads', 'path' => $uploads_dir, 'size' => $size ),
				array( 'name' => 'plugins', 'path' => WP_PLUGIN_DIR , 'size' => $size = apply_filters( 'wp-directory-size-get', 0, WP_PLUGIN_DIR  ) ),
			);

			?>
				<table class="wp-directory-size-table">
					<thead>
						<tr>
							<th><?php _e( 'Name', 'wp-directory-size' ); ?></th>
							<th><?php _e( 'Path', 'wp-directory-size' ); ?></th>
							<th><?php _e( 'Size', 'wp-directory-size' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php $this->display_size_rows( $directories ); ?>
					</tbody>
				</table>

			<?php
		}


		private function display_size_rows( $directories ) {
			foreach ( $directories as $directory ) {
				?>
					<tr>
						<td><?php echo esc_html( $directory['name'] ) ?></td>
						<td><?php $this->output_trimmed_path( $directory['path'] ) ?></td>
						<td><?php echo esc_html( size_format( $directory['size'] ) ); ?></td>
					</tr>
				<?php
			}
		}


		private function output_trimmed_path( $path ) {
			$trim_size = 30;
			$trimmed = false;
			$full_path = $path;
			if ( ! empty( $path ) && strlen( $path ) > $trim_size ) {
				$path = substr( $path, 0, $trim_size );
				$trimmed = true;
			}

			?>
				<span class="trimmed-path">
					<?php if ( $trimmed ) { ?><a class="trimmed-path-expand" href="#"><?php } ?><?php echo esc_html( $path ); ?><?php if ( $trimmed ) { ?>...<?php } ?><?php if ( $trimmed ) { ?></a><?php } ?>
				</span>
				<span class="full-path" style="display: none;">
					<?php echo esc_html( $full_path ); ?>
				</span>
			<?php

		}



	} // end class

}