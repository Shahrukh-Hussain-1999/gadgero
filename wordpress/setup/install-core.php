<?php
/**
 * Local install of WordPress core for the Gadgero build (no WP-CLI needed).
 *
 * Usage: php install-core.php <path-to-wordpress> <site-url>
 * Writes the generated admin login to wordpress/setup/local-credentials.txt (git-ignored).
 */
if ( PHP_SAPI !== 'cli' ) { exit( 1 ); }
[ , $wp_path, $site_url ] = $argv + [ null, null, null ];
if ( ! $wp_path || ! $site_url ) { fwrite( STDERR, "usage: php install-core.php <wp-path> <site-url>\n" ); exit( 1 ); }

$parts                  = parse_url( $site_url );
$_SERVER['HTTP_HOST']   = $parts['host'] . ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' );
$_SERVER['REQUEST_URI'] = ( $parts['path'] ?? '' ) . '/';
$_SERVER['SERVER_NAME'] = $parts['host'];
define( 'WP_INSTALLING', true );
define( 'WP_SITEURL', rtrim( $site_url, '/' ) );

require rtrim( $wp_path, '/\\' ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

if ( is_blog_installed() ) { echo "already installed\n"; exit( 0 ); }

$user = 'gadgero-admin';
$pass = wp_generate_password( 20, false );
$res  = wp_install( 'Gadgero', $user, 'admin@gadgero.test', false, '', $pass, 'en_US' );
update_option( 'home', rtrim( $site_url, '/' ) );
update_option( 'siteurl', rtrim( $site_url, '/' ) );

$cred_file = __DIR__ . '/local-credentials.txt';
file_put_contents( $cred_file, "Local wp-admin login (test site only)\nURL: " . rtrim( $site_url, '/' ) . "/wp-admin/\nUser: $user\nPassword: $pass\n" );
echo "installed, user id {$res['user_id']}; login written to $cred_file\n";
