<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2025 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'SucomUpdateUtilWP' ) ) {

	class SucomUpdateUtilWP {

		/*
		 * Unfiltered version of home_url() from wordpress/wp-includes/link-template.php
		 *
		 * Last synchronized with WordPress v6.6.2 on 2024/09/16.
		 */
		public static function raw_home_url( $path = '', $scheme = null ) {

			return self::raw_get_home_url( null, $path, $scheme );
		}

		/*
		 * Unfiltered version of get_home_url() from wordpress/wp-includes/link-template.php
		 *
		 * Last synchronized with WordPress v6.6.2 on 2024/09/16.
		 */
		public static function raw_get_home_url( $blog_id = null, $path = '', $scheme = null ) {

			$is_multisite = is_multisite();

			if ( $is_multisite && ! empty( $blog_id ) ) {

				switch_to_blog( $blog_id );
			}

			/*
			 * The WordPress _config_wp_home() function is hooked to the 'option_home' filter in order to override the
			 * database value. Since we're not using the default filters, check for WP_HOME or WP_SITEURL and update
			 * the stored database value if necessary.
			 *
			 * The homepage of the website:
			 *
			 *	WP_HOME
			 *	home_url()
			 *	get_home_url()
			 *	Site Address (URL)
			 *	http://example.com
			 *
			 * The WordPress installation (ie. where you can reach the site by adding /wp-admin):
			 *
			 *	WP_SITEURL
			 *	site_url()
			 *	get_site_url()
			 *	WordPress Address (URL)
			 *	http://example.com/wp/
			 *
			 * The WordPress _config_wp_home() function is unhooked for multisites as WP_HOME and WP_SITEURL are not
			 * used in a multisite configuration. If this is a multisite, ignore the WP_HOME and WP_SITEURL values.
			 *
			 * See wordpress/wp-includes/ms-default-filters.php
			 */
			if ( ! $is_multisite ) {

				if ( defined( 'WP_HOME' ) && WP_HOME ) {

					$url = untrailingslashit( WP_HOME );

					$db_url = self::raw_do_option( $action = 'get', $opt_name = 'home' );

					if ( $db_url !== $url ) {

						self::raw_do_option( $action = 'update', $opt_name = 'home', $url );
					}
				}
			}

			$url = self::raw_do_option( $action = 'get', $opt_name = 'home' );

			if ( $is_multisite && ! empty( $blog_id ) ) {

				restore_current_blog();
			}

			if ( ! in_array( $scheme, array( 'http', 'https', 'relative' ), $strict = true ) ) {

				$scheme = is_ssl() ? 'https' : wp_parse_url( $url, PHP_URL_SCHEME );
			}

			$url = self::raw_set_url_scheme( $url, $scheme );

			if ( $path && is_string( $path ) ) {

				$url .= '/' . ltrim( $path, '/' );
			}

			return $url;
		}

		/*
		 * Unfiltered version of set_url_scheme() from wordpress/wp-includes/link-template.php
		 *
		 * Last synchronized with WordPress v6.6.2 on 2024/09/16.
		 */
		private static function raw_set_url_scheme( $url, $scheme = null ) {

			if ( ! $scheme ) {

				$scheme = is_ssl() ? 'https' : 'http';

			} elseif ( 'admin' === $scheme || 'login' === $scheme || 'login_post' === $scheme || 'rpc' === $scheme ) {

				$scheme = is_ssl() || force_ssl_admin() ? 'https' : 'http';

			} elseif ( 'http' !== $scheme && 'https' !== $scheme && 'relative' !== $scheme ) {

				$scheme = is_ssl() ? 'https' : 'http';
			}

			$url = trim( $url );

			if ( str_starts_with( $url, '//' ) ) {

				$url = 'http:' . $url;
			}

			if ( 'relative' === $scheme ) {

				$url = ltrim( preg_replace( '#^\w+://[^/]*#', '', $url ) );

				if ( '' !== $url && '/' === $url[ 0 ] ) {

					$url = '/' . ltrim( $url, "/ \t\n\r\0\x0B" );
				}

			} else $url = preg_replace( '#^\w+://#', $scheme . '://', $url );

			return $url;
		}

		/*
		 * Temporarily disable filters and actions hooks before calling get_option(), update_option(), and delete_option().
		 */
		public static function raw_do_option( $action, $opt_name, $value = null, $default = false, $autoload = null ) {

			global $wp_filter, $wp_actions;

			$saved_filter  = $wp_filter;	// Save filters.
			$saved_actions = $wp_actions;	// Save actions.

			$wp_filter  = array();	// Remove all filters.
			$wp_actions = array();	// Remove all actions.

			$success   = null;
			$old_value = $default;

			switch( $action ) {

				case 'add':
				case 'add_option':

					$success = add_option( $opt_name, $value, $deprecated = '', $autoload );

					break;

				case 'delete':
				case 'delete_option':

					$success = delete_option( $opt_name );

					break;

				case 'get':
				case 'get_option':

					$success = get_option( $opt_name, $default );

					break;

				case 'update':
				case 'update_option':

					$old_value = get_option( $opt_name, $default );

					$success = update_option( $opt_name, $value, $autoload );

					break;
			}

			$wp_filter  = $saved_filter;	// Restore filters.
			$wp_actions = $saved_actions;	// Restore actions.

			unset( $saved_filter, $saved_actions );

			switch( $action ) {

				case 'update':
				case 'update_option':

					switch( $opt_name ) {

						case 'home':

							do_action( 'sucom_update_option_' . $opt_name, $old_value, $value, $opt_name );

							break;
					}

					break;
			}

			return $success;
		}
	}
}
