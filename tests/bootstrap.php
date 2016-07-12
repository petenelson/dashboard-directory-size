<?php
/**
 * PHPUnit bootstrap file
 *
 * @package dashboard-directory-size
 */

if ( ! defined( 'PROJECT' ) ) {
	define( 'PROJECT', getcwd() );
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
