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
Requires PHP: 7.0
Requires At Least: 5.0
Tested Up To: 5.8.1
Stable Tag: 4.6.2

Update Manager for the WPSSO Core Premium plugin.

== Description ==

<p><img class="readme-icon" src="https://surniaulula.github.io/wpsso-um/assets/icon-256x256.png"> The WPSSO Update Manager add-on is required to enable and update the <a href="https://wpsso.com/">WPSSO Core Premium plugin</a>.</p>

<p>The WPSSO Update Manager supports WordPress Network / Multisite installations, WordPress MU Domain Mapping, and WordPress v5.5 Automatic Updates.</p>

<p>Simply <em>download</em>, <em>install</em> and <em>activate</em>.</p>

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

<h3>Standard Version Repositories</h3>

* [GitHub](https://surniaulula.github.io/wpsso-um/)

<h3>Development Version Updates</h3>

<p><strong>WPSSO Core Premium customers have access to development, alpha, beta, and release candidate version updates:</strong></p>

<p>Under the SSO &gt; Update Manager settings page, select the "Development and Up" (for example) version filter for the WPSSO Core plugin and/or its add-ons. Save the plugin settings and click the "Check for Plugin Updates" button to fetch the latest version information. When new development versions are available, they will automatically appear under your WordPress Dashboard &gt; Updates page. You can always reselect the "Stable / Production" version filter at any time to reinstall the latest stable version.</p>

<h3>Changelog / Release Notes</h3>

**Version 4.7.0 (2021/10/15)**

* **New Features**
	* None.
* **Improvements**
	* Refactored the `SucomUpdateUtilWP` class methods for WordPress v5.8.1.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v5.0.
	* WPSSO Core v8.0.0.

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
	* Added a new WPSSO Schema Shortcode add-on to the SSO &gt; Complimentary Add-ons settings page.
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

= 4.7.0 =

(2021/10/15) Refactored the `SucomUpdateUtilWP` class methods for WordPress v5.8.1.

= 4.6.2 =

(2021/10/12) Fixed missing config cache refresh when upgrading the plugin settings.

= 4.6.1 =

(2021/10/06) Standardized `get_table_rows()` calls and filters in 'submenu' and 'sitesubmenu' classes.

= 4.6.0 =

(2021/09/24) Added a new WPSSO Schema Shortcode add-on to the SSO &gt; Complimentary Add-ons settings page.

= 4.5.0 =

(2021/09/07) Minor transient optimization for stable version data from wordpress.org.

= 4.4.1 =

(2021/02/25) Updated the banners and icons of WPSSO Core and its add-ons.

= 4.4.0 =

(2021/01/27) Added a 'upgrader_process_complete' action hook to refresh the update manager configuation.

