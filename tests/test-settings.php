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

}