<?php
/**
 * Gadgero local seed. Run with: wordpress/bin/wp eval-file wordpress/setup/seed.php
 *
 * Re-runnable: removes products it created earlier (meta _gadgero_seed) and recreates them.
 *
 * !!! TEST CATALOGUE !!!
 * Every price, SKU and stock quantity below is a PLACEHOLDER so variations, cart and checkout can be tested.
 * They are NOT approved prices. Every product is flagged _gadgero_unconfirmed = yes and listed in wp-admin.
 * Euro amounts reuse the numbers of the approved static demo; they must be replaced by the client's prices.
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( 1 );
}

$theme_dir = get_theme_root() . '/gadgero';
$content   = __DIR__ . '/content';
WP_CLI::log( 'Seeding Gadgero…' );

/* ------------------------------------------------------------------ */
/* 1. Theme, permalinks, languages                                     */
/* ------------------------------------------------------------------ */
switch_theme( 'gadgero' );
update_option( 'permalink_structure', '/%postname%/' );
$GLOBALS['wp_rewrite']->init(); // So get_permalink() below returns pretty URLs (menu links had /index.php/ otherwise).
update_option( 'WPLANG', 'de_DE_formal' ); // Site default; the gadgero-locale mu-plugin serves English on storefront routes.
update_option( 'blogname', 'Gadgero' );
update_option( 'blogdescription', 'New and refurbished phones, tablets, laptops, desktops, smartwatches and accessories' );
update_option( 'timezone_string', 'Europe/Berlin' );
update_option( 'date_format', 'd.m.Y' );
foreach ( get_users( array( 'role' => 'administrator' ) ) as $u ) {
	update_user_meta( $u->ID, 'locale', 'en_US' ); // wp-admin in English for the team.
}

/* ------------------------------------------------------------------ */
/* 2. WooCommerce core settings (Germany, EUR, guest checkout)          */
/* ------------------------------------------------------------------ */
$wc = array(
	'woocommerce_default_country'               => 'DE',
	'woocommerce_allowed_countries'             => 'specific',
	'woocommerce_specific_allowed_countries'    => array( 'DE' ), // CLIENT CONFIRMATION REQUIRED: delivery areas.
	'woocommerce_ship_to_countries'             => '',
	'woocommerce_currency'                      => 'EUR',
	'woocommerce_currency_pos'                  => 'right_space',
	'woocommerce_price_thousand_sep'            => '.',
	'woocommerce_price_decimal_sep'             => ',',
	'woocommerce_price_num_decimals'            => '2',
	'woocommerce_calc_taxes'                    => 'yes',
	'woocommerce_prices_include_tax'            => 'yes',
	'woocommerce_tax_based_on'                  => 'billing',
	'woocommerce_tax_display_shop'              => 'incl',
	'woocommerce_tax_display_cart'              => 'incl',
	'woocommerce_tax_total_display'             => 'single',
	'woocommerce_enable_guest_checkout'         => 'yes',
	'woocommerce_enable_checkout_login_reminder'=> 'no',
	'woocommerce_enable_signup_and_login_from_checkout' => 'no',
	'woocommerce_enable_myaccount_registration' => 'no',
	'woocommerce_manage_stock'                  => 'yes',
	'woocommerce_hide_out_of_stock_items'       => 'no',
	'woocommerce_stock_format'                  => '',
	'woocommerce_notify_no_stock_amount'        => '0',
	'woocommerce_cart_redirect_after_add'       => 'no',
	'woocommerce_enable_ajax_add_to_cart'       => 'yes',
	'woocommerce_enable_reviews'                => 'no',
	'woocommerce_weight_unit'                   => 'kg',
	'woocommerce_dimension_unit'                => 'cm',
	'woocommerce_ship_to_destination'           => 'billing',
	'woocommerce_coming_soon'                   => 'no',
	'woocommerce_store_pages_only'              => 'no',
	'woocommerce_checkout_phone_field'          => 'optional',
	'woocommerce_checkout_company_field'        => 'optional',
	'woocommerce_checkout_address_2_field'      => 'optional',
	'woocommerce_checkout_highlight_required_fields' => 'yes',
	'woocommerce_show_marketplace_suggestions'  => 'no',
	'woocommerce_allow_tracking'                => 'no',
	'woocommerce_task_list_hidden'              => 'yes',
	'woocommerce_onboarding_profile'            => array( 'completed' => true, 'skipped' => true ),
	'woocommerce_feature_product_block_editor_enabled' => 'no',
);
foreach ( $wc as $k => $v ) {
	update_option( $k, $v );
}

// German VAT 19 % (standard rate). Reduced/differential taxation for used goods: CLIENT CONFIRMATION REQUIRED.
global $wpdb;
$has_rate = $wpdb->get_var( "SELECT tax_rate_id FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_country='DE' AND tax_rate_class=''" );
if ( ! $has_rate ) {
	WC_Tax::_insert_tax_rate(
		array(
			'tax_rate_country'  => 'DE',
			'tax_rate'          => '19.0000',
			'tax_rate_name'     => 'MwSt.',
			'tax_rate_priority' => 1,
			'tax_rate_compound' => 0,
			'tax_rate_shipping' => 1,
			'tax_rate_order'    => 0,
			'tax_rate_class'    => '',
		)
	);
}

// Shipping: one zone for Germany with a test rate. Cost/label/delivery time: CLIENT CONFIRMATION REQUIRED.
$zone_id = 0;
foreach ( WC_Shipping_Zones::get_zones() as $z ) {
	if ( 'Deutschland' === $z['zone_name'] ) {
		$zone_id = $z['id'];
	}
}
if ( ! $zone_id ) {
	$zone = new WC_Shipping_Zone();
	$zone->set_zone_name( 'Deutschland' );
	$zone->add_location( 'DE', 'country' );
	$zone->save();
	$iid    = $zone->add_shipping_method( 'flat_rate' );
	$opt    = 'woocommerce_flat_rate_' . $iid . '_settings';
	update_option( $opt, array( 'title' => 'Standardversand', 'tax_status' => 'taxable', 'cost' => '0' ) );
}

// Payment: bank transfer only, for local testing. No bank details entered (CLIENT CONFIRMATION REQUIRED).
update_option(
	'woocommerce_bacs_settings',
	array(
		'enabled'      => 'yes',
		'title'        => 'Vorkasse per Überweisung',
		'description'  => 'Sie erhalten die Bankverbindung nach Abschluss der Bestellung.',
		'instructions' => 'Bitte überweisen Sie den Gesamtbetrag unter Angabe der Bestellnummer.',
	)
);
foreach ( array( 'cheque', 'cod', 'paypal' ) as $g ) {
	$s            = get_option( 'woocommerce_' . $g . '_settings', array() );
	$s['enabled'] = 'no';
	update_option( 'woocommerce_' . $g . '_settings', $s );
}

/* ------------------------------------------------------------------ */
/* 3. Pages                                                            */
/* ------------------------------------------------------------------ */
function gadgero_seed_page( $slug, $title, $content ) {
	$p = get_page_by_path( $slug );
	$data = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_name'    => $slug,
		'post_title'   => $title,
		'post_content' => $content,
	);
	if ( $p ) {
		$data['ID'] = $p->ID;
		return wp_update_post( $data );
	}
	return wp_insert_post( $data );
}
$pages = array(
	'home'                     => gadgero_seed_page( 'home', 'Home', '' ),
	'shop'                     => gadgero_seed_page( 'shop', 'Shop', '' ),
	'warenkorb'                => gadgero_seed_page( 'warenkorb', 'Warenkorb', '<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->' ),
	'kasse'                    => gadgero_seed_page( 'kasse', 'Kasse', '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->' ),
	'impressum'                => gadgero_seed_page( 'impressum', 'Impressum', file_get_contents( $content . '/impressum.html' ) ),
	'datenschutz'              => gadgero_seed_page( 'datenschutz', 'Datenschutzerklärung', file_get_contents( $content . '/datenschutz.html' ) ),
	'widerrufsbelehrung'       => gadgero_seed_page( 'widerrufsbelehrung', 'Widerrufsbelehrung', file_get_contents( $content . '/widerrufsbelehrung.html' ) ),
	'muster-widerrufsformular' => gadgero_seed_page( 'muster-widerrufsformular', 'Muster-Widerrufsformular', file_get_contents( $content . '/muster-widerrufsformular.html' ) ),
	'versand-und-zahlung'      => gadgero_seed_page( 'versand-und-zahlung', 'Versand & Zahlung', file_get_contents( $content . '/versand-und-zahlung.html' ) ),
	'garantie-und-service'     => gadgero_seed_page( 'garantie-und-service', 'Garantie & Service', file_get_contents( $content . '/garantie-und-service.html' ) ),
);
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $pages['home'] );
update_option( 'woocommerce_shop_page_id', $pages['shop'] );
update_option( 'woocommerce_cart_page_id', $pages['warenkorb'] );
update_option( 'woocommerce_checkout_page_id', $pages['kasse'] );
update_option( 'woocommerce_myaccount_page_id', 0 );
update_option( 'woocommerce_terms_page_id', 0 ); // No AGB yet: CLIENT CONFIRMATION REQUIRED.
update_option( 'wp_page_for_privacy_policy', $pages['datenschutz'] );
update_option( 'woocommerce_checkout_privacy_policy_text', 'Ihre personenbezogenen Daten werden zur Abwicklung Ihrer Bestellung verwendet. Details finden Sie in unserer [privacy_policy]. Informationen zu Ihrem Widerrufsrecht finden Sie in der <a href="' . esc_url( get_permalink( $pages['widerrufsbelehrung'] ) ) . '">Widerrufsbelehrung</a>.' );
update_option( 'woocommerce_registration_privacy_policy_text', '' );
update_option( 'gadgero_german_paths', array( 'warenkorb', 'kasse', 'impressum', 'datenschutz', 'widerrufsbelehrung', 'muster-widerrufsformular', 'versand-und-zahlung', 'garantie-und-service' ) );
// Remove default content.
foreach ( array( 'sample-page', 'hello-world' ) as $s ) {
	$p = get_page_by_path( $s, OBJECT, array( 'page', 'post' ) );
	if ( $p ) {
		wp_delete_post( $p->ID, true );
	}
}
foreach ( array( 'refund_returns', 'my-account', 'cart', 'checkout' ) as $s ) {
	$p = get_page_by_path( $s );
	if ( $p && ! in_array( $p->ID, $pages, true ) ) {
		wp_delete_post( $p->ID, true );
	}
}

/* ------------------------------------------------------------------ */
/* 4. Attributes and categories                                        */
/* ------------------------------------------------------------------ */
function gadgero_seed_attr( $slug, $label, array $terms ) {
	$id = wc_attribute_taxonomy_id_by_name( $slug );
	if ( ! $id ) {
		$id = wc_create_attribute( array( 'name' => $label, 'slug' => $slug, 'type' => 'select', 'order_by' => 'menu_order', 'has_archives' => false ) );
	}
	$tax = wc_attribute_taxonomy_name( $slug );
	if ( ! taxonomy_exists( $tax ) ) {
		register_taxonomy( $tax, array( 'product' ), array( 'hierarchical' => false, 'show_ui' => false, 'query_var' => true, 'rewrite' => false ) );
	}
	$order = 0;
	foreach ( $terms as $t ) {
		$term = get_term_by( 'name', $t, $tax );
		if ( ! $term ) {
			$r    = wp_insert_term( $t, $tax );
			$term = get_term( $r['term_id'], $tax );
		}
		update_term_meta( $term->term_id, 'order', $order++ );
	}
	return $tax;
}
gadgero_seed_attr( 'condition', 'Condition', array( 'New', 'Refurbished' ) );
gadgero_seed_attr( 'storage', 'Storage', array( '64 GB', '128 GB', '256 GB', '512 GB', '1 TB' ) );
gadgero_seed_attr( 'colour', 'Colour', array( 'Silver', 'Cosmic Orange', 'Deep Blue', 'Blue', 'Space Grey', 'Black', 'Ultramarine', 'Cobalt Violet', 'Onyx Black', 'Midnight', 'Space Black', 'Natural Titanium', 'White' ) );
gadgero_seed_attr( 'connectivity', 'Connectivity', array( 'Wi-Fi', 'Wi-Fi + Cellular', 'GPS + Cellular' ) );
gadgero_seed_attr( 'screen-size', 'Screen Size', array( '10.9-inch', '11-inch', '13-inch', '14-inch', '24-inch', '49 mm' ) );

$cat_ids = array();
$order   = 0;
foreach ( array( 'phones' => 'Phones', 'tablets' => 'Tablets', 'laptops' => 'Laptops', 'desktops' => 'Desktops', 'smartwatches' => 'Smartwatches', 'accessories' => 'Accessories' ) as $slug => $name ) {
	$t = get_term_by( 'slug', $slug, 'product_cat' );
	if ( ! $t ) {
		$r = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
		$t = get_term( $r['term_id'], 'product_cat' );
	}
	update_term_meta( $t->term_id, 'order', $order++ );
	$cat_ids[ $slug ] = $t->term_id;
}
$GLOBALS['cat_ids'] = $cat_ids; // eval-file runs inside a function scope.
$uncat = get_term_by( 'slug', 'uncategorized', 'product_cat' );
if ( $uncat ) {
	wp_update_term( $uncat->term_id, 'product_cat', array( 'name' => 'Other' ) );
}

/* ------------------------------------------------------------------ */
/* 5. Products (TEST CATALOGUE — placeholders, see header)              */
/* ------------------------------------------------------------------ */
foreach ( get_posts( array( 'post_type' => 'product', 'posts_per_page' => -1, 'post_status' => 'any', 'meta_key' => '_gadgero_seed', 'fields' => 'ids' ) ) as $old ) { // phpcs:ignore
	$p = wc_get_product( $old );
	if ( $p ) {
		foreach ( $p->get_children() as $c ) {
			wp_delete_post( $c, true );
		}
		$img = $p->get_image_id();
		$p->delete( true );
		if ( $img ) {
			wp_delete_attachment( $img, true );
		}
	}
}

/** Import a theme image into the media library (once per file). */
function gadgero_seed_image( $file, $title ) {
	static $cache = array();
	if ( isset( $cache[ $file ] ) ) {
		return $cache[ $file ];
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	$src = get_theme_root() . '/gadgero/assets/img/products/' . $file;
	$tmp = wp_tempnam( $file );
	copy( $src, $tmp );
	$id = media_handle_sideload( array( 'name' => $file, 'tmp_name' => $tmp ), 0, $title );
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $title );
	return $cache[ $file ] = $id;
}

function gadgero_term_ids( $tax, array $names ) {
	return array_map( fn( $n ) => get_term_by( 'name', $n, $tax )->term_id, $names );
}

/** Attribute objects: $spec = [ 'pa_x' => [ 'values' => [...], 'variation' => bool ] ] */
function gadgero_attrs( array $spec ) {
	$out = array();
	$pos = 0;
	foreach ( $spec as $tax => $s ) {
		$a = new WC_Product_Attribute();
		$a->set_id( wc_attribute_taxonomy_id_by_name( $tax ) );
		$a->set_name( $tax );
		$a->set_options( gadgero_term_ids( $tax, $s['values'] ) );
		$a->set_position( $pos++ );
		$a->set_visible( true );
		$a->set_variation( ! empty( $s['variation'] ) );
		$out[ $tax ] = $a;
	}
	return $out;
}

function gadgero_seed_common( WC_Product $p, array $d, $order ) {
	global $cat_ids;
	$p->set_name( $d['name'] );
	$p->set_status( 'publish' );
	$p->set_menu_order( $order );
	$p->set_category_ids( array( $cat_ids[ $d['cat'] ] ) );
	$p->set_short_description( $d['short'] ?? '' );
	$p->set_description( $d['desc'] ?? '' );
	if ( ! empty( $d['img'] ) ) {
		$p->set_image_id( gadgero_seed_image( $d['img'], $d['name'] ) );
	}
	$p->update_meta_data( '_gadgero_seed', 1 );
	$p->update_meta_data( '_gadgero_unconfirmed', 'yes' );
	foreach ( array( 'summary', 'sealed', 'battery', 'screen', 'frame', 'accessories', 'art', 'art_c1', 'art_c2', 'art_frame', 'glow' ) as $k ) {
		if ( isset( $d[ $k ] ) ) {
			$p->update_meta_data( '_gadgero_' . $k, $d[ $k ] );
		}
	}
}

function gadgero_slug( $tax, $name ) {
	return get_term_by( 'name', $name, $tax )->slug;
}

$catalogue = array(
	/* Phones */
	array(
		'name' => 'Apple iPhone Duo', 'cat' => 'phones', 'cond' => 'New', 'type' => 'variable',
		'short' => 'A foldable iPhone that opens into a tablet-size screen.',
		'art' => 'phone', 'art_c1' => '#35c4ff', 'art_c2' => '#0a3fb8', 'art_frame' => '#b7c3d9', 'glow' => '#7fc8ff',
		'attrs' => array( 'pa_storage' => array( 'values' => array( '256 GB', '512 GB' ), 'variation' => true ) ),
		'vars' => array(
			array( array( 'pa_storage' => '256 GB' ), 'GAD-DUO-256', 1799, 3 ),
			array( array( 'pa_storage' => '512 GB' ), 'GAD-DUO-512', 1999, 2 ),
		),
	),
	array(
		'name' => 'iPhone 17 Pro', 'cat' => 'phones', 'cond' => 'New', 'type' => 'variable',
		'art' => 'phone', 'art_c1' => '#c7ccd6', 'art_c2' => '#4a5568', 'art_frame' => '#d5d9e0', 'glow' => '#c8d2e6',
		'attrs' => array(
			'pa_colour'  => array( 'values' => array( 'Silver', 'Cosmic Orange', 'Deep Blue' ), 'variation' => true ),
			'pa_storage' => array( 'values' => array( '256 GB', '512 GB' ), 'variation' => true ),
		),
		'vars' => array(
			array( array( 'pa_colour' => 'Silver', 'pa_storage' => '256 GB' ), 'GAD-IP17P-SLV-256', 1099, 2 ),
			array( array( 'pa_colour' => 'Silver', 'pa_storage' => '512 GB' ), 'GAD-IP17P-SLV-512', 1299, 2 ),
			array( array( 'pa_colour' => 'Cosmic Orange', 'pa_storage' => '256 GB' ), 'GAD-IP17P-ORG-256', 1099, 2 ),
			array( array( 'pa_colour' => 'Cosmic Orange', 'pa_storage' => '512 GB' ), 'GAD-IP17P-ORG-512', 1299, 2 ),
			array( array( 'pa_colour' => 'Deep Blue', 'pa_storage' => '256 GB' ), 'GAD-IP17P-DBL-256', 1099, 2 ),
			array( array( 'pa_colour' => 'Deep Blue', 'pa_storage' => '512 GB' ), 'GAD-IP17P-DBL-512', 1299, 2 ),
		),
	),
	array( 'name' => 'iPhone 16', 'cat' => 'phones', 'cond' => 'New', 'sku' => 'GAD-IP16-128-UMA', 'price' => 699, 'stock' => 3, 'art' => 'phone', 'art_c1' => '#7aa2ff', 'art_c2' => '#1a2a66', 'art_frame' => '#6f7fb0', 'glow' => '#7aa2ff', 'attrs' => array( 'pa_storage' => array( 'values' => array( '128 GB' ) ), 'pa_colour' => array( 'values' => array( 'Ultramarine' ) ) ) ),
	array( 'name' => 'iPhone 15', 'cat' => 'phones', 'cond' => 'Refurbished', 'sku' => 'GAD-IP15-128-BLK-R1', 'price' => 449, 'stock' => 1, 'battery' => '94', 'art' => 'phone', 'art_c1' => '#5a5f6b', 'art_c2' => '#14161c', 'art_frame' => '#3a3d45', 'glow' => '#9aa3b5', 'attrs' => array( 'pa_storage' => array( 'values' => array( '128 GB' ) ), 'pa_colour' => array( 'values' => array( 'Black' ) ) ) ),
	array( 'name' => 'Galaxy S26', 'cat' => 'phones', 'cond' => 'New', 'sku' => 'GAD-S26-256-CVI', 'price' => 799, 'stock' => 3, 'img' => 's26.webp', 'glow' => '#b9a3ff', 'attrs' => array( 'pa_storage' => array( 'values' => array( '256 GB' ) ), 'pa_colour' => array( 'values' => array( 'Cobalt Violet' ) ) ) ),
	array( 'name' => 'Galaxy S26+', 'cat' => 'phones', 'cond' => 'New', 'sku' => 'GAD-S26P-256-ONX', 'price' => 999, 'stock' => 3, 'img' => 's26p.webp', 'glow' => '#9aa3c2', 'attrs' => array( 'pa_storage' => array( 'values' => array( '256 GB' ) ), 'pa_colour' => array( 'values' => array( 'Onyx Black' ) ) ) ),
	array( 'name' => 'Galaxy S26 Ultra', 'cat' => 'phones', 'cond' => 'New', 'sku' => 'GAD-S26U-256-SLV', 'price' => 1299, 'stock' => 2, 'img' => 's26u.webp', 'glow' => '#c8cde0', 'summary' => '256 GB · Silver · S Pen included', 'attrs' => array( 'pa_storage' => array( 'values' => array( '256 GB' ) ), 'pa_colour' => array( 'values' => array( 'Silver' ) ) ) ),
	/* Smartwatches + accessories (generation: CLIENT CONFIRMATION REQUIRED) */
	array( 'name' => 'Apple Watch Ultra', 'cat' => 'smartwatches', 'cond' => 'New', 'sku' => 'GAD-AWU-49-TI', 'price' => 799, 'stock' => 2, 'img' => 'watch.webp', 'glow' => '#d7cfc0', 'summary' => '49 mm · Titanium case · GPS + Cellular', 'attrs' => array( 'pa_screen-size' => array( 'values' => array( '49 mm' ) ), 'pa_colour' => array( 'values' => array( 'Natural Titanium' ) ), 'pa_connectivity' => array( 'values' => array( 'GPS + Cellular' ) ) ) ),
	array( 'name' => 'AirPods Pro', 'cat' => 'accessories', 'cond' => 'New', 'sku' => 'GAD-APP-WHT', 'price' => 249, 'stock' => 5, 'img' => 'airpods.webp', 'glow' => '#d9e3f2', 'summary' => 'Noise cancelling · Charging case', 'attrs' => array( 'pa_colour' => array( 'values' => array( 'White' ) ) ) ),
	/* Tablets */
	array(
		'name' => 'iPad Air M3', 'cat' => 'tablets', 'cond' => 'New', 'type' => 'variable', 'img' => 'ipadair.webp', 'glow' => '#7fb4ff',
		'short' => 'Choose screen size, storage, colour and connectivity. Each combination is a separate item with its own price and stock.',
		'attrs' => array(
			'pa_screen-size'  => array( 'values' => array( '11-inch', '13-inch' ), 'variation' => true ),
			'pa_storage'      => array( 'values' => array( '128 GB', '256 GB' ), 'variation' => true ),
			'pa_colour'       => array( 'values' => array( 'Blue', 'Space Grey' ), 'variation' => true ),
			'pa_connectivity' => array( 'values' => array( 'Wi-Fi', 'Wi-Fi + Cellular' ), 'variation' => true ),
		),
		'matrix' => true,
	),
	array( 'name' => 'iPad 10th Gen', 'cat' => 'tablets', 'cond' => 'Refurbished', 'sku' => 'GAD-IPAD10-64-SLV-WIFI-R1', 'price' => 279, 'stock' => 1, 'battery' => '96', 'art' => 'tablet', 'art_c1' => '#ffd27a', 'art_c2' => '#6a2c18', 'art_frame' => '#c9ccd1', 'glow' => '#ffd27a', 'attrs' => array( 'pa_screen-size' => array( 'values' => array( '10.9-inch' ) ), 'pa_storage' => array( 'values' => array( '64 GB' ) ), 'pa_colour' => array( 'values' => array( 'Silver' ) ), 'pa_connectivity' => array( 'values' => array( 'Wi-Fi' ) ) ) ),
	/* Laptops */
	array( 'name' => 'MacBook Air 13" M4', 'cat' => 'laptops', 'cond' => 'New', 'sku' => 'GAD-MBA13-M4-16-256-MID', 'price' => 999, 'stock' => 2, 'summary' => '16 GB / 256 GB · Midnight', 'art' => 'laptop', 'art_c1' => '#4a6fd8', 'art_c2' => '#0f1738', 'art_frame' => '#2a2f3d', 'glow' => '#4a6fd8', 'attrs' => array( 'pa_storage' => array( 'values' => array( '256 GB' ) ), 'pa_colour' => array( 'values' => array( 'Midnight' ) ) ) ),
	array( 'name' => 'MacBook Pro 14" M5', 'cat' => 'laptops', 'cond' => 'New', 'sku' => 'GAD-MBP14-M5-16-512-SBK', 'price' => 1599, 'stock' => 1, 'summary' => '16 GB / 512 GB · Space Black', 'art' => 'laptop', 'art_c1' => '#e8794a', 'art_c2' => '#3a1020', 'art_frame' => '#2b2c30', 'glow' => '#e8794a', 'attrs' => array( 'pa_storage' => array( 'values' => array( '512 GB' ) ), 'pa_colour' => array( 'values' => array( 'Space Black' ) ) ) ),
	array( 'name' => 'MacBook Air 13" M2', 'cat' => 'laptops', 'cond' => 'Refurbished', 'sku' => 'GAD-MBA13-M2-8-256-SLV-R1', 'price' => 599, 'stock' => 1, 'battery' => '91', 'summary' => '8 GB / 256 GB · Silver', 'art' => 'laptop', 'art_c1' => '#8fd0c0', 'art_c2' => '#16433f', 'art_frame' => '#c9ccd1', 'glow' => '#8fd0c0', 'attrs' => array( 'pa_storage' => array( 'values' => array( '256 GB' ) ), 'pa_colour' => array( 'values' => array( 'Silver' ) ) ) ),
	/* Desktops */
	array( 'name' => 'iMac 24" M4', 'cat' => 'desktops', 'cond' => 'New', 'sku' => 'GAD-IMAC24-M4-16-256-BLU', 'price' => 1299, 'stock' => 1, 'summary' => '16 GB / 256 GB · Blue', 'art' => 'desktop', 'art_c1' => '#7db4ff', 'art_c2' => '#142a63', 'art_frame' => '#6f93c8', 'glow' => '#7db4ff', 'attrs' => array( 'pa_screen-size' => array( 'values' => array( '24-inch' ) ), 'pa_storage' => array( 'values' => array( '256 GB' ) ), 'pa_colour' => array( 'values' => array( 'Blue' ) ) ) ),
	array( 'name' => 'Mac mini M4', 'cat' => 'desktops', 'cond' => 'New', 'sku' => 'GAD-MMINI-M4-16-256', 'price' => 599, 'stock' => 2, 'summary' => '16 GB / 256 GB · Silver', 'art' => 'mini', 'art_c1' => '#aab0bb', 'art_c2' => '#3b404a', 'art_frame' => '#c9ccd1', 'glow' => '#aab0bb', 'attrs' => array( 'pa_storage' => array( 'values' => array( '256 GB' ) ), 'pa_colour' => array( 'values' => array( 'Silver' ) ) ) ),
);

$ids = array();
foreach ( $catalogue as $i => $d ) {
	$attrs                  = $d['attrs'] ?? array();
	$attrs                  = array( 'pa_condition' => array( 'values' => array( $d['cond'] ) ) ) + $attrs;
	if ( 'variable' === ( $d['type'] ?? 'simple' ) ) {
		$p = new WC_Product_Variable();
		gadgero_seed_common( $p, $d, $i );
		$p->set_attributes( gadgero_attrs( $attrs ) );
		$pid = $p->save();

		$vars = $d['vars'] ?? array();
		if ( ! empty( $d['matrix'] ) ) { // iPad Air M3: every combination of the four variation attributes.
			foreach ( array( '11-inch', '13-inch' ) as $size ) {
				foreach ( array( '128 GB', '256 GB' ) as $st ) {
					foreach ( array( 'Blue', 'Space Grey' ) as $col ) {
						foreach ( array( 'Wi-Fi', 'Wi-Fi + Cellular' ) as $con ) {
							$price  = 599 + ( '13-inch' === $size ? 200 : 0 ) + ( '256 GB' === $st ? 120 : 0 ) + ( 'Wi-Fi' === $con ? 0 : 170 );
							$sku    = sprintf( 'GAD-IPADAIR-M3-%s-%s-%s-%s', '11-inch' === $size ? '11' : '13', str_replace( ' GB', '', $st ), 'Blue' === $col ? 'BLU' : 'SGR', 'Wi-Fi' === $con ? 'WIFI' : 'CELL' );
							// One combination is deliberately sold out to test that it cannot be added to the cart.
							$stock  = ( '13-inch' === $size && '256 GB' === $st && 'Space Grey' === $col && 'Wi-Fi + Cellular' === $con ) ? 0 : 3;
							$vars[] = array( array( 'pa_screen-size' => $size, 'pa_storage' => $st, 'pa_colour' => $col, 'pa_connectivity' => $con ), $sku, $price, $stock );
						}
					}
				}
			}
		}
		foreach ( $vars as $vd ) {
			$v   = new WC_Product_Variation();
			$v->set_parent_id( $pid );
			$set = array();
			foreach ( $vd[0] as $tax => $name ) {
				$set[ $tax ] = gadgero_slug( $tax, $name );
			}
			$v->set_attributes( $set );
			$v->set_sku( $vd[1] );
			$v->set_regular_price( (string) $vd[2] );
			$v->set_manage_stock( true );
			$v->set_stock_quantity( $vd[3] );
			$v->set_status( 'publish' );
			$v->save();
		}
		WC_Product_Variable::sync( $pid );
	} else {
		$p = new WC_Product_Simple();
		gadgero_seed_common( $p, $d, $i );
		$p->set_attributes( gadgero_attrs( $attrs ) );
		$p->set_sku( $d['sku'] );
		$p->set_regular_price( (string) $d['price'] );
		$p->set_manage_stock( true );
		$p->set_stock_quantity( $d['stock'] );
		$pid = $p->save();
	}
	$ids[ $d['name'] ] = $pid;
	WP_CLI::log( sprintf( '  %-24s #%d', $d['name'], $pid ) );
}

/* ------------------------------------------------------------------ */
/* 6. Campaign, menus                                                  */
/* ------------------------------------------------------------------ */
set_theme_mod( 'gadgero_campaign_product', $ids['Apple iPhone Duo'] );

function gadgero_seed_menu( $name, $location, array $items ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		wp_delete_nav_menu( $menu->term_id );
	}
	$mid = wp_create_nav_menu( $name );
	foreach ( $items as $i => $it ) {
		$args = array( 'menu-item-title' => $it[0], 'menu-item-status' => 'publish', 'menu-item-position' => $i + 1 );
		if ( is_int( $it[1] ) ) {
			$args += array( 'menu-item-object' => 'page', 'menu-item-object-id' => $it[1], 'menu-item-type' => 'post_type' );
		} else {
			$args += array( 'menu-item-url' => $it[1], 'menu-item-type' => 'custom' );
		}
		wp_update_nav_menu_item( $mid, 0, $args );
	}
	$locs              = get_theme_mod( 'nav_menu_locations', array() );
	$locs[ $location ] = $mid;
	set_theme_mod( 'nav_menu_locations', $locs );
}
$home = home_url( '/' );
gadgero_seed_menu(
	'Header',
	'primary',
	array( array( 'Home', $home ), array( 'Shop', $pages['shop'] ), array( 'About', $home . '#about' ), array( 'Sell yours', $home . '#sell' ), array( 'Contact', '#contact' ) )
);
gadgero_seed_menu(
	'Legal',
	'legal',
	array( array( 'Impressum', $pages['impressum'] ), array( 'Datenschutz', $pages['datenschutz'] ), array( 'Widerrufsbelehrung', $pages['widerrufsbelehrung'] ), array( 'Muster-Widerrufsformular', $pages['muster-widerrufsformular'] ) )
);
gadgero_seed_menu(
	'Service',
	'service',
	array( array( 'Versand & Zahlung', $pages['versand-und-zahlung'] ), array( 'Garantie & Service', $pages['garantie-und-service'] ), array( 'Shop new', add_query_arg( 'filter_condition', 'new', get_permalink( $pages['shop'] ) ) ), array( 'Shop refurbished', add_query_arg( 'filter_condition', 'refurbished', get_permalink( $pages['shop'] ) ) ) )
);

/* ------------------------------------------------------------------ */
/* 7. Items shown in the wp-admin warning besides products and contact   */
/* ------------------------------------------------------------------ */
update_option(
	'gadgero_pending_confirmations',
	array(
		'Shipping: zone "Deutschland" uses a TEST rate "Standardversand" at 0,00 € – real costs, delivery areas and delivery times needed.',
		'Payment: only "Vorkasse per Überweisung" is enabled for testing and has NO bank details – confirm payment methods before launch.',
		'Tax: 19 % standard VAT is set up. Confirm whether refurbished devices are sold under margin scheme (§ 25a UStG).',
		'Legal pages (Impressum, Datenschutz, Widerruf, Versand, Garantie) are drafts with placeholders and need legal review; AGB not yet provided.',
		'Electronic withdrawal function ("Widerrufsbutton", in force since 19.06.2026) – confirm requirement and implementation with legal counsel.',
		'Apple iPhone Duo: product photos, final specifications and price needed (banner uses the 3D model).',
	)
);

flush_rewrite_rules();
WP_CLI::success( 'Seed complete. Front page: ' . home_url( '/' ) );
