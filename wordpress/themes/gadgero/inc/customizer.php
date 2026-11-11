<?php
/**
 * Customizer: homepage campaign, contact details and company data.
 *
 * Contact and company fields default to EMPTY on purpose: they must be supplied by the client and are never guessed.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'customize_register',
	function ( WP_Customize_Manager $c ) {

		/* ---- Homepage campaign ---- */
		$c->add_section(
			'gadgero_campaign',
			array(
				'title'       => __( 'Gadgero: homepage campaign', 'gadgero' ),
				'description' => __( 'The banner reads its product name, starting price, image and button link from the WooCommerce product chosen here. The button and price are hidden automatically if that product is not published, not purchasable or out of stock.', 'gadgero' ),
				'priority'    => 30,
			)
		);
		$c->add_setting( 'gadgero_campaign_product', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
		$choices = array( 0 => __( '— none —', 'gadgero' ) );
		if ( function_exists( 'wc_get_products' ) ) {
			foreach ( wc_get_products( array( 'limit' => 200, 'status' => array( 'publish', 'draft', 'private' ), 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) {
				$choices[ $p->get_id() ] = $p->get_name() . ( 'publish' !== $p->get_status() ? ' (' . $p->get_status() . ')' : '' );
			}
		}
		$c->add_control( 'gadgero_campaign_product', array( 'label' => __( 'Campaign product', 'gadgero' ), 'section' => 'gadgero_campaign', 'type' => 'select', 'choices' => $choices ) );

		$text = array(
			'gadgero_campaign_word'   => array( __( 'Large background lettering (empty = product name)', 'gadgero' ), 'text' ),
			'gadgero_campaign_intro'  => array( __( 'Slide 1 text', 'gadgero' ), 'textarea' ),
			'gadgero_campaign_head2'  => array( __( 'Slide 2 heading', 'gadgero' ), 'text' ),
			'gadgero_campaign_text2'  => array( __( 'Slide 2 text', 'gadgero' ), 'textarea' ),
			'gadgero_campaign_tags'   => array( __( 'Slide 2 tags (one per line)', 'gadgero' ), 'textarea' ),
		);
		foreach ( $text as $id => $def ) {
			$c->add_setting( $id, array( 'default' => gadgero_campaign_default( $id ), 'sanitize_callback' => 'textarea' === $def[1] ? 'sanitize_textarea_field' : 'sanitize_text_field' ) );
			$c->add_control( $id, array( 'label' => $def[0], 'section' => 'gadgero_campaign', 'type' => $def[1] ) );
		}

		/* ---- Contact and company (client data only) ---- */
		$c->add_section(
			'gadgero_company',
			array(
				'title'       => __( 'Gadgero: company & contact', 'gadgero' ),
				'description' => __( 'Shown in the footer, on the contact and legal pages. Leave a field empty until the client has confirmed it; empty fields are hidden from visitors and flagged to logged-in managers.', 'gadgero' ),
				'priority'    => 31,
			)
		);
		$fields = array(
			'gadgero_company_name'  => array( __( 'Legal company name', 'gadgero' ), 'text' ),
			'gadgero_address'       => array( __( 'Business address (one line per row)', 'gadgero' ), 'textarea' ),
			'gadgero_hours'         => array( __( 'Opening hours (one line per row)', 'gadgero' ), 'textarea' ),
			'gadgero_whatsapp'      => array( __( 'WhatsApp number, international digits only (e.g. 49151…)', 'gadgero' ), 'text' ),
			'gadgero_phone'         => array( __( 'Telephone number', 'gadgero' ), 'text' ),
			'gadgero_email'         => array( __( 'Support e-mail', 'gadgero' ), 'email' ),
			'gadgero_returns'       => array( __( 'Returns address / contact (one line per row)', 'gadgero' ), 'textarea' ),
			'gadgero_delivery_time' => array( __( 'Delivery time shown at checkout, in German (e.g. "Lieferzeit: 1–3 Werktage")', 'gadgero' ), 'text' ),
		);
		foreach ( $fields as $id => $def ) {
			$san = 'textarea' === $def[1] ? 'sanitize_textarea_field' : ( 'email' === $def[1] ? 'sanitize_email' : 'sanitize_text_field' );
			$c->add_setting( $id, array( 'default' => '', 'sanitize_callback' => $san ) );
			$c->add_control( $id, array( 'label' => $def[0], 'section' => 'gadgero_company', 'type' => $def[1] ) );
		}
	}
);

/** Defaults for campaign copy: approved wording, minus claims the client has not confirmed. */
function gadgero_campaign_default( $id ) {
	$d = array(
		'gadgero_campaign_word'  => '',
		'gadgero_campaign_intro' => 'A foldable iPhone that opens into a tablet-size screen.',
		'gadgero_campaign_head2' => 'Folds flat. Opens wide.',
		'gadgero_campaign_text2' => 'A slim hinge that slips into a pocket and opens to a tablet-size OLED screen.',
		'gadgero_campaign_tags'  => "Foldable\nTablet-size OLED",
	);
	return $d[ $id ] ?? '';
}

function gadgero_campaign_text( $id ) {
	return (string) get_theme_mod( $id, gadgero_campaign_default( $id ) );
}

/** The campaign product if it is live and can actually be bought; null otherwise. */
function gadgero_campaign_product( $require_purchasable = true ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return null;
	}
	$p = wc_get_product( (int) get_theme_mod( 'gadgero_campaign_product', 0 ) );
	if ( ! $p || 'publish' !== $p->get_status() ) {
		return null;
	}
	if ( $require_purchasable && ( ! $p->is_purchasable() || ! $p->is_in_stock() ) ) {
		return null;
	}
	return $p;
}

/** Giant lettering behind the phone. */
function gadgero_campaign_word() {
	$w = gadgero_campaign_text( 'gadgero_campaign_word' );
	if ( '' === $w ) {
		$p = gadgero_campaign_product( false );
		$w = $p ? $p->get_name() : '';
	}
	return function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $w ) : strtoupper( $w );
}

/**
 * Lowest price a customer can actually pay for the campaign product (in-stock, purchasable variations only),
 * so "From €…" always matches a real, buyable configuration.
 */
function gadgero_campaign_from_price( WC_Product $p ) {
	if ( $p->is_type( 'variable' ) ) {
		$min = null;
		foreach ( $p->get_available_variations( 'objects' ) as $v ) {
			if ( $v->is_purchasable() && $v->is_in_stock() && '' !== $v->get_price() ) {
				$price = (float) wc_get_price_to_display( $v );
				$min   = null === $min ? $price : min( $min, $price );
			}
		}
		return $min;
	}
	return '' === $p->get_price() ? null : (float) wc_get_price_to_display( $p );
}
