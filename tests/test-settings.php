<?php

use WP_Mock as M;

class Test_Dashboard_Directory_Size_Settings extends Test_Dashboard_Directory_Size_Base {

	public function setUp() {
		M::setUp();
	}

	public function tearDown() {
		M::tearDown();
	}

	public function test_plugins_loaded() {

		M::expectActionAdded( 'admin_init',      'Dashboard_Directory_Size_Settings::admin_init' );
		M::expectActionAdded( 'admin_menu',      'Dashboard_Directory_Size_Settings::admin_menu' );
		M::expectActionAdded( 'admin_notices',   'Dashboard_Directory_Size_Settings::activation_admin_notice' );

		M::expectFilterAdded( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-is-enabled', 'Dashboard_Directory_Size_Settings::setting_is_enabled', 10, 3 );
		M::expectFilterAdded( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get', 'Dashboard_Directory_Size_Settings::setting_get', 10, 3 );

		Dashboard_Directory_Size_Settings::plugins_loaded();

	}

	public function test_get_default_settings() {
		$settings = Dashboard_Directory_Size_Settings::get_default_settings();
		$this->assertEquals( $settings['transient-time-minutes'], 360 );
		$this->assertEquals( $settings['common-directories'], array( 'uploads', 'themes', 'plugins' ) );
		$this->assertEquals( $settings['show-database-size'], '1' );
		$this->assertEquals( $settings['custom-directories'], '' );
		$this->assertEquals( $settings['decimal-places'], 0 );
	}

	public function test_activation_hook() {

		M::wpFunction( 'add_option', array(
			'times' => 1,
			'args' => array(
				Dashboard_Directory_Size_Settings::$settings_key_general,
				Dashboard_Directory_Size_Settings::get_default_settings(),
				'',
				'no',
				)
			)
		);

		M::wpFunction( 'add_option', array(
			'times' => 1,
			'args' => array(
				Dashboard_Directory_Size_Common::PLUGIN_NAME . '-plugin-activated',
				'1',
				)
			)
		);

		Dashboard_Directory_Size_Settings::activation_hook();
	}

	public function test_activation_admin_notice_activated() {

		M::wpFunction( 'get_option', array(
			'times' => 1,
			'args' => Dashboard_Directory_Size_Common::PLUGIN_NAME . '-plugin-activated',
			'return' => '1',
			)
		);

		M::wpFunction( 'delete_option', array(
			'times' => 1,
			'args' => Dashboard_Directory_Size_Common::PLUGIN_NAME . '-plugin-activated',
			'return' => '1',
			)
		);

		M::wpPassthruFunction( 'wp_kses_post' );
		M::wpPassthruFunction( '__' );
		M::wpPassthruFunction( 'esc_url' );
		M::wpPassthruFunction( 'admin_url' );

		ob_start();
		Dashboard_Directory_Size_Settings::activation_admin_notice();
		$results = ob_get_clean();

		$this->assertContains( 'Dashboard Directory Size activated', $results );

	}

	public function test_activation_admin_notice_not_activated() {

		M::wpFunction( 'get_option', array(
			'times' => 1,
			'args' => Dashboard_Directory_Size_Common::PLUGIN_NAME . '-plugin-activated',
			'return' => false,
			)
		);

		ob_start();
		Dashboard_Directory_Size_Settings::activation_admin_notice();
		$results = ob_get_clean();

		$this->assertEmpty( $results );

	}

	public function mock_get_option( $key, $return = false, $times = 1 ) {
		M::wpFunction( 'get_option', array(
			'times'  => $times,
			'args'   => $key,
			'return' => $return,
			)
		);
	}

	public function mock_wp_parse_args( $args, $defaults, $return, $times = 1 ) {
		M::wpFunction( 'wp_parse_args', array(
			'times'  => $times,
			'args'   => array( $args, $defaults ),
			'return' => $return,
			)
		);
	}

	public function test_setting_get() {

		// Return an actual setting
		$this->mock_get_option( 'dashboard-directory-size-settings-general', array( 'transient-time-minutes' => 60 ) );

		$this->mock_wp_parse_args(
			array( 'transient-time-minutes' => 60 ),  // args
			array( 'transient-time-minutes' => 360 ), // default
			array( 'transient-time-minutes' => 60 )   // return
			);

		// 360 would be the default
		$value = Dashboard_Directory_Size_Settings::setting_get( 360, 'dashboard-directory-size-settings-general', 'transient-time-minutes' );

		// verify that the actual setting, not the default, was returned
		$this->assertEquals( 60, $value );


		// Return a missing setting
		$this->mock_get_option( 'dashboard-directory-size-settings-general', false );

		$this->mock_wp_parse_args(
			false,                                    // args
			array( 'transient-time-minutes' => 360 ), // default
			array( 'transient-time-minutes' => 360 )  // return
			);


		// 360 would be the default
		$value = Dashboard_Directory_Size_Settings::setting_get( 360, 'dashboard-directory-size-settings-general', 'transient-time-minutes' );

		// verify that the default, not the missing setting, was returned
		$this->assertEquals( 360, $value );


	}

	public function test_get_common_dirs() {
		$common_directories = Dashboard_Directory_Size_Settings::get_common_dirs();
		$this->assertContains( 'uploads',    $common_directories );
		$this->assertContains( 'themes',     $common_directories );
		$this->assertContains( 'plugins',    $common_directories );
		$this->assertContains( 'mu-plugins', $common_directories );
	}

	public function test_admin_init() {

		M::wpPassthruFunction( '__' );

		// General tab

		// Mock the register_setting call
		M::wpFunction( 'register_setting', array(
			'times' => 1,
			'args' => array(
				Dashboard_Directory_Size_Settings::$settings_key_general,
				Dashboard_Directory_Size_Settings::$settings_key_general,
				'Dashboard_Directory_Size_Settings::sanitize_general_settings',
				),
			)
		);

		// Mock the add_settings_section call
		M::wpFunction( 'add_settings_section', array(
			'times' => 1,
			'args' => array(
				'general',
				'',
				'Dashboard_Directory_Size_Settings::section_header',
				Dashboard_Directory_Size_Settings::$settings_key_general,
				),
			)
		);

		// Mock the common-directories checkbox list
		M::wpFunction( 'add_settings_field', array(
			'times' => 1,
			'args' => array(
				'common-directories',
				'Common Directories',
				'Dashboard_Directory_Size_Settings::settings_checkbox_list',
				Dashboard_Directory_Size_Settings::$settings_key_general,
				'general',
				array(
					'key' => Dashboard_Directory_Size_Settings::$settings_key_general,
					'name' => 'common-directories',
					'items' => Dashboard_Directory_Size_Settings::get_common_dirs(),
					'legend' => 'Post Types',
					),
				),
			)
		);

		// Mock the custom-directories checkbox list
		M::wpFunction( 'add_settings_field', array(
			'times' => 1,
			'args' => array(
				'custom-directories',
				'Custom Directories',
				'Dashboard_Directory_Size_Settings::settings_textarea',
				Dashboard_Directory_Size_Settings::$settings_key_general,
				'general',
				array(
					'key' => Dashboard_Directory_Size_Settings::$settings_key_general,
					'name' => 'custom-directories',
					'rows' => 8,
					'cols' => 60,
					'after' => 'A list of names and paths separated by pipe, use ~ for the WordPress install directory, example:<br><br>nginx Cache | /var/run/nginx-cache<br>All WP Content | ~/wp-content/',
					),
				),
			)
		);

		// Mock the show-database-size checkbox list
		M::wpFunction( 'add_settings_field', array(
			'times' => 1,
			'args' => array(
				'show-database-size',
				'Show Database Size',
				'Dashboard_Directory_Size_Settings::settings_yes_no',
				Dashboard_Directory_Size_Settings::$settings_key_general,
				'general',
				array(
					'key' => Dashboard_Directory_Size_Settings::$settings_key_general,
					'name' => 'show-database-size',
					),
				),
			)
		);

		// Mock the decimal-places numeric input field
		M::wpFunction( 'add_settings_field', array(
			'times' => 1,
			'args' => array(
				'decimal-places',
				'Decimal Places',
				'Dashboard_Directory_Size_Settings::settings_input',
				Dashboard_Directory_Size_Settings::$settings_key_general,
				'general',
				array(
					'key'   => Dashboard_Directory_Size_Settings::$settings_key_general,
					'name'  =>'decimal-places',
					'type'  => 'number',
					'min'   => 0,
					'max'   => 2,
					'step'  => 1,
					'after' => 'Number of decimal places used when displaying sizes.',
					'default' => 0,
					),
				),
			)
		);

		// Mock the transient-time-minutes numeric input field
		M::wpFunction( 'add_settings_field', array(
			'times' => 1,
			'args' => array(
				'transient-time-minutes',
				'Cache Size List (minutes)',
				'Dashboard_Directory_Size_Settings::settings_input',
				Dashboard_Directory_Size_Settings::$settings_key_general,
				'general',
				array(
					'key'   => Dashboard_Directory_Size_Settings::$settings_key_general,
					'name'  =>'transient-time-minutes',
					'type'  => 'number',
					'min'   => 0,
					'max'   => 1440,
					'step'  => 1,
					'after' => 'Caches the directory sizes as a transient to reduce server load, 0 to disable',
					),
				),
			)
		);

		// Mock the rest-api yes/no input field
		M::wpFunction( 'add_settings_field', array(
			'times' => 1,
			'args' => array(
				'rest-api-support',
				'REST API Support',
				'Dashboard_Directory_Size_Settings::settings_yes_no',
				Dashboard_Directory_Size_Settings::$settings_key_general,
				'general',
				array(
					'key'   => Dashboard_Directory_Size_Settings::$settings_key_general,
					'name'  => 'rest-api-support',
					'after' => 'Exposes data via the dashboard-directory-size endpoint in the WP REST API',
					),
				),
			)
		);


		// Help tab

		// Mock the register_setting call
		M::wpFunction( 'register_setting', array(
			'times' => 1,
			'args' => array(
				Dashboard_Directory_Size_Settings::$settings_key_help,
				Dashboard_Directory_Size_Settings::$settings_key_help,
				),
			)
		);

		// Mock the add_settings_section call
		M::wpFunction( 'add_settings_section', array(
			'times' => 1,
			'args' => array(
				'help',
				'',
				'Dashboard_Directory_Size_Settings::section_header',
				Dashboard_Directory_Size_Settings::$settings_key_help,
				),
			)
		);

		Dashboard_Directory_Size_Settings::admin_init();
	}

	public function test_sanitize_general_settings() {

		// Create some unsanitized settings
		$settings = array(
			'transient-time-minutes' => 'sixty',
			'custom-directories'     => 'path<script>',
			);
	
		// Sanitize them
		$settings = Dashboard_Directory_Size_Settings::sanitize_general_settings( $settings );

		$this->assertEquals( 0,      $settings['transient-time-minutes'] );
		$this->assertEquals( 'path', $settings['custom-directories'] );  // FILTER_SANITIZE_STRING

		// Create some valid settings
		$settings = array(
			'transient-time-minutes' => '60',
			'custom-directories'     => '/var/wordpress/wp-content|WP Content',
			);

		// Sanitize them
		$settings = Dashboard_Directory_Size_Settings::sanitize_general_settings( $settings );

		$this->assertEquals( 60,                                     $settings['transient-time-minutes'] );
		$this->assertEquals( '/var/wordpress/wp-content|WP Content', $settings['custom-directories'] );  // FILTER_SANITIZE_STRING

	}

	public function test_setting_is_enabled() {

		// Return an actual setting
		$this->mock_get_option( Dashboard_Directory_Size_Settings::$settings_key_general, array( 'show-database-size' => '1' ) );

		$this->mock_wp_parse_args(
			array( 'show-database-size' => '1'  ), // args
			array( 'show-database-size' => '0'  ), // default
			array( 'show-database-size' => '1'  )  // return
			);


		$enabled = Dashboard_Directory_Size_Settings::setting_is_enabled( null, Dashboard_Directory_Size_Settings::$settings_key_general, 'show-database-size' );

		$this->assertTrue( $enabled );

	}

	public function test_settings_input() {

		M::wpPassthruFunction( 'esc_attr' );
		M::wpPassthruFunction( 'wp_kses_post' );

		// Return an actual setting
		$this->mock_get_option( Dashboard_Directory_Size_Settings::$settings_key_general, array( 'text-option' => 'text-value' ) );
		$this->mock_get_option( Dashboard_Directory_Size_Settings::$settings_key_general, array( 'text-number-option' => '60' ) );

		$args = array(
			'name'      => 'text-option',
			'key'       => Dashboard_Directory_Size_Settings::$settings_key_general,
			'maxlength' => 20,
			'size'      => 20,
			'after'     => 'After text',
			'type'      => 'text',
			'min'       => 0,
			'max'       => 0,
			'step'      => 1,
			'default'   => 'test',
		);

		$defaults = array(
			'name'      => '',
			'key'       => '',
			'maxlength' => 50,
			'size'      => 30,
			'after'     => '',
			'type'      => 'text',
			'min'       => 0,
			'max'       => 0,
			'step'      => 1,
			'default'   => '',
		);

		$return = $args;

		$this->mock_wp_parse_args(
			$args, // args
			$defaults, // default
			$return  // return
			);


		ob_start();
		Dashboard_Directory_Size_Settings::settings_input( $args );
		$input = ob_get_clean();

		$this->assertContains( '<input id="text-option"', $input );
		$this->assertContains( 'name="dashboard-directory-size-settings-general[text-option]"', $input );
		$this->assertContains( 'type="text"', $input );
		$this->assertContains( 'value="text-value"', $input );
		$this->assertContains( 'size="20"', $input );
		$this->assertContains( 'maxlength="20"', $input );


		// update the output to a number field, check it again

		$args = array(
			'name'      => 'text-number-option',
			'key'       => Dashboard_Directory_Size_Settings::$settings_key_general,
			'maxlength' => 4,
			'size'      => 4,
			'after'     => 'After text',
			'type'      => 'number',
			'min'       => '1',
			'max'       => '100',
			'step'      => '2',
			'default'   => '60',
		);

		$return = $args;

		$this->mock_wp_parse_args(
			$args, // args
			$defaults, // default
			$return  // return
			);

		ob_start();
		Dashboard_Directory_Size_Settings::settings_input( $args );
		$input = ob_get_clean();

		$this->assertContains( 'type="number"', $input );
		$this->assertContains( 'value="60"', $input );
		$this->assertContains( 'size="4"', $input );
		$this->assertContains( 'maxlength="4"', $input );
		$this->assertContains( 'min="1"', $input );
		$this->assertContains( 'max="100"', $input );
		$this->assertContains( 'step="2"', $input );


	}

	public function test_settings_checkbox_list() {

		M::wpPassthruFunction( 'esc_attr' );
		M::wpPassthruFunction( 'esc_html' );
		M::wpPassthruFunction( 'wp_kses_post' );

		// Return an actual setting
		$this->mock_get_option( Dashboard_Directory_Size_Settings::$settings_key_general, array( 'checklist-option' => array( 'item2' ) ) );

		// Mock empty setting
		$this->mock_get_option( Dashboard_Directory_Size_Settings::$settings_key_general, array( 'checklist-option-empty' => false ) );

		$args = array(
			'name'      => 'checklist-option',
			'key'       => Dashboard_Directory_Size_Settings::$settings_key_general,
			'items'     => array( 'item1' => 'Item 1', 'item2' => 'Item 2' ),
			'after'     => 'After text',
			'legend'    => 'Legend',
		);

		$defaults = array(
			'name'      => '',
			'key'       => '',
			'items'     => array(),
			'after'     => '',
			'legend'    => '',
		);

		$return = $args;

		$this->mock_wp_parse_args(
			$args, // args
			$defaults, // default
			$return  // return
			);


		// Mock the checked call, item1 is false
		M::wpFunction( 'checked', array(
			'times' => 3,
			'args' => array(
				false,
				),
			)
		);

		// item2 is true
		M::wpFunction( 'checked', array(
			'times' => 1,
			'args' => array(
				true,
				),
			'return' => 'checked="checked"',
			)
		);

		ob_start();
		Dashboard_Directory_Size_Settings::settings_checkbox_list( $args );
		$input = ob_get_clean();

		$this->assertContains( 'name="dashboard-directory-size-settings-general[checklist-option][]"', $input );
		$this->assertContains( '<input type="checkbox"', $input );
		$this->assertContains( 'value="item1"', $input );
		$this->assertContains( 'value="item2"', $input );
		$this->assertContains( 'Item 1', $input );
		$this->assertContains( 'Item 2', $input );
		$this->assertContains( 'After text', $input );

		// TODO figure out checked output

		// Update the args for the empty options
		$args['name'] = 'checklist-option-empty';

		$this->mock_wp_parse_args(
			$args, // args
			$defaults, // default
			$return  // return
			);


		ob_start();
		Dashboard_Directory_Size_Settings::settings_checkbox_list( $args );
		$input = ob_get_clean();
	}

	public function test_settings_textarea() {

		M::wpPassthruFunction( 'esc_attr' );
		M::wpPassthruFunction( 'esc_html' );
		M::wpPassthruFunction( 'wp_kses_post' );

		// Return an actual setting
		$this->mock_get_option( Dashboard_Directory_Size_Settings::$settings_key_general, array( 'textarea-option' => 'Hello world' ) );

		$args = array(
			'name'      => 'textarea-option',
			'key'       => Dashboard_Directory_Size_Settings::$settings_key_general,
			'rows'      => 8,
			'cols'      => 4,
			'after'     => 'After text',
		);

		$defaults = array(
			'name'      => '',
			'key'       => '',
			'rows'      => 10,
			'cols'      => 40,
			'after'     => '',
		);

		$return = $args;

		$this->mock_wp_parse_args(
			$args, // args
			$defaults, // default
			$return  // return
			);


		ob_start();
		Dashboard_Directory_Size_Settings::settings_textarea( $args );
		$input = ob_get_clean();

		$this->assertContains( 'name="dashboard-directory-size-settings-general[textarea-option]"', $input );
		$this->assertContains( 'id="textarea-option"', $input );
		$this->assertContains( '<textarea ', $input );
		$this->assertContains( 'rows="8"', $input );
		$this->assertContains( 'cols="4"', $input );
		$this->assertContains( '>Hello world</textarea>', $input );
		$this->assertContains( 'After text', $input );


	}




}