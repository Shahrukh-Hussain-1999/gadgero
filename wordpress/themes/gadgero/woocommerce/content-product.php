<?php
/**
 * Product card (approved compact card): photo or device illustration, New / Refurbished badge, name, spec line,
 * price and add-to-cart. Variable products link to the product page to choose a configuration.
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
?>
<article <?php wc_product_class( 'card', $product ); ?> data-cat="<?php echo esc_attr( implode( ' ', $cats ) ); ?>" data-cond="<?php echo esc_attr( $cond ); ?>">
	<a class="thumb" href="<?php the_permalink(); ?>" style="--glow:<?php echo esc_attr( gadgero_glow( $product ) ); ?>55" tabindex="-1" aria-hidden="true">
		<?php if ( $cond ) : ?>
			<span class="badge <?php echo 'new' === $cond ? 'new' : 'ref'; ?>"><?php echo esc_html( gadgero_condition_label( $cond ) ); ?></span>
		<?php endif; ?>
		<?php echo gadgero_product_visual( $product ); // phpcs:ignore ?>
	</a>
	<div class="body">
		<h4><a href="<?php the_permalink(); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h4>
		<p class="spec"><?php echo esc_html( gadgero_card_spec( $product ) ); ?></p>
		<div class="buy">
			<div class="price"><?php echo $price ? wp_kses_post( $price ) : gadgero_flag( 'price' ); // phpcs:ignore ?></div>
			<?php echo gadgero_card_button( $product ); // phpcs:ignore ?>
		</div>
	</div>
</article>
