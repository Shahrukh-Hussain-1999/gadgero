<?php
/**
 * Two shopping routes: new and refurbished. Each opens the WooCommerce shop filtered by the pa_condition attribute
 * (native layered-nav query var), so the two collections never mix.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_page_permalink' ) ) {
	return;
}
$shop  = wc_get_page_permalink( 'shop' );
$count = function ( $slug ) {
	$t = get_term_by( 'slug', $slug, 'pa_condition' );
	return $t ? (int) $t->count : 0;
};
?>
<section class="routes" id="routes" aria-labelledby="routes-h">
	<div class="wrap">
		<p class="eyebrow"><?php esc_html_e( 'Two ways to shop', 'gadgero' ); ?></p>
		<h3 class="sec" id="routes-h"><?php esc_html_e( 'New or refurbished?', 'gadgero' ); ?></h3>
		<div class="route-grid">
			<a class="route route-new" href="<?php echo esc_url( add_query_arg( 'filter_condition', 'new', $shop ) ); ?>">
				<span class="badge new"><?php esc_html_e( 'New', 'gadgero' ); ?></span>
				<span class="route-art" aria-hidden="true"><?php echo gadgero_device_svg( 'phone', '#7aa2ff', '#1a2a66', '#c9d3e6' ); // phpcs:ignore ?></span>
				<span class="route-body">
					<b><?php esc_html_e( 'Shop new devices', 'gadgero' ); ?></b>
					<span><?php esc_html_e( 'Brand-new devices. Listings marked "factory-sealed" ship in their original factory-sealed packaging.', 'gadgero' ); ?></span>
					<em><?php echo esc_html( sprintf( _n( '%d device', '%d devices', $count( 'new' ), 'gadgero' ), $count( 'new' ) ) ); ?> <span aria-hidden="true">&rarr;</span></em>
				</span>
			</a>
			<a class="route route-ref" href="<?php echo esc_url( add_query_arg( 'filter_condition', 'refurbished', $shop ) ); ?>">
				<span class="badge"><?php esc_html_e( 'Refurbished', 'gadgero' ); ?></span>
				<span class="route-art" aria-hidden="true"><?php echo gadgero_device_svg( 'laptop', '#8fd0c0', '#16433f', '#c9ccd1' ); // phpcs:ignore ?></span>
				<span class="route-body">
					<b><?php esc_html_e( 'Shop refurbished devices', 'gadgero' ); ?></b>
					<span><?php esc_html_e( 'Pre-owned devices that are inspected, cleaned, data-wiped, battery-tested and set up with fresh software.', 'gadgero' ); ?></span>
					<em><?php echo esc_html( sprintf( _n( '%d device', '%d devices', $count( 'refurbished' ), 'gadgero' ), $count( 'refurbished' ) ) ); ?> <span aria-hidden="true">&rarr;</span></em>
				</span>
			</a>
		</div>
	</div>
</section>
