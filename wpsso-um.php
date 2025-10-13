<?php
/*
 * Plugin Name: WPSSO Update Manager
 * Plugin Slug: wpsso-um
 * Text Domain: wpsso-um
 * Domain Path: /languages
 * Plugin URI: https://wpsso.com/extend/plugins/wpsso-um/
 * Assets URI: https://surniaulula.github.io/wpsso-um/assets/
 * Author: JS Morisset
 * Author URI: https://wpsso.com/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Description: Update Manager for the WPSSO Core Premium plugin.
 * Requires Plugins: wpsso
 * Requires PHP: 7.4.33
 * Requires At Least: 5.9
 * Tested Up To: 6.8.3
 * Version: 7.3.0
 *
 * Version Numbering: {major}.{minor}.{bugfix}[-{stage}.{level}]
 *
 *      {major}         Major structural code changes and/or incompatible API changes (ie. breaking changes).
 *      {minor}         New functionality was added or improved in a backwards-compatible manner.
 *      {bugfix}        Backwards-compatible bug fixes or small improvements.
 *      {stage}.{level} Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).
 *
 * Copyright 2015-2025 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoAbstractAddOn' ) ) {

	require_once dirname( __FILE__ ) . '/lib/abstract/add-on.php';
}

if ( ! class_exists( 'WpssoUm' ) ) {

	class WpssoUm extends WpssoAbstractAddOn {

		public $actions;	// WpssoUmActions class object.
		public $filters;	// WpssoUmFilters class object.
		public $update;		// SucomUpdate class object.

		protected $p;	// Wpsso class object.

		private static $instance = null;	// WpssoUm class object.

		public function __construct() {

			parent::__construct( __FILE__, __CLASS__ );
		}

		public static function &get_instance() {

			if ( null === self::$instance ) {

				self::$instance = new self;
			}

			return self::$instance;
		}

		public function init_textdomain() {

			load_plugin_textdomain( 'wpsso-um', false, 'wpsso-um/languages/' );
		}

		/*
		 * Called by Wpsso->set_objects() which runs at init priority 10.
		 */
		public function init_objects_preloader() {

			static $do_once = null;

			if ( $do_once ) return;	// Stop here.

			$do_once = true;

			$this->p =& Wpsso::get_instance();

			if ( ! empty( $this->p->debug->enabled ) ) {

				$this->p->debug->mark();
			}

			if ( $this->get_missing_requirements() ) {	// Returns false or an array of missing requirements.

				return;	// Stop here.
			}

			$text_domain = $this->cf[ 'plugin' ][ $this->ext ][ 'text_domain' ];

			new WpssoUmActions( $this->p, $this );
			new WpssoUmFilters( $this->p, $this );

			$this->update = new SucomUpdate( $this->p, $this, $text_domain );

			foreach ( $this->p->cf[ 'plugin' ] as $ext => $info ) {

				$last_check = $this->update->get_option_last_check( $ext );

				if ( $last_check < time() - ( 25 * HOUR_IN_SECONDS ) ) {	// Just in case.

					$this->update->check_ext_for_updates( $ext, $quiet = true );
				}
			}
		}

		/*
		 * Called by Wpsso->set_objects() which runs at init priority 10.
		 *
		 * Required for backwards compatibility.
		 */
		public function init_objects() {

			$this->init_objects_preloader();
		}
	}

	WpssoUm::get_instance();
}
