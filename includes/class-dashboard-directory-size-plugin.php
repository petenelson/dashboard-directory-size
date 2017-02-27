<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'Dashboard_Directory_Size_Plugin' ) ) {

	class Dashboard_Directory_Size_Plugin {

		/**
		 * Wires up WordPress hooks and filters
		 *
		 * @return void
		 */
		static public function plugins_loaded() {
			add_filter( 'plugin_action_links_' . DASHBOARD_DIRECTORY_SIZE_BASENAME, array( __CLASS__, 'plugin_action_links' ), 10, 4 );
		}

		/**
		 * Adds additional links to the row on the plugins page.
		 *
		 * @param array  $actions     An array of plugin action links.
		 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
		 * @param array  $plugin_data An array of plugin data.
		 * @param string $context     The plugin context. Defaults are 'All', 'Active',
		 *                            'Inactive', 'Recently Activated', 'Upgrade',
		 *                            'Must-Use', 'Drop-ins', 'Search'.
		 */
		static public function plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {

			if ( is_plugin_active( $plugin_file ) && current_user_can( 'manage_options' ) ) {

				// Build the URL for the settings page.
				$url = add_query_arg(
					'page',
					rawurlencode( DASHBOARD_DIRECTORY_SIZE_Settings::$settings_page ),
					admin_url( 'admin.php' )
					);

				// Add the anchor tag to the list of plugin links.
				$new_actions = array(
					'settings' => sprintf( '<a href="%1$s">%2$s</a>',
						esc_url( $url ),
						esc_html__( 'Settings' )
						)
					);

				$actions = array_merge( $new_actions, $actions );
			}

			return $actions;
		}
	}

}