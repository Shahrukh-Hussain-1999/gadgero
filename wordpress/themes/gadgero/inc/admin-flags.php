<?php
/**
 * Pre-launch safety: everything that still needs client confirmation is listed on the dashboard and in the
 * product list, so placeholder data cannot slip into a live launch unnoticed.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

/** Missing company / contact settings. */
function gadgero_missing_company_fields() {
	$need = array(
		'gadgero_company_name' => 'Legal company name',
		'gadgero_address'      => 'Business address',
		'gadgero_email'        => 'Support e-mail',
		'gadgero_phone'        => 'Telephone number',
		'gadgero_whatsapp'     => 'WhatsApp number',
		'gadgero_hours'        => 'Opening hours',
		'gadgero_returns'      => 'Returns address / contact',
		'gadgero_delivery_time' => 'Delivery time',
	);
	return array_values( array_filter( $need, fn( $k ) => '' === gadgero_opt( $k ), ARRAY_FILTER_USE_KEY ) );
}

/** IDs of products marked as unconfirmed. */
function gadgero_unconfirmed_products() {
	return get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_gadgero_unconfirmed', // phpcs:ignore
			'meta_value'     => 'yes', // phpcs:ignore
		)
	);
}

add_action(
	'admin_notices',
	function () {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'edit-product', 'woocommerce_page_wc-settings', 'customize' ), true ) ) {
			return;
		}
		$missing  = gadgero_missing_company_fields();
		$products = gadgero_unconfirmed_products();
		$pending  = (array) get_option( 'gadgero_pending_confirmations', array() );
		if ( ! $missing && ! $products && ! $pending ) {
			return;
		}
		echo '<div class="notice notice-error"><p><strong>CLIENT CONFIRMATION REQUIRED before launch</strong></p><ul style="list-style:disc;padding-left:20px">';
		if ( $missing ) {
			echo '<li>Company / contact (Appearance → Customize → Gadgero: company &amp; contact): ' . esc_html( implode( ', ', $missing ) ) . '</li>';
		}
		if ( $products ) {
			$links = array_map( fn( $id ) => '<a href="' . esc_url( get_edit_post_link( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a>', $products );
			echo '<li>' . count( $products ) . ' product(s) with placeholder price / SKU / stock / specs: ' . wp_kses_post( implode( ', ', $links ) ) . '</li>';
		}
		foreach ( $pending as $line ) {
			echo '<li>' . esc_html( $line ) . '</li>';
		}
		echo '</ul><p>Full list: <code>docs/CLIENT-CONFIRMATION-REQUIRED.md</code> in the project repository.</p></div>';
	}
);

/** "Confirmed" column on the product list. */
add_filter(
	'manage_edit-product_columns',
	function ( $cols ) {
		$cols['gadgero_confirmed'] = 'Client data';
		return $cols;
	}
);
add_action(
	'manage_product_posts_custom_column',
	function ( $col, $id ) {
		if ( 'gadgero_confirmed' === $col ) {
			echo 'yes' === get_post_meta( $id, '_gadgero_unconfirmed', true ) ? '<span style="color:#b32d2e;font-weight:600">Unconfirmed</span>' : '<span style="color:#00a32a">Confirmed</span>';
		}
	},
	10,
	2
);
