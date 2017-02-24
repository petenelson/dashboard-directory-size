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

	public function test_rest_api_init() {

		// Enable the REST API endpoint for the plugin
		M::onFilter( 'dashboard-directory-size-setting-is-enabled' )
			->with( false, 'dashboard-directory-size-settings-general', 'rest-api-support' )
			->reply( true );

		// Mock the register_rest_route() call
		M::wpFunction( 'register_rest_route', array(
			'times'  => 4,
			'return' => true,
			)
		);

		Dashboard_Directory_Size_REST_API::rest_api_init();
	}

}

class WP_REST_Server {
	const READABLE = 'GET';
	const CREATABLE = 'POST';
	const EDITABLE = 'POST, PUT, PATCH';
	const DELETABLE = 'DELETE';
	const ALLMETHODS = 'GET, POST, PUT, PATCH, DELETE';
}
