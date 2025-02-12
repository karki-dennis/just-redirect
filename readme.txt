=== Simple Redirect Plugin ===
Contributors: Dennish Karki
Tags: redirect, 301, 302, URL, admin, settings, CSV, uninstall
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 1.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

A simple plugin to manage URL redirects via an intuitive admin interface. Easily add redirect rules with custom HTTP status codes, export rules as a CSV file, and automatically clean up settings upon uninstall.

== Description ==
Simple Redirect Plugin allows you to create and manage URL redirects directly from the WordPress dashboard. In the plugin settings, you can add one redirect rule per line in the following format:

    /old-path,https://example.com/new-url,HTTP_STATUS_CODE

For example:

    /old-page,https://example.com/new-page,302

If the HTTP status code is omitted, the plugin defaults to a 301 redirect.

Features include:
* Management of URL redirects from an admin settings page.
* Support for custom HTTP status codes (such as 301 and 302).
* Option to export your redirect rules as a CSV file.
* Automatic cleanup of plugin options upon uninstall.
* Fully internationalized and ready for translation.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/simple-redirect-plugin` directory (or install via the WordPress plugin installer).
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to **Settings → Simple Redirect** in your WordPress dashboard.
4. Enter your redirect rules in the provided textarea (one rule per line using the format shown above) and save your changes.

== Frequently Asked Questions ==
= How do I add a redirect rule? =
Go to **Settings → Simple Redirect** and add each rule on a new line in the format:  
`/old-path,https://example.com/new-url,HTTP_STATUS_CODE`  
The HTTP status code is optional and will default to 301 if omitted.

= Can I export my redirect rules? =
Yes, simply click the **Download Redirects** button on the settings page to export your rules as a CSV file.


== Screenshots ==
1. **Admin Settings Page:** Manage your redirects from an intuitive settings page.
2. **CSV Export:** Export your redirect rules as a CSV file for backup or migration.

== License ==
This plugin is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. For full details, see [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).
