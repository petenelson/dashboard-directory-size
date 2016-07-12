<?php

use WP_Mock as M;

class Test_Dashboard_Directory_Size_Common extends Test_Dashboard_Directory_Size_Base {

	public function setUp() {


		M::setUp();
	}

	public function tearDown() {
		M::tearDown();
	}

	public function test_class_constants() {
		$this->assertEquals( Dashboard_Directory_Size_Common::PLUGIN_NAME, 'dashboard-directory-size' );
		$this->assertNotEmpty( Dashboard_Directory_Size_Common::VERSION );
	}

	public function test_create_directory_info_valid() {

		$directory_info = Dashboard_Directory_Size_Common::create_directory_info( 'dirname', 'dirpath' );

		$this->assertEquals( $directory_info['path'], 'dirpath' );
		$this->assertEquals( $directory_info['name'], 'dirname' );
		$this->assertEquals( $directory_info['size'], -2 );

	}

	public function test_create_directory_info_invalid() {

		$directory_info = Dashboard_Directory_Size_Common::create_directory_info( '', '' );

		$this->assertNull( $directory_info );

	}

	public function test_plugins_loaded() {

		M::expectFilterAdded( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get', 'Dashboard_Directory_Size_Common::filter_get_directory_size', 10, 2 );
		M::expectFilterAdded( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get-directories', 'Dashboard_Directory_Size_Common::filter_get_directories' );

		M::expectActionAdded( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-flush-sizes-transient', 'Dashboard_Directory_Size_Common::flush_sizes_transient' );

		// test that the transient flushers are loaded
		M::expectActionAdded( 'add_attachment',                  'Dashboard_Directory_Size_Common::flush_sizes_transient' );
		M::expectActionAdded( 'edit_attachment',                 'Dashboard_Directory_Size_Common::flush_sizes_transient' );
		M::expectActionAdded( 'upgrader_process_complete',       'Dashboard_Directory_Size_Common::flush_sizes_transient' );
		M::expectActionAdded( 'deleted_plugin',                  'Dashboard_Directory_Size_Common::flush_sizes_transient' );

		M::expectFilterAdded( 'wp_update_attachment_metadata',   'Dashboard_Directory_Size_Common::flush_sizes_transient' );
		M::expectFilterAdded( 'wp_handle_upload',                'Dashboard_Directory_Size_Common::flush_sizes_transient' );

		M::expectActionAdded( 'update_option',                   'Dashboard_Directory_Size_Common::flush_sizes_on_item_match' );
		M::expectActionAdded( 'deleted_site_transient',          'Dashboard_Directory_Size_Common::flush_sizes_on_item_match' );

		Dashboard_Directory_Size_Common::plugins_loaded();

	}

}


// class Test_Dashboard_Directory_Size_Common extends WP_UnitTestCase {

// 	var $common;
// 	var $settings;

// 	public function setUp() {
// 		parent::setUp();
// 		$this->common = new Dashboard_Directory_Size_Common();
// 		$this->settings = new Dashboard_Directory_Size_Settings();
// 	}

// 	function test_filter_get_valid_directory_size() {
// 		$size = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get', 0, ABSPATH . 'wp-includes' );
// 		$this->assertGreaterThan( 0, $size, 'valid paths should return greater than 0' );
// 	}

// 	function test_filter_get_invalid_directory_size() {
// 		$size = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get', 0, 'some-invalid-path-name' );
// 		$this->assertLessThan( 0, $size, 'invalid paths should return -1' );
// 	}

// 	function test_filter_get_database_size() {
// 		$database = $this->common->get_database_size();
// 		$this->assertNotEmpty( $database );
// 		$this->assertEquals( DB_NAME, $database[0]['path'] );
// 		$this->assertGreaterThan( 0, absint( $database[0]['size'] ) );
// 	}

// 	function test_default_settings() {
// 		$default_settings = $this->settings->get_default_settings();
// 		$this->assertNotEmpty( $default_settings );
// 		$this->assertEquals( $default_settings['transient-time-minutes'], 60 );
// 		$this->assertNotEmpty( $default_settings['common-directories'] );
// 		$this->assertEmpty( $default_settings['custom-directories'] );
// 	}

// 	function test_filter_get_directories() {

// 		$default_settings = $this->settings->get_default_settings();


// 		// update_option( 'dashboard-directory-size-settings-general', $default_settings );

// 		$directories = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get-directories', array() );

// 		// $this->assertNotEmpty( $directories );


// 	}

// }

