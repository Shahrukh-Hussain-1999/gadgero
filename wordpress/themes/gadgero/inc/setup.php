<?php
/**
 * Theme supports, menus and assets.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'gadgero', GADGERO_DIR . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 200,
				'width'       => 258,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
		add_theme_support(
			'woocommerce',
			array(
				'thumbnail_image_width' => 600,
				'single_image_width'    => 1000,
			)
		);
		register_nav_menus(
			array(
				'primary' => __( 'Header navigation', 'gadgero' ),
				'legal'   => __( 'Footer legal links', 'gadgero' ),
				'service' => __( 'Footer customer service links', 'gadgero' ),
			)
		);
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		$v = GADGERO_VERSION;

		// Approved typography. NOTE (GDPR): served from Google Fonts like the approved static site; self-host before launch.
		wp_enqueue_style( 'gadgero-fonts', 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Sora:wght@400;600;700;800&display=swap', array(), null );
		wp_enqueue_style( 'gadgero', GADGERO_URI . '/assets/css/gadgero.css', array( 'gadgero-fonts' ), $v );
		wp_enqueue_style( 'gadgero-shop', GADGERO_URI . '/assets/css/shop.css', array( 'gadgero' ), $v );

		wp_enqueue_script( 'gadgero-site', GADGERO_URI . '/assets/js/site.js', array(), $v, true );
		wp_localize_script(
			'gadgero-site',
			'GADGERO',
			array(
				'shop'      => get_bloginfo( 'name' ),
				'wa'        => gadgero_wa_number(),
				'added'     => __( 'added to cart', 'gadgero' ),
				'copied'    => __( 'Message copied', 'gadgero' ),
				'copyFail'  => __( 'Select and copy the text manually', 'gadgero' ),
				'noModel'   => __( '(model not added)', 'gadgero' ),
			)
		);

		if ( is_front_page() ) {
			// Three.js r147 as in the approved design. NOTE: self-host before launch (GDPR / availability).
			wp_enqueue_script( 'three', 'https://cdn.jsdelivr.net/npm/three@0.147.0/build/three.min.js', array(), null, true );
			wp_enqueue_script( 'three-gltf', 'https://cdn.jsdelivr.net/npm/three@0.147.0/examples/js/loaders/GLTFLoader.js', array( 'three' ), null, true );
			wp_enqueue_script( 'gadgero-hero', GADGERO_URI . '/assets/js/hero.js', array( 'three-gltf', 'gadgero-site' ), $v, true );
			wp_localize_script(
				'gadgero-hero',
				'GADGERO_HERO',
				array(
					'model' => GADGERO_URI . '/assets/model/duo.glb',
					'word'  => gadgero_campaign_word(),
				)
			);
		}

		if ( function_exists( 'is_product' ) && is_product() ) {
			wp_enqueue_script( 'gadgero-product', GADGERO_URI . '/assets/js/product.js', array( 'jquery', 'gadgero-site' ), $v, true );
		}
	}
);

/** Preconnect for the font host used by the approved design. */
add_filter(
	'wp_resource_hints',
	function ( $urls, $type ) {
		if ( 'preconnect' === $type ) {
			$urls[] = array(
				'href' => 'https://fonts.gstatic.com',
				'crossorigin',
			);
		}
		return $urls;
	},
	10,
	2
);

/** Favicons and theme colour from the approved static site. */
add_action(
	'wp_head',
	function () {
		$u = GADGERO_URI . '/assets/icons/';
		if ( ! has_site_icon() ) {
			echo '<link rel="icon" href="' . esc_url( $u . 'favicon.ico' ) . '" sizes="any">' . "\n";
			echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url( $u . 'favicon-32.png' ) . '">' . "\n";
			echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url( $u . 'apple-touch-icon.png' ) . '">' . "\n";
		}
		echo '<link rel="manifest" href="' . esc_url( $u . 'site.webmanifest' ) . '">' . "\n";
		echo '<meta name="theme-color" content="#0a7bff">' . "\n";
		if ( is_front_page() ) {
			echo '<meta property="og:image" content="' . esc_url( $u . 'og-image.png' ) . '">' . "\n";
		}
	},
	2
);

/** Logo: Customizer logo if set, otherwise the bundled approved logo. */
function gadgero_logo_url() {
	$id = get_theme_mod( 'custom_logo' );
	if ( $id ) {
		$src = wp_get_attachment_image_url( $id, 'full' );
		if ( $src ) {
			return $src;
		}
	}
	return GADGERO_URI . '/assets/img/logo.png';
}

/** Fallback header menu (used until a menu is assigned to the "primary" location). */
function gadgero_menu_fallback() {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	echo '<nav class="nav" aria-label="' . esc_attr__( 'Main', 'gadgero' ) . '">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'gadgero' ) . '</a>';
	echo '<a href="' . esc_url( $shop ) . '">' . esc_html__( 'Shop', 'gadgero' ) . '</a>';
	echo '<a href="' . esc_url( home_url( '/#about' ) ) . '">' . esc_html__( 'About', 'gadgero' ) . '</a>';
	echo '<a href="' . esc_url( home_url( '/#sell' ) ) . '">' . esc_html__( 'Sell yours', 'gadgero' ) . '</a>';
	echo '<a href="#contact">' . esc_html__( 'Contact', 'gadgero' ) . '</a>';
	echo '</nav>';
}

/** Menu links without <ul> so the approved .nav styles apply unchanged. */
function gadgero_primary_nav() {
	if ( ! has_nav_menu( 'primary' ) ) {
		gadgero_menu_fallback();
		return;
	}
	$items = wp_get_nav_menu_items( get_nav_menu_locations()['primary'] );
	echo '<nav class="nav" aria-label="' . esc_attr__( 'Main', 'gadgero' ) . '">';
	foreach ( (array) $items as $item ) {
		if ( $item->menu_item_parent ) {
			continue;
		}
		$current = ( untrailingslashit( $item->url ) === untrailingslashit( home_url( add_query_arg( array() ) ) ) ) ? ' aria-current="page"' : '';
		echo '<a href="' . esc_url( $item->url ) . '"' . $current . '>' . esc_html( $item->title ) . '</a>'; // phpcs:ignore
	}
	echo '</nav>';
}

/** Plain list of links for a footer menu location. */
function gadgero_footer_links( $location ) {
	if ( ! has_nav_menu( $location ) ) {
		return;
	}
	foreach ( (array) wp_get_nav_menu_items( get_nav_menu_locations()[ $location ] ) as $item ) {
		echo '<p><a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a></p>';
	}
}

/** Body class for the German routes (lets CSS and QA see which language a page is in). */
add_filter(
	'body_class',
	function ( $c ) {
		$c[] = 'lang-' . substr( determine_locale(), 0, 2 );
		return $c;
	}
);
