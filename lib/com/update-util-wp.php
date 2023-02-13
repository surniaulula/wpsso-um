<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2023 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'SucomUpdateUtilWP' ) ) {

	class SucomUpdateUtilWP {

		/*
		 * Unfiltered version of home_url() from wordpress/wp-includes/link-template.php
		 *
		 * Last synchronized with WordPress v5.8.1 on 2021/10/15.
		 */
		public static function raw_home_url( $path = '', $scheme = null ) {

			return self::raw_get_home_url( null, $path, $scheme );
		}

		/*
		 * Unfiltered version of get_home_url() from wordpress/wp-includes/link-template.php
		 *
		 * Last synchronized with WordPress v5.8.1 on 2021/10/15.
		 */
		public static function raw_get_home_url( $blog_id = null, $path = '', $scheme = null ) {

			$is_multisite = is_multisite();

			if ( empty( $blog_id ) || ! $is_multisite ) {

				/*
				 * The WordPress _config_wp_home() function is hooked to the 'option_home' filter in order to
				 * override the database value. Since we're not using the default filters, check for WP_HOME or
				 * WP_SITEURL and update the stored database value if necessary.
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
				 */
				if ( ! $is_multisite && defined( 'WP_HOME' ) && WP_HOME ) {

					$url = untrailingslashit( WP_HOME );

					$db_url = self::raw_do_option( $action = 'get', $opt_name = 'home' );

					if ( $db_url !== $url ) {

						self::raw_do_option( $action = 'update', $opt_name = 'home', $url );
					}

				} else {

					$url = self::raw_do_option( $action = 'get', $opt_name = 'home' );
				}

			} else {

				switch_to_blog( $blog_id );

				$url = self::raw_do_option( $action = 'get', $opt_name = 'home' );

				restore_current_blog();
			}

			if ( ! in_array( $scheme, array( 'http', 'https', 'relative' ), $strict = true ) ) {

				if ( is_ssl() ) {

					$scheme = 'https';

				} else {

					$scheme = parse_url( $url, PHP_URL_SCHEME );
				}
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
		 * Last synchronized with WordPress v5.8.1 on 2021/10/15.
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

			if ( substr( $url, 0, 2 ) === '//' ) {

				$url = 'http:' . $url;
			}

			if ( 'relative' === $scheme ) {

				$url = ltrim( preg_replace( '#^\w+://[^/]*#', '', $url ) );

				if ( '' !== $url && '/' === $url[ 0 ] ) {

					$url = '/' . ltrim( $url, "/ \t\n\r\0\x0B" );
				}

			} else {

				$url = preg_replace( '#^\w+://#', $scheme . '://', $url );
			}

			return $url;
		}

		/*
		 * Temporarily disable filters and actions hooks before calling get_option(), update_option(), and delete_option().
		 */
		public static function raw_do_option( $action, $opt_name, $value = null, $default = false ) {

			global $wp_filter, $wp_actions;

			$saved_filter  = $wp_filter;
			$saved_actions = $wp_actions;

			$wp_filter  = array();
			$wp_actions = array();

			$success   = null;
			$old_value = false;

			switch( $action ) {

				case 'get':
				case 'get_option':

					$success = get_option( $opt_name, $default );

					break;

				case 'update':
				case 'update_option':

					$old_value = get_option( $opt_name, $default );

					$success = update_option( $opt_name, $value );

					break;

				case 'delete':
				case 'delete_option':

					$success = delete_option( $opt_name );

					break;
			}

			$wp_filter  = $saved_filter;
			$wp_actions = $saved_actions;

			unset( $saved_filter, $saved_actions );

			switch( $action ) {

				case 'update':
				case 'update_option':

					do_action( 'sucom_update_option_' . $opt_name, $old_value, $value, $opt_name );

					break;
			}

			return $success;
		}
	}
}
