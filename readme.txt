=== WPSSO Update Manager ===
Plugin Name: WPSSO Update Manager
Plugin Slug: wpsso-um
Text Domain: wpsso-um
Domain Path: /languages
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.txt
Assets URI: https://surniaulula.github.io/wpsso-um/assets/
Tags: wpsso, update, manager, add-on, pro version
Contributors: jsmoriss
Requires Plugins: wpsso
Requires PHP: 7.2.34
Requires At Least: 5.8
Tested Up To: 6.6.1
Stable Tag: 5.6.1

Update Manager for the WPSSO Core Premium plugin.

== Description ==

<!-- about -->

<p>The WPSSO Update Manager add-on is required to enable and update the <a href="https://wpsso.com/">WPSSO Core Premium plugin</a>.</p>

<p>The WPSSO Update Manager supports WordPress Network / Multisite installations, WordPress MU Domain Mapping, and WordPress v5.5 automatic updates.</p>

<!-- /about -->

<h3>WPSSO Core Required</h3>

WPSSO Update Manager (WPSSO UM) is an add-on for the [WPSSO Core plugin](https://wordpress.org/plugins/wpsso/), which provides complete structured data for WordPress to present your content at its best for social sites and search results – no matter how URLs are shared, reshared, messaged, posted, embedded, or crawled.

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

* {major} = Major structural code changes and/or incompatible API changes (ie. breaking changes).
* {minor} = New functionality was added or improved in a backwards-compatible manner.
* {bugfix} = Backwards-compatible bug fixes or small improvements.
* {stage}.{level} = Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).

<h3>Standard Edition Repositories</h3>

* [GitHub](https://surniaulula.github.io/wpsso-um/)

<h3>Development Version Updates</h3>

<p><strong>WPSSO Core Premium edition customers have access to development, alpha, beta, and release candidate version updates:</strong></p>

<p>Under the SSO &gt; Update Manager settings page, select the "Development and Up" (for example) version filter for the WPSSO Core plugin and/or its add-ons. When new development versions are available, they will automatically appear under your WordPress Dashboard &gt; Updates page. You can reselect the "Stable / Production" version filter at any time to reinstall the latest stable version.</p>

<h3>Changelog / Release Notes</h3>

**Version 5.7.0-dev.1 (2024/08/21)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Added a new `SucomUpdate::get_option_last_check()` method.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.8.
	* WPSSO Core v15.0.0.

**Version 5.6.1 (2024/08/20)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Fixed an incorrect time function call in `SucomUpdate->check_ext_for_updates()`.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.8.
	* WPSSO Core v15.0.0.

**Version 5.6.0 (2024/08/20)**

* **New Features**
	* None.
* **Improvements**
	* Added a cache array to optimize update check messages.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Refactored the `WpssoUmRegister->deactivate_plugin()` method.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.8.
	* WPSSO Core v15.0.0.

**Version 5.5.0 (2024/08/19)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Removed the 'wpsso_save_setting_options' filter hook.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.8.
	* WPSSO Core v13.0.0.

**Version 5.4.0 (2024/08/15)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Removed the 'wpsso_features_status' filter hook.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.8.
	* WPSSO Core v13.0.0.

**Version 5.3.0 (2024/08/06)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Added a method to return the last update check timestamp.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.8.
	* WPSSO Core v13.0.0.

**Version 5.2.0 (2024/07/10)**

* **New Features**
	* None.
* **Improvements**
	* Updated minimum WPSSO Core version from v9.0.0 to v13.0.0.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Added check for invalid SucomUpdate class object.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.8.
	* WPSSO Core v13.0.0.

**Version 5.1.0 (2023/12/27)**

* **New Features**
	* None.
* **Improvements**
	* Updated the SSO &gt; Update Manager settings page to keep the original order of version filters.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.8.
	* WPSSO Core v9.0.0 (released on 2021/09/24).

**Version 5.0.0 (2023/11/08)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Refactored the settings page and metabox load process for WPSSO Core v17.0.0.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.5.
	* WPSSO Core v9.0.0 (released on 2021/09/24).

== Upgrade Notice ==

= 5.7.0-dev.1 =

(2024/08/21) Added a new `SucomUpdate::get_option_last_check()` method.

= 5.6.1 =

(2024/08/20) Fixed an incorrect time function call in `SucomUpdate->check_ext_for_updates()`.

= 5.6.0 =

(2024/08/20) Added a cache array to optimize update check messages. Refactored the `WpssoUmRegister->deactivate_plugin()` method.

= 5.5.0 =

(2024/08/19) Removed the 'wpsso_save_setting_options' filter hook.

= 5.4.0 =

(2024/08/15) Removed the 'wpsso_features_status' filter hook.

= 5.3.0 =

(2024/08/06) Added a method to return the last update check timestamp.

= 5.2.0 =

(2024/07/10) Updated minimum WPSSO Core version from v9.0.0 to v13.0.0.

= 5.1.0 =

(2023/12/27) Updated the SSO &gt; Update Manager settings page to keep the original order of version filters.

= 5.0.0 =

(2023/11/08) Refactored the settings page and metabox load process for WPSSO Core v17.0.0.

