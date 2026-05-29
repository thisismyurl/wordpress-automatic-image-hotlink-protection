=== WordPress Automatic Image Hotlink Protection ===
Contributors: thisismyurl
Plugin URI: https://thisismyurl.com/downloads/wordpress-automatic-image-hotlink-protection/
Donate link: https://github.com/sponsors/thisismyurl
Tags: image protection, hotlink protection, hotlink, bandwidth, save bandwidth
Requires at least: 6.0
Requires PHP: 7.4
Tested up to: 7.0
Stable tag: 26.6148.2110
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Stops other sites from hotlinking the images in your uploads directory on Apache servers, using a single clean .htaccess rule.

== Description ==

On activation, this plugin adds a hotlink-protection block to your site's root .htaccess file. The block returns 403 Forbidden when an image inside /wp-content/uploads/ is requested from a domain that is not your own. Requests with an empty Referer (direct navigation, many feed readers, some privacy browsers) are always allowed, and so are your own subdomains (www., cdn., shop., staging., and so on). On deactivation the block is removed cleanly.

Protection is scoped to your uploads directory only, so images served by your theme, plugins, or the block editor are never blocked.

= Requirements =

This plugin works on Apache (and Apache-compatible servers such as LiteSpeed) with mod_rewrite enabled. It does not work on nginx, Caddy, or IIS, because those servers do not read .htaccess files. On an unsupported server the plugin writes nothing and shows an admin notice explaining that hotlink protection must be configured at the server or CDN/edge layer instead.

= Protected file types =

jpg, jpeg, png, gif, webp, avif

SVG and ICO files are intentionally not protected: favicons and SVG sprites are commonly embedded cross-origin for legitimate reasons, and blocking them tends to break more than it protects.

== Installation ==

1. Upload the plugin folder to /wp-content/plugins/.
2. Activate the plugin through the Plugins screen in WordPress.
3. That's it. Protection is applied automatically on activation. To check its status, visit Settings > Hotlink Protection, which reports your server type, whether .htaccess is writable, and whether protection is currently active.

== Frequently Asked Questions ==

= How do I check whether protection is active? =

Visit Settings > Hotlink Protection. The page reports your server software, whether the server supports .htaccess, where your .htaccess file is, whether it is writable, and the current protection status.

= How do I remove this plugin? =

In most cases deactivation removes the rules for you. If your .htaccess is not writable, open it via an FTP client or your host's file manager and remove the lines between # Hotlink Protection START # and # Hotlink Protection END #.

= It says my server is not supported. Why? =

Your server does not read .htaccess files (nginx, Caddy, and IIS all ignore them). Configure hotlink protection at the server configuration or your CDN/edge provider instead.

== Changelog ==
= 26.05.1 =
* Scoped protection to /wp-content/uploads/ so theme, plugin, and editor images are no longer blocked.
* Allowed all of your own subdomains (cdn., shop., staging., www.) instead of only the bare domain and www.
* Dropped svg and ico from the protected file types (commonly legitimate cross-origin); added avif.
* Refuse to write a dead .htaccess on nginx and other non-Apache servers; show an explanatory admin notice instead.
* Checked the .htaccess write result and surface a real failure notice instead of reporting false success.
* Loaded wp-admin/includes/file.php before get_home_path() and guarded the parsed host key.
* Added a read-only Settings > Hotlink Protection status page; reconciled the readme to describe what the plugin actually does.
* Added Requires PHP and Requires at least headers; reconciled contributor and authorship metadata.

= 26.05.0 =
* Updated "Tested up to" to WordPress 6.9.
* Code modernization and maintenance pass.
* Repository cleanup and documentation updates.

= 3.3.3 =
* palindrome version numbers, because that's cooler than bow ties.
* updated the script from the previous owner to try and avoid sites getting the white screen of death.
* is now being maintaned as it has been adopted by planetzuda.com.

= 2.0.0 =

* Removed admin control panel
* Tested with WordPress 3.2
* Optimized code for WordPress 3.2
* Removed excess code

= 1.1.0 =

* Tested for WordPress 3.1
* Added new admin screens

= 1.0.0 =

* rewrote plugin for use with 3.x websites
* moved .htaccess file from uploads folder to root
* added file_exists checks
* tested for clean removal

= 0.1.6 =

* removed update function

== Upgrade Notice ==

= 26.05.1 =
Fixes over-blocking (now scoped to uploads, allows your subdomains), drops svg/ico, refuses to write a dead file on nginx, and adds a status page.

== Screenshots ==

Empty
