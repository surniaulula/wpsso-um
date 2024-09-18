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
Requires PHP: 7.4.33
Requires At Least: 5.9
Tested Up To: 6.6.2
Stable Tag: 7.1.0

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

**Version 7.2.0 (2024/09/18)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Additional refactoring of the `SucomUpdate->prefer_wp_org_update()` method.
* **Requires At Least**
	* PHP v7.4.33.
	* WordPress v5.9.
	* WPSSO Core v15.0.0.

**Version 7.1.0 (2024/09/15)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Added support for an update transaction status in the update option data.
	* Refactored the `SucomUpdate->prefer_wp_org_update()` method.
* **Requires At Least**
	* PHP v7.4.33.
	* WordPress v5.9.
	* WPSSO Core v15.0.0.

== Upgrade Notice ==

= 7.2.0 =

(2024/09/18) Additional refactoring of the `SucomUpdate->prefer_wp_org_update()` method.

= 7.1.0 =

(2024/09/15) Added support for an update transaction status in the update option data.

