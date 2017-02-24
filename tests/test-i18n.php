<?php

use WP_Mock as M;

class Test_Dashboard_Directory_Size_I18n extends Test_Dashboard_Directory_Size_Base {

	public function setUp() {
		M::setUp();
	}

	public function tearDown() {
		M::tearDown();
	}

	public function test_plugins_loaded() {

		M::wpFunction( 'plugin_basename', array(
			'times'  => 1,
			'return' => '/path/to/plugin',
			)
		);

		M::wpFunction( 'load_plugin_textdomain', array(
			'times' => 1,
			'args' => array(
				'dashboard-directory-size',
				false,
				'/path/languages/'
				)
			)
		);

		Dashboard_Directory_Size_i18n::plugins_loaded();
	}

}