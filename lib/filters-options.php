<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoUmFiltersOptions' ) ) {

	class WpssoUmFiltersOptions {

		private $p;	// Wpsso class object.
		private $a;	// WpssoUm class object.

		/*
		 * Instantiated by WpssoUmFilters->construct().
		 */
		public function __construct( &$plugin, &$addon ) {

			$this->p =& $plugin;
			$this->a =& $addon;

			$this->p->util->add_plugin_filters( $this, array(
				'save_setting_options'  => 3,
				'save_settings_options' => 3,
				'get_defaults'          => 1,	// Option defaults.
				'get_site_defaults'     => 1,	// Site option defaults.
				'option_type'           => 2,
			) );
		}

		/*
		 * The 'wpsso_save_settings_options' filter is applied by WpssoOptions->save_options(),
		 * WpssoAdmin->settings_sanitation(), and WpssoAdmin->save_site_settings().
		 *
		 * $opts is the new options to be saved. Wpsso->options and Wpsso->site_options are still the old options.
		 *
		 * $network is true if we're saving the multisite network settings.
		 *
		 * $is_option_upg is true when the option versions, not the plugin versions, have changed.
		 *
		 * Check for Authentication ID changes, and if the submitted values are different, refresh the update manager
		 * config and force an update check. If the saved version string is different, then just refresh the update manager
		 * config.
		 */
		public function filter_save_settings_options( array $opts, $network, $is_option_upg ) {

			if ( $network ) {

				return $opts;	// Nothing to do.
			}

			$check_ext = array();

			foreach ( $this->p->cf[ 'plugin' ] as $ext => $info ) {

				$update_auth = isset( $info[ 'update_auth' ] ) ? $info[ 'update_auth' ] : '';

				/*
				 * Compare old and new options for changes.
				 */
				foreach ( array(
					'plugin_' . $ext . '_' . $update_auth,	// Authentication ID.
					'update_filter_for_' . $ext,		// Version filter.
				) as $opt_key ) {

					/*
					 * The option key will exist only if the option was changed or received focus.
					 */
					if ( isset( $opts[ $opt_key ] ) ) {

						/*
						 * Check if the current option value is different than the submitted value.
						 */
						if ( ! isset( $this->p->options[ $opt_key ] ) || $this->p->options[ $opt_key ] !== $opts[ $opt_key ] ) {

							/*
							 * Update the current value (so we can refresh the config) and signal that
							 * an update check is required for that plugin / add-on.
							 */
							$this->p->options[ $opt_key ] = $opts[ $opt_key ];

							$check_ext[] = $ext;
						}
					}
				}
			}

			if ( $is_option_upg ) {

				$check_ext[] = 'wpsso';
			}

			$check_ext = array_unique( $check_ext );	// Just in case.

			/*
			 * Check for updates if we have one or more Auth ID or version filter changes.
			 */
			if ( ! empty( $check_ext ) ) {

				$this->a->update->refresh_upd_config();

				/*
				 * Note that SucomUpdate->check_ext_for_updates() does not throttle like SucomUpdate->check_all_for_updates().
				 */
				$this->a->update->check_ext_for_updates( $check_ext, $quiet = true );
			}

			return $opts;
		}

		public function filter_get_defaults( array $defs ) {

			$def_filter_name = $this->a->update->get_default_filter_name();

			foreach ( $this->p->cf[ 'plugin' ] as $ext => $info ) {

				$defs[ 'update_filter_for_' . $ext ] = $def_filter_name;
			}

			return $defs;
		}

		public function filter_get_site_defaults( $defs ) {

			$def_filter_name = $this->a->update->get_default_filter_name();

			foreach ( $this->p->cf[ 'plugin' ] as $ext => $info ) {

				$defs[ 'update_filter_for_' . $ext ] = $def_filter_name;

				$defs[ 'update_filter_for_' . $ext . ':use' ] = 'default';
			}

			return $defs;
		}

		/*
		 * Return the sanitation type for a given option key.
		 */
		public function filter_option_type( $type, $base_key ) {

			if ( ! empty( $type ) ) {	// Return early if we already have a type.

				return $type;

			} elseif ( strpos( $base_key, 'update_' ) !== 0 ) {	// Nothing to do.

				return $type;
			}

			switch ( $base_key ) {

				case ( strpos( $base_key, 'update_filter_for_' ) === 0 ? true : false ):

					return 'not_blank';
			}

			return $type;
		}

		/*
		 * Deprecated since WPSSO Core v15.19.0.
		 */
		public function filter_save_setting_options( array $opts, $network, $is_option_upg ) {

			_deprecated_function( __METHOD__ . '()', '2023/08/07',
				$replacement = 'WpssoUmFiltersOptions::filter_save_settings_options()' );	// Deprecation message.

			return $this->filter_save_settings_options( $opts, $network, $is_option_upg );
		}
	}
}
