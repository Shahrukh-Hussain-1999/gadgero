<?php
/**
 * Footer. Contact data comes from the Customizer only (company is still being formed – fields stay editable).
 * Brief item 09: no empty "Besuch" column; a contact e-mail slot is shown once the address is supplied.
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
$visit   = $address || $hours;
?>
<footer id="contact">
	<div class="wrap fgrid<?php echo $visit ? '' : ' no-visit'; ?>">
		<div>
			<img class="flogo" src="<?php echo esc_url( gadgero_logo_url() ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="170" height="132" loading="lazy">
			<p>Neue und refurbished Smartphones, Tablets, Laptops, Desktops, Smartwatches und Zubehör.</p>
		</div>
		<?php if ( $visit ) : ?>
			<div>
				<h2 class="fh">Besuch</h2>
				<?php
				foreach ( $address as $l ) {
					echo '<p>' . esc_html( $l ) . '</p>';
				}
				if ( $hours ) {
					echo '<p style="margin-top:10px">' . esc_html( array_shift( $hours ) ) . '</p>';
					foreach ( $hours as $l ) {
						echo '<p>' . esc_html( $l ) . '</p>';
					}
				}
				?>
			</div>
		<?php endif; ?>
		<div>
			<h2 class="fh">Kontakt</h2>
			<?php if ( $email ) : ?>
				<p><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></p>
			<?php else : ?>
				<?php echo gadgero_flag( 'contact e-mail' ); // phpcs:ignore ?>
			<?php endif; ?>
			<?php if ( $wa ) : ?>
				<p><a href="<?php echo esc_url( gadgero_wa_link() ); ?>" target="_blank" rel="noopener">WhatsApp <?php echo esc_html( $wa ); ?></a></p>
			<?php else : ?>
				<?php echo gadgero_flag( 'WhatsApp business number' ); // phpcs:ignore ?>
			<?php endif; ?>
			<?php if ( $phone ) : ?>
				<p><a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p>
			<?php endif; ?>
			<p>Beratung, Fragen zum Lagerbestand und Ankaufsangebote.</p>
			<?php echo $visit ? '' : gadgero_flag( 'address / opening hours – "Besuch" column hidden until supplied' ); // phpcs:ignore ?>
		</div>
		<div>
			<h2 class="fh">Service</h2>
			<?php gadgero_footer_links( 'service' ); ?>
		</div>
	</div>
	<div class="wrap fbar">
		<p class="rights">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <b><?php echo esc_html( $company ? $company : get_bloginfo( 'name' ) ); ?></b>. Alle Rechte vorbehalten.</p>
		<nav class="legal" aria-label="Rechtliches"><?php gadgero_footer_links( 'legal' ); ?></nav>
		<p class="fine">Apple, iPhone, iPad, Mac, Apple Watch und AirPods sind Marken der Apple Inc. Samsung und Galaxy sind Marken der Samsung Electronics Co., Ltd. Gadgero ist ein unabhängiger Händler und steht in keiner Verbindung zu diesen Unternehmen.</p>
	</div>
</footer>

<?php if ( class_exists( 'WooCommerce' ) ) : ?>
<div class="veil" id="veil"></div>
<aside class="drawer" id="drawer" aria-label="Ihr Warenkorb" aria-hidden="true" inert>
	<div class="dhead"><h2 class="dh">Ihr Warenkorb</h2><button class="x" id="closeCart" type="button" aria-label="Warenkorb schließen">&times;</button></div>
	<?php gadgero_drawer_inner(); ?>
</aside>
<?php endif; ?>
<div class="toast" id="toast" role="status"></div>
<?php
/*
 * Floating WhatsApp button (bottom right). Only rendered when the business number is configured
 * (Customizer → Gadgero: company & contact) — never a WhatsApp link without a recipient.
 * On product pages the prefilled message names the product.
 */
if ( gadgero_wa_number() ) :
	$wa_text = sprintf( 'Hallo %s, ich habe eine Frage.', get_bloginfo( 'name' ) );
	if ( function_exists( 'is_product' ) && is_product() ) {
		$wa_text = sprintf( 'Hallo %1$s, ich habe eine Frage zu: %2$s', get_bloginfo( 'name' ), get_the_title( get_queried_object_id() ) );
	}
	?>
	<a class="wa-float" href="<?php echo esc_url( gadgero_wa_link( $wa_text ) ); ?>" target="_blank" rel="noopener" aria-label="Fragen per WhatsApp stellen (öffnet WhatsApp)">
		<span class="wa-pulse" aria-hidden="true"></span>
		<svg viewBox="0 0 32 32" aria-hidden="true" focusable="false"><path fill="currentColor" d="M16.04 3C8.86 3 3.03 8.8 3.03 15.95c0 2.29.6 4.52 1.75 6.49L3 29l6.73-1.75a13.07 13.07 0 0 0 6.3 1.6h.01c7.17 0 13-5.8 13-12.95C29.04 8.8 23.21 3 16.04 3Zm0 23.66h-.01a10.8 10.8 0 0 1-5.5-1.5l-.4-.23-4 1.04 1.07-3.88-.26-.4a10.7 10.7 0 0 1-1.65-5.74c0-5.94 4.85-10.77 10.8-10.77 2.88 0 5.6 1.12 7.63 3.16a10.68 10.68 0 0 1 3.16 7.62c0 5.94-4.85 10.7-10.84 10.7Zm5.92-8.03c-.32-.16-1.92-.94-2.22-1.05-.3-.11-.51-.16-.73.16-.21.32-.84 1.05-1.03 1.27-.19.21-.38.24-.7.08-.32-.16-1.37-.5-2.6-1.6-.96-.85-1.61-1.9-1.8-2.22-.19-.32-.02-.5.14-.65.15-.15.32-.38.48-.56.16-.19.21-.32.32-.54.11-.21.05-.4-.03-.56-.08-.16-.73-1.75-1-2.4-.26-.62-.53-.54-.73-.55h-.62c-.21 0-.56.08-.86.4-.3.32-1.13 1.1-1.13 2.68 0 1.58 1.16 3.11 1.32 3.33.16.21 2.28 3.47 5.52 4.87.77.33 1.37.53 1.84.68.77.25 1.48.21 2.03.13.62-.09 1.92-.78 2.19-1.54.27-.75.27-1.4.19-1.54-.08-.13-.29-.21-.61-.37Z"/></svg>
		<span class="wa-label">Fragen? WhatsApp</span>
	</a>
<?php elseif ( current_user_can( 'manage_options' ) ) : ?>
	<div class="wa-float is-missing"><?php echo gadgero_flag( 'WhatsApp number – floating button hidden until set' ); // phpcs:ignore ?></div>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
