<?php
/**
 * PHPUnit bootstrap file
 *
 * @package dashboard-directory-size
 */

if ( ! defined( 'PHPUNIT_RUNNING' ) ) {
	define( 'PHPUNIT_RUNNING', true );
}

if ( ! defined( 'PROJECT' ) ) {
	define( 'PROJECT', getcwd() );
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/wordpress/' );
}

if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', '/wordpress/wp-content/plugins' );
}

if ( ! defined( 'WPMU_PLUGIN_DIR' ) ) {
	define( 'WPMU_PLUGIN_DIR', '/wordpress/wp-content/mu-plugins' );
}

if ( ! defined( 'DB_NAME' ) ) {
	define( 'DB_NAME', 'wordpress-default' );
}

if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
	define( 'MINUTE_IN_SECONDS', 60 );
}

require_once PROJECT . '/vendor/autoload.php';

WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();
WP_Mock::tearDown();

require_once PROJECT . '/tests/base-test.php';
require_once PROJECT . '/includes/class-dashboard-directory-size-common.php';
require_once PROJECT . '/includes/class-dashboard-directory-size-dashboard-widget.php';
require_once PROJECT . '/includes/class-dashboard-directory-size-i18n.php';
require_once PROJECT . '/includes/class-dashboard-directory-size-rest-api.php';
require_once PROJECT . '/includes/class-dashboard-directory-size-settings.php';
