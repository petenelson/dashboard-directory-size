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

}