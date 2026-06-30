<?php
/**
 * Uninstall routine for the Dashboard Directory Size plugin.
 *
 * @package Dashboard_Directory_Size
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die( 'restricted access' );
}

$keys = array(
	'dashboard-directory-size-settings-general',
);

// Remove options.
foreach ( $keys as $key ) {
	delete_option( $key );
}
