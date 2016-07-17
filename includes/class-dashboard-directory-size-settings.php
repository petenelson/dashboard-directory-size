<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'Dashboard_Directory_Size_Settings' ) ) {

	class Dashboard_Directory_Size_Settings {

		static public $settings_page         = 'dashboard-directory-size-settings';
		static public $settings_key_general  = 'dashboard-directory-size-settings-general';
		static public $settings_key_help     = 'dashboard-directory-size-settings-help';
		static public $plugin_settings_tabs  = array();


		static public function plugins_loaded() {
			// admin menus
			add_action( 'admin_init', 'Dashboard_Directory_Size_Settings::admin_init' );
			add_action( 'admin_menu', 'Dashboard_Directory_Size_Settings::admin_menu' );
			add_action( 'admin_notices', 'Dashboard_Directory_Size_Settings::activation_admin_notice' );

			// filters to get plugin settings
			add_filter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-is-enabled', 'Dashboard_Directory_Size_Settings::setting_is_enabled', 10, 3 );
			add_filter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get', 'Dashboard_Directory_Size_Settings::setting_get', 10, 3 );

		}


		static public function get_default_settings() {
			return array(
				'transient-time-minutes'   => 60,
				'common-directories'       => array( 'uploads', 'themes', 'plugins' ),
				'show-database-size'       => '1',
				'custom-directories'       => '',
			);
		}


		static public function activation_hook() {

			// create default settings
			add_option( self::$settings_key_general, self::get_default_settings(), '', 'no' );

			// add an option so we can show the activated admin notice
			add_option( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-plugin-activated', '1' );

		}


		static public function activation_admin_notice() {
			if ( '1' === get_option( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-plugin-activated' ) ) {
				?>
					<div class="updated">
						<p>
							<?php echo wp_kses_post( sprintf( __( '<strong>Dashboard Directory Size activated!</strong> Please <a href="%s">visit the Settings page</a> to customize the settings.', 'dashboard-directory-size' ), esc_url( admin_url( 'options-general.php?page=dashboard-directory-size-settings' ) ) ) ); ?>
						</p>
					</div>
				<?php
				delete_option( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-plugin-activated' );
			}
		}


		static public function deactivation_hook() {
			// placeholder in case we need deactivation code
		}


		static public function admin_init() {
			self::register_general_settings();
			self::register_help_tab();
		}


		static public function register_general_settings() {
			$key = self::$settings_key_general;
			self::$plugin_settings_tabs[$key] = __( 'General' );

			register_setting( $key, $key, 'Dashboard_Directory_Size_Settings::sanitize_general_settings' );

			$section = 'general';

			add_settings_section( $section, '', 'Dashboard_Directory_Size_Settings::section_header', $key );

			foreach ( array( 'uploads', 'themes', 'plugins', 'mu-plugins' ) as $dir ) {
				$common_directories[ $dir ] = $dir;
			}

			add_settings_field( 'common-directories', __( 'Common Directories', 'dashboard-directory-size' ), 'Dashboard_Directory_Size_Settings::settings_checkbox_list', $key, $section,
				array( 'key' => $key, 'name' => 'common-directories', 'items' => $common_directories, 'legend' => __( 'Post Types', 'dashboard-directory-size' ) ) );

			add_settings_field( 'custom-directories', __( 'Custom Directories', 'dashboard-directory-size' ), 'Dashboard_Directory_Size_Settings::settings_textarea', $key, $section,
				array( 'key' => $key, 'name' => 'custom-directories', 'rows' => 8, 'cols' => 60, 'after' => __( 'A list of names and paths separated by pipe, use ~ for the WordPress install directory, example:<br><br>nginx Cache | /var/run/nginx-cache<br>All WP Content | ~/wp-content/', 'dashboard-directory-size' ) ) );

			add_settings_field( 'show-database-size', __( 'Show Database Size', 'dashboard-directory-size' ), 'Dashboard_Directory_Size_Settings::settings_yes_no', $key, $section,
				array( 'key' => $key, 'name' => 'show-database-size' ) );

			add_settings_field( 'transient-time-minutes', __( 'Cache Size List (minutes)', 'dashboard-directory-size' ), 'Dashboard_Directory_Size_Settings::settings_input', $key, $section,
				array( 'key' => $key, 'name' => 'transient-time-minutes', 'type' => 'number', 'min' => 0, 'max' => 1440,  'step' => 1, 'after' => __( 'Caches the directory sizes as a transient to reduce server load, 0 to disable', 'dashboard-directory-size' ) ) );

			add_settings_field( 'rest-api-support', __( 'REST API Support', 'dashboard-directory-size' ), 'Dashboard_Directory_Size_Settings::settings_yes_no', $key, $section,
				array( 'key' => $key, 'name' => 'rest-api-support', 'after' => __( 'Exposes data via the dashboard-directory-size endpoint in the WP REST API', 'dashboard-directory-size' ) ) );
		}


		static public function sanitize_general_settings( $settings ) {

			$settings['transient-time-minutes'] = intval( $settings['transient-time-minutes'] );
			$settings['custom-directories'] = filter_var( $settings['custom-directories'], FILTER_SANITIZE_STRING );
			return $settings;
		}


		static private function register_help_tab() {
			$key = self::$settings_key_help;
			self::$plugin_settings_tabs[$key] =  __( 'Help' );
			register_setting( $key, $key );
			$section = 'help';
			add_settings_section( $section, '', 'Dashboard_Directory_Size_Settings::section_header', $key );
		}


		static public function setting_is_enabled( $enabled, $key, $setting ) {
			return '1' === self::setting_get( '0', $key, $setting );
		}


		static public function setting_get( $value, $key, $setting ) {

			$args = wp_parse_args( get_option( $key ),
				array(
					$setting => $value,
				)
			);

			return $args[$setting];
		}


		static public function settings_input( $args ) {

			// TODO don't use extract
			extract( wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'maxlength' => 50,
					'size' => 30,
					'after' => '',
					'type' => 'text',
					'min' => 0,
					'max' => 0,
					'step' => 1,
				)
			) );


			$option = get_option( $key );
			$value = isset( $option[$name] ) ? esc_attr( $option[$name] ) : '';

			$min_max_step = '';
			if ( $type === 'number' ) {
				$min = intval( $args['min'] );
				$max = intval( $args['max'] );
				$step = intval( $args['step'] );
				$min_max_step = " step='{$step}' min='{$min}' max='{$max}' ";
			}

			echo "<div><input id='{$name}' name='{$key}[{$name}]'  type='{$type}' value='" . $value . "' size='{$size}' maxlength='{$maxlength}' {$min_max_step} /></div>";

			self::output_after( $after );

		}


		static public function settings_checkbox_list( $args ) {
			extract( wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'items' => array(),
					'after' => '',
					'legend' => '',
				)
			) );

			$option = get_option( $key );
			$values = isset( $option[$name] ) ? $option[$name] : '';
			if ( ! is_array( $values ) ) {
				$values = array();
			}

			?>
				<fieldset>
					<legend class="screen-reader-text">
						<?php echo esc_html( $legend ) ?>
					</legend>

					<?php foreach ( $items as $value => $value_dispay ) : ?>
						<label>
							<input type="checkbox" name="<?php echo $key ?>[<?php echo $name ?>][]" value="<?php echo $value ?>" <?php checked( in_array( $value, $values) ); ?> />
							<?php echo esc_html( $value_dispay ); ?>
						</label>
						<br/>
					<?php endforeach; ?>
				</fieldset>
			<?php

		}


		static public function settings_textarea( $args ) {

			// TODO don't use extract
			extract( wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'rows' => 10,
					'cols' => 40,
					'after' => '',
				)
			) );


			$option = get_option( $key );
			$value = isset( $option[$name] ) ? esc_attr( $option[$name] ) : '';

			echo "<div><textarea id='{$name}' name='{$key}[{$name}]' rows='{$rows}' cols='{$cols}'>" . $value . "</textarea></div>";

			self::output_after( $after );

		}


		static public function settings_yes_no( $args ) {

			$args = wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'after' => '',
					)
				);

			$name    = $args['name'];
			$key     = $args['key'];
			$after   = $args['after'];

			$option  = get_option( $key );
			$value   = isset( $option[ $name ] ) ? esc_attr( $option[ $name ] ) : '';

			if ( empty( $value ) ) {
				$value = '0';
			}

			echo '<div>';
			echo "<label><input id='{$name}_1' name='{$key}[{$name}]'  type='radio' value='1' " . ( '1' === $value ? " checked=\"checked\"" : "" ) . "/>" . esc_html__( 'Yes' ) . "</label> ";
			echo "<label><input id='{$name}_0' name='{$key}[{$name}]'  type='radio' value='0' " . ( '0' === $value ? " checked=\"checked\"" : "" ) . "/>" . esc_html__( 'No' ) . "</label> ";
			echo '</div>';

			self::output_after( $after );

		}


		static public function output_after( $after ) {
			if ( ! empty( $after ) ) {
				echo '<div>' . wp_kses_post( $after ) . '</div>';
			}
		}


		static public function admin_menu() {
			add_options_page( 'Dashboard Directory Size ' . __( 'Settings' ), __( 'Dashboard Directory Size', 'dashboard-directory-size' ), 'manage_options', self::$settings_page, 'Dashboard_Directory_Size_Settings::options_page', 30 );
		}


		static public function options_page() {

			$tab = self::current_tab(); ?>
			<div class="wrap">
				<?php self::plugin_options_tabs(); ?>
				<form method="post" action="options.php" class="options-form">
					<?php settings_fields( $tab ); ?>
					<?php do_settings_sections( $tab ); ?>
					<?php
						if ( self::$settings_key_help !== $tab ) {
							submit_button( __( 'Save Changes' ), 'primary', 'submit', true );
						}
					?>
				</form>
			</div>
			<?php

			$settings_updated = filter_input( INPUT_GET, 'settings-updated', FILTER_SANITIZE_STRING );
			if ( ! empty( $settings_updated ) ) {
				do_action( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-flush-sizes-transient' );
			}

		}


		static public function current_tab() {
			$current_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
			return empty( $current_tab ) ? self::$settings_key_general : $current_tab;
		}


		static public function plugin_options_tabs() {
			$current_tab = self::current_tab();
			echo '<h2>' . __( 'Settings' ) . ' &rsaquo; Dashboard Directory Size</h2><h2 class="nav-tab-wrapper">';
			foreach ( self::$plugin_settings_tabs as $tab_key => $tab_caption ) {
				$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
				echo '<a class="nav-tab ' . $active . '" href="?page=' . urlencode( self::$settings_page ) . '&tab=' . urlencode( $tab_key ) . '">' . esc_html( $tab_caption ) . '</a>';
			}
			echo '</h2>';
		}


		static public function section_header( $args ) {

			switch ( $args['id'] ) {
				case 'help';
					include_once DASHBOARD_DIRECOTRY_SIZE_ROOT . 'admin/partials/admin-help.php';
					break;
			}

		}


	} // end class

}