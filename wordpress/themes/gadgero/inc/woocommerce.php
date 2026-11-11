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
	$de = 0 === strpos( determine_locale(), 'de' );
	$l  = array(
		'new'         => $de ? 'Neu' : 'New',
		'refurbished' => $de ? 'Refurbished (generalüberholt)' : 'Refurbished',
	);
	return $l[ $slug ] ?? '';
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
		if ( $line ) {
			array_unshift(
				$item_data,
				array(
					'key'   => 0 === strpos( determine_locale(), 'de' ) ? 'Konfiguration' : 'Configuration',
					'value' => $line,
				)
			);
		}
		return $item_data;
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
			$item->add_meta_data( '_gadgero_sku', $sku, true );
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
	$img_id = $p->get_image_id();
	if ( ! $img_id && $p->get_parent_id() ) {
		$parent = wc_get_product( $p->get_parent_id() );
		$img_id = $parent ? $parent->get_image_id() : 0;
	}
	if ( $img_id ) {
		return wp_get_attachment_image( $img_id, $size, false, array( 'alt' => $p->get_name(), 'loading' => 'lazy' ) );
	}
	$art = gadgero_art( $p );
	if ( $art ) {
		return gadgero_device_svg( $art['type'], $art['c1'], $art['c2'], $art['frame'], $p->get_name() );
	}
	return wc_placeholder_img( $size );
}

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

/** Card price in the approved style: big price, real regular price struck through only for genuine sale prices. */
function gadgero_card_price( WC_Product $p ) {
	if ( $p->is_type( 'variable' ) ) {
		$min = $p->get_variation_price( 'min', true );
		$max = $p->get_variation_price( 'max', true );
		if ( '' === $min ) {
			return '';
		}
		return ( $min !== $max ? '<small class="from">' . esc_html__( 'From', 'gadgero' ) . '</small> ' : '' ) . wc_price( $min );
	}
	if ( '' === $p->get_price() ) {
		return '';
	}
	$html = wc_price( wc_get_price_to_display( $p ) );
	if ( $p->is_on_sale() && $p->get_regular_price() ) {
		$html .= '<s>' . wc_price( wc_get_price_to_display( $p, array( 'price' => $p->get_regular_price() ) ) ) . '</s>';
	}
	return $html;
}

/** Short spec line for cards ("256 GB · Silver · Sealed"); falls back to the configuration values. */
function gadgero_card_spec( WC_Product $p ) {
	$s = get_post_meta( $p->get_id(), '_gadgero_summary', true );
	if ( $s ) {
		return $s;
	}
	if ( $p->is_type( 'variable' ) ) {
		$parts = array();
		foreach ( gadgero_attr_order() as $tax ) {
			$attr = $p->get_attributes()[ $tax ] ?? null;
			if ( $attr && $attr->get_variation() ) {
				$n       = count( $attr->get_options() );
				$parts[] = $n . ' ' . strtolower( wc_attribute_label( $tax ) ) . ( $n > 1 ? ' options' : ' option' );
			}
		}
		return implode( ' · ', $parts );
	}
	return gadgero_config_line( $p, array(), false );
}

/** Add-to-cart control for cards. */
function gadgero_card_button( WC_Product $p ) {
	if ( ! $p->is_in_stock() ) {
		return '<span class="add is-off">' . esc_html__( 'Sold out', 'gadgero' ) . '</span>';
	}
	if ( $p->is_type( 'simple' ) && $p->is_purchasable() ) {
		return sprintf(
			'<a href="%s" data-quantity="1" data-product_id="%d" data-product_sku="%s" data-name="%s" class="add add_to_cart_button ajax_add_to_cart" rel="nofollow" aria-label="%s">%s</a>',
			esc_url( $p->add_to_cart_url() ),
			$p->get_id(),
			esc_attr( $p->get_sku() ),
			esc_attr( $p->get_name() ),
			esc_attr( sprintf( __( 'Add %s to cart', 'gadgero' ), $p->get_name() ) ),
			esc_html__( 'Add to cart', 'gadgero' )
		);
	}
	return '<a class="add" href="' . esc_url( $p->get_permalink() ) . '" aria-label="' . esc_attr( sprintf( __( 'Choose options for %s', 'gadgero' ), $p->get_name() ) ) . '">' . esc_html__( 'Choose options', 'gadgero' ) . '</a>';
}

/** One-line tax/shipping notice next to prices (German price-display rules). */
function gadgero_price_notice() {
	$page = function_exists( 'gadgero_page_url' ) ? gadgero_page_url( 'versand-und-zahlung' ) : '';
	$link = $page ? '<a href="' . esc_url( $page ) . '">' . esc_html__( 'plus shipping', 'gadgero' ) . '</a>' : esc_html__( 'plus shipping', 'gadgero' );
	return '<span class="taxnote">' . esc_html__( 'incl. VAT,', 'gadgero' ) . ' ' . $link . '</span>';
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

/** Mini-cart drawer content (approved drawer layout, WooCommerce cart data). */
function gadgero_drawer_inner() {
	$cart = WC()->cart;
	echo '<div id="drawerInner" class="drawer-inner">';
	echo '<div class="items" id="items">';
	if ( ! $cart || $cart->is_empty() ) {
		echo '<p class="empty">' . esc_html__( 'Your cart is empty. Add a device from the shop.', 'gadgero' ) . '</p>';
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
			echo '<small>' . esc_html( sprintf( /* translators: %d quantity */ __( 'Qty %d', 'gadgero' ), $ci['quantity'] ) ) . '</small></div>';
			echo '<strong>' . wp_kses_post( $cart->get_product_subtotal( $p, $ci['quantity'] ) ) . '</strong></div>';
		}
	}
	echo '</div>';
	echo '<div class="dfoot">';
	echo '<div class="total"><span>' . esc_html__( 'Subtotal', 'gadgero' ) . '</span><strong id="total">' . wp_kses_post( $cart ? $cart->get_cart_subtotal() : wc_price( 0 ) ) . '</strong></div>';
	if ( $cart && ! $cart->is_empty() ) {
		echo '<a class="solid" href="' . esc_url( wc_get_checkout_url() ) . '">' . esc_html__( 'Checkout', 'gadgero' ) . '</a>';
		echo '<a class="ghost" href="' . esc_url( wc_get_cart_url() ) . '">' . esc_html__( 'View cart', 'gadgero' ) . '</a>';
	}
	echo '<p class="dnote">' . esc_html__( 'Shipping and VAT are shown at checkout. Questions before you order?', 'gadgero' ) . ' <a href="' . esc_url( gadgero_wa_link( sprintf( __( 'Hi %s, I have a question about a device.', 'gadgero' ), get_bloginfo( 'name' ) ) ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Ask on WhatsApp', 'gadgero' ) . '</a></p>';
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
			'wrap_before' => '<nav class="crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'gadgero' ) . '">',
			'wrap_after'  => '</nav>',
			'home'        => __( 'Home', 'gadgero' ),
		)
	)
);
