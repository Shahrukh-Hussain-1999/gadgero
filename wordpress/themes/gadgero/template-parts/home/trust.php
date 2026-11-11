<?php
/**
 * Trust strip. Only statements the shop actually delivers through this build are listed.
 * Removed until confirmed: "Easy EMI" (no financing provider) and "Shop warranty" (no approved warranty terms).
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="trust">
	<div class="wrap">
		<ul>
			<li><b><?php esc_html_e( 'Exact configuration listed', 'gadgero' ); ?></b><span><?php esc_html_e( 'Model, storage, colour and condition are shown before you buy.', 'gadgero' ); ?></span></li>
			<li><b><?php esc_html_e( 'Battery health on refurbished', 'gadgero' ); ?></b><span><?php esc_html_e( 'Refurbished listings show the measured battery health.', 'gadgero' ); ?></span></li>
			<li><b><?php esc_html_e( 'Guest checkout', 'gadgero' ); ?></b><span><?php esc_html_e( 'Order without creating an account.', 'gadgero' ); ?></span></li>
			<li><b><?php esc_html_e( 'Advice on WhatsApp', 'gadgero' ); ?></b><span><?php esc_html_e( 'Ask about stock, models or a buyback quote.', 'gadgero' ); ?></span></li>
		</ul>
	</div>
</div>
