<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'Dashboard_Directory_Size_i18n' ) ) {

	class Dashboard_Directory_Size_i18n {


		public function plugins_loaded() {

			load_plugin_textdomain(
				Dashboard_Directory_Size_Common::PLUGIN_NAME,
				false,
				dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
			);

		}


	} // end class

}