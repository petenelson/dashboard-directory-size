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


	public function test_trim_path_length_trimmed() {

		// Mock a shorter path length
		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-trimmed-path-length' )
			->with( 25 )
			->reply( 20 );

		$full_path = '/this/is/a/long/path/name';
		$path = Dashboard_Directory_Size_Common::trim_path( $full_path );

		$this->assertEquals( '/this/is/a/long/path', $path['path'] );
		$this->assertEquals( $full_path, $path['full_path'] );
		$this->assertTrue( $path['trimmed'] );

	}

	public function test_trim_path_length_untrimmed() {

		// Mock a longer path length
		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-trimmed-path-length' )
			->with( 25 )
			->reply( 30 );

		$full_path = '/this/is/a/long/path/name';
		$path = Dashboard_Directory_Size_Common::trim_path( $full_path );

		$this->assertEquals( $full_path, $path['path'] );
		$this->assertEquals( $full_path, $path['full_path'] );
		$this->assertFalse( $path['trimmed'] );

	}

	public function test_trim_path_length_abspath() {

		$full_path = '/wordpress/wp-content';
		$path = Dashboard_Directory_Size_Common::trim_path( $full_path );

		$this->assertEquals( 'wp-content', $path['path'] );
		$this->assertEquals( 'wp-content', $path['full_path'] );

	}

	public function test_transient_path_key() {

		$path = '/some/path';
		$key = Dashboard_Directory_Size_Common::transient_path_key( $path );

		$this->assertEquals( 'DD-Path-Size-' . md5( $path ), $key );
	}

	public function test_get_path_for_common_dir() {

		// Mock the wp_upload_dir() call
		M::wpFunction( 'wp_upload_dir', array(
			'times'  => 1,
			'return' => array(
				'basedir' => ABSPATH . 'wp-content/uploads',
				),
			)
		);
		
		// Mock the get_theme_root() call
		M::wpFunction( 'get_theme_root', array(
			'times'  => 1,
			'return' => '/wordpress/wp-content/themes'
			)
		);

		// test the uploads dir
		$path = Dashboard_Directory_Size_Common::get_path_for_common_dir( 'uploads' );
		$this->assertEquals( '/wordpress/wp-content/uploads', $path );

		// test the themes root
		$path = Dashboard_Directory_Size_Common::get_path_for_common_dir( 'themes' );
		$this->assertEquals( '/wordpress/wp-content/themes', $path );

		// test the plugins
		$path = Dashboard_Directory_Size_Common::get_path_for_common_dir( 'plugins' );
		$this->assertEquals( '/wordpress/wp-content/plugins', $path );

		// test the mu-plugins
		$path = Dashboard_Directory_Size_Common::get_path_for_common_dir( 'mu-plugins' );
		$this->assertEquals( '/wordpress/wp-content/mu-plugins', $path );

		// test an invalid value
		$path = Dashboard_Directory_Size_Common::get_path_for_common_dir( 'invalid' );
		$this->assertEmpty( $path );

	}

	public function test_flush_size_transient() {
		M::wpPassthruFunction( 'delete_transient' );
		Dashboard_Directory_Size_Common::flush_size_transient( 'path' );
	}

	public function test_get_transient_time() {

		// Mock a different transient time
		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get' )
			->with( 60, Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'transient-time-minutes' )
			->reply( 90 );

		$time = Dashboard_Directory_Size_Common::get_transient_time();
		$this->assertEquals( 90, $time );
	}

	public function test_get_decimal_places() {

		// Mock a decimal places
		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get' )
			->with( 0, Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'decimal-places' )
			->reply( 2 );

		$time = Dashboard_Directory_Size_Common::get_decimal_places();
		$this->assertEquals( 2, $time );
	}

	public function test_get_database_size() {
		global $wpdb;

		$wpdb = Mockery::mock( '\WPDB' );

		$wpdb->shouldReceive( 'prepare' )
			->once()
			->with(
				Mockery::any(), // SQL statement
				Mockery::any() // table_schema
				)
			->andReturn( 'SQL STATEMENT' );

		$wpdb->shouldReceive( 'get_var' )
			->once()
			->with( 'SQL STATEMENT' )
			->andReturn( 100000 );

		M::wpFunction( '__', array(
			'times'  => 1,
			'return' => 'Database',
			)
		);

		$database = Dashboard_Directory_Size_Common::get_database_size();

		$this->assertEquals( 'WP Database', $database[0]['name'] );
		$this->assertEquals( DB_NAME, $database[0]['path'] );
		$this->assertEquals( 100000, $database[0]['size'] );

	}

	public function test_flush_sizes_transient() {

		// Mock some directory results
		$directories = array(
			array( 'path' => '/path1' ),
			array( 'path' => '/path2' ),
			);

		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get-directories' )
			->with( array() )
			->reply( $directories );

		$data = Dashboard_Directory_Size_Common::flush_sizes_transient( 'filter value' );

		// The filter above should not change what was passed to it.
		$this->assertEquals( 'filter value', $data );

	}

	public function test_get_custom_dir() {

		// test a valid dir
		$dir_info = Dashboard_Directory_Size_Common::get_custom_dir( 'name|/path' );
		$this->assertEquals( 'name', $dir_info['name'] );
		$this->assertEquals( '/path', $dir_info['path'] );

		// test an invalid dir
		$dir_info = Dashboard_Directory_Size_Common::get_custom_dir( 'name /path' );
		$this->assertNull( $dir_info );

		// test a relative dir
		$dir_info = Dashboard_Directory_Size_Common::get_custom_dir( 'name|~/wp-content' );
		$this->assertEquals( 'name', $dir_info['name'] );
		$this->assertEquals( '/wordpress/wp-content', $dir_info['path'] );

	}


	public function test_flush_sizes_on_item_match() {

		// Mock some directory results
		$directories = array(
			array( 'path' => '/path1' ),
			array( 'path' => '/path2' ),
			);

		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get-directories' )
			->with( array() )
			->reply( $directories );

		M::wpFunction( 'delete_transient', array(
			'times' => 6,
			)
		);

		Dashboard_Directory_Size_Common::flush_sizes_on_item_match( 'active_plugins' );
		Dashboard_Directory_Size_Common::flush_sizes_on_item_match( 'uninstall_plugins' );
		Dashboard_Directory_Size_Common::flush_sizes_on_item_match( 'update_themes' );
	}


	public function test_get_custom_dirs() {

		$custom_dirs = "dir-1|/path-1\ndir-2|/path-2";

		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get' )
			->with( array(), Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'custom-directories' )
			->reply( $custom_dirs );

		$dirs = Dashboard_Directory_Size_Common::get_custom_dirs();

		$this->assertCount( 2, $dirs );

		$this->assertEquals( 'dir-1', $dirs[0]['name'] );
		$this->assertEquals( 'dir-2', $dirs[1]['name'] );

		$this->assertEquals( '/path-1', $dirs[0]['path'] );
		$this->assertEquals( '/path-2', $dirs[1]['path'] );

	}


	public function test_get_directory_size_invalid() {

		M::wpPassthruFunction( 'set_transient' );

		M::wpFunction( 'get_transient', array(
			'times'  => 1,
			'args'   => Dashboard_Directory_Size_Common::transient_path_key( '/invalid' ),
			'return' => false,
			)
		);

		$size = Dashboard_Directory_Size_Common::get_directory_size( '/invalid' );

		$this->assertEquals( -1, $size);

	}

	public function test_get_directory_size_valid() {

		$dirname = dirname( __FILE__ );

		// Mock a transient time
		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get' )
			->with( 60, Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'transient-time-minutes' )
			->reply( 60 );

		M::wpFunction( 'get_transient', array(
			'times'  => 1,
			'args'   => Dashboard_Directory_Size_Common::transient_path_key( $dirname ),
			'return' => false,
			)
		);

		M::wpFunction( 'recurse_dirsize', array(
			'times'  => 1,
			'args'   => $dirname,
			'return' => 100
			)
		);

		M::wpFunction( 'delete_transient', array(
			'times'  => 1,
			'args'   => Dashboard_Directory_Size_Common::transient_path_key( $dirname ),
			'return' => true,
			)
		);

		M::wpFunction( 'set_transient', array(
			'times'  => 1,
			'args'   => array(
				Dashboard_Directory_Size_Common::transient_path_key( $dirname ),
				100,
				MINUTE_IN_SECONDS * 60,
				),
			'return' => true,
			)
		);

		$size = Dashboard_Directory_Size_Common::get_directory_size( $dirname, true );

		$this->assertEquals( 100, $size);

	}


	public function test_get_directory_size_cached() {

		$dirname = dirname( __FILE__ );

		// Mock a transient time
		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get' )
			->with( 60, Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'transient-time-minutes' )
			->reply( 60 );

		M::wpFunction( 'get_transient', array(
			'times'  => 1,
			'args'   => Dashboard_Directory_Size_Common::transient_path_key( $dirname ),
			'return' => 100,
			)
		);

		$size = Dashboard_Directory_Size_Common::filter_get_directory_size( 100, $dirname );
		$this->assertEquals( 100, $size);

	}


	public function test_get_common_dirs() {

		// Mock some common dirs
		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get' )
			->with( array(), Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'common-directories' )
			->reply( array( 'uploads', 'plugins' ) );

		M::wpFunction( 'wp_upload_dir', array(
			'times'  => 1,
			'return' => array(
				'basedir' => ABSPATH . 'wp-content/uploads',
				),
			)
		);

		$dir_list = Dashboard_Directory_Size_Common::get_common_dirs();

		$this->assertCount( 2, $dir_list );

		$this->assertEquals( '/wordpress/wp-content/uploads', $dir_list[0]['path'] );
		$this->assertEquals( 'uploads', $dir_list[0]['name'] );

		$this->assertEquals( '/wordpress/wp-content/plugins', $dir_list[1]['path'] );
		$this->assertEquals( 'plugins', $dir_list[1]['name'] );

	}

	public function test_apply_friendly_sizes() {

		// Mock decimal places
		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get' )
			->with( 0, Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'decimal-places' )
			->reply( 0 );

		$dirs = array(
				array( 'size' => 100 ),
				array( 'size' => 200 ),
				array( 'size' => 0 ),
			);

		M::wpFunction( 'size_format', array(
			'times' => 1,
			'args' => array( 100, 0 ),
			'return' => '100MB',
			)
		);

		M::wpFunction( 'size_format', array(
			'times' => 1,
			'args' => array( 200, 0 ),
			'return' => '200MB',
			)
		);

		M::wpFunction( '__', array(
			'times'  => 1,
			'return' => 'Empty',
			)
		);

		$dirs = Dashboard_Directory_Size_Common::apply_friendly_sizes( $dirs );

		$this->assertCount( 3, $dirs );

		$this->assertEquals( '100MB', $dirs[0]['size_friendly'] );
		$this->assertEquals( '200MB', $dirs[1]['size_friendly'] );
		$this->assertEquals( 'Empty', $dirs[2]['size_friendly'] );

	}

	public function test_filter_get_directories() {

		// Mock decimal places
		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get' )
			->with( 0, Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'decimal-places' )
			->reply( 0 );

		// Mock some common dirs
		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get' )
			->with( array(), Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'common-directories' )
			->reply( array( 'uploads', 'plugins' ) );

		M::wpFunction( 'wp_upload_dir', array(
			'times'  => 1,
			'return' => array(
				'basedir' => ABSPATH . 'wp-content/uploads',
				),
			)
		);

		// Mocks some custom dirs
		$custom_dirs = "dir-1|/path-1\ndir-2|/path-2";

		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get' )
			->with( array(), Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'custom-directories' )
			->reply( $custom_dirs );

		// Mock the database size and wpdb
		M::onFilter( 'dashboard-directory-size-setting-is-enabled' )
			->with( false, 'dashboard-directory-size-settings-general', 'show-database-size' )
			->reply( true );


		// Mock the total sum
		M::onFilter( 'dashboard-directory-size-setting-is-enabled' )
			->with( false, 'dashboard-directory-size-settings-general', 'show-sum' )
			->reply( true );

		global $wpdb;

		$wpdb = Mockery::mock( '\WPDB' );

		$wpdb->shouldReceive( 'prepare' )
			->once()
			->with(
				Mockery::any(), // SQL statement
				Mockery::any() // table_schema
				)
			->andReturn( 'SQL STATEMENT' );

		$wpdb->shouldReceive( 'get_var' )
			->once()
			->with( 'SQL STATEMENT' )
			->andReturn( 100000 );

		M::wpFunction( '__', array(
			'times'  => 1,
			'return' => 'Database',
			)
		);

		M::wpFunction( '__', array(
			'times'  => 1,
			'return' => 'Total Size',
			)
		);

		M::wpFunction( 'size_format', array(
			'times' => 5,
			'args' => array( -2, 0 ),
			'return' => '',
			)
		);

		M::wpFunction( 'size_format', array(
			'times' => 1,
			'args' => array( 100000, 0 ),
			'return' => '100MB',
			)
		);


		// Get the directories above
		$dirs = Dashboard_Directory_Size_Common::filter_get_directories( array() );

		$this->assertCount( 6, $dirs );

		$this->assertEquals( 'uploads', $dirs[0]['name'] );
		$this->assertEquals( 'plugins', $dirs[1]['name'] );
		$this->assertEquals( 'dir-1',   $dirs[2]['name'] );
		$this->assertEquals( 'dir-2',   $dirs[3]['name'] );
		$this->assertEquals( 'WP Database',   $dirs[4]['name'] );
		$this->assertEquals( 'Total Size',    $dirs[5]['name'] );

	}

}
