<?php

use WP_Mock as M;

class Test_Dashboard_Directory_Size_Rest_API extends Test_Dashboard_Directory_Size_Base {

	public function setUp() {
		M::setUp();
	}

	public function tearDown() {
		M::tearDown();
	}

	public function test_plugins_loaded() {

		M::expectActionAdded( 'rest_api_init',      'Dashboard_Directory_Size_REST_API::rest_api_init' );

		Dashboard_Directory_Size_REST_API::plugins_loaded();
	}

	public function test_api_namespace() {

		// Create a mock filter to return 'test-namespace'
		M::onFilter( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-rest-api-namespace' )
			->with( 'dashboard-directory-size' )
			->reply( 'test-namespace' );

		// Call the method that uses this filter.
		$namespace = Dashboard_Directory_Size_REST_API::api_namespace();

		// Verify the filter worked.
		$this->assertEquals( 'test-namespace', $namespace );
	}

}
