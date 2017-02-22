<?php

// class Test_Dashboard_Directory_Size_Plugin_File extends WP_UnitTestCase {

// 	var $_plugin;

// 	public function setUp() {
// 		parent::setUp();
// 		$this->_plugin = new Dashboard_Directory_Size_Plugin();
// 		$this->_plugin->define_constants();
// 		$this->_plugin->require_files( $this->_plugin->get_required_files() );
// 	}

// 	public function test_declarations() {

// 		$this->assertTrue( defined( 'DASHBOARD_DIRECOTRY_SIZE_ROOT' ) );
// 		$this->assertNotEmpty( DASHBOARD_DIRECOTRY_SIZE_ROOT );

// 	}

// 	public function test_includes_files() {

// 		$files = $this->_plugin->get_required_files();

// 		$this->assertTrue( in_array( DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/class-dashboard-directory-size-common.php',           $files ) );
// 		$this->assertTrue( in_array( DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/class-dashboard-directory-size-i18n.php',             $files ) );
// 		$this->assertTrue( in_array( DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/class-dashboard-directory-size-settings.php',         $files ) );
// 		$this->assertTrue( in_array( DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/class-dashboard-directory-size-dashboard-widget.php', $files ) );
// 		$this->assertTrue( in_array( DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/class-dashboard-directory-size-rest-api.php',         $files ) );

// 	}

// 	public function test_classes_exist() {

// 		$class_names = $this->_plugin->get_class_names();

// 		foreach ( $class_names as $class_name ) {
// 			$this->assertTrue( class_exists( $class_name ), $class_name );
// 		}


// 	}

// 	public function test_plugins_loaded() {

// 		// do_action( 'plugins_loaded' );



// 	}

// }