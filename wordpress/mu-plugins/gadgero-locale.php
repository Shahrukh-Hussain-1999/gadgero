<?php
/**
 * Plugin Name: Gadgero locale routing
 * Description: Storefront in English; cart, checkout, order confirmation, customer e-mails and legal pages in German (de_DE).
 *
 * Runs as an mu-plugin because the locale must be decided before WordPress and WooCommerce load their translations,
 * which happens before a theme's functions.php is read.
 *
 * German routes are page slugs stored in the option `gadgero_german_paths` (set by wordpress/setup/seed.php),
 * plus the WooCommerce AJAX endpoints the cart and checkout call.
 */

defined( 'ABSPATH' ) || exit;

/** Is the current front-end request one of the German routes? */
function gadgero_is_german_route() {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}

	$german_ajax = array( 'update_order_review', 'checkout', 'apply_coupon', 'remove_coupon', 'update_shipping_method', 'get_cart_totals', 'remove_from_cart_page' );
	if ( isset( $_GET['wc-ajax'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$ep = sanitize_key( wp_unslash( $_GET['wc-ajax'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( in_array( $ep, $german_ajax, true ) ) {
			return $cached = true;
		}
		// Mini-cart fragments etc. follow the page that asked for them.
		$ref = isset( $_SERVER['HTTP_REFERER'] ) ? wp_unslash( $_SERVER['HTTP_REFERER'] ) : '';
		return $cached = $ref ? gadgero_path_is_german( (string) wp_parse_url( $ref, PHP_URL_PATH ) ) : false;
	}

	$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	return $cached = gadgero_path_is_german( (string) wp_parse_url( $uri, PHP_URL_PATH ) );
}

/** Does a URL path (with or without the install sub-directory) start with a German page slug? */
function gadgero_path_is_german( $path ) {
	$home_path = (string) wp_parse_url( get_option( 'home' ), PHP_URL_PATH );
	if ( $home_path && 0 === strpos( $path, $home_path ) ) {
		$path = substr( $path, strlen( $home_path ) );
	}
	$first = strtok( trim( $path, '/' ), '/' );
	if ( ! $first ) {
		return false;
	}
	$paths = (array) get_option( 'gadgero_german_paths', array() );
	return in_array( $first, $paths, true );
}

add_filter(
	'locale',
	function ( $locale ) {
		// Back office, cron and CLI: site locale is German so customer e-mails sent from wp-admin are German.
		// Each admin user's own UI language is set separately (user profile language).
		if ( is_admin() && ! wp_doing_ajax() ) {
			return 'de_DE_formal';
		}
		if ( wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return 'de_DE_formal';
		}
		return gadgero_is_german_route() ? 'de_DE_formal' : 'en_US';
	},
	1
);

/*
 * Customer e-mails: WooCommerce switches to the site locale (get_locale()) before sending. Orders are placed
 * through the German checkout and status mails are sent from wp-admin, both of which resolve to German above.
 * de_DE_formal = WooCommerce's German with "Sie", matching the legal pages.
 */

/** de_DE_formal would print lang="de-DE-formal"; browsers and screen readers expect lang="de-DE". */
add_filter( 'language_attributes', fn( $out ) => str_replace( 'de-DE-formal', 'de-DE', $out ) );
