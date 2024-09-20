<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoUmRegister' ) ) {

	class WpssoUmRegister {

		public function __construct() {

			register_activation_hook( WPSSOUM_FILEPATH, array( $this, 'network_activate' ) );

			register_deactivation_hook( WPSSOUM_FILEPATH, array( $this, 'network_deactivate' ) );

			if ( is_multisite() ) {

				add_action( 'wpmu_new_blog', array( $this, 'wpmu_new_blog' ), 10, 6 );

				add_action( 'wpmu_activate_blog', array( $this, 'wpmu_activate_blog' ), 10, 5 );
			}
		}

		/*
		 * Fires immediately after a new site is created.
		 */
		public function wpmu_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

			switch_to_blog( $blog_id );

			$this->activate_plugin();

			restore_current_blog();
		}

		/*
		 * Fires immediately after a site is activated (not called when users and sites are created by a Super Admin).
		 */
		public function wpmu_activate_blog( $blog_id, $user_id, $password, $signup_title, $meta ) {

			switch_to_blog( $blog_id );

			$this->activate_plugin();

			restore_current_blog();
		}

		public function network_activate( $sitewide ) {

			self::do_multisite( $sitewide, array( $this, 'activate_plugin' ) );
		}

		public function network_deactivate( $sitewide ) {

			self::do_multisite( $sitewide, array( $this, 'deactivate_plugin' ) );
		}

		/*
		 * uninstall.php defines constants before calling network_uninstall().
		 */
		public static function network_uninstall() {

			$sitewide = true;

			/*
			 * Uninstall from the individual blogs first.
			 */
			self::do_multisite( $sitewide, array( __CLASS__, 'uninstall_plugin' ) );
		}

		private static function do_multisite( $sitewide, $method, $args = array() ) {

			if ( is_multisite() && $sitewide ) {

				global $wpdb;

				$db_query = 'SELECT blog_id FROM ' . $wpdb->blogs;
				$blog_ids = $wpdb->get_col( $db_query );

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );

					call_user_func_array( $method, array( $args ) );
				}

				restore_current_blog();

			} else {
				call_user_func_array( $method, array( $args ) );
			}
		}

		private function activate_plugin() {

			/*
			 * Register plugin install, activation, update times.
			 */
			if ( class_exists( 'WpssoUtilReg' ) ) {

				$version = WpssoUmConfig::get_version();

				WpssoUtilReg::update_ext_version( 'wpssoum', $version );
			}
		}

		private function deactivate_plugin() {

			$wpssoum =& WpssoUm::get_instance();

			if ( is_object( $wpssoum ) ) {	// Just in case.

				/*
				 * The WPSSO UM add-on depends on the WPSSO Core plugin, which calls WpssoUm->init_objects() to
				 * define the $wpssoum->update property. If WPSSO Core has been deactivated first, the
				 * $wpssoum->update property will be null.
				 */
				if ( is_object( $wpssoum->update ) ) {	// Just in case.

					$wpssoum->update->clear_sched_hooks();

					$wpssoum->update->clear_upd_config();
				}
			}
		}

		private static function uninstall_plugin() {}
	}
}
