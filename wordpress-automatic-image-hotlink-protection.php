<?php
/*
Plugin Name: Hotlink Protection
Plugin URI:
Description: The WP Hotlink Protection plugin is a single step script designed to add an .htaccess file to your WordPress site thereby stopping external web servers from linking directly to your files.
Author: Planet Zuda
Author URI: https://planetzuda.com
Version: 3.3.3
*/

/**
 * Hotlink Protection core file
 *
 * This file contains all the logic required for the plugin
 *
 * @link		http://wordpress.org/extend/plugins/hotlink-protection/
 *
 * @package		Hotlink Protection
 * @copyright		Copyright ( c ) 2017, Hors Hipsrectors
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 ( or newer )
 *
 * @since		Hotlink Protection 1.0
 */



// on activate
global $horshipsrectors_hotlink_protection_file;
global $horshipsrectors_hotlink_protection_file_hlp;


$url = strtolower( get_bloginfo( 'url' ) );
$url = str_replace( 'https://','',$url );
$url = str_replace( 'http://','',$url );
$url = str_replace( 'www.','',$url );
$horshipsrectors_hotlink_protection_file_hlp = ";
//updated with stackoverflow solution to the problem as to why this keeps failing
# Hotlink Protection START #
RewriteEngine On
RewriteCond $1 !\.(gif|jpe?g|png)$ [NC]

# Force HTTPS for 
RewriteCond %{HTTPS} !=on
RewriteRule  https://%{HTTP_HOST}%{REQUEST_URI} [NC,R=301,L]

# Force HTTP 
RewriteCond %{HTTPS} =on
RewriteRule  http://%{HTTP_HOST}%{REQUEST_URI} [NC,R=301,L]

# Remove index.php from URLs
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /index.php/$1
# Hotlink Protection END #

";

$horshipsrectors_hotlink_protection_file = ABSPATH . '.htaccess';

// on activate
function horshipsrectors_wpaihp_active() {
	global $horshipsrectors_hotlink_protection_file;
	global $horshipsrectors_hotlink_protection_file_hlp;

	if ( file_exists( $horshipsrectors_hotlink_protection_file ) ) {

		$fh = fopen( $horshipsrectors_hotlink_protection_file, 'r' );
		$htaccess = fread( $fh, filesize( $horshipsrectors_hotlink_protection_file ) );
		fclose( $fh );
	}

	$fh = fopen( $horshipsrectors_hotlink_protection_file, 'w' ) or die( "can't open file" );
	fwrite( $fh, $htaccess.$horshipsrectors_hotlink_protection_file_hlp );
	fclose( $fh );

}
register_activation_hook( __FILE__, 'horshipsrectors_wpaihp_active' );


// on deactivate
function horshipsrectors_wpaihp_deactivate() {
	global $horshipsrectors_hotlink_protection_file;
	global $horshipsrectors_hotlink_protection_file_hlp;

	if ( file_exists( $horshipsrectors_hotlink_protection_file ) ) {

		$fh = fopen( $horshipsrectors_hotlink_protection_file, 'r' );
		$htaccess = fread( $fh, filesize( $horshipsrectors_hotlink_protection_file ) );
		fclose( $fh );

		$htaccess = str_replace( $horshipsrectors_hotlink_protection_file_hlp,"",$htaccess );

		$fh = fopen( $horshipsrectors_hotlink_protection_file, 'w' ) or die( "can't open file" );
		fwrite( $fh, $htaccess );
		fclose( $fh );

	}
}
register_deactivation_hook( __FILE__, 'horshipsrectors_wpaihp_deactivate' );

?>