=== MCM Protected File View ===
Contributors: mcmwebsol
Tags: plugin, file
Requires at least: 4.6
Tested up to: 5.8
Requires PHP: 5.6
Stable tag: 1.9
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Protects uploaded files so they can only be viewed by logged-in users

== Description ==

Protects uploaded files so they can only be viewed by logged-in users

This plugin does not work with the media library.   It only protects files uploaded through the plugin itself.

Requires a web server that supports .htaccess files for access control, such as Apache on Linux.  Please note that even Apache on Linux
disables .htaccess files in newer versions in some configurations, so please check to make sure your files aren't publicly accessible.
You can test that your files are protected by using the wp-content/uploads/ path or
            by using the "Test link security" link on the "List all protected uploads" page.  You should receive a 403 Forbidden error
            if it's working correctly.

This plugin does not collect any private data itself though it's possible logged-in users may upload private data to your website using this plugin.

PRO SUPPORT
While we attempt to provide basic support for this plugin here on the WordPress.org forums, for advanced or priority support please use the pro version. 
The [pro version](https://www.mcmwebsite.com/mcm-protected-file-view-pro/) also adds per user role file permissions, so, for instance, you can have a file that's only accessible to a premium role, while not accessible to a basic role.


== Installation ==

1. Unzip the plugin zip file
2. Upload the entire folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress


== Changelog ==

= 1.9 =
* Improved Security

= 1.7 =
* Add link security checker

= 1.4 =
* Fix for deprecated code in add_options_page() call

= 1.3 =
* Fix for possible XSS and CSRF issues 

= 1.2 =
* Fix for Multi-site compatibility


= 1.1 =
* Fix for MS Word documents not uploading


= 1.0 =
* Initial version
