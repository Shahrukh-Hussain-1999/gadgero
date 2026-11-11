<?php
/**
 * Extra product fields (product edit screen → "Gadgero" tab):
 * card summary, device illustration, factory-sealed flag, refurbished-unit details, data-confirmation flag.
 *
 * Refurbished details are only shown on the product page when filled in; nothing is displayed by default.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

/** Field definitions: meta key => [label, type, description]. */
function gadgero_product_field_defs() {
	return array(
		'_gadgero_unconfirmed'     => array( 'Data not yet confirmed by client', 'checkbox', 'Keep ticked while price, SKU, stock, specs or images are placeholders. Listed in the dashboard warning. Untick only after the client has confirmed every value.' ),
		'_gadgero_summary'         => array( 'Card summary', 'text', 'Short line under the name on product cards, e.g. "256 GB · Silver · Sealed".' ),
		'_gadgero_sealed'          => array( 'Original factory-sealed packaging', 'checkbox', 'New devices only. Shows "Original factory-sealed packaging" on the product page.' ),
		'_gadgero_battery'         => array( 'Battery health (%)', 'number', 'Refurbished only. Measured value for this unit, e.g. 94.' ),
		'_gadgero_screen'          => array( 'Screen condition', 'text', 'Refurbished only, e.g. "No visible scratches".' ),
		'_gadgero_frame'           => array( 'Frame / housing condition', 'text', 'Refurbished only.' ),
		'_gadgero_accessories'     => array( 'Included accessories', 'text', 'e.g. "USB-C cable".' ),
		'_gadgero_photos_actual'   => array( 'Photos show this exact unit', 'checkbox', 'Tick only when the gallery shows the actual device. Otherwise the page says images are illustrative.' ),
		'_gadgero_inspected_by'    => array( 'Inspected / certified by', 'text', 'Only if confirmed (company or programme name). Leave empty otherwise.' ),
		'_gadgero_programme'       => array( 'Certification programme', 'text', 'Only if confirmed.' ),
		'_gadgero_art'             => array( 'Illustration when no photo', 'select', 'Device drawing from the approved design, used until a real photo is added.' ),
		'_gadgero_art_c1'          => array( 'Illustration colour 1', 'text', 'Hex, e.g. #7aa2ff' ),
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
		foreach ( gadgero_product_field_defs() as $key => $d ) {
			$val = get_post_meta( $post->ID, $key, true );
			switch ( $d[1] ) {
				case 'checkbox':
					woocommerce_wp_checkbox( array( 'id' => $key, 'label' => $d[0], 'description' => $d[2], 'value' => $val ? 'yes' : 'no', 'cbvalue' => 'yes' ) );
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
			$raw = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
			// phpcs:enable
			if ( 'number' === $d[1] ) {
				$raw = '' === $raw ? '' : (string) max( 0, min( 100, (int) $raw ) );
			}
			$product->update_meta_data( $key, $raw );
		}
	}
);

/** Refurbished-unit detail rows for the product page (only filled fields). */
function gadgero_refurb_details( WC_Product $p ) {
	$id   = $p->get_id();
	$rows = array();
	$bat  = get_post_meta( $id, '_gadgero_battery', true );
	if ( '' !== $bat ) {
		$rows[] = array( __( 'Battery health', 'gadgero' ), (int) $bat . '%' );
	}
	foreach (
		array(
			'_gadgero_screen'       => __( 'Screen', 'gadgero' ),
			'_gadgero_frame'        => __( 'Frame', 'gadgero' ),
			'_gadgero_accessories'  => __( 'In the box', 'gadgero' ),
			'_gadgero_inspected_by' => __( 'Inspected by', 'gadgero' ),
			'_gadgero_programme'    => __( 'Programme', 'gadgero' ),
		) as $key => $label
	) {
		$v = get_post_meta( $id, $key, true );
		if ( '' !== $v ) {
			$rows[] = array( $label, $v );
		}
	}
	return $rows;
}
