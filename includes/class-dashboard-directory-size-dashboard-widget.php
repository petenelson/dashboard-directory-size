<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'Dashboard_Directory_Size_Dashboard_Widget' ) ) {

	class Dashboard_Directory_Size_Dashboard_Widget {


		public function plugins_loaded( ) {

			add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widgets' ) );
			add_action( 'admin_init', array( $this, 'check_refresh_size_list' ) );
			add_action( 'admin_init', array( $this, 'register_scripts') );
		}


		public function register_dashboard_widgets() {

			// filterable
			$can_show_widget =  $this->can_show_widget();

			if ( $can_show_widget ) {
				wp_add_dashboard_widget( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-dashboard-widget',
					__('Dashboard Directory Size', 'dashboard-directory-size' ),
					array( $this, 'dashboard_widget' )
				);
			}
		}


		public function register_scripts() {

			wp_register_script( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-dashboard-widget', plugins_url( '/admin/js/dashboard-widget.js', dirname( __FILE__ ) ), array( 'jquery' ), Dashboard_Directory_Size_Common::VERSION, true );
			wp_register_style( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-dashboard-widget', plugins_url( '/admin/css/dashboard-widget.css', dirname( __FILE__ ) ), array( ), Dashboard_Directory_Size_Common::VERSION );

		}


		public function dashboard_widget() {

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-dashboard-widget' );
			wp_enqueue_style( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-dashboard-widget' );

			$refresh_url = wp_nonce_url( add_query_arg(
				array(
					Dashboard_Directory_Size_Common::PLUGIN_NAME . '-action' => 'refresh',
				), admin_url( '/') ), 'refresh' );

			?>
				<div class="inside">
					<?php $this->display_sizes_table(); ?>
					<p>
						<a href="<?php echo admin_url( 'options-general.php?page=' . Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings' ); ?>"><?php _e( 'Settings' ); ?></a> | 
						<a href="<?php echo $refresh_url; ?>"><?php _e( 'Refresh', 'dashboard-directory-size' ); ?></a>
					</p>
				</div>

			<?php
		}


		public function check_refresh_size_list() {
			$action = filter_input( INPUT_GET, Dashboard_Directory_Size_Common::PLUGIN_NAME . '-action', FILTER_SANITIZE_STRING );
			if ( $this->can_show_widget() && $action === 'refresh' && wp_verify_nonce( filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING ), 'refresh' ) ) {
				do_action( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-flush-sizes-transient' );
			}
		}


		private function can_show_widget() {
			return apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-can-show-widget', current_user_can( 'manage_options' ) );
		}


		private function display_sizes_table() {

			$directories = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get-directories', array() );
			$classes     = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-sizes-table-classes', 'wp-list-table widefat striped dashboard-directory-size-table' );

			?>
				<table class="<?php echo esc_attr( $classes ); ?>">
					<thead>
						<tr>
							<th><?php _e( 'Name', 'dashboard-directory-size' ); ?></th>
							<th><?php _e( 'Path', 'dashboard-directory-size' ); ?></th>
							<th><?php _e( 'Size', 'dashboard-directory-size' ); ?></th>
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
						<td class="cell-name"><?php echo esc_html( $directory['name'] ) ?></td>
						<td class="cell-path"><?php $this->output_trimmed_path( $directory['path'] ) ?></td>
						<td class="cell-size"><?php

							switch ( intval( $directory['size'] ) ) {
								case -1:
									esc_html_e( 'Error', 'dashboard-directory-size' );
									break;
								case 0;
									_e( 'Empty', 'dashboard-directory-size' );
									break;
								default:
									echo esc_html( $directory['size_friendly'] );
								break;
							}

						?></td>
					</tr>
				<?php
			}
		}


		private function output_trimmed_path( $path ) {

			$trim_results = Dashboard_Directory_Size_Common::trim_path( $path );

			$full_path = $trim_results['full_path'];
			$path      = $trim_results['path'];
			$trimmed   = $trim_results['trimmed'];

			?>
				<span class="trimmed-path">
					<?php if ( $trimmed ) { ?><a title="<?php echo esc_attr( $full_path ); ?>" class="trimmed-path-expand" href="#<?php echo esc_attr( $full_path ); ?>"><?php } ?><?php echo esc_html( $path ); ?><?php if ( $trimmed ) { ?>...<?php } ?><?php if ( $trimmed ) { ?></a><?php } ?>
				</span>
				<span class="full-path hidden">
					<?php echo esc_html( $full_path ); ?>
				</span>
			<?php

		}


	} // end class

}