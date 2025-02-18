<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2025 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoUmFilters' ) ) {

	class WpssoUmFilters {

		private $p;	// Wpsso class object.
		private $a;	// WpssoUm class object.
		private $opts;	// WpssoUmFiltersOptions class object.
		private $upg;	// WpssoUmFiltersUpgrade class object.

		/*
		 * Instantiated by WpssoUm->init_objects().
		 */
		public function __construct( &$plugin, &$addon ) {

			static $do_once = null;

			if ( $do_once ) return;	// Stop here.

			$do_once = true;

			$this->p =& $plugin;
			$this->a =& $addon;

			if ( ! empty( $this->p->debug->enabled ) ) {

				$this->p->debug->mark();
			}

			require_once WPSSOUM_PLUGINDIR . 'lib/filters-options.php';

			$this->opts = new WpssoUmFiltersOptions( $plugin, $addon );

			require_once WPSSOUM_PLUGINDIR . 'lib/filters-upgrade.php';

			$this->upg = new WpssoUmFiltersUpgrade( $plugin, $addon );

			if ( is_admin() ) {

				$this->p->util->add_plugin_filters( $this, array(
					'cache_refreshed_notice'  => 2,
					'readme_upgrade_notices'  => 2,
					'newer_version_available' => 5,
				) );
			}
		}

		/*
		 * See WpssoUmActions->action_cache_refresh_scheduled().
		 */
		public function filter_cache_refreshed_notice( $notice_msg, $user_id = null ) {

			/*
			 * When $quiet is false the following notices may be shown:
			 *
			 *	- Please note that one or more non-stable / development Update Version Filters have been selected.
			 */
			$this->a->update->refresh_upd_config( $quiet = true );

			return $notice_msg;
		}

		public function filter_readme_upgrade_notices( $upgrade_notices, $ext ) {

			$filter_regex = $this->a->update->get_ext_filter_regex( $ext );

			foreach ( $upgrade_notices as $version => $info ) {

				if ( 0 === preg_match( $filter_regex, $version ) ) {

					unset( $upgrade_notices[ $version ] );
				}
			}

			return $upgrade_notices;
		}

		public function filter_newer_version_available( $newer_avail, $ext, $plugin_version, $stable_version, $latest_version ) {

			if ( $newer_avail ) return $newer_avail;	// Already true.

			$filter_name = $this->a->update->get_ext_filter_name( $ext );

			if ( 'stable' !== $filter_name && version_compare( $plugin_version, $latest_version, '<' ) ) return true;

			return $newer_avail;
		}
	}
}
