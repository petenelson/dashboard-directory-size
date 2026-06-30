<?php
/**
 * WP-CLI setup for the Dashboard Directory Size plugin.
 *
 * @package Dashboard_Directory_Size
 */

if ( ! defined( 'DASHBOARD_DIRECOTRY_SIZE_ROOT' ) ) {
	return;
}

// Our WP-CLI commands.
$includes = array(
	'class-dashboard-directory-size-base-command.php',
	'class-dashboard-directory-size-command.php',
);

foreach ( $includes as $include ) {
	require_once DASHBOARD_DIRECOTRY_SIZE_ROOT . 'includes/wp-cli/' . $include;
}
