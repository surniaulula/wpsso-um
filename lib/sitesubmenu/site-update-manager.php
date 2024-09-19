<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! defined( 'WPSSOUM_PLUGINDIR' ) ) {

	die( 'Do. Or do not. There is no try.' );
}

if ( ! class_exists( 'WpssoSubmenuAdvanced' ) ) {

	require_once WPSSOUM_PLUGINDIR . 'lib/submenu/update-manager.php';
}

if ( ! class_exists( 'WpssoUmSitesubmenuSiteUpdateManager' ) && class_exists( 'WpssoUmSubmenuUpdateManager' ) ) {

	class WpssoUmSitesubmenuSiteUpdateManager extends WpssoUmSubmenuUpdateManager {

		protected function set_form_object( $menu_ext ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'setting site form object for ' . $menu_ext );
			}

			$def_site_opts = $this->p->opt->get_site_defaults();

			$this->form = new SucomForm( $this->p, WPSSO_SITE_OPTIONS_NAME, $this->p->site_options, $def_site_opts, $menu_ext );
		}
	}
}
