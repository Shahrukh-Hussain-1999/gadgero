<?php
/**
 * Shortcodes for legal / service pages, so company data is entered once (Customizer) and never typed into page text.
 *
 * [gadgero_field key="company_name|address|email|phone|whatsapp|hours|returns" label="…"]
 * [gadgero_actions]  – customer service buttons (report a problem, contact, start a return, request assistance)
 * [gadgero_todo]Text[/gadgero_todo] – visible placeholder for content the client still has to supply
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

function gadgero_todo_html( $what ) {
	return '<span class="placeholder">[CLIENT CONFIRMATION REQUIRED: ' . esc_html( $what ) . ']</span>';
}

add_shortcode(
	'gadgero_todo',
	fn( $atts, $content = '' ) => gadgero_todo_html( wp_strip_all_tags( (string) $content ) )
);

add_shortcode(
	'gadgero_field',
	function ( $atts ) {
		$a     = shortcode_atts( array( 'key' => '', 'label' => '' ), $atts );
		$key   = 'gadgero_' . sanitize_key( $a['key'] );
		$label = $a['label'] ? $a['label'] : $a['key'];
		$v     = gadgero_opt( $key );
		if ( '' === $v ) {
			return gadgero_todo_html( $label );
		}
		if ( in_array( $a['key'], array( 'address', 'hours', 'returns' ), true ) ) {
			return implode( '<br>', array_map( 'esc_html', gadgero_lines( $key ) ) );
		}
		if ( 'email' === $a['key'] ) {
			$e = antispambot( $v );
			return '<a href="mailto:' . esc_attr( $e ) . '">' . esc_html( $e ) . '</a>';
		}
		if ( 'whatsapp' === $a['key'] ) {
			return '<a href="' . esc_url( gadgero_wa_link() ) . '" target="_blank" rel="noopener">' . esc_html( $v ) . '</a>';
		}
		return esc_html( $v );
	}
);

/**
 * [gadgero_shipping] – the shipping zones, methods and costs exactly as configured in WooCommerce, plus the
 * delivery time from the Customizer and the enabled payment methods. Brief item 10: the published delivery terms
 * can never differ from what the checkout charges.
 */
add_shortcode(
	'gadgero_shipping',
	function () {
		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			return '';
		}
		$out = '<table class="ship-table"><thead><tr><th>Liefergebiet</th><th>Versandart</th><th>Kosten (inkl. MwSt.)</th></tr></thead><tbody>';
		$n   = 0;
		foreach ( WC_Shipping_Zones::get_zones() as $z ) {
			$zone = new WC_Shipping_Zone( $z['id'] );
			foreach ( $zone->get_shipping_methods( true ) as $m ) {
				$cost = $m->get_option( 'cost', '' );
				if ( 'free_shipping' === $m->id ) {
					$label = 'kostenlos';
				} elseif ( '' === $cost || is_numeric( str_replace( ',', '.', $cost ) ) ) {
					$label = wc_price( (float) str_replace( ',', '.', (string) $cost ) );
				} else {
					$label = 'wird an der Kasse berechnet';
				}
				$out .= '<tr><td>' . esc_html( $z['zone_name'] ) . '</td><td>' . esc_html( $m->get_title() ) . '</td><td>' . wp_kses_post( $label ) . '</td></tr>';
				++$n;
			}
		}
		$out .= '</tbody></table>';
		if ( ! $n ) {
			$out = '<p>' . gadgero_todo_html( 'Versandzonen und -kosten' ) . '</p>';
		}
		$time = gadgero_opt( 'gadgero_delivery_time' );
		$out .= '<p><strong>Lieferzeit:</strong> ' . ( $time ? esc_html( $time ) : gadgero_todo_html( 'Lieferzeit' ) ) . '</p>';
		$gw    = function_exists( 'WC' ) && WC()->payment_gateways() ? WC()->payment_gateways()->payment_gateways() : array();
		$names = array();
		foreach ( $gw as $g ) {
			if ( 'yes' === $g->enabled ) {
				$names[] = $g->get_title();
			}
		}
		$out .= '<p><strong>Zahlungsarten:</strong> ' . ( $names ? esc_html( implode( ', ', $names ) ) : gadgero_todo_html( 'Zahlungsarten' ) ) . '</p>';
		return $out;
	}
);

add_shortcode(
	'gadgero_actions',
	function () {
		$email = gadgero_opt( 'gadgero_email' );
		$mail  = function ( $subject ) use ( $email ) {
			return $email ? 'mailto:' . antispambot( $email ) . '?subject=' . rawurlencode( $subject ) : '';
		};
		$shop    = get_bloginfo( 'name' );
		$actions = array(
			array( 'Problem melden', $mail( 'Problem mit meinem Gerät' ), gadgero_wa_link( "Hallo $shop, ich möchte ein Problem mit meinem Gerät melden. Bestellnummer: " ) ),
			array( 'Support kontaktieren', $mail( 'Supportanfrage' ), gadgero_wa_link( "Hallo $shop, ich habe eine Frage." ) ),
			array( 'Rücksendung starten', function_exists( 'gadgero_page_url' ) ? gadgero_page_url( 'muster-widerrufsformular' ) : '', '' ),
			array( 'Hilfe anfordern', $mail( 'Bitte um Unterstützung' ), gadgero_wa_link( "Hallo $shop, ich benötige Hilfe zu meiner Bestellung. Bestellnummer: " ) ),
		);
		$out = '<div class="svc-actions">';
		foreach ( $actions as $a ) {
			$out .= '<div class="svc"><b>' . esc_html( $a[0] ) . '</b>';
			if ( $a[1] ) {
				$out .= '<a class="ghost" href="' . esc_url( $a[1] ) . '">' . ( 0 === strpos( $a[1], 'mailto:' ) ? 'Per E-Mail' : 'Formular öffnen' ) . '</a>';
			}
			if ( $a[2] ) {
				$out .= '<a class="ghost" href="' . esc_url( $a[2] ) . '" target="_blank" rel="noopener">Per WhatsApp</a>';
			}
			if ( ! $a[1] && ! $a[2] ) {
				$out .= gadgero_todo_html( 'Kontaktweg' );
			}
			$out .= '</div>';
		}
		if ( ! $email ) {
			$out .= '<p>' . gadgero_todo_html( 'Support-E-Mail-Adresse' ) . '</p>';
		}
		return $out . '</div>';
	}
);
