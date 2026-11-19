<?php
/**
 * Refurbishment process — refurbished journey only (refurbished shop filter and refurbished product pages).
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

$steps = array(
	array( 'Prüfung', 'Jedes Gerät wird geprüft, bevor es angeboten wird.' ),
	array( 'Reinigung & Datenlöschung', 'Das Gerät wird gereinigt, alle Daten des Vorbesitzers werden gelöscht.' ),
	array( 'Akkutest', 'Die Akkukapazität wird gemessen und im Angebot angegeben.' ),
	array( 'Software-Installation', 'Ein frisches Betriebssystem wird installiert und ist bereit zur Einrichtung.' ),
);
?>
<div class="refurb-process" id="refurbished-process">
	<div class="proc-head">
		<div>
			<p class="eyebrow">Nur Refurbished-Geräte</p>
			<h2>Von unserer Werkbank zu Ihnen.</h2>
		</div>
		<p>Jedes Refurbished-Gerät durchläuft diese Schritte, bevor es angeboten wird. Neugeräte verkaufen wir so, wie sie vom Hersteller geliefert werden.</p>
	</div>
	<ol class="proc" id="proc">
		<?php foreach ( $steps as $i => $s ) : ?>
			<li class="step-p"><i><?php echo (int) $i + 1; ?></i><h3><?php echo esc_html( $s[0] ); ?></h3><p><?php echo esc_html( $s[1] ); ?></p></li>
		<?php endforeach; ?>
	</ol>
</div>
