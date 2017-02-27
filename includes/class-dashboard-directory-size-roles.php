<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'Dashboard_Directory_Size_Roles' ) ) {

	class Dashboard_Directory_Size_Roles {

		/**
		 * Sets up WordPress hooks and filters.
		 *
		 * @return void
		 */
		static public function plugins_loaded() {
			add_filter( 'user_has_cap', 'Dashboard_Directory_Size_Roles::add_view_widget_cap', 10, 4 );
		}

		/**
		 * Returns a list of roles that have access to the dashboard
		 * widget.
		 *
		 * @return array
		 */
		static public function get_widget_roles() {

			$roles = apply_filters( Dashboard_Directory_Size_Common::PLUGIN_NAME . '-setting-get', false, Dashboard_Directory_Size_Common::PLUGIN_NAME . '-settings-general', 'assigned-widget-roles' );

			return $roles;

			// Do we have no roles assigned?
			if ( false === $roles && function_exists( 'get_editable_roles' ) ) {

				// Get a list of roles from the system, and include the
				// Admin role.
				$editable_roles = get_editable_roles();
				if ( !  empty( $editable_roles ) && isset( $editable_roles['administrator'] ) ) {
					$roles = array( 'administrator' );
				}
			}

			return $roles;
		}

		/**
		 * Dynamically filter a user's capabilities.
		 *
		 * @param array   $allcaps An array of all the user's capabilities.
		 * @param array   $caps    Actual capabilities for meta capability.
		 * @param array   $args    Optional parameters passed to has_cap(), typically object ID.
		 * @param WP_User $user    The user object.
		 */
		static function add_view_widget_cap( $allcaps, $caps, $args, $user ) {

			// Get a list of roles assigned to the widget.
			$widget_roles = self::get_widget_roles();

			if ( is_array( $widget_roles ) ) {
				foreach( array_keys( $widget_roles ) as $widget_role ) {

					// Does the user have one of the roles assigned to the
					// dashboard widget?
					if ( self::user_has_role( $user->ID, $widget_role ) ) {

						// They can view the widget.
						$allcaps[ Dashboard_Directory_Size_Dashboard_Widget::get_view_cap() ] = true;
					}
				}
			}

			return $allcaps;
		}

		/**
		 * Determines if the supplied user ID has the supplied role.
		 *
		 * @param  int    $user_id The user ID.
		 * @param  string $role    The role name.
		 * @return boolean
		 */
		static function user_has_role( $user_id, $role ) {
			$user = get_userdata( $user_id );
			return ! empty( $user ) && ! empty( $user->roles ) && in_array( $role, $user->roles );
		}
	}
}
