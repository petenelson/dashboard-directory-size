<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'Dashboard_Directory_Size_i18n' ) ) {

	class Dashboard_Directory_Size_i18n {

		/**
		 * Loads the text domain for the plugin.
		 *
		 * @return void
		 */
		static public function plugins_loaded() {
			load_plugin_textdomain(
				'dashboard-directory-size',
				false,
				dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
			);
		}


	} // end class

}