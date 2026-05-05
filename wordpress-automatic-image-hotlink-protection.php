<?php
/**
 * Plugin Name: WordPress Automatic Image Hotlink Protection
 * Plugin URI:  https://thisismyurl.com/downloads/wordpress-automatic-image-hotlink-protection/
 * Description: Stops other sites from hotlinking your WordPress images. Adds an .htaccess rule that returns 403 for image requests originating from other domains — no bandwidth leakage, no configuration required.
 * Author:      Christopher Ross
 * Author URI:  https://thisismyurl.com/
 * Version:     26.05.0
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wordpress-automatic-image-hotlink-protection
 *
 * @package WordPress_Automatic_Image_Hotlink_Protection
 * @copyright Copyright (c) 2008, Christopher Ross
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'THISISMYURL_WAIHP_VERSION',   '26.05.0' );
define( 'THISISMYURL_WAIHP_MARKER',    'Hotlink Protection' );
define( 'THISISMYURL_WAIHP_NAMESPACE', 'wordpress-automatic-image-hotlink-protection' );

register_activation_hook( __FILE__, 'thisismyurl_waihp_activate' );
register_deactivation_hook( __FILE__, 'thisismyurl_waihp_deactivate' );

/**
 * On activation: insert hotlink-protection rules into .htaccess.
 *
 * Uses WP's insert_with_markers() so the block is idempotent and cleanly
 * bounded. Does nothing if the server is nginx (no .htaccess support).
 *
 * @since 26.05.0
 */
function thisismyurl_waihp_activate() {
	if ( ! function_exists( 'insert_with_markers' ) ) {
		require_once ABSPATH . 'wp-admin/includes/misc.php';
	}

	$htaccess = get_home_path() . '.htaccess';
	$rules     = thisismyurl_waihp_rules();

	insert_with_markers( $htaccess, THISISMYURL_WAIHP_MARKER, $rules );
}

/**
 * On deactivation: remove hotlink-protection rules from .htaccess.
 *
 * Passes an empty array to insert_with_markers() which removes the block.
 *
 * @since 26.05.0
 */
function thisismyurl_waihp_deactivate() {
	if ( ! function_exists( 'insert_with_markers' ) ) {
		require_once ABSPATH . 'wp-admin/includes/misc.php';
	}

	$htaccess = get_home_path() . '.htaccess';
	insert_with_markers( $htaccess, THISISMYURL_WAIHP_MARKER, array() );
}

/**
 * Build the htaccess rewrite rules for hotlink protection.
 *
 * Allows requests with an empty Referer (direct navigation, feed readers, etc.)
 * and requests originating from the site's own domain. All other requests for
 * image files return 403 Forbidden.
 *
 * @since 26.05.0
 * @return string[] Lines to write between the marker comments.
 */
function thisismyurl_waihp_rules() {
	$site_url = wp_parse_url( home_url() );
	$domain   = $site_url['host'];

	// Strip www. prefix so both www and non-www originate cleanly.
	$domain_bare = preg_replace( '/^www\./i', '', $domain );

	return array(
		'<IfModule mod_rewrite.c>',
		'  RewriteEngine On',
		'  RewriteCond %{HTTP_REFERER} !^$',
		'  RewriteCond %{HTTP_REFERER} !^https?://(www\.)?' . preg_quote( $domain_bare, '/' ) . ' [NC]',
		'  RewriteRule \.(jpg|jpeg|png|gif|webp|svg|ico)$ - [NC,F,L]',
		'</IfModule>',
	);
}
