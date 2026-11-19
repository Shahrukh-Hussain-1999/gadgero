<?php
/**
 * Catalogue v3 (client list of 19.11.2026): Samsung Galaxy S24 / S25 / S26 families, Apple iPhone 14 – 17 Pro Max,
 * iPad 10th / 11th gen, iPad Air M3 / M4, all "Neu". The Apple iPhone Duo campaign product is kept.
 * Every other product is moved to the trash (restorable).
 *
 * One listing per model; storage, colour and (iPad Air) screen size are variations, each with its own SKU,
 * price and stock. Colour names exactly as supplied by the client (capitalisation / official spelling only).
 *
 * !!! PRICES AND STOCK ARE PLACEHOLDERS !!! Every product is flagged "_gadgero_unconfirmed" until the client
 * confirms prices, SKUs and stock.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

/** Verified technical data (official manufacturer documentation), reused by name. */
function gadgero_spec_library() {
	return array(
		'iphone16' => array(
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
		'iphone15' => array(
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
				'Lieferumfang | Im Karton | iPhone, USB-C Ladekabel (1 m), Dokumentation',
			),
		),
		'ipadair_m3' => array(
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
				'Konnektivität | Anschluss | USB-C (USB 3, bis zu 10 Gbit/s)',
				'Abmessungen & Gewicht | 11 Zoll | 247,6 × 178,5 × 6,1 mm, 460 g',
				'Abmessungen & Gewicht | 13 Zoll | 280,6 × 214,9 × 6,1 mm, 616 g',
				'Lieferumfang | Im Karton | iPad Air, USB-C Ladekabel (1 m)',
			),
		),
		'ipadair_m4' => array(
			'src'  => 'https://www.apple.com/de/ipad-air/specs/',
			'rows' => array(
				'Display | Typ | Liquid Retina Display (IPS)',
				'Display | 11 Zoll | 10,86 Zoll Diagonale, 2360 × 1640 Pixel bei 264 ppi, 500 Nits',
				'Display | 13 Zoll | 12,9 Zoll Diagonale, 2732 × 2048 Pixel bei 264 ppi, 600 Nits',
				'Leistung | Chip | Apple M4 (8-Core CPU, 9-Core GPU)',
				'Leistung | Arbeitsspeicher | 12 GB',
				'Kamera | Rückkamera | 12 MP Weitwinkel (ƒ/1.8)',
				'Kamera | Frontkamera | 12 MP Center Stage, im Querformat (ƒ/2.0)',
				'Konnektivität | WLAN | WLAN 7 (802.11be)',
				'Konnektivität | Bluetooth | 6',
				'Konnektivität | Anschluss | USB-C (USB 3, bis zu 10 Gbit/s)',
				'Abmessungen & Gewicht | 11 Zoll | 247,6 × 178,5 × 6,1 mm, 464 g',
				'Abmessungen & Gewicht | 13 Zoll | 280,6 × 214,9 × 6,1 mm, 616 g',
				'Lieferumfang | Im Karton | iPad Air, USB-C Ladekabel (1 m)',
			),
		),
	);
}

/**
 * The client's model list. p = placeholder price per storage option (index matches 'st'); 'sz' adds screen-size
 * variations with a placeholder surcharge. Colours exactly as supplied.
 */
function gadgero_catalogue_v3() {
	$s4  = array( 'Onyx Black', 'Marble Gray', 'Cobalt Violet', 'Amber Yellow' );
	$s5  = array( 'Blueblack', 'Navy', 'Silver Shadow', 'Icyblue' );
	$s6  = array( 'Black', 'Cobalt Violet', 'White', 'Sky Blue' );
	$tit = array( 'Titan Black', 'Titan White', 'Titan Natural', 'Titan Desert' );
	$pro = array( 'Silver', 'Cosmic Orange', 'Deep Blue' );
	$ipd = array( 'Silver', 'Blue', 'Pink', 'Yellow' );
	$air = array( 'Space Gray', 'Starlight', 'Purple', 'Blue' );
	$ph  = array( 'cat' => 'phones', 'art' => 'phone' );
	$sam = $ph + array( 'brand' => 'Samsung' );
	$app = $ph + array( 'brand' => 'Apple' );
	$tab = array( 'cat' => 'tablets', 'art' => 'tablet', 'brand' => 'Apple', 'conn' => 'Wi-Fi' );

	return array(
		/* Samsung */
		$sam + array( 'n' => 'Galaxy S24', 'code' => 'S24', 'st' => array( '128 GB', '256 GB' ), 'p' => array( 599, 659 ), 'col' => $s4 ),
		$sam + array( 'n' => 'Galaxy S24+', 'code' => 'S24P', 'st' => array( '128 GB', '256 GB' ), 'p' => array( 749, 809 ), 'col' => $s4 ),
		$sam + array( 'n' => 'Galaxy S24 FE', 'code' => 'S24FE', 'st' => array( '128 GB', '256 GB' ), 'p' => array( 479, 539 ), 'col' => array( 'Graphite', 'Gray', 'Blue', 'Mint' ) ),
		$sam + array( 'n' => 'Galaxy S24 Ultra', 'code' => 'S24U', 'st' => array( '256 GB' ), 'p' => array( 999 ), 'col' => array( 'Titanium Black', 'Titanium Blue', 'Titanium Violet' ), 'short' => '256 GB Speicher, 12 GB Arbeitsspeicher.' ),
		$sam + array( 'n' => 'Galaxy S25', 'code' => 'S25', 'st' => array( '128 GB', '256 GB' ), 'p' => array( 749, 809 ), 'col' => $s5 ),
		$sam + array( 'n' => 'Galaxy S25+', 'code' => 'S25P', 'st' => array( '128 GB', '256 GB' ), 'p' => array( 899, 959 ), 'col' => $s5 ),
		$sam + array( 'n' => 'Galaxy S25 FE', 'code' => 'S25FE', 'st' => array( '128 GB', '256 GB' ), 'p' => array( 599, 659 ), 'col' => array( 'Jetblack', 'Navy', 'White', 'Icyblue' ) ),
		$sam + array( 'n' => 'Galaxy S25 Ultra', 'code' => 'S25U', 'st' => array(), 'p' => array( 1199 ), 'col' => array( 'Titanium Jetblack', 'Titanium Silverblue', 'Titanium Whitesilver' ), 'todo' => 'storage options' ),
		$sam + array( 'n' => 'Galaxy S26', 'code' => 'S26', 'st' => array( '128 GB', '256 GB' ), 'p' => array( 899, 959 ), 'col' => $s6 ),
		$sam + array( 'n' => 'Galaxy S26+', 'code' => 'S26P', 'st' => array( '128 GB', '256 GB' ), 'p' => array( 1049, 1109 ), 'col' => $s6 ),
		$sam + array( 'n' => 'Galaxy S26 FE', 'code' => 'S26FE', 'st' => array( '128 GB', '256 GB' ), 'p' => array( 649, 709 ), 'col' => array( 'Graphite', 'Blueberry', 'Pistachio' ) ),
		$sam + array( 'n' => 'Galaxy S26 Ultra', 'code' => 'S26U', 'st' => array(), 'p' => array( 1399 ), 'col' => $s6, 'todo' => 'storage options' ),
		/* Apple iPhone */
		$app + array( 'n' => 'iPhone 14', 'code' => 'IP14', 'st' => array( '128 GB' ), 'p' => array( 599 ), 'col' => array( 'Midnight', 'Starlight', 'Blue', 'Purple', 'Yellow' ) ),
		$app + array( 'n' => 'iPhone 15', 'code' => 'IP15', 'st' => array( '128 GB' ), 'p' => array( 699 ), 'col' => array( 'Black', 'Blue', 'Green', 'Yellow' ), 'spec' => 'iphone15' ),
		$app + array( 'n' => 'iPhone 16', 'code' => 'IP16', 'st' => array( '128 GB' ), 'p' => array( 799 ), 'col' => array( 'Black', 'White', 'Ultramarine', 'Teal', 'Pink' ), 'spec' => 'iphone16' ),
		$app + array( 'n' => 'iPhone 16e', 'code' => 'IP16E', 'st' => array( '128 GB' ), 'p' => array( 599 ), 'col' => array( 'Black', 'White' ) ),
		$app + array( 'n' => 'iPhone 16 Plus', 'code' => 'IP16PL', 'st' => array( '128 GB' ), 'p' => array( 899 ), 'col' => array( 'Black', 'White', 'Ultramarine', 'Teal', 'Pink' ) ),
		$app + array( 'n' => 'iPhone 17', 'code' => 'IP17', 'st' => array( '256 GB' ), 'p' => array( 949 ), 'col' => array( 'Black', 'White', 'Mist Blue', 'Sage', 'Lavender' ) ),
		$app + array( 'n' => 'iPhone 17e', 'code' => 'IP17E', 'st' => array( '256 GB' ), 'p' => array( 699 ), 'col' => array( 'Black', 'White', 'Soft Pink' ) ),
		$app + array( 'n' => 'iPhone 16 Pro', 'code' => 'IP16PR', 'st' => array( '128 GB', '256 GB' ), 'p' => array( 1099, 1229 ), 'col' => $tit ),
		$app + array( 'n' => 'iPhone 16 Pro Max', 'code' => 'IP16PM', 'st' => array( '256 GB' ), 'p' => array( 1399 ), 'col' => $tit ),
		$app + array( 'n' => 'iPhone 17 Pro', 'code' => 'IP17PR', 'st' => array( '256 GB' ), 'p' => array( 1299 ), 'col' => $pro ),
		$app + array( 'n' => 'iPhone 17 Pro Max', 'code' => 'IP17PM', 'st' => array( '256 GB' ), 'p' => array( 1449 ), 'col' => $pro ),
		/* Apple iPad */
		$tab + array( 'n' => 'iPad 10,9 Zoll (10. Generation, 2022)', 'code' => 'IPAD10', 'size' => '10,9 Zoll', 'st' => array( '64 GB', '256 GB' ), 'p' => array( 379, 549 ), 'col' => $ipd ),
		$tab + array( 'n' => 'iPad 10,9 Zoll (11. Generation, 2025)', 'code' => 'IPAD11', 'size' => '10,9 Zoll', 'st' => array( '128 GB', '256 GB' ), 'p' => array( 399, 509 ), 'col' => $ipd ),
		$tab + array( 'n' => 'iPad Air M3 (7. Generation, 2025)', 'code' => 'AIRM3', 'sz' => array( '11 Zoll' => 0, '13 Zoll' => 200 ), 'st' => array( '128 GB' ), 'p' => array( 599 ), 'col' => $air, 'spec' => 'ipadair_m3' ),
		$tab + array( 'n' => 'iPad Air M4 (8. Generation, 2026)', 'code' => 'AIRM4', 'sz' => array( '11 Zoll' => 0, '13 Zoll' => 200 ), 'st' => array( '128 GB' ), 'p' => array( 699 ), 'col' => $air, 'spec' => 'ipadair_m4' ),
	);
}

/** Homepage selection (featured). */
function gadgero_featured_v3() {
	return array( 'Apple iPhone Duo', 'iPhone 17 Pro', 'iPhone 17', 'iPhone 16', 'Galaxy S26 Ultra', 'Galaxy S25', 'iPad Air M4 (8. Generation, 2026)', 'iPad 10,9 Zoll (11. Generation, 2025)' );
}

/**
 * Term by client name. For colours, an existing term with the same slug is renamed to the client's name
 * (e.g. "Schwarz" → "Black") so names match the supplied list exactly.
 */
function gadgero_mig_term( $tax, $name, $rename = false ) {
	$t = get_term_by( 'name', $name, $tax );
	if ( ! $t ) {
		$t = get_term_by( 'slug', sanitize_title( $name ), $tax );
		if ( $t && $rename && $t->name !== $name ) {
			wp_update_term( $t->term_id, $tax, array( 'name' => $name ) );
			$t = get_term( $t->term_id, $tax );
		}
	}
	if ( ! $t || is_wp_error( $t ) ) {
		$r = wp_insert_term( $name, $tax );
		$t = is_wp_error( $r ) ? get_term_by( 'slug', sanitize_title( $name ), $tax ) : get_term( $r['term_id'], $tax );
	}
	return $t;
}

/** WC_Product_Attribute from term objects. */
function gadgero_mig_attr( $tax, array $terms, $variation, $pos ) {
	$a = new WC_Product_Attribute();
	$a->set_id( wc_attribute_taxonomy_id_by_name( $tax ) );
	$a->set_name( $tax );
	$a->set_options( array_map( fn( $t ) => (int) $t->term_id, $terms ) );
	$a->set_position( $pos );
	$a->set_visible( true );
	$a->set_variation( $variation );
	return $a;
}

/** Colour part of the SKU, unique per colour name: "Titanium Black" → "TITANIUM-BLACK". */
function gadgero_sku_colour( $name ) {
	return strtoupper( sanitize_title( $name ) );
}

/** Create one catalogue product with its variations. */
function gadgero_mig_create( array $d, $order ) {
	if ( gadgero_mig_product_any( $d['n'] ) ) {
		return; // already created (re-run safe)
	}
	$tx_col = 'pa_colour';
	$cond   = get_term_by( 'slug', 'new', 'pa_condition' );
	$cols   = array_map( fn( $c ) => gadgero_mig_term( $tx_col, $c, true ), $d['col'] );
	$sts    = array_map( fn( $s ) => gadgero_mig_term( 'pa_storage', $s ), $d['st'] );
	$sizes  = isset( $d['sz'] ) ? array_map( fn( $s ) => gadgero_mig_term( 'pa_screen-size', $s ), array_keys( $d['sz'] ) ) : array();

	$attrs = array( 'pa_condition' => gadgero_mig_attr( 'pa_condition', array( $cond ), false, 0 ) );
	$pos   = 1;
	if ( $sizes ) {
		$attrs['pa_screen-size'] = gadgero_mig_attr( 'pa_screen-size', $sizes, true, $pos++ );
	} elseif ( ! empty( $d['size'] ) ) {
		$attrs['pa_screen-size'] = gadgero_mig_attr( 'pa_screen-size', array( gadgero_mig_term( 'pa_screen-size', $d['size'] ) ), false, $pos++ );
	}
	if ( $sts ) {
		$attrs['pa_storage'] = gadgero_mig_attr( 'pa_storage', $sts, count( $sts ) > 1, $pos++ );
	}
	$attrs['pa_colour'] = gadgero_mig_attr( 'pa_colour', $cols, true, $pos++ );
	if ( ! empty( $d['conn'] ) ) {
		$attrs['pa_connectivity'] = gadgero_mig_attr( 'pa_connectivity', array( gadgero_mig_term( 'pa_connectivity', $d['conn'] ) ), false, $pos++ );
	}

	$p = new WC_Product_Variable();
	$p->set_name( $d['n'] );
	$p->set_status( 'publish' );
	$p->set_menu_order( $order );
	$cat = get_term_by( 'slug', $d['cat'], 'product_cat' );
	if ( $cat ) {
		$p->set_category_ids( array( $cat->term_id ) );
	}
	if ( ! empty( $d['short'] ) ) {
		$p->set_short_description( $d['short'] );
	}
	$p->set_attributes( $attrs );
	$p->update_meta_data( '_gadgero_unconfirmed', 'yes' );
	$p->update_meta_data( '_gadgero_catalogue', 'v3' );
	$p->update_meta_data( '_gadgero_art', $d['art'] );
	$p->update_meta_data( '_gadgero_art_c1', 'phone' === $d['art'] ? '#5b6270' : '#7fa6e8' );
	$p->update_meta_data( '_gadgero_art_c2', 'phone' === $d['art'] ? '#1d2026' : '#1a2f66' );
	$p->update_meta_data( '_gadgero_art_frame', '#b9bfca' );
	$p->update_meta_data( '_gadgero_glow', '#d9dee8' );
	if ( ! empty( $d['spec'] ) ) {
		$lib = gadgero_spec_library()[ $d['spec'] ];
		$p->update_meta_data( '_gadgero_specs', implode( "\n", $lib['rows'] ) );
		$p->update_meta_data( '_gadgero_spec_source', $lib['src'] );
	}
	$pid = $p->save();
	$brand = get_term_by( 'name', $d['brand'], 'product_brand' );
	if ( $brand ) {
		wp_set_object_terms( $pid, array( (int) $brand->term_id ), 'product_brand' );
	}

	$size_list = $sizes ? $d['sz'] : array( '' => 0 );
	$st_list   = $sts ? $d['st'] : array( '' );
	foreach ( $size_list as $size => $surcharge ) {
		foreach ( $st_list as $i => $st ) {
			foreach ( $d['col'] as $ci => $col ) {
				$v   = new WC_Product_Variation();
				$set = array( 'pa_colour' => $cols[ $ci ]->slug );
				$sku = array( 'GAD', $d['code'] );
				if ( $size ) {
					$set['pa_screen-size'] = gadgero_mig_term( 'pa_screen-size', $size )->slug;
					$sku[]                 = (int) $size;
				}
				if ( $st && count( $sts ) > 1 ) {
					$set['pa_storage'] = $sts[ $i ]->slug;
				}
				if ( $st ) {
					$sku[] = (int) $st;
				}
				$sku[] = gadgero_sku_colour( $col );
				$v->set_parent_id( $pid );
				$v->set_attributes( $set );
				$v->set_sku( implode( '-', $sku ) );
				$v->set_regular_price( (string) ( ( $d['p'][ $i ] ?? $d['p'][0] ) + $surcharge ) );
				$v->set_manage_stock( true );
				$v->set_stock_quantity( 3 );
				$v->set_status( 'publish' );
				$v->save();
			}
		}
	}
	WC_Product_Variable::sync( $pid );
	wc_delete_product_transients( $pid );
}

/** Product by exact title in any status except trash. */
function gadgero_mig_product_any( $name ) {
	$q = get_posts( array( 'post_type' => 'product', 'post_status' => array( 'publish', 'draft', 'private', 'pending' ), 'title' => $name, 'posts_per_page' => 1, 'fields' => 'ids' ) );
	return $q ? (int) $q[0] : 0;
}

/**
 * Migration v3, batched. Returns true when finished.
 * Steps: 0 = trash old products, 1..n = create catalogue in batches of 4, last = featured + flags.
 */
function gadgero_migration_3() {
	$state = (array) get_option( 'gadgero_mig3_state', array( 'step' => 0 ) );
	$list  = gadgero_catalogue_v3();

	if ( 0 === (int) $state['step'] ) {
		$keep = array_merge( array( 'Apple iPhone Duo' ), array_column( $list, 'n' ) );
		foreach ( get_posts( array( 'post_type' => 'product', 'post_status' => array( 'publish', 'draft', 'private', 'pending' ), 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $pid ) {
			if ( ! in_array( get_the_title( $pid ), $keep, true ) || 'v3' !== get_post_meta( $pid, '_gadgero_catalogue', true ) && 'Apple iPhone Duo' !== get_the_title( $pid ) ) {
				wp_trash_post( $pid ); // restorable from Products → Trash
			}
		}
		update_option( 'gadgero_mig3_state', array( 'step' => 1 ), false );
		return false;
	}

	$batch = 4;
	$start = ( (int) $state['step'] - 1 ) * $batch;
	if ( $start < count( $list ) ) {
		foreach ( array_slice( $list, $start, $batch ) as $k => $d ) {
			try {
				gadgero_mig_create( $d, 10 + $start + $k );
			} catch ( Throwable $e ) {
				// Never leave a half-built product or a stuck migration: remove it, log, carry on.
				$pid = gadgero_mig_product_any( $d['n'] );
				if ( $pid && ( $bad = wc_get_product( $pid ) ) ) {
					foreach ( $bad->get_children() as $c ) {
						wp_delete_post( $c, true );
					}
					$bad->delete( true );
				}
				error_log( 'Gadgero catalogue v3: ' . $d['n'] . ' failed: ' . $e->getMessage() ); // phpcs:ignore
				$failed   = (array) get_option( 'gadgero_mig3_failed', array() );
				$failed[] = $d['n'] . ': ' . $e->getMessage();
				update_option( 'gadgero_mig3_failed', $failed, false );
			}
		}
		update_option( 'gadgero_mig3_state', array( 'step' => (int) $state['step'] + 1 ), false );
		return false;
	}

	// Finish: homepage selection, Duo first, flags, notices.
	$featured = gadgero_featured_v3();
	foreach ( get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $pid ) {
		$p = wc_get_product( $pid );
		if ( $p ) {
			$p->set_featured( in_array( $p->get_name(), $featured, true ) );
			$p->save();
		}
	}
	$duo = gadgero_mig_product_any( 'Apple iPhone Duo' );
	if ( $duo ) {
		wp_update_post( array( 'ID' => $duo, 'menu_order' => 0 ) );
		set_theme_mod( 'gadgero_campaign_product', $duo );
	}
	$pending   = (array) get_option( 'gadgero_pending_confirmations', array() );
	$pending[] = 'Catalogue v3 (client list 19.11.2026): all prices and stock are placeholders; storage options for Galaxy S25 Ultra and S26 Ultra not supplied; product photos needed for every colour.';
	update_option( 'gadgero_pending_confirmations', array_values( array_unique( $pending ) ) );
	delete_option( 'gadgero_mig3_state' );
	wc_delete_product_transients();
	return true;
}
