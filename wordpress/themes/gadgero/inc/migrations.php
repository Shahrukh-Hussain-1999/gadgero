<?php
/**
 * Versioned data migrations. They run once per site (local and staging) on the first request after a theme update,
 * so a `git pull` on the server brings content changes along with the code.
 *
 * v2 (brief 15.11.2026): German storefront labels, brands, featured selection, German menu labels,
 *    shipping terms generated from WooCommerce, verified technical data for the example products.
 *
 * Every step is idempotent and only touches Gadgero-owned data (attributes, terms, menus, products of this shop).
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

const GADGERO_DB_VERSION = 2;

add_action( 'init', 'gadgero_run_migrations', 99 );

function gadgero_run_migrations( $force = false ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	$v = (int) get_option( 'gadgero_db_version', 1 );
	if ( $v >= GADGERO_DB_VERSION && ! $force ) {
		return;
	}
	if ( get_transient( 'gadgero_migrating' ) && ! $force ) {
		return;
	}
	set_transient( 'gadgero_migrating', 1, 120 );
	if ( $v < 2 || $force ) {
		gadgero_migration_2();
	}
	update_option( 'gadgero_db_version', GADGERO_DB_VERSION );
	delete_transient( 'gadgero_migrating' );
}

/** Find a product of this shop by exact title. */
function gadgero_mig_product( $name ) {
	$q = get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'title' => $name, 'posts_per_page' => 1, 'fields' => 'ids' ) );
	return $q ? wc_get_product( $q[0] ) : null;
}

function gadgero_migration_2() {
	/* 1. Attribute labels (slugs stay the same, so URLs, filters and variations keep working). */
	$labels = array( 'condition' => 'Zustand', 'storage' => 'Speicher', 'colour' => 'Farbe', 'connectivity' => 'Konnektivität', 'screen-size' => 'Bildschirmgröße' );
	foreach ( $labels as $slug => $label ) {
		$id = wc_attribute_taxonomy_id_by_name( $slug );
		if ( $id ) {
			wc_update_attribute( $id, array( 'name' => $label, 'slug' => $slug, 'type' => 'select', 'order_by' => 'menu_order', 'has_archives' => false ) );
		}
	}
	delete_transient( 'wc_attribute_taxonomies' );

	/* 2. Term names in German (slugs unchanged). Brand colour names Apple / Samsung use in Germany. */
	$terms = array(
		'pa_condition'    => array( 'new' => 'Neu', 'refurbished' => 'Refurbished' ),
		'pa_colour'       => array( 'silver' => 'Silber', 'black' => 'Schwarz', 'blue' => 'Blau', 'space-grey' => 'Space Grau', 'white' => 'Weiß', 'midnight' => 'Mitternacht', 'ultramarine' => 'Ultramarin', 'natural-titanium' => 'Titan Natur', 'deep-blue' => 'Tiefblau', 'space-black' => 'Space Schwarz' ),
		'pa_screen-size'  => array( '10-9-inch' => '10,9 Zoll', '11-inch' => '11 Zoll', '13-inch' => '13 Zoll', '14-inch' => '14 Zoll', '24-inch' => '24 Zoll' ),
	);
	foreach ( $terms as $tax => $map ) {
		if ( ! taxonomy_exists( $tax ) ) {
			register_taxonomy( $tax, 'product' );
		}
		foreach ( $map as $slug => $name ) {
			$t = get_term_by( 'slug', $slug, $tax );
			if ( $t && $t->name !== $name ) {
				wp_update_term( $t->term_id, $tax, array( 'name' => $name ) );
			}
		}
	}

	/* 3. Categories (slugs unchanged). */
	foreach ( array( 'phones' => 'Smartphones', 'accessories' => 'Zubehör', 'uncategorized' => 'Sonstiges' ) as $slug => $name ) {
		$t = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $t ) {
			wp_update_term( $t->term_id, 'product_cat', array( 'name' => $name ) );
		}
	}

	/* 4. Brands (WooCommerce Brands) and the homepage selection (featured). */
	$brand_ids = array();
	foreach ( array( 'Apple', 'Samsung' ) as $b ) {
		$t = get_term_by( 'name', $b, 'product_brand' );
		if ( ! $t ) {
			$r = wp_insert_term( $b, 'product_brand' );
			$t = is_wp_error( $r ) ? null : get_term( $r['term_id'], 'product_brand' );
		}
		if ( $t ) {
			$brand_ids[ $b ] = (int) $t->term_id;
		}
	}
	$featured = array( 'Apple iPhone Duo', 'iPhone 17 Pro', 'Galaxy S26 Ultra', 'iPad Air M3', 'MacBook Air 13" M4', 'Apple Watch Ultra', 'iPhone 15', 'MacBook Air 13" M2' );
	foreach ( get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $pid ) {
		$p = wc_get_product( $pid );
		if ( ! $p ) {
			continue;
		}
		$brand = 0 === strpos( $p->get_name(), 'Galaxy' ) ? 'Samsung' : 'Apple';
		if ( isset( $brand_ids[ $brand ] ) && ! has_term( '', 'product_brand', $pid ) ) {
			wp_set_object_terms( $pid, array( $brand_ids[ $brand ] ), 'product_brand' );
		}
		$p->set_featured( in_array( $p->get_name(), $featured, true ) );
		$p->save();
	}

	/* 5. German short texts / card summaries that were English. */
	$texts = array(
		'Apple iPhone Duo'   => array( 'short' => 'Ein faltbares iPhone, das sich zu einem Display in Tablet-Größe öffnet.' ),
		'iPad Air M3'        => array( 'short' => 'Wählen Sie Bildschirmgröße, Speicher, Farbe und Konnektivität. Jede Kombination ist ein eigener Artikel mit eigenem Preis und Lagerbestand.' ),
		'Galaxy S26 Ultra'   => array( 'summary' => '256 GB · Silver · inkl. S Pen' ),
		'Apple Watch Ultra'  => array( 'summary' => '49 mm · Titangehäuse · GPS + Cellular' ),
		'AirPods Pro'        => array( 'summary' => 'Aktive Geräuschunterdrückung · Ladecase' ),
		'MacBook Air 13" M4' => array( 'summary' => '16 GB RAM · 256 GB SSD · Mitternacht' ),
		'MacBook Pro 14" M5' => array( 'summary' => '16 GB RAM · 512 GB SSD · Space Schwarz' ),
		'MacBook Air 13" M2' => array( 'summary' => '8 GB RAM · 256 GB SSD · Silber' ),
		'iMac 24" M4'        => array( 'summary' => '16 GB RAM · 256 GB SSD · Blau' ),
		'Mac mini M4'        => array( 'summary' => '16 GB RAM · 256 GB SSD · Silber' ),
	);
	foreach ( $texts as $name => $t ) {
		$p = gadgero_mig_product( $name );
		if ( ! $p ) {
			continue;
		}
		if ( isset( $t['short'] ) ) {
			$p->set_short_description( $t['short'] );
		}
		if ( isset( $t['summary'] ) ) {
			$p->update_meta_data( '_gadgero_summary', $t['summary'] );
		}
		$p->save();
	}

	/* 6. Verified technical data (official manufacturer documentation, retrieved 19.11.2026). */
	$specs = array(
		'iPhone 16' => array(
			'src'  => 'https://www.apple.com/de/iphone-16/specs/',
			'rows' => array(
				'Display | Typ | Super Retina XDR Display (OLED)',
				'Display | Größe | 6,1 Zoll (15,5 cm Diagonale)',
				'Display | Auflösung | 2556 × 1179 Pixel bei 460 ppi',
				'Display | Helligkeit | bis zu 1.600 Nits (HDR), bis zu 2.000 Nits (im Freien)',
				'Leistung | Chip | A18',
				'Kamera | Rückkamera | 48 MP Fusion Hauptkamera (ƒ/1.6), 12 MP Ultraweitwinkel (ƒ/2.2)',
				'Kamera | Frontkamera | 12 MP (ƒ/1.9)',
				'Konnektivität | Mobilfunk | 5G (sub-6 GHz)',
				'Konnektivität | WLAN | WLAN 7 (802.11be)',
				'Konnektivität | Bluetooth | 5.3',
				'Konnektivität | Weitere | NFC, Ultrabreitband-Chip (2. Generation)',
				'Konnektivität | SIM | Dual SIM (Nano-SIM und eSIM)',
				'Konnektivität | Anschluss | USB-C (USB 2, bis zu 480 Mbit/s)',
				'Abmessungen & Gewicht | Höhe × Breite × Tiefe | 147,6 × 71,6 × 7,80 mm',
				'Abmessungen & Gewicht | Gewicht | 170 g',
				'Abmessungen & Gewicht | Wasser- und Staubschutz | IP68 (IEC 60529, bis 6 m für bis zu 30 Minuten)',
				'Lieferumfang | Im Karton | iPhone, USB-C Ladekabel (1 m), Dokumentation',
			),
		),
		'iPhone 15' => array(
			'src'  => 'https://support.apple.com/de-de/111831',
			'rows' => array(
				'Display | Typ | Super Retina XDR Display (OLED)',
				'Display | Größe | 6,1 Zoll (15,5 cm Diagonale)',
				'Display | Auflösung | 2556 × 1179 Pixel bei 460 ppi',
				'Leistung | Chip | A16 Bionic',
				'Kamera | Rückkamera | 48 MP Hauptkamera, 12 MP Ultraweitwinkel',
				'Kamera | Frontkamera | 12 MP TrueDepth',
				'Konnektivität | Mobilfunk | 5G (sub-6 GHz)',
				'Konnektivität | WLAN | WLAN 6 (802.11ax)',
				'Konnektivität | Bluetooth | 5.3',
				'Konnektivität | Weitere | NFC, Ultrabreitband-Chip (2. Generation)',
				'Konnektivität | Anschluss | USB-C (USB 2, bis zu 480 Mbit/s)',
				'Abmessungen & Gewicht | Höhe × Breite × Tiefe | 147,6 × 71,6 × 7,80 mm',
				'Abmessungen & Gewicht | Gewicht | 171 g',
				'Abmessungen & Gewicht | Wasser- und Staubschutz | IP68 (IEC 60529, bis 6 m für bis zu 30 Minuten)',
				// Lieferumfang of a refurbished unit is unit-specific → "Included items" field, not the model spec.
			),
		),
		'iPad Air M3' => array(
			'src'  => 'https://support.apple.com/de-de/122241 ; https://support.apple.com/de-de/122242',
			'rows' => array(
				'Display | Typ | Liquid Retina Display (IPS, LED-Hintergrundbeleuchtung)',
				'Display | 11 Zoll | 10,86 Zoll Diagonale, 2360 × 1640 Pixel bei 264 ppi, 500 Nits',
				'Display | 13 Zoll | 12,9 Zoll Diagonale, 2732 × 2048 Pixel bei 264 ppi, 600 Nits',
				'Leistung | Chip | Apple M3 (8-Core CPU, 9-Core GPU)',
				'Leistung | Arbeitsspeicher | 8 GB',
				'Kamera | Rückkamera | 12 MP Weitwinkel (ƒ/1.8)',
				'Kamera | Frontkamera | 12 MP Center Stage, im Querformat (ƒ/2.0)',
				'Konnektivität | WLAN | WLAN 6E (802.11ax)',
				'Konnektivität | Bluetooth | 5.3',
				'Konnektivität | Mobilfunk (Wi-Fi + Cellular) | 5G (sub-6 GHz), Gigabit LTE, nur eSIM',
				'Konnektivität | Anschluss | USB-C (USB 3, bis zu 10 Gbit/s)',
				'Abmessungen & Gewicht | 11 Zoll | 247,6 × 178,5 × 6,1 mm, 460 g',
				'Abmessungen & Gewicht | 13 Zoll | 280,6 × 214,9 × 6,1 mm, 616 g (Wi-Fi) / 617 g (Wi-Fi + Cellular)',
				'Lieferumfang | Im Karton | iPad Air, USB-C Ladekabel (1 m)',
			),
		),
	);
	foreach ( $specs as $name => $s ) {
		$p = gadgero_mig_product( $name );
		if ( $p && '' === (string) $p->get_meta( '_gadgero_specs' ) ) {
			$p->update_meta_data( '_gadgero_specs', implode( "\n", $s['rows'] ) );
			$p->update_meta_data( '_gadgero_spec_source', $s['src'] );
			$p->save();
		}
	}

	/* 7. German navigation labels. */
	$menu_labels = array(
		'Home'             => 'Startseite',
		'About'            => 'Über uns',
		'Sell yours'       => 'Gerät verkaufen',
		'Contact'          => 'Kontakt',
		'Shop new'         => 'Neue Geräte',
		'Shop refurbished' => 'Refurbished-Geräte',
	);
	foreach ( array( 'Header', 'Service' ) as $menu ) {
		foreach ( (array) wp_get_nav_menu_items( $menu ) as $item ) {
			if ( isset( $menu_labels[ $item->title ] ) ) {
				wp_update_post( array( 'ID' => $item->ID, 'post_title' => $menu_labels[ $item->title ] ) );
			}
		}
	}

	/* 8. Versand & Zahlung: costs, delivery time and payment methods come from WooCommerce settings. */
	$page = get_page_by_path( 'versand-und-zahlung' );
	if ( $page && false === strpos( $page->post_content, '[gadgero_shipping]' ) ) {
		$c = preg_replace( '#<h2>Liefergebiete</h2>.*?(<h2>Versanddienstleister</h2>)#s', '<h2>Liefergebiete, Versandkosten und Lieferzeit</h2>' . "\n[gadgero_shipping]\n" . '$1', $page->post_content, 1, $count );
		$c = preg_replace( '#<h2>Zahlungsarten</h2>\s*<p>.*?</p>\s*#s', '', $c );
		if ( $count ) {
			wp_update_post( array( 'ID' => $page->ID, 'post_content' => $c ) );
		}
	}

	update_option( 'blogdescription', 'Neue und refurbished Smartphones, Tablets, Laptops, Desktops, Smartwatches und Zubehör' );
	wc_delete_product_transients();
}
