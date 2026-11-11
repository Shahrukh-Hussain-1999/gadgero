<?php
/**
 * Product page in the Gadgero design (Phase 9). All data is WooCommerce data: name, images, condition, price,
 * sale price, variation selectors, stock, SKU, attributes, refurbished-unit details.
 *
 * The native WooCommerce variation form is used unchanged underneath, so price / stock / availability updates and
 * the "only add a valid, in-stock variation" rule are WooCommerce's own. assets/js/product.js only adds the
 * pill-style selectors and the "Selected configuration" line on top.
 *
 * @package Gadgero
 * @version 1.6.4
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	global $product;
	$product = wc_get_product( get_the_ID() );
	$cond    = gadgero_condition( $product );
	$cats    = wc_get_product_terms( $product->get_id(), 'product_cat' );
	$gallery = array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) );
	$refurb  = 'refurbished' === $cond ? gadgero_refurb_details( $product ) : array();
	$sealed  = 'new' === $cond && 'yes' === get_post_meta( $product->get_id(), '_gadgero_sealed', true );
	$actual  = 'yes' === get_post_meta( $product->get_id(), '_gadgero_photos_actual', true );
	$attrs   = array_filter( $product->get_attributes(), fn( $a ) => $a->get_visible() || $a->get_variation() );
	?>
<main id="main" class="page-main pdp-main">
	<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'pdp', $product ); ?>>
		<section class="pdp-top">
			<div class="wrap">
				<?php woocommerce_breadcrumb(); ?>
				<?php woocommerce_output_all_notices(); ?>
				<div class="pdp-grid">
					<div class="pdp-media">
						<div class="pdp-stage" style="--glow:<?php echo esc_attr( gadgero_glow( $product ) ); ?>66">
							<?php if ( $cond ) : ?><span class="badge <?php echo 'new' === $cond ? 'new' : 'ref'; ?>"><?php echo esc_html( gadgero_condition_label( $cond ) ); ?></span><?php endif; ?>
							<div class="pdp-main-img" id="pdpImg">
								<?php
								if ( $gallery ) {
									echo wp_get_attachment_image( reset( $gallery ), 'woocommerce_single', false, array( 'alt' => $product->get_name(), 'loading' => 'eager', 'fetchpriority' => 'high' ) );
								} else {
									echo gadgero_product_visual( $product, 'woocommerce_single' ); // phpcs:ignore
								}
								?>
							</div>
						</div>
						<?php if ( count( $gallery ) > 1 ) : ?>
							<div class="pdp-thumbs" role="list">
								<?php foreach ( $gallery as $i => $img ) : ?>
									<button type="button" role="listitem" class="pdp-thumb" data-full="<?php echo esc_url( wp_get_attachment_image_url( $img, 'woocommerce_single' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Show image %d', 'gadgero' ), $i + 1 ) ); ?>"<?php echo 0 === $i ? ' aria-pressed="true"' : ''; ?>><?php echo wp_get_attachment_image( $img, 'thumbnail' ); ?></button>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<?php if ( 'refurbished' === $cond && ! $actual ) : ?>
							<p class="pdp-imgnote"><?php esc_html_e( 'Images are illustrative and do not show the cosmetic condition of this unit. See the condition details in this listing.', 'gadgero' ); ?></p>
						<?php elseif ( ! $gallery ) : ?>
							<p class="pdp-imgnote"><?php esc_html_e( 'Illustration. Product photos will follow.', 'gadgero' ); ?></p>
						<?php endif; ?>
					</div>

					<div class="pdp-info">
						<?php if ( $cats ) : ?><p class="eyebrow"><?php echo esc_html( $cats[0]->name ); ?></p><?php endif; ?>
						<h1 class="pdp-title product_title"><?php echo esc_html( $product->get_name() ); ?></h1>
						<?php if ( $sealed ) : ?><p class="pdp-sealed"><?php echo gadgero_icon( 'box' ); // phpcs:ignore ?> <?php esc_html_e( 'Original factory-sealed packaging', 'gadgero' ); ?></p><?php endif; ?>

						<div class="pdp-price" id="pdpPrice" data-initial="<?php echo esc_attr( gadgero_card_price( $product ) ); ?>"><?php echo wp_kses_post( gadgero_card_price( $product ) ); ?></div>
						<p class="pdp-tax"><?php echo wp_kses_post( gadgero_price_notice() ); ?></p>

						<?php if ( $product->get_short_description() ) : ?>
							<div class="pdp-short"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
						<?php endif; ?>

						<?php if ( $refurb ) : ?>
							<dl class="pdp-refurb">
								<?php foreach ( $refurb as $r ) : ?><div><dt><?php echo esc_html( $r[0] ); ?></dt><dd><?php echo esc_html( $r[1] ); ?></dd></div><?php endforeach; ?>
							</dl>
						<?php endif; ?>

						<?php
						// Options with a single fixed value (not chosen through a variation) are shown as preselected,
						// in the same order and style as the selectable ones, so storage / colour / screen size /
						// connectivity / condition are always visible above "Add to cart".
						$fixed = array();
						foreach ( array_merge( gadgero_attr_order(), array( 'pa_condition' ) ) as $tax ) {
							if ( isset( $attrs[ $tax ] ) && ! $attrs[ $tax ]->get_variation() ) {
								$names = wc_get_product_terms( $product->get_id(), $tax, array( 'fields' => 'names' ) );
								if ( $names ) {
									$fixed[ $tax ] = $names;
								}
							}
						}
						?>
						<?php if ( $fixed ) : ?>
							<div class="pdp-fixed">
								<?php foreach ( $fixed as $tax => $names ) : ?>
									<div class="pdp-opt">
										<p class="pdp-opt-label"><?php echo esc_html( wc_attribute_label( $tax ) ); ?></p>
										<div class="pills" role="radiogroup" aria-label="<?php echo esc_attr( wc_attribute_label( $tax ) ); ?>">
											<?php foreach ( $names as $n ) : ?>
												<span class="pill is-on is-fixed" role="radio" aria-checked="true" aria-disabled="true"><?php echo esc_html( 'pa_condition' === $tax ? gadgero_condition_label( $cond ) : $n ); ?></span>
											<?php endforeach; ?>
											<?php if ( 1 === count( $names ) ) : ?><small class="pdp-opt-note"><?php esc_html_e( 'Only option', 'gadgero' ); ?></small><?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<div class="pdp-buy">
							<?php woocommerce_template_single_add_to_cart(); ?>
						</div>

						<p class="pdp-config" id="pdpConfig" data-name="<?php echo esc_attr( $product->get_name() ); ?>" data-cond="<?php echo esc_attr( gadgero_condition_label( $cond ) ); ?>" aria-live="polite">
							<?php
							if ( ! $product->is_type( 'variable' ) ) {
								$line = gadgero_config_line( $product );
								echo '<span>' . esc_html__( 'Configuration', 'gadgero' ) . '</span>' . esc_html( $product->get_name() . ( $line ? ' · ' . $line : '' ) );
							}
							?>
						</p>

						<div class="product_meta pdp-meta">
							<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
								<span class="sku_wrapper"><?php esc_html_e( 'SKU', 'gadgero' ); ?>: <span class="sku"><?php echo esc_html( $product->get_sku() ? $product->get_sku() : __( 'select a configuration', 'gadgero' ) ); ?></span></span>
							<?php endif; ?>
						</div>

						<p class="pdp-help"><?php echo gadgero_icon( 'chat' ); // phpcs:ignore ?> <?php esc_html_e( 'Questions about this device?', 'gadgero' ); ?> <a href="<?php echo esc_url( gadgero_wa_link( sprintf( __( 'Hi %1$s, I have a question about: %2$s', 'gadgero' ), get_bloginfo( 'name' ), $product->get_name() ) ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Ask on WhatsApp', 'gadgero' ); ?></a> <small><?php esc_html_e( '(advice only – orders are placed through the checkout)', 'gadgero' ); ?></small></p>
					</div>
				</div>
			</div>
		</section>

		<section class="pdp-details">
			<div class="wrap pdp-details-grid">
				<?php if ( get_the_content() ) : ?>
					<div class="pdp-desc prose">
						<h2><?php esc_html_e( 'About this device', 'gadgero' ); ?></h2>
						<?php the_content(); ?>
					</div>
				<?php endif; ?>
				<?php if ( $attrs ) : ?>
					<div class="pdp-specs">
						<h2><?php esc_html_e( 'Specifications', 'gadgero' ); ?></h2>
						<dl>
							<?php foreach ( $attrs as $tax => $a ) : ?>
								<div><dt><?php echo esc_html( wc_attribute_label( $tax ) ); ?></dt><dd><?php echo esc_html( implode( ', ', $a->is_taxonomy() ? wc_get_product_terms( $product->get_id(), $tax, array( 'fields' => 'names' ) ) : $a->get_options() ) ); ?></dd></div>
							<?php endforeach; ?>
						</dl>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<?php if ( 'refurbished' === $cond ) : ?>
			<section class="about proc-band"><div class="wrap"><?php get_template_part( 'template-parts/home/process', null, array( 'compact' => true ) ); ?></div></section>
		<?php endif; ?>

		<?php $related = gadgero_related( $product ); ?>
		<?php if ( $related ) : ?>
			<section class="pdp-related">
				<div class="wrap">
					<h2 class="sec-s"><?php echo esc_html( 'refurbished' === $cond ? __( 'More refurbished devices', 'gadgero' ) : __( 'You may also like', 'gadgero' ) ); ?></h2>
					<div class="grid">
						<?php
						foreach ( $related as $rp ) {
							$post_object = get_post( $rp->get_id() );
							setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore
							$GLOBALS['product'] = $rp;
							wc_get_template_part( 'content', 'product' );
						}
						wp_reset_postdata();
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</div>
</main>
	<?php
endwhile;

get_footer();
