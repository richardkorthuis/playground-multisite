<?php
/**
 * The plugin bootstrap file
 *
 * @package Playground_Multisite
 *
 * @wordpress-plugin
 * Plugin Name:       Playground Multisite
 * Description:       Convert a default WordPress Playground site to a multisite.
 * Version:           0.1.0
 * Author:            Richard Korthuis - Acato
 * Author URI:        https://www.acato.nl
 * Text Domain:       playground-multisite
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Add activation hook.
register_activation_hook( __FILE__, 'pgms_convert_to_multisite' );

/**
 * Convert the site to a multisite.
 *
 * @return void
 */
function pgms_convert_to_multisite() {
	global $wpdb;

	if ( defined( 'WP_ALLOW_MULTISITE' ) && WP_ALLOW_MULTISITE && ! is_multisite() ) {
		require_once ABSPATH . 'wp-admin/includes/network.php';
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// We need to create references to ms global tables to enable Network.
		foreach ( $wpdb->tables( 'ms_global' ) as $table => $prefixed_table ) {
			$wpdb->$table = $prefixed_table;
		}

		\install_network();
		$base = wp_parse_url( trailingslashit( get_option( 'home' ) ), PHP_URL_PATH );
		if ( ! \network_domain_check() ) {
			\populate_network( 1, get_clean_basedomain(), sanitize_email( get_bloginfo( 'admin_email' ) ), wp_unslash( get_bloginfo( 'name' ) ), $base, false );

			$hostname = get_clean_basedomain();

			if (
				defined( 'PGMS_CONFIG_UPDATE' )
				&& PGMS_CONFIG_UPDATE
				&& file_exists( ABSPATH . 'wp-config.php' )
				&& is_writable( ABSPATH . 'wp-config.php' )
			) {
				$config = file_get_contents( ABSPATH . 'wp-config.php' );

				$new_config = preg_replace(
					'/<\?(php)?/',
					"\\0\r\ndefine('MULTISITE', true );\r\ndefine('SUBDOMAIN_INSTALL',false);\r\ndefine('DOMAIN_CURRENT_SITE','" . addslashes( $hostname ) . "');\r\ndefine('PATH_CURRENT_SITE','" . addslashes( $base ) . "');\r\ndefine('SITE_ID_CURRENT_SITE', 1);\r\ndefine('BLOG_ID_CURRENT_SITE', 1);\r\n",
					$config,
					1
				);

				file_put_contents( ABSPATH . 'wp-config.php', $new_config );
			}

			if (
				defined( 'PGMS_JSON_UPDATE' )
				&& PGMS_JSON_UPDATE
				&& file_exists( '/tmp/consts.json' )
				&& is_writable( '/tmp/consts.json' )
			) {
				$consts = json_decode( file_get_contents( '/tmp/consts.json' ), true );

				$consts['MULTISITE']            = true;
				$consts['SUBDOMAIN_INSTALL']    = false;
				$consts['DOMAIN_CURRENT_SITE']  = $hostname;
				$consts['PATH_CURRENT_SITE']    = $base;
				$consts['SITE_ID_CURRENT_SITE'] = 1;
				$consts['BLOG_ID_CURRENT_SITE'] = 1;

				file_put_contents( '/tmp/consts.json', wp_json_encode( $consts ) );
			}

			if (
				file_exists( ABSPATH . '.htaccess' )
				&& is_writable( ABSPATH . '.htaccess' )
			) {
				$htaccess = file_get_contents( ABSPATH . '.htaccess' );

				$new_htaccess = preg_replace(
					'/(?s)(?<=# BEGIN WordPress\n).*?(?=\n# END WordPress)/',
					'RewriteEngine On' . PHP_EOL
					. 'RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]' . PHP_EOL
					. 'RewriteBase ' . $base . PHP_EOL . 'RewriteRule ^index\.php$ - [L]' . PHP_EOL
					. PHP_EOL
					. '# add a trailing slash to /wp-admin' . PHP_EOL
					. 'RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ \$1wp-admin/ [R=301,L]' . PHP_EOL
					. PHP_EOL
					. 'RewriteCond %{REQUEST_FILENAME} -f [OR]' . PHP_EOL
					. 'RewriteCond %{REQUEST_FILENAME} -d' . PHP_EOL
					. 'RewriteRule ^ - [L]' . PHP_EOL
					. 'RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) wordpress/\$2 [L]' . PHP_EOL
					. 'RewriteRule ^([_0-9a-zA-Z-]+/)?(.*\.php)$ wordpress/\$2 [L]' . PHP_EOL
					. 'RewriteRule . index.php [L]',
					$htaccess,
					1
				);

				file_put_contents( ABSPATH . '.htaccess', $new_htaccess );
			}
		}
	}
}
