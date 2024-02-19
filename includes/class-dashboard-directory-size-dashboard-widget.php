<?php

use function \DashboardDirectorySize\Sanitizers\sanitized_get_field;

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'Dashboard_Directory_Size_Dashboard_Widget' ) ) {

	class Dashboard_Directory_Size_Dashboard_Widget {


		static public function plugins_loaded( ) {
			add_action( 'wp_dashboard_setup', 'Dashboard_Directory_Size_Dashboard_Widget::register_dashboard_widgets' );
			add_action( 'admin_init', 'Dashboard_Directory_Size_Dashboard_Widget::check_refresh_size_list' );
			add_action( 'admin_init', 'Dashboard_Directory_Size_Dashboard_Widget::register_scripts' );
		}


		static public function register_dashboard_widgets() {

			// filterable
			$can_show_widget =  self::can_show_widget();

			if ( $can_show_widget ) {
				wp_add_dashboard_widget( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-dashboard-widget',
					__('Dashboard Directory Size', 'dashboard-directory-size' ),
					'Dashboard_Directory_Size_Dashboard_Widget::dashboard_widget'
				);
			}
		}


		static public function register_scripts() {

			wp_register_script( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-dashboard-widget', plugins_url( '/admin/js/dashboard-widget.js', dirname( __FILE__ ) ), array( 'jquery' ), Dashboard_Directory_Size_Common::VERSION, true );
			wp_register_style( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-dashboard-widget', plugins_url( '/admin/css/dashboard-widget.css', dirname( __FILE__ ) ), array( ), Dashboard_Directory_Size_Common::VERSION );

		}


		static public function dashboard_widget() {

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-dashboard-widget' );
			wp_enqueue_style( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-dashboard-widget' );

			$size_endpont =  rest_url( 'dashboard-directory-size/v1/size' );
			$size_format_endpont =  rest_url( 'dashboard-directory-size/v1/size-format' );
			if ( is_ssl() ) {
				$size_endpont = set_url_scheme( $size_endpont, 'https' );
				$size_format_endpont = set_url_scheme( $size_format_endpont, 'https' );
			}

			$settings = array(
				'nonce'           => wp_create_nonce( 'wp_rest' ),
				'endpoints'       => array(
					'size'           => $size_endpont,
					'size_format'    => $size_format_endpont,
					),
				);

			wp_localize_script( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-dashboard-widget', 'Dashboard_Directory_Size_Settings', $settings );

			?>
				<div class="inside">
					<?php self::display_sizes_table(); ?>
					<p>
						<a href="<?php echo esc_url( admin_url( 'options-general.php?page=' . Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings' ) ); ?>"><?php esc_html_e( 'Settings' ); ?></a> | 
						<a class="refresh" href="#refresh"><?php esc_html_e( 'Refresh', 'dashboard-directory-size' ); ?></a>
					</p>
				</div>

			<?php
		}


		static public function check_refresh_size_list() {
			$action = sanitized_get_field( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-action' );
			if ( self::can_show_widget() && $action === 'refresh' && wp_verify_nonce( sanitized_get_field( '_wpnonce' ), 'refresh' ) ) {
				do_action( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-flush-sizes-transient' );
			}
		}


		static public function can_show_widget() {
			return apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-can-show-widget', current_user_can( 'manage_options' ) );
		}


		static public function display_sizes_table() {

			$directories = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get-directories', array() );
			$classes     = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-sizes-table-classes', 'wp-list-table widefat striped dashboard-directory-size-table' );

			?>
				<table class="<?php echo esc_attr( $classes ); ?>">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Name', 'dashboard-directory-size' ); ?></th>
							<th><?php esc_html_e( 'Path', 'dashboard-directory-size' ); ?></th>
							<th><?php esc_html_e( 'Size', 'dashboard-directory-size' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php self::display_size_rows( $directories ); ?>
					</tbody>
				</table>

			<?php
		}


		static public function display_size_rows( $directories ) {
			foreach ( $directories as $directory ) {
				$size = intval( $directory['size'] );

				$cell_size_class = array( 'cell-size' );
				$data_size = '';

				if ( ! empty( $directory['sum'] ) ) {
					$cell_size_class[] = 'cell-sum';
				} else if ( ! empty( $directory['database'] ) ) {
					$cell_size_class[] = 'cell-database';
					$cell_size_class[] = 'cell-has-size';
					$data_size = ' data-size="' . esc_attr( $directory['size'] ) . '"';
				} else if ( -2 === $size ) {
					// a size of -2 means we need to load it via the REST API
					$cell_size_class[] = 'cell-size-data';
					$cell_size_class[] = 'cell-size-needed';
				} else if ( $size > -1 ) {
					$cell_size_class[] = 'cell-size-data';
					$cell_size_class[] = 'cell-has-size';
				}

				?>
					<tr>
						<td class="cell-name"><?php echo esc_html( $directory['name'] ) ?></td>
						<td class="cell-path"><?php self::output_trimmed_path( $directory['path'] ) ?></td>
						<td class="<?php echo esc_attr( implode( ' ', $cell_size_class ) ); ?>" data-path="<?php echo esc_attr( $directory['path'] ); ?>">

							<span class="spinner <?php echo ( -2 === $size ? 'is-active' : '' ); ?> hidden"></span>
							<span class="size" <?php echo $data_size; ?>>
							<?php

								switch ( $size ) {
									case -1:
										esc_html_e( 'Error', 'dashboard-directory-size' );
										break;
									case 0;
										esc_html_e( 'Empty', 'dashboard-directory-size' );
										break;
									default:
										echo esc_html( $directory['size_friendly'] );
										break;
								}

							?></span>
						</td>
					</tr>
				<?php
			}
		}


		static public function output_trimmed_path( $path ) {

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