<?php
/**
 * Plugin Name: Gadgero locale
 * Description: The whole storefront is German ("Sie", de_DE_formal), as requested in the client brief of 15.11.2026.
 *              wp-admin follows each user's own profile language.
 *
 * Runs as an mu-plugin so the locale is fixed before WordPress and WooCommerce load their translations.
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'locale',
	function ( $locale ) {
		return 'de_DE_formal';
	},
	1
);

/** de_DE_formal would print lang="de-DE-formal"; browsers and screen readers expect lang="de-DE". */
add_filter( 'language_attributes', fn( $out ) => str_replace( 'de-DE-formal', 'de-DE', $out ) );
