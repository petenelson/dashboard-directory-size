<?php
/**
 * Class SampleTest
 *
 * @package 
 */

/**
 * Sample test case.
 */
class Test_Dashboard_Directory_Size_Common extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
	}

	function test_filter_get_valid_directory_size() {
		$size = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get', 0, ABSPATH . 'wp-includes' );
		$this->assertGreaterThan( 0, $size, 'valid paths should return greater than 0' );
	}

	function test_filter_get_invalid_directory_size() {
		$size = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get', 0, 'some-invalid-path-name' );
		$this->assertLessThan( 0, $size, 'invalid paths should return -1' );
	}

}

