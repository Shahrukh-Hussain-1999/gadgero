<?php
/**
 * Extra product fields (product edit screen → "Gadgero" tab).
 *
 * - Card summary, factory-sealed flag, illustration fallback, data-confirmation flag.
 * - Refurbished unit (brief item 08): grade, measured battery, screen / housing condition, cosmetic notes,
 *   included items, "photos show this exact unit", inspected by / programme.
 * - Technical data (brief item 07): one line per row "Gruppe | Merkmal | Wert", plus the source of the data
 *   (official manufacturer documentation and the actual SKU record). Required groups per category are checked
 *   and missing ones are flagged internally only.
 *
 * Nothing is displayed to customers unless filled in.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

/** Field definitions: meta key => [label, type, description]. */
function gadgero_product_field_defs() {
	return array(
		'_gadgero_unconfirmed'     => array( 'Data not yet confirmed by client', 'checkbox', 'Keep ticked while price, SKU, stock, specs or images are placeholders. Listed in the dashboard warning.' ),
		'_gadgero_summary'         => array( 'Card summary (optional)', 'text', 'Overrides the automatic configuration line on product cards.' ),
		'_gadgero_sealed'          => array( 'Original factory-sealed packaging', 'checkbox', 'New devices only, when confirmed for this listing.' ),
		'_gadgero_specs'           => array( 'Technical data', 'textarea', 'One row per line: Gruppe | Merkmal | Wert  (e.g. "Display | Größe | 6,1 Zoll"). Only verified data from official manufacturer documentation and the actual SKU.' ),
		'_gadgero_spec_source'     => array( 'Source of technical data', 'text', 'URL(s) of the official documentation used; internal only.' ),
		'_gadgero_grade'           => array( 'Refurbished: grade', 'text', 'e.g. "Sehr gut". Refurbished only.' ),
		'_gadgero_battery'         => array( 'Refurbished: battery health (%)', 'number', 'Measured value for this unit.' ),
		'_gadgero_screen'          => array( 'Refurbished: screen condition', 'text', 'e.g. "Keine sichtbaren Kratzer".' ),
		'_gadgero_frame'           => array( 'Refurbished: housing condition', 'text', '' ),
		'_gadgero_cosmetic'        => array( 'Refurbished: cosmetic notes', 'textarea', 'Describe every visible mark and where it is; add close-up photos to the gallery.' ),
		'_gadgero_accessories'     => array( 'Included items', 'text', 'e.g. "USB-C Ladekabel".' ),
		'_gadgero_photos_actual'   => array( 'Photos show this exact unit', 'checkbox', 'Tick only when the gallery shows the actual device.' ),
		'_gadgero_inspected_by'    => array( 'Inspected / certified by', 'text', 'Only if confirmed. Leave empty otherwise.' ),
		'_gadgero_programme'       => array( 'Certification programme', 'text', 'Only if confirmed.' ),
		'_gadgero_art'             => array( 'Illustration when no photo', 'select', 'Device drawing used until a real photo is added.' ),
		'_gadgero_art_c1'          => array( 'Illustration colour 1', 'text', 'Hex' ),
		'_gadgero_art_c2'          => array( 'Illustration colour 2', 'text', 'Hex' ),
		'_gadgero_art_frame'       => array( 'Illustration frame colour', 'text', 'Hex' ),
		'_gadgero_glow'            => array( 'Card glow colour', 'text', 'Hex' ),
	);
}

add_filter(
	'woocommerce_product_data_tabs',
	function ( $tabs ) {
		$tabs['gadgero'] = array(
			'label'    => 'Gadgero',
			'target'   => 'gadgero_product_data',
			'priority' => 15,
		);
		return $tabs;
	}
);

add_action(
	'woocommerce_product_data_panels',
	function () {
		global $post;
		echo '<div id="gadgero_product_data" class="panel woocommerce_options_panel"><div class="options_group">';
		$p = wc_get_product( $post->ID );
		if ( $p ) {
			$missing = gadgero_spec_missing( $p );
			if ( $missing ) {
				echo '<p style="color:#b32d2e"><strong>Missing technical data groups:</strong> ' . esc_html( implode( ', ', $missing ) ) . '</p>';
			}
		}
		foreach ( gadgero_product_field_defs() as $key => $d ) {
			$val = get_post_meta( $post->ID, $key, true );
			switch ( $d[1] ) {
				case 'checkbox':
					woocommerce_wp_checkbox( array( 'id' => $key, 'label' => $d[0], 'description' => $d[2], 'value' => $val ? 'yes' : 'no', 'cbvalue' => 'yes' ) );
					break;
				case 'textarea':
					woocommerce_wp_textarea_input( array( 'id' => $key, 'label' => $d[0], 'description' => $d[2], 'value' => $val, 'rows' => '_gadgero_specs' === $key ? 14 : 4, 'style' => 'font-family:monospace;min-height:' . ( '_gadgero_specs' === $key ? '260px' : '80px' ) ) );
					break;
				case 'select':
					woocommerce_wp_select(
						array(
							'id'          => $key,
							'label'       => $d[0],
							'description' => $d[2],
							'value'       => $val,
							'options'     => array( '' => '— none —', 'phone' => 'Phone', 'tablet' => 'Tablet', 'laptop' => 'Laptop', 'desktop' => 'iMac', 'mini' => 'Mac mini' ),
						)
					);
					break;
				default:
					woocommerce_wp_text_input( array( 'id' => $key, 'label' => $d[0], 'description' => $d[2], 'desc_tip' => true, 'value' => $val, 'type' => 'number' === $d[1] ? 'number' : 'text', 'custom_attributes' => 'number' === $d[1] ? array( 'min' => 0, 'max' => 100, 'step' => 1 ) : array() ) );
			}
		}
		echo '</div></div>';
	}
);

add_action(
	'woocommerce_admin_process_product_object',
	function ( WC_Product $product ) {
		foreach ( gadgero_product_field_defs() as $key => $d ) {
			// phpcs:disable WordPress.Security.NonceVerification -- WooCommerce verifies the product save nonce before this hook.
			if ( 'checkbox' === $d[1] ) {
				$product->update_meta_data( $key, isset( $_POST[ $key ] ) ? 'yes' : '' );
				continue;
			}
			$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
			// phpcs:enable
			$raw = 'textarea' === $d[1] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
			if ( 'number' === $d[1] ) {
				$raw = '' === $raw ? '' : (string) max( 0, min( 100, (int) $raw ) );
			}
			$product->update_meta_data( $key, $raw );
		}
	}
);

/* ------------------------------------------------------------------ */
/* Technical data                                                       */
/* ------------------------------------------------------------------ */

/** Parsed technical data: [ 'Gruppe' => [ [Merkmal, Wert], … ], … ] in entry order. */
function gadgero_specs( WC_Product $p ) {
	$id   = $p->get_parent_id() ? $p->get_parent_id() : $p->get_id();
	$raw  = (string) get_post_meta( $id, '_gadgero_specs', true );
	$out  = array();
	foreach ( preg_split( '/\R/', $raw ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line ) );
		if ( count( $parts ) < 3 || '' === $parts[2] ) {
			continue;
		}
		$out[ $parts[0] ][] = array( $parts[1], implode( ' | ', array_slice( $parts, 2 ) ) );
	}
	return $out;
}

/** Spec groups required per category (brief item 07). */
function gadgero_spec_required( WC_Product $p ) {
	$req = array(
		'phones'       => array( 'Display', 'Leistung', 'Kamera', 'Konnektivität', 'Abmessungen & Gewicht', 'Lieferumfang' ),
		'tablets'      => array( 'Display', 'Leistung', 'Kamera', 'Konnektivität', 'Abmessungen & Gewicht', 'Lieferumfang' ),
		'laptops'      => array( 'Display', 'Leistung', 'Anschlüsse', 'Tastatur', 'Abmessungen & Gewicht', 'Lieferumfang' ),
		'desktops'     => array( 'Leistung', 'Anschlüsse', 'Tastatur', 'Abmessungen & Gewicht', 'Lieferumfang' ),
		'smartwatches' => array( 'Modell', 'Display', 'Konnektivität', 'Lieferumfang' ),
		'accessories'  => array( 'Modell', 'Konnektivität', 'Lieferumfang' ),
	);
	$id   = $p->get_parent_id() ? $p->get_parent_id() : $p->get_id();
	$cats = wc_get_product_terms( $id, 'product_cat', array( 'fields' => 'slugs' ) );
	foreach ( $cats as $c ) {
		if ( isset( $req[ $c ] ) ) {
			return $req[ $c ];
		}
	}
	return array();
}

/** Required groups not yet filled in. */
function gadgero_spec_missing( WC_Product $p ) {
	$have = array_keys( gadgero_specs( $p ) );
	return array_values( array_diff( gadgero_spec_required( $p ), $have ) );
}

/* ------------------------------------------------------------------ */
/* Refurbished unit                                                      */
/* ------------------------------------------------------------------ */

/** Condition rows for a refurbished unit (only filled fields). */
function gadgero_refurb_details( WC_Product $p ) {
	$id   = $p->get_id();
	$rows = array();
	$map  = array(
		'_gadgero_grade'        => 'Zustandsnote',
		'_gadgero_battery'      => 'Akkukapazität (gemessen)',
		'_gadgero_screen'       => 'Display',
		'_gadgero_frame'        => 'Gehäuse',
		'_gadgero_accessories'  => 'Im Lieferumfang',
		'_gadgero_inspected_by' => 'Geprüft von',
		'_gadgero_programme'    => 'Programm',
	);
	foreach ( $map as $key => $label ) {
		$v = get_post_meta( $id, $key, true );
		if ( '' !== $v ) {
			$rows[] = array( $label, '_gadgero_battery' === $key ? (int) $v . ' %' : $v );
		}
	}
	return $rows;
}

/** Refurbished fields still empty (flagged internally). */
function gadgero_refurb_missing( WC_Product $p ) {
	$need = array(
		'_gadgero_grade'       => 'grade',
		'_gadgero_battery'     => 'battery health',
		'_gadgero_screen'      => 'screen condition',
		'_gadgero_frame'       => 'housing condition',
		'_gadgero_cosmetic'    => 'cosmetic notes',
		'_gadgero_accessories' => 'included items',
	);
	$miss = array();
	foreach ( $need as $k => $l ) {
		if ( '' === (string) get_post_meta( $p->get_id(), $k, true ) ) {
			$miss[] = $l;
		}
	}
	if ( 'yes' !== get_post_meta( $p->get_id(), '_gadgero_photos_actual', true ) ) {
		$miss[] = 'actual photos of this unit';
	}
	return $miss;
}
