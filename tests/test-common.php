<?php

class Test_Dashboard_Directory_Size_Common extends WP_UnitTestCase {

	var $common;
	var $settings;

	public function setUp() {
		parent::setUp();
		$this->common = new Dashboard_Directory_Size_Common();
		$this->settings = new Dashboard_Directory_Size_Settings();
	}

	function test_filter_get_valid_directory_size() {
		$size = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get', 0, ABSPATH . 'wp-includes' );
		$this->assertGreaterThan( 0, $size, 'valid paths should return greater than 0' );
	}

	function test_filter_get_invalid_directory_size() {
		$size = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get', 0, 'some-invalid-path-name' );
		$this->assertLessThan( 0, $size, 'invalid paths should return -1' );
	}

	function test_filter_get_database_size() {
		$database = $this->common->get_database_size();
		$this->assertNotEmpty( $database );
		$this->assertEquals( DB_NAME, $database[0]['path'] );
		$this->assertGreaterThan( 0, absint( $database[0]['size'] ) );
	}

	function test_default_settings() {
		$default_settings = $this->settings->get_default_settings();
		$this->assertNotEmpty( $default_settings );
		$this->assertEquals( $default_settings['transient-time-minutes'], 60 );
		$this->assertNotEmpty( $default_settings['common-directories'] );
		$this->assertEmpty( $default_settings['custom-directories'] );
	}

	function test_filter_get_directories() {

		$default_settings = $this->settings->get_default_settings();


		// update_option( 'dashboard-directory-size-settings-general', $default_settings );

		$directories = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get-directories', array() );

		// $this->assertNotEmpty( $directories );


	}

}
