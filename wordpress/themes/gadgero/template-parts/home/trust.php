<?php
/**
 * Four service points (brief item 02) in one grid: icon tops, heading baselines and description starts line up
 * (CSS subgrid); two-line titles allowed; four columns on desktop, two on phones.
 * Only statements this shop actually delivers. "Easy EMI" and the shop-warranty claim stay removed.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

$items = array(
	array( 'tag', 'Alle Gerätedetails im Angebot', 'Modell, Speicher, Farbe und Zustand stehen in jedem Angebot und bleiben bis zur Bestellbestätigung gleich.' ),
	array( 'battery', 'Akkuzustand bei Refurbished', 'Für jedes Refurbished-Gerät geben wir die gemessene Akkukapazität an.' ),
	array( 'user', 'Bestellen als Gast', 'Sie bestellen ohne Kundenkonto. Ihre Daten nutzen wir nur für die Bestellung.' ),
	array( 'support', 'Persönliche Beratung', 'Fragen zu Modell, Lagerbestand oder Ankauf? Wir helfen vor dem Kauf weiter.' ),
);
?>
<section class="service" aria-label="Service">
	<div class="wrap">
		<ul class="service-grid">
			<?php foreach ( $items as $i => $it ) : ?>
				<li class="svc-item">
					<span class="svc-ico"><?php echo gadgero_icon( $it[0] ); // phpcs:ignore ?></span>
					<h3><?php echo esc_html( $it[1] ); ?></h3>
					<p><?php echo esc_html( $it[2] ); ?>
						<?php if ( 3 === $i ) : ?>
							<a class="svc-link" href="<?php echo esc_url( gadgero_advice_link( sprintf( 'Hallo %s, ich habe eine Frage.', get_bloginfo( 'name' ) ) ) ); ?>"><?php echo esc_html( gadgero_advice_label() ); ?> <span aria-hidden="true">&rarr;</span></a>
							<?php echo gadgero_wa_number() ? '' : gadgero_flag( 'WhatsApp business number – link falls back to e-mail / contact' ); // phpcs:ignore ?>
						<?php endif; ?>
					</p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
