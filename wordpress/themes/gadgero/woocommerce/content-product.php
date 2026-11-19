<?php
/**
 * Product card (brief item 04): identical image stage, title position and price baseline in every category.
 * Shows brand + model, one configuration line and the condition.
 *
 * @package Gadgero
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
if ( ! $product || ! $product->is_visible() ) {
	return;
}
$cond  = gadgero_condition( $product );
$cats  = wc_get_product_terms( $product->get_id(), 'product_cat', array( 'fields' => 'slugs' ) );
$price = gadgero_card_price( $product );
$brand = gadgero_brand( $product );
?>
<article <?php wc_product_class( 'card', $product ); ?> data-cat="<?php echo esc_attr( implode( ' ', $cats ) ); ?>" data-cond="<?php echo esc_attr( $cond ); ?>">
	<a class="thumb" href="<?php the_permalink(); ?>" style="--glow:<?php echo esc_attr( gadgero_glow( $product ) ); ?>40" tabindex="-1" aria-hidden="true">
		<span class="thumb-media"><?php echo gadgero_product_visual( $product ); // phpcs:ignore ?></span>
	</a>
	<div class="body">
		<p class="card-meta">
			<?php if ( $brand ) : ?><span class="brand"><?php echo esc_html( $brand ); ?></span><?php endif; ?>
			<?php if ( $cond ) : ?><span class="cond cond-<?php echo esc_attr( $cond ); ?>"><?php echo esc_html( gadgero_condition_label( $cond ) ); ?></span><?php endif; ?>
		</p>
		<h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
		<p class="spec"><?php echo esc_html( gadgero_card_spec( $product ) ); ?></p>
		<div class="buy">
			<div class="price"><?php echo $price ? wp_kses_post( $price ) : gadgero_flag( 'price' ); // phpcs:ignore ?></div>
			<?php echo gadgero_card_button( $product ); // phpcs:ignore ?>
		</div>
	</div>
</article>
