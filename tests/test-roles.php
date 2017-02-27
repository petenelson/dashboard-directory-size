<?php

use WP_Mock as M;

class Test_Dashboard_Directory_Size_Roles extends Test_Dashboard_Directory_Size_Base {

	public function setUp() {
		M::setUp();
	}

	public function tearDown() {
		M::tearDown();
	}

	public function test_plugins_loaded() {

		M::expectFilterAdded( 'user_has_cap', 'Dashboard_Directory_Size_Roles::add_view_widget_cap', 10, 4 );

		Dashboard_Directory_Size_Roles::plugins_loaded();
	}

	public function test_get_widget_roles() {

		// Create a mock filter to return roles
		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get' )
			->with( false, Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'assigned-widget-roles' )
			->reply( array( 'administrator', 'editor' ) );

		$widget_roles = Dashboard_Directory_Size_Roles::get_widget_roles();

		$this->assertContains( 'administrator', $widget_roles );
		$this->assertContains( 'editor', $widget_roles );
	}

	public function test_get_view_cap() {
		// Create a mock filter to return view cap
		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-view-widget-cap' )
			->with( '' )
			->reply( 'view_dashboard_directory_size_widget' );

		$cap = Dashboard_Directory_Size_Dashboard_Widget::get_view_cap();

		$this->assertEquals( 'view_dashboard_directory_size_widget', $cap );
	}

}
