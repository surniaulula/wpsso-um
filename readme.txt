=== WPSSO Update Manager ===
Plugin Name: WPSSO Update Manager
Plugin Slug: wpsso-um
Text Domain: wpsso-um
Domain Path: /languages
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.txt
Assets URI: https://surniaulula.github.io/wpsso-um/assets/
Tags: wpsso, update, manager, schedule, add-on, pro version
Contributors: jsmoriss
Requires PHP: 7.2
Requires At Least: 5.2
Tested Up To: 5.9.0
Stable Tag: 4.11.0

Update Manager for the WPSSO Core Premium plugin.

== Description ==

<!-- about -->

<p>The WPSSO Update Manager add-on is required to enable and update the <a href="https://wpsso.com/">WPSSO Core Premium plugin</a>.</p>

<p>The WPSSO Update Manager supports WordPress Network / Multisite installations, WordPress MU Domain Mapping, and WordPress v5.5 Automatic Updates.</p>

<!-- /about -->

<h3>WPSSO Core Required</h3>

WPSSO Update Manager (WPSSO UM) is an add-on for the [WPSSO Core plugin](https://wordpress.org/plugins/wpsso/).

== Installation ==

<h3 class="top">Install and Uninstall</h3>

* [Install the WPSSO Update Manager add-on](https://wpsso.com/docs/plugins/wpsso-um/installation/install-the-plugin/).
* [Uninstall the WPSSO Update Manager add-on](https://wpsso.com/docs/plugins/wpsso-um/installation/uninstall-the-plugin/).

== Frequently Asked Questions ==

== Screenshots ==

01. Update Manager settings - customize the update check frequency (once a day by default) and/or choose to install one of the development versions.

== Changelog ==

<h3 class="top">Version Numbering</h3>

Version components: `{major}.{minor}.{bugfix}[-{stage}.{level}]`

* {major} = Major structural code changes / re-writes or incompatible API changes.
* {minor} = New functionality was added or improved in a backwards-compatible manner.
* {bugfix} = Backwards-compatible bug fixes or small improvements.
* {stage}.{level} = Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).

<h3>Standard Edition Repositories</h3>

* [GitHub](https://surniaulula.github.io/wpsso-um/)

<h3>Development Version Updates</h3>

<p><strong>WPSSO Core Premium customers have access to development, alpha, beta, and release candidate version updates:</strong></p>

<p>Under the SSO &gt; Update Manager settings page, select the "Development and Up" (for example) version filter for the WPSSO Core plugin and/or its add-ons. Save the plugin settings and click the "Check for Plugin Updates" button to fetch the latest version information. When new development versions are available, they will automatically appear under your WordPress Dashboard &gt; Updates page. You can always reselect the "Stable / Production" version filter at any time to reinstall the latest stable version.</p>

<h3>Changelog / Release Notes</h3>

**Version 4.12.0-b.1 (2022/02/09)**

* **New Features**
	* None.
* **Improvements**
	* Added a 'user_direction' argument to compliment 'user_locale' for the update information query.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v7.0.0.

**Version 4.11.0 (2022/01/19)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Renamed the lib/abstracts/ folder to lib/abstract/.
	* Renamed the `SucomAddOn` class to `SucomAbstractAddOn`.
	* Renamed the `WpssoAddOn` class to `WpssoAbstractAddOn`.
	* Renamed the `WpssoWpMeta` class to `WpssoAbstractWpMeta`.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v7.0.0.

**Version 4.10.2 (2021/12/16)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Added a 'wpsso_clear_cache' action hook to refresh the update manager config.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v7.0.0.

**Version 4.10.1 (2021/11/16)**

* **New Features**
	* None.
* **Improvements**
	* Added a query argument to prevent a second check when reloading the '/update-core.php?force-check=1' page.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Refactored the `SucomAddOn->get_missing_requirements()` method.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v7.0.0.

**Version 4.10.0 (2021/11/10)**

* **New Features**
	* Added a new WPSSO Google Merchant Feeds XML add-on to the SSO &gt; Plugin Add-ons settings page.
* **Improvements**
	* Moved the WPSSO Add Five Stars add-on config to the WPSSO Core plugin.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v5.0.
	* WPSSO Core v7.0.0.

**Version 4.9.0 (2021/10/21)**

* **New Features**
	* None.
* **Improvements**
	* Moved the update manager config from transient cache to the options table for a small performance improvement.
	* Refactored several methods to offer selective local caching (enabled by default).
* **Bugfixes**
	* Fixed the update manager config refresh after updating the WPSSO Core plugin or its add-ons.
* **Developer Notes**
	* Re-added the 'activated_plugin' and 'upgrader_process_complete' action hooks in `WpssoUmActions`.
	* Refactored the `SucomUpdate->get_ext_status()` method to add a `$read_cache` argument (true by default).
	* Refactored the `SucomUpdate->get_ext_version()` method to add a `$read_cache` argument (true by default).
	* Refactored the `SucomUpdate::get_ext_dir()` method to add a `$read_cache` argument (true by default).
	* Refactored the `SucomUpdate::prefer_wp_org_update()` method to add a `$read_cache` argument (true by default).
	* Refactored the `SucomUpdateUtil::get_plugins()` method to add a `$read_cache` argument (true by default).
	* Deprecated the `SucomUpdateUtil::clear_plugins_cache()` method.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v5.0.
	* WPSSO Core v7.0.0.

**Version 4.8.0 (2021/10/19)**

* **New Features**
	* Added a new WPSSO Add Five Stars add-on to the SSO &gt; Plugin Add-ons settings page.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Added a new lib/filters-options.php library file.
	* Removed the 'activated_plugin' and 'upgrader_process_complete' action hooks.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v5.0.
	* WPSSO Core v7.0.0.

**Version 4.7.0 (2021/10/16)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* Fixed a condition where WP_HOME could be used for the Site Address URL for non-default sites in a multisite setup.
* **Developer Notes**
	* Updated the `SucomUpdateUtilWP` class methods for WordPress v5.8.1.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v5.0.
	* WPSSO Core v7.0.0.

**Version 4.6.2 (2021/10/12)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* Fixed missing config cache refresh when upgrading the plugin settings.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v5.0.
	* WPSSO Core v8.0.0.

**Version 4.6.1 (2021/10/06)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Standardized `get_table_rows()` calls and filters in 'submenu' and 'sitesubmenu' classes.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v5.0.
	* WPSSO Core v8.0.0.

**Version 4.6.0 (2021/09/24)**

* **New Features**
	* Added a new WPSSO Schema Shortcode add-on to the SSO &gt; Plugin Add-ons settings page.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v5.0.
	* WPSSO Core v7.0.0.

**Version 4.5.0 (2021/09/07)**

* **New Features**
	* None.
* **Improvements**
	* Minor transient optimization for stable version data from wordpress.org.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v5.0.
	* WPSSO Core v7.0.0.

**Version 4.4.1 (2021/02/25)**

* **New Features**
	* None.
* **Improvements**
	* Updated the banners and icons of WPSSO Core and its add-ons.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v5.0.
	* WPSSO Core v6.0.0.

**Version 4.4.0 (2021/01/27)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Added a 'upgrader_process_complete' action hook to refresh the update manager configuation.
	* Removed the 'wpsso_version_updates' action hook.
	* Updated the API version to 4.4.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v4.5.
	* WPSSO Core v5.0.0.

== Upgrade Notice ==

= 4.12.0-b.1 =

(2022/02/09) Added a 'user_direction' argument to compliment 'user_locale' for the update information query.

= 4.11.0 =

(2022/01/19) Renamed the lib/abstracts/ folder and its classes.

= 4.10.2 =

(2021/12/16) Added a 'wpsso_clear_cache' action hook to refresh the update manager config.

= 4.10.1 =

(2021/11/16) Added a query argument to prevent a second check when reloading the '/update-core.php?force-check=1' page.

= 4.10.0 =

(2021/11/10) Added a new WPSSO Google Merchant Feeds XML add-on to the SSO &gt; Plugin Add-ons settings page.

= 4.9.0 =

(2021/10/21) Moved the update manager config from transient cache to the options table. Fixed the update manager config refresh.

= 4.8.0 =

(2021/10/19) Added a new WPSSO Add Five Stars add-on to the SSO &gt; Plugin Add-ons settings page.

= 4.7.0 =

(2021/10/16) Fixed a condition where WP_HOME could be used for the Site Address URL for non-default sites in a multisite setup.

= 4.6.2 =

(2021/10/12) Fixed missing config cache refresh when upgrading the plugin settings.

= 4.6.1 =

(2021/10/06) Standardized `get_table_rows()` calls and filters in 'submenu' and 'sitesubmenu' classes.

= 4.6.0 =

(2021/09/24) Added a new WPSSO Schema Shortcode add-on to the SSO &gt; Plugin Add-ons settings page.

= 4.5.0 =

(2021/09/07) Minor transient optimization for stable version data from wordpress.org.

= 4.4.1 =

(2021/02/25) Updated the banners and icons of WPSSO Core and its add-ons.

= 4.4.0 =

(2021/01/27) Added a 'upgrader_process_complete' action hook to refresh the update manager configuation.

