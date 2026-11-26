<?php
/**
 * WooCommerce integration. Native WooCommerce does the work (products, variations, stock, cart, checkout, orders);
 * this file only adapts output to the Gadgero design and keeps the exact configuration visible end to end.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Styling: the theme provides all WooCommerce styles itself.          */
/* ------------------------------------------------------------------ */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
add_filter( 'loop_shop_per_page', fn() => 24 );

/* ------------------------------------------------------------------ */
/* Condition (product level: New / Refurbished) and attributes         */
/* ------------------------------------------------------------------ */

/** 'new', 'refurbished' or '' for a product or variation. */
function gadgero_condition( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}
	$id    = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
	$slugs = wc_get_product_terms( $id, 'pa_condition', array( 'fields' => 'slugs' ) );
	return $slugs ? (string) $slugs[0] : '';
}

/** Human label for a condition, in the language of the current page. */
function gadgero_condition_label( $slug ) {
	$l = array(
		'new'         => 'Neu',
		'refurbished' => 'Refurbished',
	);
	return $l[ $slug ] ?? '';
}

/** Brand name (WooCommerce Brands taxonomy), or ''. */
function gadgero_brand( WC_Product $p ) {
	$id    = $p->get_parent_id() ? $p->get_parent_id() : $p->get_id();
	$names = taxonomy_exists( 'product_brand' ) ? wc_get_product_terms( $id, 'product_brand', array( 'fields' => 'names' ) ) : array();
	return $names ? (string) $names[0] : '';
}

/** Attribute order used for configuration lines: screen size · storage · colour · connectivity · others. */
function gadgero_attr_order() {
	return array( 'pa_screen-size', 'pa_storage', 'pa_colour', 'pa_connectivity' );
}

/** Attribute labels in German on the German routes (cart, checkout, e-mails). */
add_filter(
	'woocommerce_attribute_label',
	function ( $label, $name ) {
		if ( 0 !== strpos( determine_locale(), 'de' ) ) {
			return $label;
		}
		$map = array(
			'pa_storage'      => 'Speicher',
			'pa_colour'       => 'Farbe',
			'pa_connectivity' => 'Konnektivität',
			'pa_screen-size'  => 'Bildschirmgröße',
			'pa_condition'    => 'Zustand',
		);
		$key = 0 === strpos( $name, 'pa_' ) ? $name : 'pa_' . $name;
		return $map[ $key ] ?? $label;
	},
	10,
	2
);

/**
 * Configuration values for a product: for a variation the chosen attribute terms, for a simple product its
 * visible attribute values. Returns an ordered list of strings, e.g. ['11-inch', '128 GB', 'Blue', 'Wi-Fi'].
 *
 * @param WC_Product $product   Product or variation.
 * @param array      $variation Cart-style attribute array (attribute_pa_x => slug) when known.
 */
function gadgero_config_values( WC_Product $product, array $variation = array() ) {
	$vals = array();
	if ( $product->is_type( 'variation' ) ) {
		// Fixed attributes of the parent (e.g. one storage size for all colours) belong to the configuration too.
		$parent = wc_get_product( $product->get_parent_id() );
		if ( $parent ) {
			foreach ( $parent->get_attributes() as $tax => $attr ) {
				if ( 'pa_condition' === $tax || $attr->get_variation() || ! $attr->get_visible() ) {
					continue;
				}
				$vals[ $tax ] = implode( ' / ', $attr->is_taxonomy() ? wc_get_product_terms( $parent->get_id(), $tax, array( 'fields' => 'names' ) ) : $attr->get_options() );
			}
		}
		$attrs = $variation ? $variation : $product->get_variation_attributes();
		foreach ( $attrs as $key => $slug ) {
			$tax = str_replace( 'attribute_', '', $key );
			if ( '' === $slug ) {
				continue;
			}
			if ( taxonomy_exists( $tax ) ) {
				$term          = get_term_by( 'slug', $slug, $tax );
				$vals[ $tax ]  = $term ? $term->name : $slug;
			} else {
				$vals[ $tax ] = $slug;
			}
		}
	} else {
		foreach ( $product->get_attributes() as $tax => $attr ) {
			if ( 'pa_condition' === $tax || ! $attr->get_visible() ) {
				continue;
			}
			$vals[ $tax ] = implode( ' / ', $attr->is_taxonomy() ? wc_get_product_terms( $product->get_id(), $tax, array( 'fields' => 'names' ) ) : $attr->get_options() );
		}
	}
	$ordered = array();
	foreach ( gadgero_attr_order() as $tax ) {
		if ( isset( $vals[ $tax ] ) ) {
			$ordered[] = $vals[ $tax ];
			unset( $vals[ $tax ] );
		}
	}
	return array_values( array_filter( array_merge( $ordered, array_values( $vals ) ) ) );
}

/** "11-inch · 128 GB · Blue · Wi-Fi · New" */
function gadgero_config_line( WC_Product $product, array $variation = array(), $with_condition = true ) {
	$vals = gadgero_config_values( $product, $variation );
	if ( $with_condition ) {
		$c = gadgero_condition_label( gadgero_condition( $product ) );
		if ( $c ) {
			$vals[] = $c;
		}
	}
	return implode( ' · ', $vals );
}

/* ------------------------------------------------------------------ */
/* Exact configuration in cart → checkout → order → e-mails            */
/* ------------------------------------------------------------------ */

// Never fold attributes into the product name; they are shown as one explicit configuration line instead.
add_filter( 'woocommerce_product_variation_title_include_attributes', '__return_false' );
add_filter( 'woocommerce_is_attribute_in_product_name', '__return_false' );

add_filter(
	'woocommerce_get_item_data',
	function ( $item_data, $cart_item ) {
		$product = $cart_item['data'] ?? null;
		if ( ! $product instanceof WC_Product ) {
			return $item_data;
		}
		// Replace WooCommerce's one-row-per-attribute list with a single configuration row; keep any other data rows.
		$item_data = array_values(
			array_filter(
				$item_data,
				function ( $row ) use ( $cart_item ) {
					foreach ( (array) ( $cart_item['variation'] ?? array() ) as $key => $v ) {
						if ( isset( $row['key'] ) && wc_attribute_label( str_replace( 'attribute_', '', $key ), $cart_item['data'] ) === $row['key'] ) {
							return false;
						}
					}
					return true;
				}
			)
		);
		$line = gadgero_config_line( $product, (array) ( $cart_item['variation'] ?? array() ) );
		$add  = array();
		if ( $line ) {
			$add[] = array( 'key' => 'Konfiguration', 'value' => $line );
		}
		if ( $product->get_sku() ) { // Brief item 08: the selected SKU stays visible through basket and checkout.
			$add[] = array( 'key' => 'Art.-Nr.', 'value' => $product->get_sku() );
		}
		return array_merge( $add, $item_data );
	},
	20,
	2
);

/** Store the configuration on each order line (German label, as orders are placed through the German checkout). */
add_action(
	'woocommerce_checkout_create_order_line_item',
	function ( $item, $cart_item_key, $values ) {
		$product = $values['data'] ?? null;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$vals = gadgero_config_values( $product, (array) ( $values['variation'] ?? array() ) );
		$cond = gadgero_condition( $product );
		if ( $cond ) {
			$vals[] = 'new' === $cond ? 'Neu' : 'Refurbished (generalüberholt)';
		}
		if ( $vals ) {
			$item->add_meta_data( 'Konfiguration', implode( ' · ', $vals ), true );
		}
		$sku = $product->get_sku();
		if ( $sku ) {
			$item->add_meta_data( 'Art.-Nr.', $sku, true ); // visible on confirmation page and e-mails (brief item 08)
		}
	},
	10,
	3
);

/** Customer-facing order views and e-mails: show the single configuration row instead of repeating each attribute. */
add_filter(
	'woocommerce_order_item_get_formatted_meta_data',
	function ( $meta, $item ) {
		$admin_screen = is_admin() && ! did_action( 'woocommerce_email_header' );
		if ( $admin_screen || ! $item->get_meta( 'Konfiguration' ) ) {
			return $meta;
		}
		foreach ( $meta as $id => $m ) {
			if ( 0 === strpos( $m->key, 'pa_' ) || 0 === strpos( $m->key, 'attribute_' ) ) {
				unset( $meta[ $id ] );
			}
		}
		return $meta;
	},
	10,
	2
);

/* ------------------------------------------------------------------ */
/* Shipping: always show the amount (also 0,00 €) and, once confirmed,  */
/* the delivery time (Customizer → Gadgero: company & contact).          */
/* ------------------------------------------------------------------ */
add_filter(
	'woocommerce_cart_shipping_method_full_label',
	function ( $label, $method ) {
		if ( (float) $method->cost <= 0 && false === strpos( $label, 'amount' ) ) {
			$label .= ': ' . wc_price( 0 );
		}
		$time = gadgero_opt( 'gadgero_delivery_time' );
		if ( $time ) {
			$label .= '<small class="dtime">' . esc_html( $time ) . '</small>';
		}
		return $label;
	},
	10,
	2
);

/** Order totals (confirmation page, e-mails): show the shipping amount even when it is 0,00 €. */
add_filter(
	'woocommerce_order_shipping_to_display',
	function ( $shipping, $order ) {
		if ( $order->get_shipping_method() && (float) $order->get_shipping_total() <= 0 ) {
			$zero = wc_price( 0, array( 'currency' => $order->get_currency() ) );
			// E-mails already print the method name next to the amount.
			return did_action( 'woocommerce_email_header' ) ? $zero : $zero . '&nbsp;<small class="shipped_via">' . esc_html( sprintf( 'via %s', $order->get_shipping_method() ) ) . '</small>';
		}
		return $shipping;
	},
	10,
	2
);

/* ------------------------------------------------------------------ */
/* German checkout wording and guest checkout                          */
/* ------------------------------------------------------------------ */

// Button wording required for binding online orders in Germany (§ 312j Abs. 3 BGB).
add_filter( 'woocommerce_order_button_text', fn() => 'Zahlungspflichtig bestellen' );

/** Note on cart and checkout that separates WhatsApp enquiries from binding orders. */
function gadgero_binding_order_note() {
	echo '<p class="binding-note">' . esc_html__( 'Verbindliche Bestellungen sind nur über diese Kasse möglich. Über WhatsApp beraten wir zu Geräten, beantworten Fragen zum Lagerbestand und erstellen Ankaufsangebote – dort werden keine Bestellungen abgeschlossen.', 'gadgero' ) . '</p>';
}
add_action( 'woocommerce_after_cart_table', 'gadgero_binding_order_note' );
add_action( 'woocommerce_review_order_before_payment', 'gadgero_binding_order_note' );

/** Show the chosen payment method name next to the total before submitting (in addition to the radio list). */
add_action(
	'woocommerce_review_order_after_order_total',
	function () {
		$gateways = WC()->payment_gateways() ? WC()->payment_gateways()->get_available_payment_gateways() : array();
		$chosen   = WC()->session ? WC()->session->get( 'chosen_payment_method' ) : '';
		if ( ! $chosen && $gateways ) {
			$chosen = array_key_first( $gateways );
		}
		if ( $chosen && isset( $gateways[ $chosen ] ) ) {
			echo '<tr class="gadgero-paymethod"><th>' . esc_html__( 'Zahlungsart', 'gadgero' ) . '</th><td data-title="' . esc_attr__( 'Zahlungsart', 'gadgero' ) . '">' . esc_html( $gateways[ $chosen ]->get_title() ) . '</td></tr>';
		}
	}
);

/* ------------------------------------------------------------------ */
/* Thumbnails: product photo, or the approved device illustration       */
/* ------------------------------------------------------------------ */

/** Device art settings stored on the parent product. */
function gadgero_art( WC_Product $p ) {
	$id   = $p->get_parent_id() ? $p->get_parent_id() : $p->get_id();
	$type = get_post_meta( $id, '_gadgero_art', true );
	if ( ! $type ) {
		return null;
	}
	return array(
		'type'  => $type,
		'c1'    => get_post_meta( $id, '_gadgero_art_c1', true ),
		'c2'    => get_post_meta( $id, '_gadgero_art_c2', true ),
		'frame' => get_post_meta( $id, '_gadgero_art_frame', true ),
	);
}

/** Card glow colour. */
function gadgero_glow( WC_Product $p ) {
	$id = $p->get_parent_id() ? $p->get_parent_id() : $p->get_id();
	return gadgero_hex( get_post_meta( $id, '_gadgero_glow', true ), '#cfe0ff' );
}

/** Photo if one exists, otherwise the device illustration, otherwise WooCommerce's placeholder. */
function gadgero_product_visual( WC_Product $p, $size = 'woocommerce_thumbnail' ) {
	// A variation shows only its own photo. Never fall back to the parent's photo, which may show another colour.
	$img_id = $p->is_type( 'variation' ) ? $p->get_image_id( 'edit' ) : $p->get_image_id();
	if ( $img_id ) {
		return wp_get_attachment_image( $img_id, $size, false, array( 'alt' => $p->get_name(), 'loading' => 'lazy' ) );
	}
	$art = gadgero_art( $p );
	return gadgero_no_photo_svg( $art ? $art['type'] : 'phone', $p->get_name() );
}

/** Product page: tell product.js which variations have no photo of their own colour. */
add_filter(
	'woocommerce_available_variation',
	function ( $data, $product, $variation ) {
		$data['gadgero_no_photo'] = ! $variation->get_image_id( 'edit' );
		if ( '' === $variation->get_price() ) {
			$data['price_html'] = '<span class="price on-request">Preis auf Anfrage</span>';
		}
		return $data;
	},
	10,
	3
);

add_filter(
	'woocommerce_cart_item_thumbnail',
	function ( $thumb, $cart_item ) {
		return isset( $cart_item['data'] ) ? '<span class="mini">' . gadgero_product_visual( $cart_item['data'], 'thumbnail' ) . '</span>' : $thumb;
	},
	10,
	2
);

/* ------------------------------------------------------------------ */
/* Prices for cards                                                     */
/* ------------------------------------------------------------------ */

/** Card price: one baseline; "ab" for variable products with different prices; struck-through regular price only for real sale prices. */
function gadgero_card_price( WC_Product $p ) {
	if ( $p->is_type( 'variable' ) ) {
		$min = $p->get_variation_price( 'min', true );
		$max = $p->get_variation_price( 'max', true );
		if ( '' === $min || false === $min || null === $min ) { // no priced variation yet
			return '<span class="on-request">Preis auf Anfrage</span>';
		}
		return ( $min !== $max ? '<small class="from">ab</small> ' : '' ) . wc_price( $min );
	}
	if ( '' === $p->get_price() ) {
		return '<span class="on-request">Preis auf Anfrage</span>';
	}
	$html = wc_price( wc_get_price_to_display( $p ) );
	if ( $p->is_on_sale() && $p->get_regular_price() ) {
		$html .= '<s>' . wc_price( wc_get_price_to_display( $p, array( 'price' => $p->get_regular_price() ) ) ) . '</s>';
	}
	return $html;
}

/**
 * One useful configuration line for cards (brief item 04): the fixed configuration of a simple product
 * ("128 GB · Ultramarin"), or the choices of a variable product ("11 / 13 Zoll · 128 / 256 GB · 2 Farben").
 * A manual card summary (Gadgero tab) overrides it.
 */
function gadgero_card_spec( WC_Product $p ) {
	$s = get_post_meta( $p->get_id(), '_gadgero_summary', true );
	if ( $s ) {
		return $s;
	}
	if ( ! $p->is_type( 'variable' ) ) {
		return gadgero_config_line( $p, array(), false );
	}
	$parts = array();
	foreach ( gadgero_attr_order() as $tax ) {
		$attr = $p->get_attributes()[ $tax ] ?? null;
		if ( ! $attr || ! $attr->get_variation() ) {
			continue;
		}
		$names = wc_get_product_terms( $p->get_id(), $tax, array( 'fields' => 'names' ) );
		if ( 'pa_colour' === $tax && count( $names ) > 1 ) {
			$parts[] = count( $names ) . ' Farben';
		} elseif ( 'pa_screen-size' === $tax ) {
			$parts[] = implode( ' / ', array_map( fn( $n ) => trim( str_replace( 'Zoll', '', $n ) ), $names ) ) . ' Zoll';
		} else {
			$parts[] = implode( ' / ', $names );
		}
	}
	return implode( ' · ', $parts );
}

/** Add-to-cart control for cards. */
function gadgero_card_button( WC_Product $p ) {
	if ( ! $p->is_in_stock() ) {
		return '<span class="add is-off">Ausverkauft</span>';
	}
	if ( $p->is_type( 'simple' ) && $p->is_purchasable() ) {
		return sprintf(
			'<a href="%s" data-quantity="1" data-product_id="%d" data-product_sku="%s" data-name="%s" class="add add_to_cart_button ajax_add_to_cart" rel="nofollow" aria-label="%s">In den Warenkorb</a>',
			esc_url( $p->add_to_cart_url() ),
			$p->get_id(),
			esc_attr( $p->get_sku() ),
			esc_attr( $p->get_name() ),
			esc_attr( sprintf( '%s in den Warenkorb legen', $p->get_name() ) )
		);
	}
	return '<a class="add" href="' . esc_url( $p->get_permalink() ) . '" aria-label="' . esc_attr( sprintf( 'Konfiguration für %s wählen', $p->get_name() ) ) . '">Auswählen</a>';
}

/** Does the product have any price yet? (variable: at least one priced variation) */
function gadgero_has_price( WC_Product $p ) {
	$v = $p->is_type( 'variable' ) ? $p->get_variation_price( 'min' ) : $p->get_price();
	return '' !== $v && false !== $v && null !== $v;
}

/** One-line tax/shipping notice next to prices (German price-display rules). */
function gadgero_price_notice() {
	$page = function_exists( 'gadgero_page_url' ) ? gadgero_page_url( 'versand-und-zahlung' ) : '';
	$link = $page ? '<a href="' . esc_url( $page ) . '">zzgl. Versand</a>' : 'zzgl. Versand';
	return '<span class="taxnote">inkl. MwSt., ' . $link . '</span>';
}

/** Permalink of a page by slug, or ''. */
function gadgero_page_url( $slug ) {
	$p = get_page_by_path( $slug );
	return ( $p && 'publish' === $p->post_status ) ? get_permalink( $p ) : '';
}

/* ------------------------------------------------------------------ */
/* Header cart count + drawer via fragments                             */
/* ------------------------------------------------------------------ */
function gadgero_cart_count_html() {
	$n = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	return '<span class="n" id="cartN">' . (int) $n . '</span>';
}

add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( $fragments ) {
		$fragments['span#cartN'] = gadgero_cart_count_html();
		ob_start();
		gadgero_drawer_inner();
		$fragments['div#drawerInner'] = ob_get_clean();
		return $fragments;
	}
);

/** Mini-cart drawer content (German; WooCommerce cart data incl. configuration and SKU). */
function gadgero_drawer_inner() {
	$cart = WC()->cart;
	echo '<div id="drawerInner" class="drawer-inner">';
	echo '<div class="items" id="items">';
	if ( ! $cart || $cart->is_empty() ) {
		echo '<p class="empty">Ihr Warenkorb ist leer.</p>';
	} else {
		foreach ( $cart->get_cart() as $key => $ci ) {
			$p = $ci['data'];
			if ( ! $p || ! $p->exists() ) {
				continue;
			}
			$line = gadgero_config_line( $p, (array) ( $ci['variation'] ?? array() ) );
			echo '<div class="row"><div class="mini">' . gadgero_product_visual( $p, 'thumbnail' ) . '</div><div><b><a href="' . esc_url( $p->get_permalink( $ci ) ) . '">' . esc_html( $p->get_name() ) . '</a></b>'; // phpcs:ignore
			if ( $line ) {
				echo '<small class="cfg">' . esc_html( $line ) . '</small>';
			}
			if ( $p->get_sku() ) {
				echo '<small>Art.-Nr. ' . esc_html( $p->get_sku() ) . '</small>';
			}
			echo '<small>Menge: ' . (int) $ci['quantity'] . '</small></div>';
			echo '<strong>' . wp_kses_post( $cart->get_product_subtotal( $p, $ci['quantity'] ) ) . '</strong></div>';
		}
	}
	echo '</div>';
	echo '<div class="dfoot">';
	echo '<div class="total"><span>Zwischensumme</span><strong id="total">' . wp_kses_post( $cart ? $cart->get_cart_subtotal() : wc_price( 0 ) ) . '</strong></div>';
	if ( $cart && ! $cart->is_empty() ) {
		echo '<a class="solid" href="' . esc_url( wc_get_checkout_url() ) . '">Zur Kasse</a>';
		echo '<a class="ghost" href="' . esc_url( wc_get_cart_url() ) . '">Warenkorb ansehen</a>';
	}
	echo '<p class="dnote">Versandkosten und MwSt. sehen Sie an der Kasse. Fragen vor der Bestellung? <a href="' . esc_url( gadgero_advice_link( sprintf( 'Hallo %s, ich habe eine Frage zu einem Gerät.', get_bloginfo( 'name' ) ) ) ) . '">Beratung anfragen</a></p>';
	echo '</div></div>';
}

/* ------------------------------------------------------------------ */
/* Single product: related products of the same condition               */
/* ------------------------------------------------------------------ */
function gadgero_related( WC_Product $p, $limit = 4 ) {
	$cats = wc_get_product_term_ids( $p->get_id(), 'product_cat' );
	$cond = gadgero_condition( $p );
	$args = array(
		'status'  => 'publish',
		'limit'   => $limit,
		'exclude' => array( $p->get_id() ),
		'orderby' => 'menu_order',
		'order'   => 'ASC',
	);
	$tax  = array( 'relation' => 'AND' );
	if ( $cats ) {
		$tax[] = array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $cats );
	}
	if ( $cond ) {
		$tax[] = array( 'taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => array( $cond ) );
	}
	$q = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'post__not_in'   => array( $p->get_id() ),
			'tax_query'      => $tax, // phpcs:ignore
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'fields'         => 'ids',
		)
	);
	return array_filter( array_map( 'wc_get_product', $q->posts ) );
}

/* ------------------------------------------------------------------ */
/* Breadcrumbs (light)                                                  */
/* ------------------------------------------------------------------ */
add_filter(
	'woocommerce_breadcrumb_defaults',
	fn( $d ) => array_merge(
		$d,
		array(
			'delimiter'   => '<span aria-hidden="true">/</span>',
			'wrap_before' => '<nav class="crumbs" aria-label="' . esc_attr__( 'Brotkrümelnavigation', 'gadgero' ) . '">',
			'wrap_after'  => '</nav>',
			'home'        => 'Startseite',
		)
	)
);


/* ------------------------------------------------------------------ */
/* No prices yet (26.11.2026): show every configuration, "Preis auf     */
/* Anfrage", never orderable until a price is entered in WooCommerce.    */
/* ------------------------------------------------------------------ */
add_filter( 'woocommerce_variation_is_visible', '__return_true', 10 ); // keep configurations selectable without a price

add_filter(
	'woocommerce_get_script_data',
	function ( $params, $handle ) {
		if ( 'wc-add-to-cart-variation' === $handle && is_array( $params ) ) {
			$params['i18n_unavailable_text'] = 'Diese Konfiguration kann derzeit nicht bestellt werden – Preis auf Anfrage.';
		}
		return $params;
	},
	10,
	2
);
