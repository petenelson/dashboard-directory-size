<?php
if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'Dashboard_Directory_Size_REST_API' ) ) {

	class Dashboard_Directory_Size_REST_API {



		public function plugins_loaded() {
			add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		}

		public function rest_api_init() {
			$enabled = apply_filters( 'dashboard-directory-size-setting-is-enabled', false, 'dashboard-directory-size-settings-general', 'rest-api-support' );
			if ( $enabled ) {
				register_rest_route( $this->api_namespace(), '/v1/sizes',
					array(
						'methods'    => WP_REST_Server::READABLE,
						'callback'   => array( $this, 'get_sizes' ),
						)
					);
			}
		}


		public function api_namespace() {
			return apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-rest-api-namespace', 'dashboard-directory-size' );
		}


		public function get_sizes( WP_REST_Request $request ) {
			return apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get-directories', array() );
		}


	}

}