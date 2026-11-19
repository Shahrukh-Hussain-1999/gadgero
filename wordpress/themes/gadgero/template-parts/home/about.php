<?php
/**
 * Über Gadgero. Client copy (brief Phase 5, now in German). The inspection process moved to the refurbished
 * journey (brief item 03). No statistics or ratings without a linked source.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="about" id="about">
	<div class="wrap">
		<div class="about-top">
			<div class="about-copy">
				<p class="eyebrow rv">Über Gadgero</p>
				<h2 class="sec rv" style="--d:.08s">Premium-Technik,<br><em>fair bepreist.</em></h2>
				<p class="rv" style="--d:.16s">Bei Gadgero kaufen Sie neue und refurbished Smartphones, Tablets, Laptops, Desktops, Smartwatches und Zubehör. Jedes Angebot nennt Modell, Konfiguration und Zustand, damit Sie genau wissen, was Sie kaufen.</p>
			</div>
			<div class="orbit rv" style="--d:.2s" aria-hidden="true">
				<div class="orb o1">
					<div class="arm" style="--a:0deg"><div class="node"><?php echo gadgero_icon( 'phone' ); // phpcs:ignore ?></div></div>
					<div class="arm" style="--a:120deg"><div class="node"><?php echo gadgero_icon( 'laptop' ); // phpcs:ignore ?></div></div>
					<div class="arm" style="--a:240deg"><div class="node"><?php echo gadgero_icon( 'tablet' ); // phpcs:ignore ?></div></div>
				</div>
				<div class="orb o2">
					<div class="arm" style="--a:60deg"><div class="node"><?php echo gadgero_icon( 'watch' ); // phpcs:ignore ?></div></div>
					<div class="arm" style="--a:240deg"><div class="node"><?php echo gadgero_icon( 'buds' ); // phpcs:ignore ?></div></div>
				</div>
				<div class="core"><img src="<?php echo esc_url( gadgero_logo_url() ); ?>" alt="" width="258" height="200" loading="lazy"></div>
			</div>
		</div>
	</div>
</section>
