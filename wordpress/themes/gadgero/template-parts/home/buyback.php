<?php
/**
 * Buyback (trade-in). Phase 6: tablets added; device type, model, storage and condition are all carried into the
 * WhatsApp message and the copied message. No promises about reply time, same-day payment or in-store handover.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

$types = array(
	'Phone'        => __( 'Phone', 'gadgero' ),
	'Tablet'       => __( 'Tablet', 'gadgero' ),
	'Smartwatch'   => __( 'Smartwatch', 'gadgero' ),
	'Laptop'       => __( 'Laptop', 'gadgero' ),
	'Desktop'      => __( 'Desktop', 'gadgero' ),
	'Headphones'   => __( 'AirPods / headphones', 'gadgero' ),
);
// Model hints per type, used as the placeholder of the model field.
$hints = array(
	'Phone'      => __( 'e.g. iPhone 14 Pro, Galaxy S23', 'gadgero' ),
	'Tablet'     => __( 'e.g. iPad Air, iPad Pro 11-inch, Galaxy Tab S9', 'gadgero' ),
	'Smartwatch' => __( 'e.g. Apple Watch Series 9, 45 mm', 'gadgero' ),
	'Laptop'     => __( 'e.g. MacBook Air 13" M2', 'gadgero' ),
	'Desktop'    => __( 'e.g. iMac 24" M3, Mac mini M2', 'gadgero' ),
	'Headphones' => __( 'e.g. AirPods Pro', 'gadgero' ),
);
$storage = array( '', '32 GB', '64 GB', '128 GB', '256 GB', '512 GB', '1 TB', '2 TB' );
$conds   = array( __( 'Like new', 'gadgero' ), __( 'Good, minor marks', 'gadgero' ), __( 'Cracked or damaged', 'gadgero' ) );
?>
<section class="sell" id="sell">
	<div class="wrap sellgrid">
		<div>
			<p class="eyebrow"><?php esc_html_e( 'Trade in', 'gadgero' ); ?></p>
			<h3><?php esc_html_e( 'Sell your phone, tablet, smartwatch or laptop.', 'gadgero' ); ?></h3>
			<p class="lead"><?php esc_html_e( 'Tell us what you have and we reply on WhatsApp with a price offer.', 'gadgero' ); ?></p>
			<form class="form" id="sellForm" data-hints="<?php echo esc_attr( wp_json_encode( $hints ) ); ?>">
				<label><?php esc_html_e( 'Device type', 'gadgero' ); ?>
					<select id="sDev" name="device">
						<?php foreach ( $types as $v => $l ) : ?><option value="<?php echo esc_attr( $v ); ?>"><?php echo esc_html( $l ); ?></option><?php endforeach; ?>
					</select>
				</label>
				<label><?php esc_html_e( 'Storage', 'gadgero' ); ?>
					<select id="sStore" name="storage">
						<?php foreach ( $storage as $s ) : ?><option value="<?php echo esc_attr( $s ); ?>"><?php echo esc_html( '' === $s ? __( 'Not sure / not applicable', 'gadgero' ) : $s ); ?></option><?php endforeach; ?>
					</select>
				</label>
				<label class="full"><?php esc_html_e( 'Model', 'gadgero' ); ?>
					<input id="sModel" name="model" type="text" autocomplete="off" placeholder="<?php echo esc_attr( $hints['Phone'] ); ?>">
				</label>
				<label class="full"><?php esc_html_e( 'Condition', 'gadgero' ); ?>
					<select id="sCond" name="condition">
						<?php foreach ( $conds as $c ) : ?><option value="<?php echo esc_attr( $c ); ?>"><?php echo esc_html( $c ); ?></option><?php endforeach; ?>
					</select>
				</label>
				<p class="full sell-preview" aria-live="polite"><span><?php esc_html_e( 'Your message', 'gadgero' ); ?></span><output id="sPreview"></output></p>
				<div class="btns">
					<a class="solid" id="sellWa" href="<?php echo esc_url( gadgero_wa_link() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get a quote on WhatsApp', 'gadgero' ); ?></a>
					<button class="ghost" type="button" id="sellCopy"><?php esc_html_e( 'Copy message', 'gadgero' ); ?></button>
				</div>
				<?php echo gadgero_wa_number() ? '' : gadgero_flag( 'WhatsApp number (Customizer)' ); // phpcs:ignore ?>
				<?php echo gadgero_flag( 'which devices / tablet brands are accepted for buyback' ); // phpcs:ignore ?>
			</form>
		</div>
		<div class="ringbox" id="sellArt" aria-hidden="true"><?php echo gadgero_device_svg( 'phone', '#35c4ff', '#0a3fb8', '#b7c3d9' ); // phpcs:ignore ?></div>
	</div>
</section>
