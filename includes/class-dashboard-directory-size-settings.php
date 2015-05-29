<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'Dashboard_Directory_Size_Settings' ) ) {

	class Dashboard_Directory_Size_Settings {

		private $settings_page         = 'dashboard-directory-size-settings';
		private $settings_key_general  = 'dashboard-directory-size-settings-general';
		private $settings_key_help     = 'dashboard-directory-size-settings-help';
		private $plugin_settings_tabs  = array();


		public function plugins_loaded() {
			// admin menus
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_notices', array( $this, 'activation_admin_notice' ) );

			// filters to get plugin settings
			add_filter( Dashboard_Directory_Size_Common::$plugin_name . '-setting-is-enabled', array( $this, 'setting_is_enabled' ), 10, 3 );
			add_filter( Dashboard_Directory_Size_Common::$plugin_name . '-setting-get', array( $this, 'setting_get' ), 10, 3 );

		}


		public function activation_hook() {

			// create default settings
			add_option( $this->settings_key_general, array(
					'transient-time-minutes'   => 60,
					'common-directories'       => array( 'uploads', 'themes', 'plugins' ),
					'show-database-size'       => '1',
				), '', $autoload = 'no' );

			// add an option so we can show the activated admin notice
			add_option( Dashboard_Directory_Size_Common::$plugin_name . '-plugin-activated', '1' );

		}


		public function activation_admin_notice() {
			if ( '1' === get_option( Dashboard_Directory_Size_Common::$plugin_name . '-plugin-activated' ) ) { ?>
					<div class="updated">
						<p><?php
				echo sprintf( __( '<strong>Dashboard Directory Size activated!</strong> Please <a href="%s">visit the Settings page</a> to customize the settings.', 'dashboard-directory-size' ), admin_url( 'options-general.php?page=dashboard-directory-size-settings' ) );
				?></p>
					</div>
				<?php
				delete_option( Dashboard_Directory_Size_Common::$plugin_name . '-plugin-activated' );
			}
		}


		public function deactivation_hook() {
			// placeholder in case we need deactivation code
		}


		public function admin_init() {
			$this->register_general_settings();
			$this->register_help_tab();
		}


		private function register_general_settings() {
			$key = $this->settings_key_general;
			$this->plugin_settings_tabs[$key] = __( 'General', 'dashboard-directory-size' );

			register_setting( $key, $key, array( $this, 'sanitize_general_settings') );

			$section = 'general';

			add_settings_section( $section, '', array( $this, 'section_header' ), $key );

			foreach ( array( 'uploads', 'themes', 'plugins', 'mu-plugins' ) as $dir ) {
				$common_directories[ $dir ] = $dir;
			}

			add_settings_field( 'common-directories', __( 'Common Directories', 'dashboard-directory-size' ), array( $this, 'settings_checkbox_list' ), $key, $section,
				array( 'key' => $key, 'name' => 'common-directories', 'items' => $common_directories, 'legend' => __( 'Post Types', 'dashboard-directory-size' ) ) );

			add_settings_field( 'custom-directories', __( 'Custom Directories', 'dashboard-directory-size' ), array( $this, 'settings_textarea' ), $key, $section,
				array( 'key' => $key, 'name' => 'custom-directories', 'rows' => 8, 'cols' => 60, 'after' => __( 'A list of names and paths separated by pipe, use ~ for the WordPress install directory, example:<br/><br/>nginx Cache | /var/run/nginx-cache<br/>All WP Content | ~/wp-content/', 'dashboard-directory-size' ) ) );

			add_settings_field( 'show-database-size', __( 'Show Database Size', 'dashboard-directory-size' ), array( $this, 'settings_yes_no' ), $key, $section,
				array( 'key' => $key, 'name' => 'show-database-size' ) );

			add_settings_field( 'transient-time-minutes', __( 'Cache Size List (minutes)', 'dashboard-directory-size' ), array( $this, 'settings_input' ), $key, $section,
				array( 'key' => $key, 'name' => 'transient-time-minutes', 'type' => 'number', 'min' => 0, 'max' => 1440,  'step' => 1, 'after' => __( 'Caches the directory sizes as a transient to reduce server load, 0 to disable' ) ) );

		}


		public function sanitize_general_settings( $settings ) {

			$settings['transient-time-minutes'] = intval( $settings['transient-time-minutes'] );
			$settings['custom-directories'] = filter_var( $settings['custom-directories'], FILTER_SANITIZE_STRING );
			return $settings;
		}


		private function register_help_tab() {
			$key = $this->settings_key_help;
			$this->plugin_settings_tabs[$key] =  __( 'Help' );
			register_setting( $key, $key );
			$section = 'help';
			add_settings_section( $section, '', array( $this, 'section_header' ), $key );
		}


		public function setting_is_enabled( $enabled, $key, $setting ) {
			return '1' === $this->setting_get( '0', $key, $setting );
		}


		public function setting_get( $value, $key, $setting ) {

			$args = wp_parse_args( get_option( $key ),
				array(
					$setting => $value,
				)
			);

			return $args[$setting];
		}


		public function settings_input( $args ) {

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

			$this->output_after( $after );

		}


		public function settings_checkbox_list( $args ) {
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

			<?php
			foreach ( $items as $post_type => $post_type_dispay ) {
				?>
					<label>
						<input type="checkbox" name="<?php echo $key ?>[<?php echo $name ?>][]" value="<?php echo $post_type ?>"<?php echo in_array( $post_type, $values) ? ' checked="checked"' : ''  ?> />
						<?php echo esc_html( $post_type_dispay ); ?>
					</label>
					<br/>
				<?php
			}
			?>
				</fieldset>
			<?php

		}


		public function settings_textarea( $args ) {

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

			$this->output_after( $after );

		}


		public function settings_yes_no( $args ) {

			extract( wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'after' => '',
				)
			) );

			$option = get_option( $key );
			$value = isset( $option[$name] ) ? esc_attr( $option[$name] ) : '';

			if ( empty( $value ) ) {
				$value = '0';
			}

			echo '<div>';
			echo "<label><input id='{$name}_1' name='{$key}[{$name}]'  type='radio' value='1' " . ( '1' === $value ? " checked=\"checked\"" : "" ) . "/>" . __( 'Yes', 'dashboard-directory-size' ) . "</label> ";
			echo "<label><input id='{$name}_0' name='{$key}[{$name}]'  type='radio' value='0' " . ( '0' === $value ? " checked=\"checked\"" : "" ) . "/>" . __( 'No', 'dashboard-directory-size' ) . "</label> ";
			echo '</div>';

			$this->output_after( $after );

		}


		private function output_after( $after ) {
			if ( !empty( $after ) ) {
				echo '<div>' . $after . '</div>';
			}
		}


		public function admin_menu() {
			add_options_page( __( 'Dashboard Directory Size Settings', 'dashboard-directory-size' ), __( 'Dashboard Directory Size', 'dashboard-directory-size' ), 'manage_options', $this->settings_page, array( $this, 'options_page' ), 30 );
		}


		public function options_page() {

			$tab = $this->current_tab(); ?>
			<div class="wrap">
				<?php $this->plugin_options_tabs(); ?>
				<form method="post" action="options.php" class="options-form">
					<?php settings_fields( $tab ); ?>
					<?php do_settings_sections( $tab ); ?>
					<?php
						if ( $this->settings_key_help !== $tab ) {
							submit_button( __( 'Save Settings', 'dashboard-directory-size' ), 'primary', 'submit', true );
						}
					?>
				</form>
			</div>
			<?php

			$settings_updated = filter_input( INPUT_GET, 'settings-updated', FILTER_SANITIZE_STRING );
			if ( ! empty( $settings_updated ) ) {
				do_action( Dashboard_Directory_Size_Common::$plugin_name . '-flush-sizes-transient' );
			}

		}


		private function current_tab() {
			$current_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
			return empty( $current_tab ) ? $this->settings_key_general : $current_tab;
		}


		private function plugin_options_tabs() {
			$current_tab = $this->current_tab();
			echo '<h2>' . __( 'Dashboard Directory Size Settings', 'dashboard-directory-size' ) . '</h2><h2 class="nav-tab-wrapper">';
			foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
				$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
				echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->settings_page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
			}
			echo '</h2>';
		}


		public function section_header( $args ) {

			switch ( $args['id'] ) {
				case 'help';
					include_once trailingslashit( WP_PLUGIN_DIR ) . 'dashboard-directory-size/admin/partials/admin-help.php';
					break;
			}

			if ( ! empty( $output ) ) {
				echo '<p class="settings-section-header">' . $output . '</p>';
			}

		}


	} // end class

}