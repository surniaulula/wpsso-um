<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2025 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoUmConfig' ) ) {

	class WpssoUmConfig {

		public static $cf = array(
			'plugin' => array(
				'wpssoum' => array(			// Plugin acronym.
					'version'     => '7.3.0',	// Plugin version.
					'opt_version' => '10',		// Increment when changing default option values.
					'short'       => 'WPSSO UM',	// Short plugin name.
					'name'        => 'WPSSO Update Manager',
					'desc'        => 'Update Manager for the WPSSO Core Premium plugin.',
					'slug'        => 'wpsso-um',
					'base'        => 'wpsso-um/wpsso-um.php',
					'update_auth' => '',		// No premium version.
					'text_domain' => 'wpsso-um',
					'domain_path' => '/languages',

					/*
					 * Required plugin and its version.
					 */
					'req' => array(
						'wpsso' => array(
							'name'          => 'WPSSO Core',
							'home'          => 'https://wordpress.org/plugins/wpsso/',
							'plugin_class'  => 'Wpsso',
							'version_const' => 'WPSSO_VERSION',
							'min_version'   => '17.0.0',	// Released on 2023/11/11.
						),
					),

					/*
					 * URLs or relative paths to plugin banners and icons.
					 */
					'assets' => array(

						/*
						 * Icon image array keys are '1x' and '2x'.
						 */
						'icons' => array(
							'1x' => 'images/icon-128x128.png',
							'2x' => 'images/icon-256x256.png',
						),
					),

					/*
					 * Library files loaded and instantiated by WPSSO.
					 */
					'lib' => array(
						'sitesubmenu' => array(
							'site-update-manager' => 'Update Manager',
						),
						'submenu' => array(
							'update-manager' => 'Update Manager',
						),
					),
				),
			),
		);

		public static function get_version( $add_slug = false ) {

			$info =& self::$cf[ 'plugin' ][ 'wpssoum' ];

			return $add_slug ? $info[ 'slug' ] . '-' . $info[ 'version' ] : $info[ 'version' ];
		}

		public static function set_constants( $plugin_file ) {

			if ( defined( 'WPSSOUM_VERSION' ) ) {	// Define constants only once.

				return;
			}

			$info =& self::$cf[ 'plugin' ][ 'wpssoum' ];

			/*
			 * Define fixed constants.
			 */
			define( 'WPSSOUM_FILEPATH', $plugin_file );
			define( 'WPSSOUM_PLUGINBASE', $info[ 'base' ] );	// Example: wpsso-um/wpsso-um.php.
			define( 'WPSSOUM_PLUGINDIR', trailingslashit( realpath( dirname( $plugin_file ) ) ) );
			define( 'WPSSOUM_PLUGINSLUG', $info[ 'slug' ] );	// Example: wpsso-um.
			define( 'WPSSOUM_URLPATH', trailingslashit( plugins_url( '', $plugin_file ) ) );
			define( 'WPSSOUM_VERSION', $info[ 'version' ] );
		}

		public static function require_libs( $plugin_file ) {

			require_once WPSSOUM_PLUGINDIR . 'lib/com/update.php';

			require_once WPSSOUM_PLUGINDIR . 'lib/actions.php';
			require_once WPSSOUM_PLUGINDIR . 'lib/filters.php';
			require_once WPSSOUM_PLUGINDIR . 'lib/register.php';

			add_filter( 'wpssoum_load_lib', array( __CLASS__, 'load_lib' ), 10, 3 );
		}

		public static function load_lib( $success = false, $filespec = '', $classname = '' ) {

			if ( false !== $success ) {

				return $success;
			}

			if ( ! empty( $classname ) ) {

				if ( class_exists( $classname ) ) {

					return $classname;
				}
			}

			if ( ! empty( $filespec ) ) {

				$file_path = WPSSOUM_PLUGINDIR . 'lib/' . $filespec . '.php';

				if ( file_exists( $file_path ) ) {

					require_once $file_path;

					if ( empty( $classname ) ) {

						return SucomUtil::sanitize_classname( 'wpssoum' . $filespec, $allow_underscore = false );
					}

					return $classname;
				}
			}

			return $success;
		}
	}
}
