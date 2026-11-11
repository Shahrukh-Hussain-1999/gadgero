<?php
/**
 * Header: approved fixed white logo bar with nav and cart pill.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#main"><?php esc_html_e( 'Skip to content', 'gadgero' ); ?></a>
<header class="top" id="top">
	<div class="wrap">
		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( '%s home', 'gadgero' ), get_bloginfo( 'name' ) ) ); ?>"><img src="<?php echo esc_url( gadgero_logo_url() ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="258" height="200"></a>
		<?php gadgero_primary_nav(); ?>
		<div class="tools">
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<button class="cartbtn" id="openCart" type="button" aria-controls="drawer" aria-expanded="false"><?php echo esc_html( 0 === strpos( determine_locale(), 'de' ) ? 'Warenkorb' : __( 'Cart', 'gadgero' ) ); ?> <?php echo gadgero_cart_count_html(); // phpcs:ignore ?></button>
			<?php endif; ?>
			<button class="menubtn" id="menuBtn" type="button" aria-controls="mnav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Menu', 'gadgero' ); ?>"><?php echo gadgero_icon( 'menu' ); // phpcs:ignore ?></button>
		</div>
	</div>
	<div class="mnav" id="mnav" hidden><?php gadgero_primary_nav(); ?></div>
</header>
