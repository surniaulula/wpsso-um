<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoUmFiltersUpgrade' ) ) {

	class WpssoUmFiltersUpgrade {

		private $p;	// Wpsso class object.
		private $a;	// WpssoUm class object.

		/*
		 * Instantiated by WpssoUmFilters->__construct().
		 */
		public function __construct( &$plugin, &$addon ) {

			$this->p =& $plugin;
			$this->a =& $addon;

			if ( ! empty( $this->p->debug->enabled ) ) {

				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array(
				'rename_options_keys' => 1,
			) );
		}

		public function filter_rename_options_keys( $rename_options ) {

			$rename_options[ 'wpssoum' ] = array(
				6 => array(
					'update_check_hours' => '',
				),
				8 => array(
					'plugin_wpssoipm_filter'      => '',
					'plugin_wpssojson_filter'     => '',
					'plugin_wpssoorg_filter'      => '',
					'plugin_wpssoplm_filter'      => '',
					'plugin_wpssossb_filter'      => '',
					'plugin_wpssotaq_filter'      => '',
					'update_filter_for_wpssoipm'  => '',
					'update_filter_for_wpssojson' => '',
					'update_filter_for_wpssoorg'  => '',
					'update_filter_for_wpssoplm'  => '',
					'update_filter_for_wpssossb'  => '',
					'update_filter_for_wpssotaq'  => '',
				),
			);

			return $rename_options;
		}
	}
}
