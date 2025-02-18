<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2025 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'SucomPluginData' ) ) {

	require_once dirname( __FILE__ ) . '/plugin-data.php';
}

if ( ! class_exists( 'SucomPluginUpdate' ) ) {

	require_once dirname( __FILE__ ) . '/plugin-update.php';
}

if ( ! class_exists( 'SucomUpdateUtil' ) ) {

	require_once dirname( __FILE__ ) . '/update-util.php';
}

if ( ! class_exists( 'SucomUpdateUtilWP' ) ) {

	require_once dirname( __FILE__ ) . '/update-util-wp.php';
}

if ( class_exists( 'SucomUpdate' ) ) {

	die( 'Invalid SucomUpdate class object.' );

} else {

	class SucomUpdate {

		private $p;	// Plugin class object.
		private $a;	// Add-on class object.

		private $p_id          = '';
		private $p_slug        = '';
		private $p_text_domain = '';
		private $p_avail_csv   = '';
		private $p_cron_hook   = '';
		private $p_ucfg_name   = '';
		private $text_domain   = '';
		private $sched_hours   = 24;
		private $sched_name    = 'every24hours';

		private static $api_version = '7.1';
		private static $offer_updfn = 'offerup.txt';
		private static $pkg_sessfn  = 'session.txt';
		private static $umsg_cache  = null;
		private static $upd_config  = array();

		private static $http_error_codes = array(
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
		);

		public function __construct( &$plugin, &$addon, $ext_text_domain = '' ) {

			$this->p =& $plugin;
			$this->a =& $addon;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark( 'update manager constructor' );	// Begin timer.
			}

			if ( isset( $this->p->id ) ) {

				$this->p_id = $this->p->id;

				if ( isset( $this->p->cf[ 'plugin' ][ $this->p_id ][ 'slug' ] ) ) {	// Just in case.

					$this->p_slug        = $this->p->cf[ 'plugin' ][ $this->p_id ][ 'slug' ];
					$this->p_text_domain = $this->p->cf[ 'plugin' ][ $this->p_id ][ 'text_domain' ];
					$this->p_cron_hook   = $this->p_id . '_um_check';
					$this->p_ucfg_name   = $this->p_id . '_um_config';
					$this->text_domain   = $ext_text_domain;

					if ( isset( $this->p->avail ) && is_array( $this->p->avail ) ) {	// Just in case.

						/*
						 * Returns an imploded string of active modules.
						 */
						$this->p_avail_csv = SucomUpdateUtil::encode_avail( $this->p->avail );
					}

					/*
					 * Support the "Check Again" feature on the WordPress Dashboard > Updates page.
					 */
					if ( get_current_user_id() && 
						false !== strpos( $_SERVER[ 'REQUEST_URI' ], '/update-core.php?force-check=1' ) && 
							false === strpos( $_SERVER[ 'REQUEST_URI' ], $this->p_id . '-check-done=1' ) ) {

						/*
						 * When $quiet is false the following notices may be shown:
						 *
						 *	- Please note that one or more non-stable / development Update Version Filters have been selected.
						 */
						$this->refresh_upd_config( $quiet = false );

						$this->add_wp_callbacks();

						$this->manual_update_check();

						/*
						 * Prevent a second check when reloading the '/update-core.php?force-check=1' page.
						 */
						$_SERVER[ 'REQUEST_URI' ] = add_query_arg( array( $this->p_id . '-check-done' => 1 ) );

					} else {

						$this->set_upd_config();

						$this->add_wp_callbacks();
					}

				} elseif ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'config plugin slug not found' );
				}

			} elseif ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'plugin id property not defined' );
			}

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark( 'update manager constructor' );	// End timer.
			}
		}

		public function clear_upd_config() {

			if ( ! empty( $this->p_ucfg_name ) ) {	// Just in case.

				delete_option( $this->p_ucfg_name );
			}
		}

		/*
		 * When $quiet is false the following notices may be shown:
		 *
		 *	- Please note that one or more non-stable / development Update Version Filters have been selected.
		 *
		 * See SucomUpdate->__construct().
		 * See SucomUpdate->add_wp_cron_callbacks().
		 * See SucomUpdate->wp_site_option_changed().
		 * See WpssoUmActions->action_load_settings_page_create_offers().
		 * See WpssoUmFiltersOptions->filter_save_settings_options().
		 */
		public function refresh_upd_config( $quiet = true ) {	// $quiet = true for cron job.

			return $this->set_upd_config( $quiet, $read_cache = false );
		}

		/*
		 * When $quiet is false the following notices may be shown:
		 *
		 *	- Please note that one or more non-stable / development Update Version Filters have been selected.
		 *
		 * See SucomUpdate->__construct().
		 * See SucomUpdate->refresh_upd_config().
		 */
		private function set_upd_config( $quiet = false, $read_cache = true ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark( 'update manager config' );	// Begin timer.
			}

			if ( $read_cache ) {

				if ( ! empty( $this->p_ucfg_name ) ) {	// Just in case.

					self::$upd_config = get_option( $this->p_ucfg_name, $default_value = false );	// Option is autoloaded.

					if ( is_array( self::$upd_config ) ) {	// Just in case.

						if ( $this->p->debug->enabled ) {

							$this->p->debug->log( 'update manager config from option' );

							$this->p->debug->mark( 'update manager config' );	// End timer.
						}

						return;
					}
				}
			}

			self::$upd_config = array();	// Init a new config array.

			$has_dev_filter = false;	// Assume we're using the production version filter by default.

			foreach ( $this->p->cf[ 'plugin' ] as $ext => $info ) {

				/*
				 * Prefer a 'urls' array key instead of 'url'.
				 */
				if ( ! empty( $info[ 'url' ] ) ) {

					if ( empty( $info[ 'urls' ] ) ) {

						$info[ 'urls' ] = $info[ 'url' ];
					}

					unset( $info[ 'url' ] );
				}

				if ( empty( $info[ 'urls' ][ 'update' ] ) ) {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: skipped - missing update url' );
					}

					continue;
				}

				foreach ( array( 'name', 'short', 'slug', 'base' ) as $key ) {

					if ( empty( $info[ $key ] ) ) {

						if ( $this->p->debug->enabled ) {

							$this->p->debug->log( $ext . ' plugin: skipped - missing ' . $key );
						}

						continue 2;
					}
				}

				/*
				 * Saved as the 'plugin_status' value.
				 */
				$ext_status = $this->get_ext_status( $ext, $read_cache );	// Uses a local cache.

				/*
				 * Saved as the 'plugin_version' value.
				 */
				$ext_version = $this->get_ext_version( $ext, $read_cache );	// Uses a local cache.

				if ( false === $ext_version ) {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: skipped - version is false' );
					}

					continue;
				}

				/*
				 * Saved as the 'version_filter' value.
				 */
				$filter_name = false !== strpos( $ext_version, 'not-installed' ) ? 'stable' : $this->get_ext_filter_name( $ext );

				if ( 'stable' !== $filter_name ) {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: non-stable filter found' );
					}

					$has_dev_filter = true;
				}

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: installed version is ' . $ext_version . ' with ' . $filter_name . ' version filter' );
				}

				/*
				 * Translate the plugin name for notification mesages.
				 */
				$name_transl = _x( $info[ 'name' ], 'plugin name', $this->p_text_domain );

				$name_transl_link  = empty( $info[ 'urls' ][ 'home' ] ) ? $name_transl :
					'<a href="' . $info[ 'urls' ][ 'home' ] . '">' . $name_transl . '</a>';

				/*
			 	 * Define some standard error messages for consistency checks.
				 */
				$inconsistency_msg = sprintf( __( 'An inconsistency was found in the %1$s update server information - ',
					$this->text_domain ), $name_transl_link );

				$update_disabled_msg = sprintf( __( 'Update checks for %1$s are disabled while this inconsistency persists.',
					$this->text_domain ), $info[ 'short' ] );

				$update_disabled_msg .= empty( $info[ 'urls' ][ 'support' ] ) ? '' : ' ' .
					sprintf( __( 'You may <a href="%1$s">open a new support ticket</a> if you believe this error message is incorrect.',
						$this->text_domain ), $info[ 'urls' ][ 'support' ] );

				/*
				 * Add query arguments to the update URL.
				 */
				global $wp_version;	// Defined by ABSPATH . WPINC . '/version.php'.

				$ext_auth_type = $this->get_ext_auth_type( $ext );
				$ext_auth_id   = $this->get_ext_auth_id( $ext );
				$ext_pkg_sess  = self::get_ext_file_content( $ext, self::$pkg_sessfn );
				$json_args     = array( 'api_version' => self::$api_version );

				if ( 'none' !== $ext_auth_type ) $json_args[ $ext_auth_type ] = $ext_auth_id;

				if ( ! empty( $ext_pkg_sess ) ) $json_args[ 'pkg_sess' ] = $ext_pkg_sess;

				$json_args[ 'plugin_status' ]  = $ext_status;
				$json_args[ 'plugin_version' ] = $ext_version;
				$json_args[ 'version_filter' ] = $filter_name;
				$json_args[ 'user_direction' ] = is_rtl() ? 'rtl' : 'ltr';
				$json_args[ 'user_locale' ]    = is_admin() ? get_user_locale() : get_locale();
				$json_args[ 'php_version' ]    = phpversion();
				$json_args[ 'wp_version' ]     = $wp_version;

				if ( method_exists( $this->a, 'get_ext' ) ) {	// Just in case.

					if ( $ext === $this->a->get_ext() ) {	// Only add for the update manager.

						if ( defined( 'WC_VERSION' ) ) {

							$json_args[ 'wc_version' ] = WC_VERSION;
						}

						$json_args[ 'plugin_avail' ] = $this->p_avail_csv;
					}
				}

				/*
				 * Create the json query URL using the json args array.
				 */
				$json_url = SucomUpdateUtil::decode_url_add_query( $info[ 'urls' ][ 'update' ], $json_args );

				if ( false === filter_var( $json_url, FILTER_VALIDATE_URL ) ) {	// Check for invalid URL.

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: invalid update url "' . $json_url . '"' );
					}

					$error_msg = $inconsistency_msg . sprintf( __( 'invalid update URL (%1$s).', $this->text_domain ), $json_url ) .
						' ' . $update_disabled_msg;

					self::set_umsg( $ext, 'err', $error_msg );

					continue;
				}

				self::$upd_config[ $ext ] = array(
					'name'             => $info[ 'name' ],
					'name_transl'      => $name_transl,
					'name_transl_link' => $name_transl_link,
					'short'            => $info[ 'short' ],
					'slug'             => $info[ 'slug' ],				// Example: wpsso.
					'base'             => $info[ 'base' ],				// Example: wpsso/wpsso.php.
					'api_version'      => self::$api_version,
					'auth_type'        => $ext_auth_type,
					'auth_id'          => $ext_auth_id,
					'pkg_sess'         => $ext_pkg_sess,
					'plugin_status'    => $ext_status,
					'plugin_version'   => $ext_version,
					'version_filter'   => $filter_name,
					'hosts'            => empty( $info[ 'hosts' ] ) ? array() : $info[ 'hosts' ],
					'external'         => isset( $info[ 'hosts' ][ 'wp_org' ] ) && empty( $info[ 'hosts' ][ 'wp_org' ] ) ? true : false,
					'urls'             => empty( $info[ 'urls' ] ) ? array() : $info[ 'urls' ],
					'data_json_url'    => $json_url,
					'data_expire'      => 86100,					// Plugin data expiration (almost 24 hours).
					'option_name'      => 'external_update-' . $info[ 'slug' ],	// Example: external_update-wpsso.
				);

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: update info configured (auth_type is ' . $ext_auth_type . ')' );
				}
			}

			if ( $has_dev_filter ) {

				$user_id    = get_current_user_id();
				$notice_key = 'non-stable-update-version-filters-selected';

				if ( ! $quiet && $user_id && $this->p->notice->is_admin_pre_notices( $notice_key, $user_id ) ) {

					$um_metabox_title = _x( 'Update Version Filters', 'metabox title', $this->text_domain );

					$um_general_page_link = $this->p->util->get_admin_url( 'um-general', $um_metabox_title );

					$notice_msg = sprintf( __( 'Please note that one or more non-stable / development %s have been selected.',
						$this->text_domain ), $um_general_page_link );

					$dismiss_time = MONTH_IN_SECONDS;

					$this->p->notice->warn( $notice_msg, $user_id, $notice_key, $dismiss_time );
				}
			}

			if ( ! empty( $this->p_ucfg_name ) ) {	// Just in case.

				update_option( $this->p_ucfg_name, self::$upd_config, $autoload = true );
			}

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark( 'update manager config' );	// End timer.
			}

			return;
		}

		private function add_wp_callbacks() {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			/*
			 * Refresh the update manager config and the plugin update data when the WordPress "home" option is changed.
			 *
			 * See WpssAdmin->wp_site_option_changed().
			 * See SucomUpdate->wp_site_option_changed().
			 * See SucomUtilWP->raw_do_option().
			 * See SucomUpdateUtilWP->raw_do_option().
			 */
			add_action( 'update_option_home', array( $this, 'wp_site_option_changed' ), PHP_INT_MAX - 100, 3 );
			add_action( 'sucom_update_option_home', array( $this, 'wp_site_option_changed' ), PHP_INT_MAX - 100, 3 );

			add_filter( 'http_headers_useragent', array( $this, 'maybe_update_wpua' ), PHP_INT_MAX, 1 );
			add_filter( 'http_request_host_is_external', array( $this, 'allow_update_package' ), PHP_INT_MAX, 3 );

			/*
			 * Provide plugin data from the json api for add-ons not hosted on wordpress.org.
			 */
			add_filter( 'plugins_api_result', array( $this, 'external_plugin_data' ), PHP_INT_MAX, 3 );

			/*
			 * Called by get_transient().
			 *
			 * Hook 'pre_transient_update_plugins' to check if the WordPress update system has been disabled and/or
			 * manipulated, and if so, then reenable plugin updates by including our update data (if a new plugin
			 * version is available).
			 */
			add_filter( 'pre_transient_update_plugins', array( $this, 'reenable_plugin_updates' ), PHP_INT_MAX, 1 );
			add_filter( 'transient_update_plugins', array( $this, 'maybe_add_plugin_update' ), PHP_INT_MAX, 1 );

			/*
			 * Called by get_site_transient().
			 *
			 * Hook 'pre_site_transient_update_plugins' to check if the WordPress update system has been disabled
			 * and/or manipulated, and if so, then reenable plugin updates by including our update data (if a new
			 * plugin version is available).
			 */
			add_filter( 'pre_site_transient_update_plugins', array( $this, 'reenable_plugin_updates' ), PHP_INT_MAX, 1 );
			add_filter( 'site_transient_update_plugins', array( $this, 'maybe_add_plugin_update' ), PHP_INT_MAX, 1 );

			$this->add_wp_cron_callbacks();
		}

		private function add_wp_cron_callbacks() {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( empty( $this->p_cron_hook ) ) {	// Just in case.

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'invalid cron hook name' );
				}

				return;
			}

			add_action( $this->p_cron_hook, array( $this, 'refresh_upd_config' ) );

			add_action( $this->p_cron_hook, array( $this, 'quiet_update_check' ) );

			add_filter( 'cron_schedules', array( $this, 'add_custom_schedule_name' ) );

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'validating ' . $this->p_cron_hook . ' schedule ' . $this->sched_name );
			}

			$is_scheduled  = false;
			$hook_schedule = wp_get_schedule( $this->p_cron_hook );

			if ( empty( $hook_schedule ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'schedule for ' . $this->p_cron_hook . ' not found' );
				}

			} elseif ( $hook_schedule !== $this->sched_name ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'changing ' . $this->p_cron_hook . ' schedule from ' . $hook_schedule . ' to ' . $this->sched_name );
				}

				wp_clear_scheduled_hook( $this->p_cron_hook );

			} elseif ( $ts = wp_next_scheduled( $this->p_cron_hook ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $this->p_cron_hook . ' next scheduled for ' . gmdate( 'c', $ts ) );
				}

				$is_scheduled = true;
			}

			if ( ! $is_scheduled ) {

				if ( defined( 'WP_INSTALLING' ) ) {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( 'wp is instaling' );
					}

				} else {

					$this->clear_sched_hooks();	// Just in case.

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( 'scheduling ' . $this->p_cron_hook . ' for ' . $this->sched_name );
					}

					wp_schedule_event( time(), $this->sched_name, $this->p_cron_hook );
				}
			}

			return;
		}

		public function add_custom_schedule_name( $schedules ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$schedules[ $this->sched_name ] = array(
				'interval' => $this->sched_hours * HOUR_IN_SECONDS,
				'display'  => sprintf( 'Every %d hours', $this->sched_hours )
			);

			return $schedules;
		}

		public function clear_sched_hooks() {

			$cron = _get_cron_array();

			if ( ! is_array( $cron ) ) return;	// Just in case.

			foreach ( array( 'wpsso_um_', 'wpsso_update_manager_' ) as $hook_prefix ) {

				foreach ( $cron as $event ) {

					if ( is_array( $event ) ) {

						foreach ( $event as $hook_name => $arr ) {

							if ( 0 === strpos( $hook_name, $hook_prefix ) ) {

								if ( $this->p->debug->enabled ) {

									$this->p->debug->log( 'removing ' . $hook_name . ' from schedule' );
								}

								wp_clear_scheduled_hook( $hook_name );
							}
						}
					}
				}
			}
		}

		/*
		 * Called when the SSO > Tools > Check for Plugin Updates or WordPress Dashboard > Updates > Check Again buttons are used.
		 *
		 * See SucomUpdate->__construct().
		 * See WpssoUmActions->action_load_settings_page_check_for_updates().
		 */
		public function manual_update_check() {

			$this->check_all_for_updates( $quiet = false );	// Throttled.
		}

		/*
		 * Called by the scheduled cron job.
		 */
		public function quiet_update_check() {

			$this->check_all_for_updates( $quiet = true );	// Throttled.
		}

		/*
		 * Called by both the scheduled cron job and manual update check buttons.
		 *
		 * This method is throttled and will only execute once every 5 minutes.
		 *
		 * When $quiet is false the following notice may be shown:
		 *
		 *	- Update manager cache refresh ignored. Please wait a few more minutes before requesting another refresh.
		 *
		 * See SucomUpdate->manual_update_check().
		 * See SucomUpdate->quiet_update_check().
		 * See SucomUpdate->wp_site_option_changed().
		 */
		public function check_all_for_updates( $quiet = false ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$cache_md5_pre  = $this->p_id . '_!_';
			$cache_exp_secs = 300;	// Throttle executions to one per 5 minutes.
			$cache_salt     = __METHOD__;
			$cache_id       = $cache_md5_pre . md5( $cache_salt );	// Last update time transient key (40 characters maximum).
			$last_time      = get_transient( $cache_id );		// Last update time, returns false if transient expired or does not exist.

			if ( is_numeric( $last_time ) && time() < $last_time + $cache_exp_secs ) {	// Throttle mins not yet exceeded.

				$user_id         = get_current_user_id();
				$human_cache_exp = human_time_diff( 0, $cache_exp_secs );
				$human_last_time = human_time_diff( $last_time );
				$human_wait_time = human_time_diff( $last_time, $last_time + $cache_exp_secs );

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'exiting early: ' . $human_last_time . ' since last refresh (minimum is ' . $human_cache_exp . ')' );
				}

				if ( ! $quiet && $user_id ) {

					$notice_msg = __( 'Update manager cache refresh ignored.', $this->text_domain ) . ' ';

					$notice_msg .= sprintf( __( 'It has been %s since the last refresh.', $this->text_domain ), $human_last_time ) . ' ';

					$notice_msg .= sprintf( __( 'Please wait %s before requesting another cache refresh.', $this->text_domain ), $human_wait_time ) . ' ';

					$notice_key = __FUNCTION__ . '_throttling';

					$this->p->notice->warn( $notice_msg, $user_id, $notice_key );
				}

				return;
			}

			set_transient( $cache_id, time(), $cache_exp_secs );	// Prevent another execution within the next 5 minutes.

			$this->check_ext_for_updates( $check_ext = null, $quiet );
		}

		/*
		 * Note that this method does not throttle like SucomUpdate->check_all_for_updates().
		 *
		 * When $quiet is false the following notices may be shown:
		 *
		 *	- No plugins defined for updates.
		 *	- Update information for %s has been retrieved and saved.
		 *	- An error was returned while getting update information for %s.
		 *	- Failed saving retrieved update information for %s.
		 *
		 * See SucomUpdate->check_all_for_updates().
		 * See WpssoUmFiltersOptions->filter_save_settings_options().
		 */
		public function check_ext_for_updates( $check_ext = null, $quiet = false ) {

			$user_id = get_current_user_id();

			$ext_upd_cfg = array();

			if ( null === $check_ext ) {

				$ext_upd_cfg = self::$upd_config;	// Check all plugins defined.

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'checking all known plugins for updates' );
				}

			} elseif ( is_array( $check_ext ) ) {

				foreach ( $check_ext as $ext ) {

					if ( isset( self::$upd_config[ $ext ] ) ) {

						$ext_upd_cfg[ $ext ] = self::$upd_config[ $ext ];
					}
				}

			} elseif ( is_string( $check_ext ) ) {

				if ( isset( self::$upd_config[ $check_ext ] ) ) {

					$ext_upd_cfg[ $check_ext ] = self::$upd_config[ $check_ext ];
				}
			}

			if ( empty( $ext_upd_cfg ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'exiting early: no plugins to check for updates' );
				}

				if ( ! $quiet && $user_id ) {

					$notice_key = __FUNCTION__ . '_no_plugins_defined';

					$this->p->notice->err( __( 'No plugins defined for updates.', $this->text_domain ), $user_id, $notice_key );
				}

				return;
			}

			foreach ( $ext_upd_cfg as $ext => $upd_cfg_info ) {

				if ( ! self::is_installed( $ext ) ) {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: not installed' );
					}

					continue;
				}

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: checking for update' );
				}

				$update_data                 = new StdClass;
				$update_data->lastCheck      = time();	// Number of seconds since the Unix Epoch (January 1 1970 00:00:00 GMT).
				$update_data->checkedVersion = $upd_cfg_info[ 'plugin_version' ];
				$update_data->update         = $this->get_update_data( $ext, $read_cache = false );

				if ( self::update_option_data( $ext, $update_data ) ) {

					if ( empty( self::$upd_config[ $ext ][ 'uerr' ] ) ) {

						if ( $this->p->debug->enabled ) {

							$this->p->debug->log( $ext . ' plugin: update information saved in ' . $upd_cfg_info[ 'option_name' ] );
						}

						if ( ! empty( self::$upd_config[ $ext ][ 'plugin_data' ] ) ) {

							if ( ! $quiet && $user_id ) {

								$notice_key = __FUNCTION__ . '_' . $ext . '_' . $upd_cfg_info[ 'option_name' ] . '_success';

								$this->p->notice->inf( sprintf( __( 'Update information for %s has been retrieved and saved.',
									$this->text_domain ), $upd_cfg_info[ 'name_transl_link' ] ), $user_id, $notice_key );
							}
						}

					} else {

						if ( $this->p->debug->enabled ) {

							$this->p->debug->log( $ext . ' plugin: error returned getting update information' );
						}

						if ( ! $quiet && $user_id ) {

							$notice_key = __FUNCTION__ . '_' . $ext . '_' . $upd_cfg_info[ 'option_name' ] . '_error_returned';

							$this->p->notice->warn( sprintf( __( 'An error was returned while getting update information for %s.',
								$this->text_domain ), $upd_cfg_info[ 'name_transl_link' ] ), $user_id, $notice_key );
						}
					}

				} else {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: failed saving update information in ' . $upd_cfg_info[ 'option_name' ] );
					}

					if ( ! $quiet && $user_id ) {

						$notice_key = __FUNCTION__ . '_' . $ext . '_' . $upd_cfg_info[ 'option_name' ] . '_failed_saving';

						$this->p->notice->err( sprintf( __( 'Failed saving retrieved update information for %s.',
							$this->text_domain ), $upd_cfg_info[ 'name_transl_link' ] ), $user_id, $notice_key );
					}
				}
			}
		}

		/*
		 * Refresh the update manager config and the plugin update data when the WordPress "home" option is changed.
		 *
		 * See WpssAdmin->wp_site_option_changed().
		 * See SucomUpdate->wp_site_option_changed().
		 * See SucomUtilWP->raw_do_option().
		 * See SucomUpdateUtilWP->raw_do_option().
		 */
		public function wp_site_option_changed( $old_value, $new_value, $option_name ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			static $do_once = null;

			if ( $do_once ) return;	// Stop here.

			$do_once = true;

			/*
			 * Standardize old and new values for string comparison.
			 */
			$old_value = strtolower( untrailingslashit( maybe_serialize( $old_value ) ) );
			$new_value = strtolower( untrailingslashit( maybe_serialize( $new_value ) ) );

			if ( $old_value !== $new_value ) {

				/*
				 * When $quiet is false the following notices may be shown:
				 *
				 *	- Please note that one or more non-stable / development Update Version Filters have been selected.
				 */
				$this->refresh_upd_config( $quiet = true );

				$this->check_all_for_updates( $quiet = true );	// Throttled.
			}
		}

		private function check_pp( $ext = '', $li = true, $rv = true, $read_cache = true ) {

			if ( isset( $this->p->check ) ) {

				if ( method_exists( $this->p->check, 'pp' ) ) {

					return $this->p->check->pp( $ext, $li, $rv, $read_cache );
				}
			}

			return false;
		}

		public function allow_update_package( $is_allowed, $ip, $url ) {

			if ( $is_allowed ) {	// Already allowed.

				return $is_allowed;
			}

			foreach ( self::$upd_config as $ext => $upd_cfg_info ) {

				if ( ! empty( $upd_cfg_info[ 'response' ]->package ) && $url === $upd_cfg_info[ 'response' ]->package ) {

					return true;
				}
			}

			return $is_allowed;
		}

		/*
		 * A filter for 'http_headers_useragent' to make sure we have a standard WordPress useragent string. The
		 * 'http_headers_useragent' filter hook offers two arguments, but only since WP v5.1.0, so require one argument to
		 * stay backwards compatible with older WP versions.
		 */
		public function maybe_update_wpua( $wpua ) {

			global $wp_version;	// Defined by ABSPATH . WPINC . '/version.php'.

			$correct_wpua = 'WordPress/' . $wp_version . '; ' . SucomUpdateUtilWP::raw_get_home_url();

			if ( $correct_wpua !== $wpua ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'incorrect wordpress id: ' . $wpua );
				}

				return $correct_wpua;
			}

			return $wpua;
		}

		/*
		 * Provide plugin data from the json api for add-ons not hosted on wordpress.org.
		 */
		public function external_plugin_data( $result, $action = null, $args = null ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( 'plugin_information' !== $action ) {	// This filter only provides plugin data.

				return $result;

			} elseif ( empty( $args->slug ) ) {	// Make sure we have a slug in the request.

				return $result;

			} elseif ( ! empty( $args->unfiltered ) ) {	// Flag for the update manager filter.

				return $result;

			} elseif ( empty( $this->p->cf[ '*' ][ 'slug' ][ $args->slug ] ) ) {	// Make sure the plugin slug is one of ours.

				return $result;
			}

			$ext = $this->p->cf[ '*' ][ 'slug' ][ $args->slug ];	// Get the plugin acronym to read its config.

			if ( empty( self::$upd_config[ $ext ][ 'slug' ] ) ) {	// Make sure we have an update config for acronym.

				return $result;
			}

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'getting plugin data for ' . $ext );
			}

			$plugin_data = $this->get_plugin_data( $ext, $read_cache = true );	// Get plugin data from the json api.

			if ( ! is_object( $plugin_data ) || ! method_exists( $plugin_data, 'json_to_wp' ) ) {

				return $result;
			}

			$result = $plugin_data->json_to_wp();

			if ( ! empty( self::$upd_config[ $ext ][ 'external' ] ) ) {

				$result->external = true;
			}

			return $result;
		}

		/*
		 * If the WordPress update system has been disabled and/or manipulated (ie. $pre is not false), then reenable
		 * updates by including our update data (if a new plugin version is available).
		 */
		public function reenable_plugin_updates( $pre = false ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( false === $pre ) {	// Default value.

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'exiting early: $pre is false (default value)' );
				}

				return $pre;
			}

			return $this->maybe_add_plugin_update( $pre );
		}

		/*
		 * $transient can be false or stdClass object.
		 */
		public function maybe_add_plugin_update( $transient = false ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark( 'maybe add plugin and add-on update(s) to transient' );	// Begin timer.
			}

			foreach ( self::$upd_config as $ext => $upd_cfg_info ) {

				/*
				 * Check to see if we can use the update information from wp.org (ie. free / standard plugins
				 * hosted on wp.org that use the stable version filter).
				 */
				if ( $this->prefer_wp_org_update( $ext ) ) {	// Uses a local cache.

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: prefer update information from wp.org' );
					}

					continue;	// Get the next plugin from the config.
				}

				/*
				 * Check the static cache first.
				 */
				if ( isset( self::$upd_config[ $ext ][ 'response' ] ) ) {	// False or update object.

					/*
					 * Installed version is older than the update version.
					 */
					if ( ! empty( self::$upd_config[ $ext ][ 'response' ] ) ) {

						if ( $this->p->debug->enabled ) {

							$this->p->debug->log( $ext . ' plugin: using static cache response data' );
						}

						$transient = $this->update_transient_response( $ext, $transient );

					/*
					 * Installed version is current or newer than the update version.
					 */
					} elseif ( ! empty( self::$upd_config[ $ext ][ 'no_update' ] ) ) {

						if ( $this->p->debug->enabled ) {

							$this->p->debug->log( $ext . ' plugin: using static cache no_update data' );
						}

						$transient = $this->update_transient_no_update( $ext, $transient );

					} elseif ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: static cache data is false' );
					}

					continue;	// Get the next plugin from the config.
				}

				if ( ! self::is_installed( $ext ) ) {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: not installed' );
					}

					continue;	// Get the next plugin from the config.
				}

				self::$upd_config[ $ext ][ 'response' ] = false;

				$update_data = self::get_option_data( $ext );

				if ( empty( $update_data ) ) {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: update data is empty' );
					}

				} elseif ( empty( $update_data->update ) ) {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: missing update property' );
					}

				} elseif ( ! is_object( $update_data->update ) ) {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: update property not an object' );
					}

				} elseif ( version_compare( self::$upd_config[ $ext ][ 'plugin_version' ], $update_data->update->version, '<' ) ) {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: installed version older (' . self::$upd_config[ $ext ][ 'plugin_version' ] .
							' vs ' . $update_data->update->version . ')' );
					}

					/*
					 * Update the static cache.
					 */
					self::$upd_config[ $ext ][ 'response' ] = $update_data->update->json_to_wp();

					$transient = $this->update_transient_response( $ext, $transient );

				} else {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: installed version current or newer' );
					}

					/*
					 * Update the static cache for 'no_update' data since WordPress v5.5.
					 */
					self::$upd_config[ $ext ][ 'no_update' ] = $update_data->update->json_to_wp();

					$transient = $this->update_transient_no_update( $ext, $transient );
				}
			}

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark( 'maybe add plugin and add-on update(s) to transient' );	// End timer.
			}

			return $transient;
		}

		private function update_transient_response( $ext, $transient ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			return $this->update_transient_data( $ext, $transient, $prop_name = 'response', $un_prop_name = 'no_update' );
		}

		private function update_transient_no_update( $ext, $transient ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			return $this->update_transient_data( $ext, $transient, $prop_name = 'no_update', $un_prop_name = 'response' );
		}

		private function update_transient_data( $ext, $transient, $prop_name, $un_prop_name ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( empty( self::$upd_config[ $ext ][ 'base' ] ) ) {	// Make sure we have a valid config.

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: update config missing plugin base' );
				}

				return $transient;
			}

			$base = self::$upd_config[ $ext ][ 'base' ];

			if ( is_object( $transient ) && isset( $transient->$prop_name[ $base ] ) ) {	// Avoid a "modify non-object" error.

				unset( $transient->$prop_name[ $base ] );	// Remove potentially invalid update information.
			}

			if ( empty( self::$upd_config[ $ext ][ $prop_name ] ) ) {	// No update information.

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: update config ' . $prop_name . ' is empty' );
				}

				return $transient;
			}

			$update_obj =& self::$upd_config[ $ext ][ $prop_name ];	// Shortcut variable name.

			if ( empty( $update_obj->plugin ) ) {	// Example: wpsso/wpsso.php

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: update object is incomplete' );
				}

				return $transient;

			} elseif ( $base !== $update_obj->plugin ) {	// Example: wpsso/wpsso.php

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: base mismatch (' . $base. ' vs ' . $update_obj->plugin . ')' );
				}

				return $transient;
			}

			if ( ! is_object( $transient ) ) {

				$transient = new stdClass;

				$transient->last_checked = time();	// Number of seconds since the Unix Epoch (January 1 1970 00:00:00 GMT).

				$transient->checked = array();
			}

			if ( isset( $transient->checked[ $base ] ) ) {

				unset( $transient->checked[ $base ] );
			}

			$transient->checked[ $base ] = self::$upd_config[ $ext ][ 'plugin_version' ];

			$transient->$prop_name[ $base ] = $update_obj;

			if ( isset( $transient->$un_prop_name[ $base ] ) ) {	// Avoid a "modify non-object" error.

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: unsetting ' . $un_prop_name . ' object property' );
				}

				unset( $transient->$un_prop_name[ $base ] );
			}

			return $transient;
		}

		public function get_update_data( $ext, $read_cache = true ) {

			/*
			 * Get plugin data from the json api.
			 */
			$plugin_data = $this->get_plugin_data( $ext, $read_cache );

			if ( ! is_object( $plugin_data ) || ! method_exists( $plugin_data, 'json_to_wp' ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: returned update data is invalid' );
				}

				return null;
			}

			return SucomPluginUpdate::update_from_data( $plugin_data );
		}

		/*
		 * Get plugin data from the json api.
		 */
		public function get_plugin_data( $ext, $read_cache = true ) {

			if ( empty( self::$upd_config[ $ext ][ 'slug' ] ) ) {	// Make sure we have a valid config.

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: exiting early - missing slug' );
				}

				return $plugin_data = null;

			} elseif ( empty( self::$upd_config[ $ext ][ 'data_json_url' ] ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: exiting early - update json_url is empty' );
				}

				return $plugin_data = null;
			}

			$home_url      = SucomUpdateUtilWP::raw_get_home_url();
			$json_url      = self::$upd_config[ $ext ][ 'data_json_url' ];
			$cache_md5_pre = $this->p_id . '_!_';
			$cache_salt    = __METHOD__ . '(home_url:' . $home_url . '_json_url:' . $json_url . ')';
			$cache_id      = $cache_md5_pre . md5( $cache_salt );	// Plugin data transient key (40 characters maximum).

			if ( $this->prefer_wp_org_update( $ext, $read_cache ) ) {	// Uses a local cache.

				return $plugin_data = null;	// Stop here.
			}

			if ( $read_cache ) {

				/*
				 * Check static cache first, then check the transient cache.
				 */
				if ( isset( self::$upd_config[ $ext ][ 'plugin_data' ]->plugin ) ) {

					$plugin_data = self::$upd_config[ $ext ][ 'plugin_data' ];

				} else $plugin_data = self::$upd_config[ $ext ][ 'plugin_data' ] = get_transient( $cache_id );

				if ( false !== $plugin_data ) {	// False if transient is expired or not found.

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: returning plugin data from cache' );
					}

					return $plugin_data;
				}

			} else delete_transient( $cache_id );

			$plugin_data = null;

			/*
			 * Define some standard error messages for consistency checks.
			 */
			$inconsistency_msg = sprintf( __( 'An inconsistency was found in the %1$s update server information - ',
				$this->text_domain ), self::$upd_config[ $ext ][ 'name_transl_link' ] );

			$update_disabled_msg = sprintf( __( 'Update checks for %1$s are disabled while this inconsistency persists.',
				$this->text_domain ), self::$upd_config[ $ext ][ 'short' ] );

			$update_disabled_msg .= empty( self::$upd_config[ $ext ][ 'urls' ][ 'support' ] ) ? '' : ' ' .
				sprintf( __( 'You may <a href="%1$s">open a new support ticket</a> if you believe this error message is incorrect.',
					$this->text_domain ), self::$upd_config[ $ext ][ 'urls' ][ 'support' ] );

			/*
			 * Check the local resolver and DNS IPv4 values for inconsistencies.
			 */
			$json_host = wp_parse_url( $json_url, PHP_URL_HOST );

			if ( empty( $json_host ) || $json_host === $json_url ) {	// Check for false or original URL.

				$error_msg = $inconsistency_msg . sprintf( __( 'the update server URL (%1$s) does not appear to be a valid URL.',
					$this->text_domain ), $json_url ) . ' ' . $update_disabled_msg;

				self::set_umsg( $ext, 'err', $error_msg );

				self::$upd_config[ $ext ][ 'plugin_data' ] = $plugin_data;

				set_transient( $cache_id, new stdClass, self::$upd_config[ $ext ][ 'data_expire' ] );

				return $plugin_data;	// Returns null.
			}

			static $host_cache = array();	// Local cache to lookup the host ip only once.

			if ( ! isset( $host_cache[ $json_host ][ 'ip' ] ) ) {

				$host_cache[ $json_host ][ 'ip' ] = gethostbyname( $json_host );	// Returns an IPv4 address, or the hostname on failure.

				if ( $host_cache[ $json_host ][ 'ip' ] === $json_host ) {

					$host_cache[ $json_host ][ 'ip' ] = 'ERROR';
				}
			}

			if ( ! isset( $host_cache[ $json_host ][ 'a' ] ) ) {

				$dns_rec = dns_get_record( $json_host . '.', DNS_A );	// Returns an array of associative arrays.

				$host_cache[ $json_host ][ 'a' ] = empty( $dns_rec[ 0 ][ 'ip' ] ) ? 'ERROR' : $dns_rec[ 0 ][ 'ip' ];
			}

			if ( 'ERROR' === $host_cache[ $json_host ][ 'ip' ] || 'ERROR' === $host_cache[ $json_host ][ 'a' ] ||
				$host_cache[ $json_host ][ 'ip' ] !== $host_cache[ $json_host ][ 'a' ] ) {

				$error_msg = $inconsistency_msg;

				if ( 'ERROR' === $host_cache[ $json_host ][ 'ip' ] || 'ERROR' === $host_cache[ $json_host ][ 'a' ] ) {

					if ( 'ERROR' === $host_cache[ $json_host ][ 'ip' ] ) {

						$func_name = 'gethostbyname()';
						$func_url  = 'https://www.php.net/manual/en/function.gethostbyname.php';

					} else {

						$func_name = 'dns_get_record()';
						$func_url  = 'https://www.php.net/manual/en/function.dns-get-record.php';
					}

					$error_msg .= sprintf( __( 'the <a href="%1$s">PHP %2$s function</a> did not return an IPv4 address.', $this->text_domain ),
						$func_url, $func_name ) . ' ';

					$error_msg .= __( 'Please contact your hosting provider to have this PHP issue fixed.', $this->text_domain ) . ' ';

				} else {

					$error_msg .= sprintf( __( 'the IPv4 address (%1$s) from the local host does not match the DNS IPv4 address (%2$s).',
						$this->text_domain ), $host_cache[ $json_host ][ 'ip' ], $host_cache[ $json_host ][ 'a' ] ) . ' ';
				}

				$error_msg .= $update_disabled_msg;

				self::set_umsg( $ext, 'err', $error_msg );

				self::$upd_config[ $ext ][ 'plugin_data' ] = $plugin_data;

				set_transient( $cache_id, new stdClass, self::$upd_config[ $ext ][ 'data_expire' ] );

				return $plugin_data;	// Returns null.
			}

			/*
			 * Set wp_remote_get() options.
			 */
			global $wp_version;	// Defined by ABSPATH . WPINC . '/version.php'.

			$ua_wpid = 'WordPress/' . $wp_version . ' (' .
				self::$upd_config[ $ext ][ 'slug' ] . '/' .
				self::$upd_config[ $ext ][ 'plugin_version' ] . '/' .
				self::$upd_config[ $ext ][ 'plugin_status' ] . '); ' . $home_url;

			$ssl_verify = apply_filters( $this->p_id . '_um_sslverify', true );

			$wp_get_options = array(
				'timeout'     => 15,	// Default timeout is 5 seconds.
				'redirection' => 5,	// Default redirection is 5.
				'sslverify'   => $ssl_verify,
				'user-agent'  => $ua_wpid,
				'headers'     => array(
					'Accept'         => 'application/json',
					'X-WordPress-Id' => $ua_wpid
				)
			);

			/*
			 * Call wp_remote_get().
			 */
			if ( $this->p->debug->enabled ) {

				$this->p->debug->log( $ext . ' plugin: sslverify is ' . ( $ssl_verify ? 'true' : 'false' ) );
				$this->p->debug->log( $ext . ' plugin: calling wp_remote_get() for ' . $json_url );
			}

			global $wp_filter, $wp_actions;

			$saved_filter  = $wp_filter;
			$saved_actions = $wp_actions;

			$wp_filter  = array();
			$wp_actions = array();

			/*
			 * Re-add the 'http_headers_useragent' to make sure we have a standard WordPress useragent string.
			 *
			 * The 'http_headers_useragent' filter hook offers two arguments, but only since WP v5.1.0, so require one
			 * argument to stay backwards compatible with older WP versions.
			 */
			add_filter( 'http_headers_useragent', array( $this, 'maybe_update_wpua' ), PHP_INT_MAX, 1 );

			$request = wp_remote_get( $json_url, $wp_get_options );

			/*
			 * Check for "cURL error 52: Empty reply from server" and retry wp_remote_get() after pausing for 1 second.
			 */
			if ( is_wp_error( $request ) && strpos( $request->get_error_message(), 'cURL error 52:' ) === 0 ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: wp error code ' . $request->get_error_code() . ' - ' . $request->get_error_message() );
					$this->p->debug->log( $ext . ' plugin: retrying wp_remote_get() for ' . $json_url );
				}

				sleep( 1 );	// Pause 1 second before retrying.

				$request = wp_remote_get( $json_url, $wp_get_options );
			}

			remove_filter( 'http_headers_useragent', array( $this, 'maybe_update_wpua' ), PHP_INT_MAX );

			$wp_filter  = $saved_filter;
			$wp_actions = $saved_actions;

			unset( $saved_filter, $saved_actions );

			if ( is_wp_error( $request ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: wp error code ' . $request->get_error_code() . ' - ' . $request->get_error_message() );
				}

				$this->p->notice->err( sprintf( __( 'Update error from the WordPress %1$s function: %2$s',
					$this->text_domain ), 'wp_remote_get()', $request->get_error_message() ) );

			} elseif ( isset( $request[ 'response' ][ 'code' ] ) ) {

				$http_code = (int) $request[ 'response' ][ 'code' ];

				if ( 200 === $http_code ) {

					if ( ! empty( $request[ 'body' ] ) ) {

						$payload = json_decode( $request[ 'body' ], $assoc = false );

						/*
						 * Add or remove any error and informational messages.
						 */
						foreach ( array( 'err', 'inf' ) as $type ) {

							$api_resp = empty( $payload->api_response->$type ) ? false : $payload->api_response->$type;

							self::set_umsg( $ext, $type, $api_resp );
						}

						/*
						 * X-Update-Error will be true if there was an authentication error and the payload
						 * does not contain any update information.
						 */
						if ( empty( $request[ 'headers' ][ 'x-update-error' ] ) ) {

							$plugin_data = SucomPluginData::data_from_json( $request[ 'body' ] );	// Returns null on json error.

							if ( empty( $plugin_data->plugin ) ) {

								if ( $this->p->debug->enabled ) {

									$this->p->debug->log( $ext . ' plugin: returned plugin data is incomplete' );
								}

								$plugin_data = null;

							} elseif ( $plugin_data->plugin !== self::$upd_config[ $ext ][ 'base' ] ) {

								if ( $this->p->debug->enabled ) {

									$this->p->debug->log( $ext . ' plugin: property ' . $plugin_data->plugin .
										' does not match ' . self::$upd_config[ $ext ][ 'base' ] );
								}

								$plugin_data = null;
							}
						}
					}

				} elseif ( isset( self::$http_error_codes[ $http_code ] ) ) {

					self::$upd_config[ $ext ][ 'uerr' ] = sprintf( __( 'The WordPress %1$s function returned HTTP %2$d (%3$s) for %4$s.',
						$this->text_domain ), 'wp_remote_get()', $http_code, self::$http_error_codes[ $http_code ], $json_url );
				}
			}

			self::$upd_config[ $ext ][ 'plugin_data' ] = $plugin_data;	// Save to static cache.

			if ( null === $plugin_data ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: saving empty stdClass to transient ' . $cache_id );
				}

				set_transient( $cache_id, new stdClass, self::$upd_config[ $ext ][ 'data_expire' ] );

			} else {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: saving plugin data to transient ' . $cache_id );
				}

				set_transient( $cache_id, $plugin_data, self::$upd_config[ $ext ][ 'data_expire' ] );
			}

			return $plugin_data;
		}

		public function get_default_filter_name() {

			$filter_name = 'stable';

			/*
			 * Possible values are 'local', 'development', 'staging', and 'production'.
			 */
			switch( wp_get_environment_type() ) {

				case 'development':

					$filter_name = 'dev';

					break;
			}

			return $filter_name;
		}

		public function get_ext_status( $ext, $read_cache = true ) {

			static $local_cache = array();

			if ( $read_cache ) {

				if ( isset( $local_cache[ $ext ] ) ) {

					return $local_cache[ $ext ];	// Return from cache.
				}
			}

			$ext_pdir    = $this->check_pp( $ext, $li = false );
			$ext_auth_id = $this->get_ext_auth_id( $ext );
			$ext_pp      = $ext_auth_id && $this->check_pp( $ext, $li = true, -100 ) === -100 ? true : false;
			$ext_status  = ( $ext_pp ? 'L' : ( $ext_pdir ? 'U' : 'S' ) ) . ( $ext_auth_id ? '*' : '' );

			return $local_cache[ $ext ] = $ext_status;
		}

		public function get_ext_version( $ext, $read_cache = true ) {

			static $local_cache = array();

			if ( $read_cache ) {

				if ( isset( $local_cache[ $ext ] ) ) {

					return $local_cache[ $ext ];	// Return from cache.
				}
			}

			$info = array();

			$local_cache[ $ext ] = 0;

			if ( isset( $this->p->cf[ 'plugin' ][ $ext ] ) ) {

				$info = $this->p->cf[ 'plugin' ][ $ext ];
			}

			/*
			 * Plugin is active - get the plugin version from the config array.
			 */
			if ( isset( $info[ 'version' ] ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: version from plugin config' );
				}

				$local_cache[ $ext ] = $info[ 'version' ];

			/*
			 * Plugin is not active or not installed - use get_plugins() to get the plugin version.
			 */
			} elseif ( isset( $info[ 'base' ] ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: not active / installed' );
				}

				$wp_plugins = SucomUpdateUtil::get_plugins( $read_cache );

				/*
				 * The plugin is installed.
				 */
				if ( isset( $wp_plugins[ $info[ 'base' ] ] ) ) {

					/*
					 * Maybe the plugin version found in the WordPress plugins array.
					 */
					if ( isset( $wp_plugins[ $info[ 'base' ]][ 'Version' ] ) ) {

						/*
						 * Save the plugin version.
						 */
						$local_cache[ $ext ] = $wp_plugins[ $info[ 'base' ] ][ 'Version' ];

						if ( $this->p->debug->enabled ) {

							$this->p->debug->log( $ext . ' plugin: installed version is ' . $local_cache[ $ext ] . ' according to WordPress' );
						}

					} else {

						if ( $this->p->debug->enabled ) {

							$this->p->debug->log( $ext . ' plugin: ' . $info[ 'base' ] . ' version key missing from plugins array' );
						}

						$name_transl = _x( $info[ 'name' ], 'plugin name', $this->p_text_domain );

						$this->p->notice->err( sprintf( __( 'The %1$s plugin (%2$s) version number is missing from the WordPress plugins array.',
							$this->text_domain ), $name_transl, $info[ 'base' ] ) );

						/*
						 * Save to cache and stop here.
						 */
						return $local_cache[ $ext ] = false;
					}

				/*
				 * Plugin is not installed.
				 */
				} else {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: ' . $info[ 'base' ] . ' plugin not installed' );
					}

					/*
					 * Save to cache and stop here.
					 */
					return $local_cache[ $ext ] = 'not-installed';
				}

			/*
			 * Plugin missing version and/or slug.
			 */
			} else {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: config is missing version and plugin base keys' );
				}

				/*
				 * Save to cache and stop here.
				 */
				return $local_cache[ $ext ] = false;
			}

			/*
			 * Maybe re-offer plugin update.
			 */
			if ( $this->has_offer( $ext ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: has offer' );
				}

				/*
				 * Save to cache and stop here.
				 */
				return $local_cache[ $ext ] = '0.' . $local_cache[ $ext ];
			}

			$filter_regex = $this->get_ext_filter_regex( $ext );

			if ( ! preg_match( $filter_regex, $local_cache[ $ext ] ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: ' . $local_cache[ $ext ] . ' does not match version filter' );
				}

				/*
				 * Save to cache and stop here.
				 */
				return $local_cache[ $ext ] = '0.' . $local_cache[ $ext ];
			}

			$ext_auth_type = $this->get_ext_auth_type( $ext );
			$ext_auth_id   = $this->get_ext_auth_id( $ext );

			if ( 'none' !== $ext_auth_type ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $ext . ' plugin: auth type is defined' );
				}

				if ( $this->check_pp( $ext, $li = false ) ) {

					if ( empty( $ext_auth_id ) ) {

						if ( $this->p->debug->enabled ) {

							$this->p->debug->log( $ext . ' plugin: pdir but no auth_id' );
						}

						return $local_cache[ $ext ] = '0.' . $local_cache[ $ext ];

					} elseif ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: pdir with auth_id' );
					}

				} elseif ( ! empty( $ext_auth_id ) ) {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $ext . ' plugin: standard with auth_id' );
					}

					return $local_cache[ $ext ] = '0.' . $local_cache[ $ext ];
				}

			} elseif ( $this->p->debug->enabled ) {

				$this->p->debug->log( $ext . ' plugin: no auth type' );
			}

			return $local_cache[ $ext ];
		}

		/*
		 * See WpssoCheck->get_ext_auth_type().
		 */
		public function get_ext_auth_type( $ext ) {

			return empty( $this->p->cf[ 'plugin' ][ $ext ][ 'update_auth' ] ) ? 'none' : $this->p->cf[ 'plugin' ][ $ext ][ 'update_auth' ];
		}

		/*
		 * See WpssoCheck->get_ext_auth_id().
		 */
		public function get_ext_auth_id( $ext ) {

			$ext_auth_type = $this->get_ext_auth_type( $ext );

			$ext_auth_key = 'plugin_' . $ext . '_' . $ext_auth_type;

			return empty( $this->p->options[ $ext_auth_key ] ) ? '' : $this->p->options[ $ext_auth_key ];
		}

		public function get_ext_filter_name( $ext ) {

			if ( ! empty( $this->p->options[ 'update_filter_for_' . $ext ] ) ) {

				$filter_name = $this->p->options[ 'update_filter_for_' . $ext ];

				if ( ! empty( $this->p->cf[ 'um' ][ 'version_regex' ][ $filter_name ] ) ) {	// Make sure the name is valid.

					return $filter_name;
				}
			}

			return $this->get_default_filter_name();
		}

		/*
		 * Include extra checks to make sure we have fallback values.
		 */
		public function get_ext_filter_regex( $ext ) {

			$filter_regex = '/^[0-9][0-9\.\-]+$/';	// Default stable regex.

			$filter_name  = $this->get_ext_filter_name( $ext );

			if ( ! empty( $this->p->cf[ 'um' ][ 'version_regex' ][ $filter_name ] ) ) {

				$filter_regex = $this->p->cf[ 'um' ][ 'version_regex' ][ $filter_name ];
			}

			return $filter_regex;
		}

		public function prefer_wp_org_update( $ext, $read_cache = true ) {

			static $local_cache = array();

			if ( $read_cache && isset( $local_cache[ $ext ] ) ) {

				return $local_cache[ $ext ];	// Return from cache.
			}

			if ( ! isset( self::$upd_config[ $ext ] ) ) {

				return $local_cache[ $ext ] = false;
			}

			$upd_cfg_info =& self::$upd_config[ $ext ];

			/*
			 * Make sure the plugin is available on wordpress.org.
			 */
			if ( empty( $upd_cfg_info[ 'hosts' ][ 'wp_org' ] ) ) {

				return $local_cache[ $ext ] = false;
			}

			/*
			 * Make sure we don't have an authentication id.
			 */
			if ( ! empty( $upd_cfg_info[ 'auth_id' ] ) ) {

				if ( $this->get_ext_auth_id( $ext ) ) {	// Just in case.

					return $local_cache[ $ext ] = false;
				}
			}

			/*
			 * Make sure we don't have an authentication type.
			 */
			if ( 'none' === $upd_cfg_info[ 'auth_type' ] ) {

				if ( 'none' === $this->get_ext_auth_type( $ext ) ) {	// Just in case.

					/*
					 * Make sure we're using the stable version filter.
					 */
					if ( 'stable' !== $upd_cfg_info[ 'version_filter' ] ) {

						return $local_cache[ $ext ] = false;
					}

					/*
					 * Make sure we're not forcing an update or switching the version filter.
					 */
					if ( 0 === strpos( $upd_cfg_info[ 'plugin_version' ], '0.' ) ) {

						return $local_cache[ $ext ] = false;
					}
				}
			}

			/*
			 * All tests passed - use the wordpress update data instead.
			 */
			return $local_cache[ $ext ] = true;
		}

		public static function is_enabled() {

			return empty( self::$upd_config ) ? false : true;
		}

		public static function is_configured( $ext = null ) {

			if ( empty( $ext ) ) {

				return count( self::$upd_config );
			}

			if ( isset( self::$upd_config[ $ext ] ) ) {

				return true;
			}

			return false;
		}

		public static function is_installed( $ext ) {

			if ( empty( $ext ) ) {

				return false;
			}

			if ( ! isset( self::$upd_config[ $ext ] ) ) {

				return false;
			}

			$upd_cfg_info = self::$upd_config[ $ext ];	// Shortcut variable name.

			if ( ! isset( $upd_cfg_info[ 'plugin_version' ] ) ) {

				return false;
			}

			if ( false !== strpos( $upd_cfg_info[ 'plugin_version' ], 'not-installed' ) ) {	// Anywhere in string.

				return false;
			}

			return true;
		}

		private static function set_umsg( $ext, $type, $value ) {

			$umsg_name = md5( __FILE__ . '-' . self::$api_version );

			self::$umsg_cache = SucomUpdateUtilWP::raw_do_option( $action = 'get', $umsg_name, null, $default = array() );

			if ( empty( $value ) ) {	// Empty array or string, 0, false, or null.

				$value = false;		// Standardize cached value as false.

				unset( self::$umsg_cache[ $ext ][ $type ] );

				if ( empty( self::$umsg_cache[ $ext ] ) ) unset( self::$umsg_cache[ $ext ] );

			} else self::$umsg_cache[ $ext ][ $type ] = base64_encode( serialize( $value ) );	// Convert object or array to string.

			if ( isset( self::$upd_config[ $ext ] ) ) self::$upd_config[ $ext ][ 'u' . $type ] = $value;	// Save to cache.

			if ( empty( self::$umsg_cache ) ) {

				SucomUpdateUtilWP::raw_do_option( $action = 'delete', $umsg_name );

			} else SucomUpdateUtilWP::raw_do_option( $action = 'update', $umsg_name, self::$umsg_cache, $default = array(), $autoload = true );

			return $value;
		}

		public static function get_umsg( $ext, $type = 'err' ) {

			if ( isset( self::$upd_config[ $ext ][ 'u' . $type ] ) ) {

				return self::$upd_config[ $ext ][ 'u' . $type ];	// Get from cache.

			} elseif ( null === self::$umsg_cache ) {

				$umsg_name = md5( __FILE__ . '-' . self::$api_version );

				self::$umsg_cache = SucomUpdateUtilWP::raw_do_option( $action = 'get', $umsg_name, null, $default = array() );
			}

			$value = empty( self::$umsg_cache[ $ext ][ $type ] ) ? false : self::$umsg_cache[ $ext ][ $type ];

			if ( is_string( $value ) ) $value = unserialize( base64_decode( $value ) );	// Convert string back to object or array.

			if ( isset( self::$upd_config[ $ext ] ) ) {

				self::$upd_config[ $ext ][ 'u' . $type ] = $value;

				if ( $value && '' ===  self::$upd_config[ $ext ][ 'auth_id' ] ) self::set_umsg( $ext, $type, false );	// Only show once.
			}

			return $value;
		}

		/*
		 * Used to show the exp_date, qty_used, qty_reg, and qty_total property values.
		 *
		 * See WpssoSubmenuLicenses->show_metabox_licenses().
		 */
		public static function get_option( $ext, $prop_name = false ) {

			$not_found = false !== $prop_name ? null : false;	// Return null if $prop_name does not exist.

			if ( ! empty( self::$upd_config[ $ext ][ 'option_name' ] ) ) {	// Just in case.

				$option_data = self::get_option_data( $ext );

				if ( false !== $prop_name ) {

					if ( isset( $option_data->update->$prop_name ) ) {

						return $option_data->update->$prop_name;
					}

					return $not_found;
				}

				return $option_data;
			}

			return $not_found;
		}

		private static function get_option_data( $ext, $default = false ) {

			if ( ! isset( self::$upd_config[ $ext ][ 'option_data' ] ) ) {	// Cached option data.

				if ( ! empty( self::$upd_config[ $ext ][ 'option_name' ] ) ) {

					$opt_name = self::$upd_config[ $ext ][ 'option_name' ];

					self::$upd_config[ $ext ][ 'option_data' ] = SucomUpdateUtilWP::raw_do_option( $action = 'get', $opt_name, null, $default );

				} else self::$upd_config[ $ext ][ 'option_data' ] = $default;
			}

			return self::$upd_config[ $ext ][ 'option_data' ];
		}

		public static function get_option_last_check( $ext ) {

			$option_data = self::get_option_data( $ext );

			return isset( $option_data->lastCheck ) ? $option_data->lastCheck : null;
		}

		private static function update_option_data( $ext, $option_data ) {

			self::$upd_config[ $ext ][ 'option_data' ] = $option_data;

			if ( ! empty( self::$upd_config[ $ext ][ 'option_name' ] ) ) {

				return SucomUpdateUtilWP::raw_do_option( $action = 'update', self::$upd_config[ $ext ][ 'option_name' ], $option_data );
			}

			return false;
		}

		private static function clear_option_data( $ext ) {

			unset( self::$upd_config[ $ext ][ 'option_data' ] );

			if ( ! empty( self::$upd_config[ $ext ][ 'option_name' ] ) ) {

				return SucomUpdateUtilWP::raw_do_option( $action = 'delete', self::$upd_config[ $ext ][ 'option_name' ] );
			}

			return false;
		}

		public function create_offer( $ext ) {

			if ( $ext_dir = self::get_ext_dir( $ext ) ) {	// True if plugin directory exists.

				touch( $ext_dir . self::$offer_updfn );
			}
		}

		public function has_offer( $ext ) {

			return self::get_ext_file_path( $ext, self::$offer_updfn );	// True if filename exists.
		}

		public function cancel_offer( $ext ) {

			if ( $file_path = $this->has_offer( $ext ) ) {	// True if filename exists.

				unlink( $file_path );
			}
		}

		/*
		 * Returns false or a slashed directory path.
		 */
		private static function get_ext_dir( $ext, $read_cache = true ) {

			static $local_cache = array();

			if ( $read_cache ) {

				if ( isset( $local_cache[ $ext ] ) ) {

					return $local_cache[ $ext ];
				}
			}

			/*
			 * Check for active plugin constant first.
			 */
			$ext_dir_const = strtoupper( $ext ) . '_PLUGINDIR';

			if ( defined( $ext_dir_const ) && is_dir( $ext_dir = constant( $ext_dir_const ) ) ) {

				return $local_cache[ $ext ] = trailingslashit( $ext_dir );
			}

			if ( isset( self::$upd_config[ $ext ][ 'slug' ] ) ) {

				$slug = self::$upd_config[ $ext ][ 'slug' ];

				if ( defined ( 'WPMU_PLUGIN_DIR' ) && is_dir( $ext_dir = trailingslashit( WPMU_PLUGIN_DIR ) . $slug . '/' ) ) {

					return $local_cache[ $ext ] = $ext_dir;
				}

				if ( defined ( 'WP_PLUGIN_DIR' ) && is_dir( $ext_dir = trailingslashit( WP_PLUGIN_DIR ) . $slug . '/' ) ) {

					return $local_cache[ $ext ] = $ext_dir;
				}
			}

			return $local_cache[ $ext ] = false;
		}

		/*
		 * Returns false, a slashed directory path, or the file name path.
		 *
		 * Use $file_is_dir = true when specifically checking for a sub-folder path.
		 */
		private static function get_ext_file_path( $ext, $rel_file, $file_is_dir = false ) {

			$file_path = false;

			if ( $ext_dir = self::get_ext_dir( $ext ) ) {	// Returns false or a slashed directory path.

				if ( $file_is_dir ) {	// Must be a directory.

					if ( is_dir( trailingslashit( $ext_dir . $rel_file ) ) ) {

						$file_path = trailingslashit( $ext_dir . $rel_file );
					}

				} elseif ( file_exists( $ext_dir . $rel_file ) ) {

					$file_path = $ext_dir . $rel_file;
				}
			}

			return $file_path;
		}

		private static function get_ext_file_content( $ext, $rel_file ) {

			if ( $file_path = self::get_ext_file_path( $ext, $rel_file ) ) {

				return file_get_contents( $file_path );
			}

			return '';
		}
	}
}
