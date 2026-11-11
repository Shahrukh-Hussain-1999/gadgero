<?php
/**
 * Footer: approved navy footer. Contact data comes from the Customizer and is never hard-coded.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

$address = gadgero_lines( 'gadgero_address' );
$hours   = gadgero_lines( 'gadgero_hours' );
$phone   = gadgero_opt( 'gadgero_phone' );
$email   = gadgero_opt( 'gadgero_email' );
$wa      = gadgero_wa_display();
$company = gadgero_opt( 'gadgero_company_name' );
?>
<footer id="contact">
	<div class="wrap fgrid">
		<div>
			<img class="flogo" src="<?php echo esc_url( gadgero_logo_url() ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="170" height="132" loading="lazy">
			<p><?php esc_html_e( 'New and refurbished phones, tablets, laptops, desktops, smartwatches and accessories.', 'gadgero' ); ?></p>
		</div>
		<div>
			<h5><?php esc_html_e( 'Visit', 'gadgero' ); ?></h5>
			<?php
			foreach ( $address as $l ) {
				echo '<p>' . esc_html( $l ) . '</p>';
			}
			echo $address ? '' : gadgero_flag( 'business address' ); // phpcs:ignore
			if ( $hours ) {
				echo '<p style="margin-top:10px">' . esc_html( array_shift( $hours ) ) . '</p>';
				foreach ( $hours as $l ) {
					echo '<p>' . esc_html( $l ) . '</p>';
				}
			} else {
				echo gadgero_flag( 'opening hours' ); // phpcs:ignore
			}
			?>
		</div>
		<div>
			<h5><?php esc_html_e( 'Contact', 'gadgero' ); ?></h5>
			<?php if ( $wa ) : ?>
				<p class="sel"><a href="<?php echo esc_url( gadgero_wa_link() ); ?>" target="_blank" rel="noopener">WhatsApp <?php echo esc_html( $wa ); ?></a></p>
			<?php else : ?>
				<?php echo gadgero_flag( 'WhatsApp number' ); // phpcs:ignore ?>
			<?php endif; ?>
			<?php if ( $phone ) : ?>
				<p><a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p>
			<?php else : ?>
				<?php echo gadgero_flag( 'telephone' ); // phpcs:ignore ?>
			<?php endif; ?>
			<?php if ( $email ) : ?>
				<p><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></p>
			<?php else : ?>
				<?php echo gadgero_flag( 'support e-mail' ); // phpcs:ignore ?>
			<?php endif; ?>
			<p><?php esc_html_e( 'WhatsApp: advice, stock questions and buyback quotes.', 'gadgero' ); ?></p>
		</div>
		<div>
			<h5><?php esc_html_e( 'Service', 'gadgero' ); ?></h5>
			<?php gadgero_footer_links( 'service' ); ?>
		</div>
	</div>
	<div class="wrap fbar">
		<p class="rights">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <b><?php echo esc_html( $company ? $company : get_bloginfo( 'name' ) ); ?></b>. <?php esc_html_e( 'All rights reserved.', 'gadgero' ); ?></p>
		<nav class="legal" aria-label="<?php esc_attr_e( 'Legal', 'gadgero' ); ?>"><?php gadgero_footer_links( 'legal' ); ?></nav>
		<p class="fine"><?php esc_html_e( 'Apple, iPhone, iPad, Mac, Apple Watch and AirPods are trademarks of Apple Inc. Samsung and Galaxy are trademarks of Samsung Electronics. Gadgero is an independent reseller and is not affiliated with these companies.', 'gadgero' ); ?></p>
	</div>
</footer>

<?php if ( class_exists( 'WooCommerce' ) ) : ?>
<div class="veil" id="veil"></div>
<aside class="drawer" id="drawer" aria-label="<?php esc_attr_e( 'Your cart', 'gadgero' ); ?>" aria-hidden="true" inert>
	<div class="dhead"><h4><?php esc_html_e( 'Your cart', 'gadgero' ); ?></h4><button class="x" id="closeCart" type="button" aria-label="<?php esc_attr_e( 'Close cart', 'gadgero' ); ?>">&times;</button></div>
	<?php gadgero_drawer_inner(); ?>
</aside>
<?php endif; ?>
<div class="toast" id="toast" role="status"></div>
<?php wp_footer(); ?>
</body>
</html>
