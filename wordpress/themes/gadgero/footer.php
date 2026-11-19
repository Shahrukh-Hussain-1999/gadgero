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
<?php wp_footer(); ?>
</body>
</html>
