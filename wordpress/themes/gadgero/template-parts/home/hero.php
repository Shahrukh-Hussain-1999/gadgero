<?php
/**
 * Hero: approved scroll story with the 3D phone. Product name, starting price and CTA come from the WooCommerce
 * campaign product (Customizer → Gadgero: homepage campaign). The CTA links to that exact product page.
 * Price and CTA are only rendered when the product is published, purchasable and in stock.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

$product = gadgero_campaign_product();
$any     = $product ? $product : gadgero_campaign_product( false );
$name    = $any ? $any->get_name() : '';
$from    = $product ? gadgero_campaign_from_price( $product ) : null;
$url     = $product ? $product->get_permalink() : '';
/* translators: %s product name */
$cta     = $name ? sprintf( __( 'Shop %s', 'gadgero' ), $name ) : '';
$tags    = array_filter( array_map( 'trim', preg_split( '/\R/', gadgero_campaign_text( 'gadgero_campaign_tags' ) ) ) );

$price_block = function () use ( $product, $from, $url, $cta ) {
	if ( ! $product || null === $from ) {
		echo gadgero_flag( 'campaign product is missing, unpublished, not purchasable or out of stock – price and button hidden' ); // phpcs:ignore
		return;
	}
	echo '<span class="from">' . esc_html__( 'From', 'gadgero' ) . ' ' . wp_kses_post( wc_price( $from ) ) . '</span>';
	echo '<span class="taxnote hero-tax">' . esc_html__( 'incl. VAT, plus shipping', 'gadgero' ) . '</span>';
	echo '<a class="explore" href="' . esc_url( $url ) . '">' . esc_html( $cta ) . ' <span aria-hidden="true">&rarr;</span></a>';
};
?>
<div class="story" id="story" aria-label="<?php echo esc_attr( $name ? $name . ', ' . __( 'scroll to explore', 'gadgero' ) : __( 'Featured device', 'gadgero' ) ); ?>">
	<div class="stage" id="stage">
		<div class="counter" aria-hidden="true"><span id="cur">01</span><small>/ 02</small></div>
		<div class="rail" aria-hidden="true"><i id="prog"></i></div>
		<div class="aurora" aria-hidden="true"><i></i><i></i><i></i></div>
		<div class="word" aria-hidden="true" id="word"></div>
		<div class="ring" id="ring" aria-hidden="true"></div>
		<canvas id="gl" aria-hidden="true"></canvas>
		<div class="fallback" id="fallback" aria-hidden="true"><?php echo gadgero_device_svg( 'phone', '#35c4ff', '#0a3fb8', '#b7c3d9' ); // phpcs:ignore ?></div>
		<div class="steps" id="steps">
			<div class="step" data-from=".34" data-to="1.01">
				<div class="num" style="--k:0">02</div>
				<?php if ( $name ) : ?><p class="step-label" style="--k:1"><?php echo esc_html( $name ); ?></p><?php endif; ?>
				<h2 style="--k:2"><?php echo esc_html( gadgero_campaign_text( 'gadgero_campaign_head2' ) ); ?></h2>
				<p style="--k:3"><?php echo esc_html( gadgero_campaign_text( 'gadgero_campaign_text2' ) ); ?></p>
				<?php if ( $tags ) : ?>
					<ul class="tags" style="--k:4"><?php foreach ( $tags as $t ) { echo '<li>' . esc_html( $t ) . '</li>'; } ?></ul>
				<?php endif; ?>
				<div class="step-buy" style="--k:5"><?php $price_block(); ?></div>
			</div>
		</div>
		<div class="intro" id="intro">
			<div class="wrap">
				<div class="copy">
					<?php if ( $name ) : ?><h2><?php echo esc_html( $name ); ?></h2><?php endif; ?>
					<p><?php echo esc_html( gadgero_campaign_text( 'gadgero_campaign_intro' ) ); ?></p>
					<?php $price_block(); ?>
				</div>
			</div>
		</div>
		<div class="hint" id="hint" aria-hidden="true"><?php esc_html_e( 'Scroll', 'gadgero' ); ?><i></i></div>
	</div>
</div>
