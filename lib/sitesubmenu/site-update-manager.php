<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2023 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoUmSitesubmenuSiteUpdateManager' ) && class_exists( 'WpssoAdmin' ) ) {

	class WpssoUmSitesubmenuSiteUpdateManager extends WpssoAdmin {

		public function __construct( &$plugin, $id, $name, $lib, $ext ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$this->menu_id   = $id;
			$this->menu_name = $name;
			$this->menu_lib  = $lib;
			$this->menu_ext  = $ext;

			$this->menu_metaboxes = array(
				'settings' => _x( 'Update Manager Settings', 'metabox title', 'wpsso-um' ),
			);
		}

		protected function add_plugin_hooks() {

			$this->p->util->add_plugin_filters( $this, array( 'form_button_rows' => 2 ) );
		}

		public function filter_form_button_rows( $form_button_rows, $menu_id ) {

			switch ( $menu_id ) {

				case 'site-update-manager':

					/*
					 * Remove the Change to "All Options" View button.
					 */
					if ( isset( $form_button_rows[ 0 ] ) ) {

						$form_button_rows[ 0 ] = SucomUtil::preg_grep_keys( '/^change_show_options/', $form_button_rows[ 0 ], $invert = true );
					}

					$form_button_rows[ 0 ][ 'check_for_updates' ] = _x( 'Check for Plugin Updates', 'submit button', 'wpsso-um' );

					break;

				case 'site-tools':

					$form_button_rows[ 0 ][ 'check_for_updates' ] = _x( 'Check for Plugin Updates', 'submit button', 'wpsso-um' );
					$form_button_rows[ 0 ][ 'create_offers' ]     = _x( 'Re-Offer Plugin Updates', 'submit button', 'wpsso-um' );

					break;
			}

			return $form_button_rows;
		}

		protected function set_form_object( $menu_ext ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'setting site form object for ' . $menu_ext );
			}

			$def_site_opts = $this->p->opt->get_site_defaults();

			$this->form = new SucomForm( $this->p, WPSSO_SITE_OPTIONS_NAME, $this->p->site_options, $def_site_opts, $menu_ext );
		}

		public function show_metabox_settings( $obj, $mb ) {

			$tabs = array(
				'filters' => _x( 'Version Filters', 'metabox tab', 'wpsso-um' ),
			);

			$this->form->set_text_domain( 'wpsso' );	// Translate option values using wpsso text_domain.

			if ( method_exists( $this, 'show_metabox_tabbed' ) ) {	// Since WPSSO Core v16.7.0.

				$this->show_metabox_tabbed( $obj, $mb, $tabs );

			} else {

				$args          = isset( $mb[ 'args' ] ) ? $mb[ 'args' ] : array();
				$page_id       = isset( $args[ 'page_id' ] ) ? $args[ 'page_id' ] : '';
				$metabox_id    = isset( $args[ 'metabox_id' ] ) ? $args[ 'metabox_id' ] : '';
				$table_rows = array();

				foreach ( $tabs as $tab_key => $tab_title ) {

					$table_rows[ $tab_key ] = $this->get_table_rows( $page_id, $metabox_id, $tab_key, $args );
				}

				if ( isset( $this->p->util->metabox ) ) {	// Since WPSSO Core v8.0.0.

					$this->p->util->metabox->do_tabbed( $metabox_id, $tabs, $table_rows );

				} else $this->p->util->do_metabox_tabbed( $metabox_id, $tabs, $table_rows );
			}
		}

		protected function get_table_rows( $page_id, $metabox_id, $tab_key = '', $args = array() ) {

			$ext_sorted      = WpssoConfig::get_ext_sorted();	// Since WPSSO Core v3.38.3.
			$version_filters = $this->p->cf[ 'um' ][ 'version_filter' ];
			$table_rows      = array();

			foreach ( $ext_sorted as $ext => $info ) {

				if ( SucomUpdate::is_installed( $ext ) ) {

					$opt_key     = 'update_filter_for_' . $ext;
					$name_transl = _x( $info[ 'name' ], 'plugin name', 'wpsso' );

					$table_rows[ $opt_key ] = '' .
						$this->form->get_th_html( $name_transl, $css_class = 'plugin_name' ) .
						'<td>' . $this->form->get_select( $opt_key, $version_filters,
							$css_class = 'update_filter', $css_id = '', $is_assoc = true ) . '</td>' .
						WpssoAdmin::get_option_site_use( $opt_key, $this->form, $network = true, $enabled = true );
				}
			}

			return $table_rows;
		}
	}
}
