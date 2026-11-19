<?php
/**
 * Ankauf (trade-in). Brief item 09: a model is required before a quote request is generated; the request goes to
 * the Gadgero WhatsApp business number, or by e-mail while no number is configured — never to a WhatsApp link
 * without a recipient. Device type, model, storage and condition are carried into the message and the copy.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

$types = array(
	'Smartphone'  => 'z. B. iPhone 14 Pro, Galaxy S23',
	'Tablet'      => 'z. B. iPad Air, iPad Pro 11 Zoll, Galaxy Tab S9',
	'Smartwatch'  => 'z. B. Apple Watch Series 9, 45 mm',
	'Laptop'      => 'z. B. MacBook Air 13" M2',
	'Desktop'     => 'z. B. iMac 24" M3, Mac mini M2',
	'Kopfhörer'   => 'z. B. AirPods Pro (2. Generation)',
);
$storage = array( '', '32 GB', '64 GB', '128 GB', '256 GB', '512 GB', '1 TB', '2 TB' );
$conds   = array( 'Wie neu', 'Gut, leichte Gebrauchsspuren', 'Displaybruch oder Defekt' );
$channel = gadgero_wa_number() ? 'wa' : ( gadgero_opt( 'gadgero_email' ) ? 'mail' : '' );
?>
<section class="sell" id="sell">
	<div class="wrap sellgrid">
		<div>
			<p class="eyebrow">Ankauf</p>
			<h2 class="sell-h">Verkaufen Sie Ihr Smartphone, Tablet, Ihre Smartwatch oder Ihren Laptop.</h2>
			<p class="lead">Beschreiben Sie Ihr Gerät, wir melden uns mit einem Preisangebot.</p>
			<form class="form" id="sellForm" novalidate data-hints="<?php echo esc_attr( wp_json_encode( $types ) ); ?>" data-channel="<?php echo esc_attr( $channel ); ?>">
				<label>Geräteart
					<select id="sDev" name="device">
						<?php foreach ( $types as $v => $hint ) : ?><option value="<?php echo esc_attr( $v ); ?>"><?php echo esc_html( $v ); ?></option><?php endforeach; ?>
					</select>
				</label>
				<label>Speicher
					<select id="sStore" name="storage">
						<?php foreach ( $storage as $s ) : ?><option value="<?php echo esc_attr( $s ); ?>"><?php echo esc_html( '' === $s ? 'Unbekannt / nicht zutreffend' : $s ); ?></option><?php endforeach; ?>
					</select>
				</label>
				<label class="full"><span>Modell <span class="req" aria-hidden="true">*</span></span>
					<input id="sModel" name="model" type="text" autocomplete="off" required aria-required="true" aria-describedby="sModelErr" placeholder="<?php echo esc_attr( reset( $types ) ); ?>">
					<span class="field-err" id="sModelErr" hidden>Bitte geben Sie das Modell an.</span>
				</label>
				<label class="full">Zustand
					<select id="sCond" name="condition">
						<?php foreach ( $conds as $c ) : ?><option value="<?php echo esc_attr( $c ); ?>"><?php echo esc_html( $c ); ?></option><?php endforeach; ?>
					</select>
				</label>
				<p class="full sell-preview" aria-live="polite"><span>Ihre Nachricht</span><output id="sPreview"></output></p>
				<div class="btns">
					<?php if ( $channel ) : ?>
						<a class="solid" id="sellSend" href="#sell" <?php echo 'wa' === $channel ? 'target="_blank" rel="noopener"' : ''; ?>><?php echo 'wa' === $channel ? 'Angebot per WhatsApp anfragen' : 'Angebot per E-Mail anfragen'; ?></a>
					<?php endif; ?>
					<button class="ghost" type="button" id="sellCopy">Nachricht kopieren</button>
				</div>
				<?php echo $channel ? '' : gadgero_flag( 'WhatsApp business number or support e-mail – no quote channel yet' ); // phpcs:ignore ?>
				<?php echo gadgero_flag( 'accepted devices / tablet brands for buyback' ); // phpcs:ignore ?>
			</form>
		</div>
		<div class="ringbox" id="sellArt" aria-hidden="true"><?php echo gadgero_device_svg( 'phone', '#5b6270', '#1d2026', '#9aa1ad' ); // phpcs:ignore ?></div>
	</div>
</section>
