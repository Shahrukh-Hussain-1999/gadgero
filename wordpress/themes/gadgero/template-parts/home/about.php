<?php
/**
 * About Gadgero. Copy supplied by the client (Phase 5). The previous Copenhagen story and the unverified statistics
 * (10 yrs, 25,000+ devices, 40 checks, 4.9/5) are removed. Add ratings back only with a linked source and review count.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="about" id="about">
	<div class="wrap">
		<div class="about-top">
			<div class="about-copy">
				<p class="eyebrow rv"><?php esc_html_e( 'About Gadgero', 'gadgero' ); ?></p>
				<h3 class="sec rv" style="--d:.08s"><?php esc_html_e( 'Premium tech,', 'gadgero' ); ?><br><em><?php esc_html_e( 'honestly priced.', 'gadgero' ); ?></em></h3>
				<p class="rv" style="--d:.16s"><?php esc_html_e( 'At Gadgero, you can shop for new and refurbished phones, tablets, laptops, desktops, smartwatches and accessories. Each listing shows the model, configuration and condition so you can see exactly what you’re buying.', 'gadgero' ); ?></p>
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

		<div class="values">
			<article class="val rv"><div class="ico"><?php echo gadgero_icon( 'tag' ); // phpcs:ignore ?></div><h4><?php esc_html_e( 'Clear listings', 'gadgero' ); ?></h4><p><?php esc_html_e( 'Model, storage, colour, connectivity and condition are listed for every device, and the same configuration follows your order through cart, checkout and confirmation.', 'gadgero' ); ?></p></article>
			<article class="val rv" style="--d:.1s"><div class="ico"><?php echo gadgero_icon( 'box' ); // phpcs:ignore ?></div><h4><?php esc_html_e( 'New or refurbished', 'gadgero' ); ?></h4><p><?php esc_html_e( 'New devices and refurbished devices are listed separately, so you always know which one you are looking at.', 'gadgero' ); ?></p></article>
			<article class="val rv" style="--d:.2s"><div class="ico"><?php echo gadgero_icon( 'support' ); // phpcs:ignore ?></div><h4><?php esc_html_e( 'Advice on WhatsApp', 'gadgero' ); ?></h4><p><?php esc_html_e( 'Not sure which model fits? Ask us about stock, specifications or a buyback quote on WhatsApp.', 'gadgero' ); ?></p></article>
		</div>

		<?php get_template_part( 'template-parts/home/process' ); ?>
	</div>
</section>
