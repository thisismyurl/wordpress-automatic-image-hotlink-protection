<?php
/**
 * Plugin Name: WordPress Automatic Image Hotlink Protection
 * Plugin URI:  https://thisismyurl.com/downloads/wordpress-automatic-image-hotlink-protection/
 * Description: Stops other sites from hotlinking your WordPress images. Adds an Apache .htaccess rule that returns 403 for image requests in /wp-content/uploads/ originating from other domains. Apache + mod_rewrite only.
 * Author:      Christopher Ross
 * Author URI:  https://thisismyurl.com/
 * Version:     26.05.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
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
 * Whether the current server is Apache (the only server that honours .htaccess).
 *
 * nginx, LiteSpeed-as-nginx, Caddy, IIS and friends ignore .htaccess entirely,
 * so writing the file there is silently inert. We check SERVER_SOFTWARE rather
 * than got_url_rewrite(), because got_url_rewrite() returns true on nginx hosts
 * configured for pretty permalinks even though no .htaccess is ever read.
 *
 * @since 26.05.0
 * @return bool True when the server reports as Apache or LiteSpeed.
 */
function thisismyurl_waihp_server_is_apache() {
	$software = isset( $_SERVER['SERVER_SOFTWARE'] )
		? strtolower( sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) )
		: '';

	// LiteSpeed reads .htaccess in Apache-compatibility mode, so it counts.
	return ( false !== strpos( $software, 'apache' ) || false !== strpos( $software, 'litespeed' ) );
}

/**
 * Resolve the absolute path to the site's root .htaccess file.
 *
 * get_home_path() lives in wp-admin/includes/file.php, which is not guaranteed
 * to be loaded outside the admin context, so we require it explicitly first.
 *
 * @since 26.05.0
 * @return string Absolute path to the .htaccess file.
 */
function thisismyurl_waihp_htaccess_path() {
	if ( ! function_exists( 'get_home_path' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	return get_home_path() . '.htaccess';
}

/**
 * On activation: insert hotlink-protection rules into .htaccess.
 *
 * Refuses on non-Apache servers (the .htaccess would be inert) and records the
 * outcome in an option so the admin status page can report what happened. Uses
 * WP's insert_with_markers() so the block is idempotent and cleanly bounded.
 *
 * @since 26.05.0
 */
function thisismyurl_waihp_activate() {
	if ( ! thisismyurl_waihp_server_is_apache() ) {
		update_option( 'thisismyurl_waihp_status', 'unsupported_server', false );
		return;
	}

	if ( ! function_exists( 'insert_with_markers' ) ) {
		require_once ABSPATH . 'wp-admin/includes/misc.php';
	}

	$htaccess = thisismyurl_waihp_htaccess_path();
	$rules    = thisismyurl_waihp_rules();

	// insert_with_markers() returns false when the .htaccess is missing and the
	// directory is not writable, or when the file exists but cannot be written.
	$written = insert_with_markers( $htaccess, THISISMYURL_WAIHP_MARKER, $rules );

	update_option( 'thisismyurl_waihp_status', $written ? 'active' : 'write_failed', false );
}

/**
 * On deactivation: remove hotlink-protection rules from .htaccess.
 *
 * Passes an empty array to insert_with_markers() which removes the block. Skips
 * the write on non-Apache servers, where nothing was ever inserted.
 *
 * @since 26.05.0
 */
function thisismyurl_waihp_deactivate() {
	delete_option( 'thisismyurl_waihp_status' );

	if ( ! thisismyurl_waihp_server_is_apache() ) {
		return;
	}

	if ( ! function_exists( 'insert_with_markers' ) ) {
		require_once ABSPATH . 'wp-admin/includes/misc.php';
	}

	$htaccess = thisismyurl_waihp_htaccess_path();
	insert_with_markers( $htaccess, THISISMYURL_WAIHP_MARKER, array() );
}

/**
 * Build the htaccess rewrite rules for hotlink protection.
 *
 * Scope: only requests under /wp-content/uploads/ are protected, so theme,
 * plugin, and block-editor assets embedded elsewhere are never blocked.
 *
 * Allowed: an empty Referer (direct navigation, feed readers, some privacy
 * browsers) and any request whose Referer host is the site's registrable
 * domain or any subdomain of it (cdn., shop., staging., www.). Everything else
 * requesting a protected image file returns 403 Forbidden.
 *
 * @since 26.05.0
 * @return string[] Lines to write between the marker comments.
 */
function thisismyurl_waihp_rules() {
	$site_url = wp_parse_url( home_url() );
	$domain   = isset( $site_url['host'] ) ? $site_url['host'] : '';

	// Strip www. so the bare registrable domain anchors the subdomain match.
	$domain_bare = preg_replace( '/^www\./i', '', $domain );

	// Allow the bare domain and any subdomain: ^https?://([^/]+\.)?example\.com
	$referer_pattern = '!^https?://([^/]+\.)?' . preg_quote( $domain_bare, '/' ) . '(/|$)';

	$uploads_path = thisismyurl_waihp_uploads_request_path();

	return array(
		'<IfModule mod_rewrite.c>',
		'  RewriteEngine On',
		'  RewriteCond %{REQUEST_URI} ' . $uploads_path . ' [NC]',
		'  RewriteCond %{HTTP_REFERER} !^$',
		'  RewriteCond %{HTTP_REFERER} ' . $referer_pattern . ' [NC]',
		'  RewriteRule \.(jpe?g|png|gif|webp|avif)$ - [NC,F,L]',
		'</IfModule>',
	);
}

/**
 * Request-URI fragment that scopes protection to the uploads directory.
 *
 * Derived from the uploads base URL so it works on installs that moved
 * wp-content or use a custom UPLOADS constant. Falls back to the default path.
 *
 * @since 26.05.0
 * @return string Escaped path fragment for a RewriteCond, e.g. "/wp-content/uploads/".
 */
function thisismyurl_waihp_uploads_request_path() {
	$uploads = wp_get_upload_dir();
	$baseurl = isset( $uploads['baseurl'] ) ? $uploads['baseurl'] : '';
	$parsed  = wp_parse_url( $baseurl );
	$path    = isset( $parsed['path'] ) ? $parsed['path'] : '/wp-content/uploads';

	// Ensure a trailing slash and escape regex metacharacters for mod_rewrite.
	$path = '/' . trim( $path, '/' ) . '/';

	return preg_quote( $path, '/' );
}

if ( is_admin() ) {
	add_action( 'admin_menu', 'thisismyurl_waihp_register_settings_page' );
	add_action( 'admin_notices', 'thisismyurl_waihp_admin_notices' );
}

/**
 * Register the read-only status page under Settings.
 *
 * @since 26.05.0
 */
function thisismyurl_waihp_register_settings_page() {
	add_options_page(
		__( 'Hotlink Protection', 'wordpress-automatic-image-hotlink-protection' ),
		__( 'Hotlink Protection', 'wordpress-automatic-image-hotlink-protection' ),
		'manage_options',
		'thisismyurl-waihp',
		'thisismyurl_waihp_render_settings_page'
	);
}

/**
 * Render the status page: server type, protection state, and .htaccess writability.
 *
 * Read-only by design — the plugin protects on activation with no options to
 * configure, so this page reports state rather than collecting input.
 *
 * @since 26.05.0
 */
function thisismyurl_waihp_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$is_apache = thisismyurl_waihp_server_is_apache();
	$status    = get_option( 'thisismyurl_waihp_status', 'unknown' );
	$htaccess  = thisismyurl_waihp_htaccess_path();
	$writable  = file_exists( $htaccess ) ? wp_is_writable( $htaccess ) : wp_is_writable( dirname( $htaccess ) );

	$software = isset( $_SERVER['SERVER_SOFTWARE'] )
		? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) )
		: __( 'Unknown', 'wordpress-automatic-image-hotlink-protection' );

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Hotlink Protection', 'wordpress-automatic-image-hotlink-protection' ); ?></h1>
		<p><?php esc_html_e( 'This page reports the current protection status. There is nothing to configure: protection is applied automatically on activation for image files in your uploads directory.', 'wordpress-automatic-image-hotlink-protection' ); ?></p>

		<table class="widefat striped" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Server software', 'wordpress-automatic-image-hotlink-protection' ); ?></th>
					<td><?php echo esc_html( $software ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Supports .htaccess', 'wordpress-automatic-image-hotlink-protection' ); ?></th>
					<td>
						<?php if ( $is_apache ) : ?>
							<strong><?php esc_html_e( 'Yes (Apache-compatible)', 'wordpress-automatic-image-hotlink-protection' ); ?></strong>
						<?php else : ?>
							<strong><?php esc_html_e( 'No', 'wordpress-automatic-image-hotlink-protection' ); ?></strong>
							&mdash;
							<?php esc_html_e( 'this server ignores .htaccess. Configure hotlink protection at the server or CDN/edge layer instead.', 'wordpress-automatic-image-hotlink-protection' ); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( '.htaccess location', 'wordpress-automatic-image-hotlink-protection' ); ?></th>
					<td><code><?php echo esc_html( $htaccess ); ?></code></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( '.htaccess writable', 'wordpress-automatic-image-hotlink-protection' ); ?></th>
					<td>
						<?php if ( $writable ) : ?>
							<strong><?php esc_html_e( 'Yes', 'wordpress-automatic-image-hotlink-protection' ); ?></strong>
						<?php else : ?>
							<strong><?php esc_html_e( 'No', 'wordpress-automatic-image-hotlink-protection' ); ?></strong>
							&mdash;
							<?php esc_html_e( 'the rules cannot be written or removed automatically until this file is writable.', 'wordpress-automatic-image-hotlink-protection' ); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Protection status', 'wordpress-automatic-image-hotlink-protection' ); ?></th>
					<td><?php echo esc_html( thisismyurl_waihp_status_label( $status ) ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Human-readable label for a stored status value.
 *
 * @since 26.05.0
 * @param string $status One of: active, write_failed, unsupported_server, unknown.
 * @return string Translated label.
 */
function thisismyurl_waihp_status_label( $status ) {
	switch ( $status ) {
		case 'active':
			return __( 'Active — hotlink rules are in your .htaccess.', 'wordpress-automatic-image-hotlink-protection' );
		case 'write_failed':
			return __( 'Inactive — the .htaccess file could not be written.', 'wordpress-automatic-image-hotlink-protection' );
		case 'unsupported_server':
			return __( 'Inactive — this server does not support .htaccess.', 'wordpress-automatic-image-hotlink-protection' );
		default:
			return __( 'Unknown — re-activate the plugin to refresh.', 'wordpress-automatic-image-hotlink-protection' );
	}
}

/**
 * Surface activation failures as admin notices.
 *
 * Fires only for users who can act on the problem, and only when the stored
 * status records a failure mode worth interrupting for.
 *
 * @since 26.05.0
 */
function thisismyurl_waihp_admin_notices() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$status = get_option( 'thisismyurl_waihp_status', '' );

	if ( 'unsupported_server' === $status ) {
		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html__( 'Hotlink Protection: this server does not use .htaccess (e.g. nginx), so no rules were written. Configure hotlink protection at the server or CDN/edge layer instead.', 'wordpress-automatic-image-hotlink-protection' )
		);
	} elseif ( 'write_failed' === $status ) {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'Hotlink Protection: your .htaccess file is not writable, so the protection rules could not be added. Make .htaccess writable and re-activate the plugin.', 'wordpress-automatic-image-hotlink-protection' )
		);
	}
}
