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

			register_rest_route( $this->api_namespace(), '/v1/size',
				array(
					'methods'    => WP_REST_Server::READABLE,
					'callback'   => array( $this, 'get_size' ),
					'args'       => array(
						'directory' => array(
							'required' => true,
							'validate_callback' => 'Dashboard_Directory_Size_REST_API::is_valid_directory',
							),
						'refresh'   => array(
							'required' => false,
							'sanitize_callback' => 'absint',
							),
						),
					)
				);

			register_rest_route( $this->api_namespace(), '/v1/directories',
				array(
					'methods'    => WP_REST_Server::READABLE,
					'callback'   => array( $this, 'get_directories' ),
					)
				);

		}


		public function api_namespace() {
			return apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-rest-api-namespace', 'dashboard-directory-size' );
		}


		public function get_sizes( WP_REST_Request $request ) {
			return apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get-directories', array() );
		}

		public function get_size( WP_REST_Request $request ) {

			$response = new stdClass();
			$response->directory = $request['directory'];
			$response->size = Dashboard_Directory_Size_Common::get_directory_size( $request['directory'], ! empty( $request['refresh'] ) );
			$response->size_friendly = size_format( $response->size );

			return rest_ensure_response( $response );
		}


		public function get_directories( WP_REST_Request $request ) {

			$response = new stdClass();
			$response->directories = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get-directories', array() );

			return rest_ensure_response( $response );
		}

		static public function is_valid_directory( $directory ) {
			$directories = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-get-directories', array() );
			return in_array( $directory, wp_list_pluck( $directories, 'path' ) );
		}


	}

}