=== Plugin Name ===
Contributors: mcmwebsol
Tags: plugin, file
Requires at least: 4.6
Tested up to: 4.8
Stable tag: 1.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Protects uploaded files so they can only be viewed by logged-in users

== Description ==

Protects uploaded files so they can only be viewed by logged-in users

This plugin does not work with the media library.   It only protects files uploaded through the plugin itself.

Requires a web server that supports .htaccess files for access control, such as Apache on Linux.



== Installation ==

1. Unzip the plugin zip file
2. Upload the entire folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress


== Changelog ==

= 1.3 =
* Fix for possible XSS and CSRF issues 

= 1.2 =
* Fix for Multi-site compatibility


= 1.1 =
* Fix for MS Word documents not uploading


= 1.0 =
* Initial version
