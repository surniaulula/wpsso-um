<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoUmSubmenuUpdateManager' ) && class_exists( 'WpssoAdmin' ) ) {

	class WpssoUmSubmenuUpdateManager extends WpssoAdmin {

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

			$this->p->util->add_plugin_filters( $this, array( 'form_button_rows' => 2 ) );
		}

		public function filter_form_button_rows( $form_button_rows, $menu_id ) {

			switch ( $menu_id ) {

				case 'tools':
				case 'site-tools':

					$form_button_rows[ 0 ][ 'check_for_updates' ] = _x( 'Check for Plugin Updates', 'submit button', 'wpsso-um' );
					$form_button_rows[ 0 ][ 'create_offers' ]     = _x( 'Re-Offer Plugin Updates', 'submit button', 'wpsso-um' );

					break;
			}

			return $form_button_rows;
		}

		/*
		 * Remove the "Change to View" button from this settings page.
		 */
		protected function add_form_buttons_change_show_options( &$form_button_rows ) {
		}

		public function show_metabox_settings( $obj, $mb ) {

			$tabs = array(
				'filters' => _x( 'Version Filters', 'metabox tab', 'wpsso-um' ),
			);

			$this->form->set_text_domain( 'wpsso' );	// Translate option values using wpsso text_domain.

			if ( method_exists( $this, 'show_metabox_tabbed' ) ) {	// Since WPSSO Core v17.0.0.

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

					/*
					 * Use $is_assoc = 'sorted' to keep the array order.
					 */
					$table_rows[ $opt_key ] = '' .
						$this->form->get_th_html( $name_transl, $css_class = 'plugin_name' ) .
						'<td>' . $this->form->get_select( $opt_key, $version_filters,
							$css_class = 'update_filter', $css_id = '', $is_assoc = 'sorted' ) . '</td>' .
						WpssoAdmin::get_option_site_use( $opt_key, $this->form, $args[ 'network' ] );
				}
			}

			return $table_rows;
		}
	}
}
