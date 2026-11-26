<?php
/**
 * Hero (brief 15.11.2026, item 01): the existing 3D Duo with its rotation / fold sequence on its own stage.
 * Desktop: headline, text, price and action on the left, animation on the right. Mobile: title, animation, action.
 * One headline only (no oversized duplicate lettering). Reduced motion: a single static frame (see hero.js).
 *
 * Name, "ab" price and link come from the WooCommerce campaign product and are only shown when that product is
 * published, purchasable and in stock.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

$product = gadgero_campaign_product();
$any     = $product ? $product : gadgero_campaign_product( false );
$name    = $any ? $any->get_name() : '';
$from    = $product ? gadgero_campaign_from_price( $product ) : null;
$tags    = array_filter( array_map( 'trim', preg_split( '/\R/', gadgero_campaign_text( 'gadgero_campaign_tags' ) ) ) );
?>
<section class="story" id="story" aria-label="<?php echo esc_attr( $name ? $name : 'Gadgero' ); ?>">
	<div class="stage" id="stage">
		<div class="rail" aria-hidden="true"><i id="prog"></i></div>
		<div class="aurora" aria-hidden="true"><i></i><i></i><i></i></div>
		<div class="ring" id="ring" aria-hidden="true"></div>
		<canvas id="gl" aria-hidden="true"></canvas>
		<div class="fallback" id="fallback" aria-hidden="true"><?php echo gadgero_device_svg( 'phone', '#5b6270', '#1d2026', '#9aa1ad' ); // phpcs:ignore ?></div>

		<div class="hero-copy" id="heroCopy">
			<div class="hero-head">
				<p class="hero-eyebrow"><?php echo esc_html( gadgero_condition_label( $any ? gadgero_condition( $any ) : '' ) ); ?></p>
				<?php if ( $name ) : ?><h1 class="hero-title"><?php echo esc_html( $name ); ?></h1><?php endif; ?>
				<div class="hero-slides">
					<div class="hero-slide is-on" data-from="0" data-to=".34">
						<p><?php echo esc_html( gadgero_campaign_text( 'gadgero_campaign_intro' ) ); ?></p>
					</div>
					<div class="hero-slide" data-from=".34" data-to="1.01">
						<p class="hero-sub"><?php echo esc_html( gadgero_campaign_text( 'gadgero_campaign_head2' ) ); ?></p>
						<p><?php echo esc_html( gadgero_campaign_text( 'gadgero_campaign_text2' ) ); ?></p>
						<?php if ( $tags ) : ?><ul class="tags"><?php foreach ( $tags as $t ) { echo '<li>' . esc_html( $t ) . '</li>'; } ?></ul><?php endif; ?>
					</div>
				</div>
			</div>
			<div class="hero-buy">
				<?php if ( $any && 'publish' === $any->get_status() ) : ?>
					<?php if ( null !== $from ) : ?>
						<p class="from">ab <?php echo wp_kses_post( wc_price( $from ) ); ?></p>
						<p class="taxnote hero-tax">inkl. MwSt., zzgl. Versand</p>
					<?php else : ?>
						<p class="from on-request">Preis auf Anfrage</p>
					<?php endif; ?>
					<a class="explore" href="<?php echo esc_url( $any->get_permalink() ); ?>"><?php echo esc_html( sprintf( '%s ansehen', $name ) ); ?> <span aria-hidden="true">&rarr;</span></a>
				<?php else : ?>
					<?php echo gadgero_flag( 'campaign product missing or unpublished' ); // phpcs:ignore ?>
				<?php endif; ?>
			</div>
		</div>
		<div class="hint" id="hint" aria-hidden="true">Scrollen<i></i></div>
	</div>
</section>
