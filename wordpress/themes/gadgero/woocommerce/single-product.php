<?php
/**
 * Product page (brief 15.11.2026, items 06–08).
 *
 * - Gallery: whole device in view (proportional containment, no cropping), further slides for back / side /
 *   close-ups, thumbnails, zoom view with touch zoom; colour choice selects the matching image (product.js).
 * - Fixed attributes in a compact "Ausstattung" summary; selectors only for real choices.
 * - Refurbished: grade, cosmetic notes, measured battery, included items and photo status together.
 * - Technical data grouped per category from verified sources; missing groups flagged internally only.
 * - Configuration, price, image, stock and SKU follow the selection into basket and checkout (WooCommerce).
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
	$brand   = gadgero_brand( $product );
	$gallery = array_values( array_unique( array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) ) ) );
	$refurb  = 'refurbished' === $cond;
	$rows    = $refurb ? gadgero_refurb_details( $product ) : array();
	$cosm    = $refurb ? (string) get_post_meta( $product->get_id(), '_gadgero_cosmetic', true ) : '';
	$sealed  = 'new' === $cond && 'yes' === get_post_meta( $product->get_id(), '_gadgero_sealed', true );
	$actual  = 'yes' === get_post_meta( $product->get_id(), '_gadgero_photos_actual', true );
	$attrs   = array_filter( $product->get_attributes(), fn( $a ) => $a->get_visible() || $a->get_variation() );
	$specs   = gadgero_specs( $product );
	$missing = gadgero_spec_missing( $product );

	// Fixed (non-selectable) configuration → compact summary. Condition is shown separately as a badge.
	$fixed     = array();
	$fixed_tax = array(); // for the "Ihre Auswahl" line in product.js
	foreach ( gadgero_attr_order() as $tax ) {
		if ( isset( $attrs[ $tax ] ) && ! $attrs[ $tax ]->get_variation() ) {
			$names = wc_get_product_terms( $product->get_id(), $tax, array( 'fields' => 'names' ) );
			if ( $names ) {
				$fixed[ wc_attribute_label( $tax ) ] = implode( ', ', $names );
				$fixed_tax[ $tax ]                    = implode( ' / ', $names );
			}
		}
	}
	?>
<main id="main" class="page-main pdp-main">
	<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'pdp', $product ); ?>>
		<section class="pdp-top">
			<div class="wrap">
				<?php woocommerce_breadcrumb(); ?>
				<?php woocommerce_output_all_notices(); ?>
				<div class="pdp-grid">
					<div class="pdp-media" id="pdpMedia">
						<div class="pdp-stage" style="--glow:<?php echo esc_attr( gadgero_glow( $product ) ); ?>40">
							<div class="pdp-main-img" id="pdpImg" data-art="<?php echo esc_attr( gadgero_device_svg( ( gadgero_art( $product ) ?? array( 'type' => 'phone' ) )['type'], ( gadgero_art( $product ) ?? array() )['c1'] ?? '#5b6270', ( gadgero_art( $product ) ?? array() )['c2'] ?? '#1d2026', ( gadgero_art( $product ) ?? array() )['frame'] ?? '#b9bfca' ) ); ?>">
								<?php
								if ( $gallery ) {
									echo wp_get_attachment_image( $gallery[0], 'woocommerce_single', false, array( 'alt' => $product->get_name(), 'loading' => 'eager', 'fetchpriority' => 'high', 'data-full' => wp_get_attachment_image_url( $gallery[0], 'full' ) ) );
								} else {
									echo gadgero_product_visual( $product, 'woocommerce_single' ); // phpcs:ignore
								}
								?>
							</div>
							<?php if ( count( $gallery ) > 1 ) : ?>
								<button type="button" class="pdp-nav prev" data-step="-1" aria-label="Vorheriges Bild"><?php echo gadgero_icon( 'chev-l' ); // phpcs:ignore ?></button>
								<button type="button" class="pdp-nav next" data-step="1" aria-label="Nächstes Bild"><?php echo gadgero_icon( 'chev-r' ); // phpcs:ignore ?></button>
							<?php endif; ?>
							<?php if ( $gallery ) : ?>
								<button type="button" class="pdp-zoom" id="pdpZoom" aria-label="Bild vergrößern"><?php echo gadgero_icon( 'zoom' ); // phpcs:ignore ?></button>
							<?php endif; ?>
						</div>
						<?php if ( count( $gallery ) > 1 ) : ?>
							<div class="pdp-thumbs" role="list" aria-label="Produktbilder">
								<?php foreach ( $gallery as $i => $img ) : ?>
									<button type="button" role="listitem" class="pdp-thumb" data-id="<?php echo (int) $img; ?>" data-src="<?php echo esc_url( wp_get_attachment_image_url( $img, 'woocommerce_single' ) ); ?>" data-full="<?php echo esc_url( wp_get_attachment_image_url( $img, 'full' ) ); ?>" data-alt="<?php echo esc_attr( get_post_meta( $img, '_wp_attachment_image_alt', true ) ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Bild %d von %d', $i + 1, count( $gallery ) ) ); ?>"<?php echo 0 === $i ? ' aria-current="true"' : ''; ?>><?php echo wp_get_attachment_image( $img, 'thumbnail' ); ?></button>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<?php if ( $refurb && ! $actual ) : ?>
							<p class="pdp-imgnote">Symbolbild – zeigt nicht den optischen Zustand dieses Geräts. Der Zustand ist unter „Zustand dieses Geräts“ beschrieben.</p>
						<?php elseif ( ! $gallery ) : ?>
							<p class="pdp-imgnote">Symbolbild</p>
						<?php endif; ?>
						<?php echo $gallery ? '' : gadgero_flag( 'approved original photos (front, back, side) with source and usage rights' ); // phpcs:ignore ?>
					</div>

					<div class="pdp-info">
						<p class="eyebrow"><?php echo esc_html( implode( ' · ', array_filter( array( $brand, $cats ? $cats[0]->name : '' ) ) ) ); ?></p>
						<h1 class="pdp-title product_title"><?php echo esc_html( $product->get_name() ); ?></h1>
						<p class="pdp-badges">
							<?php if ( $cond ) : ?><span class="cond cond-<?php echo esc_attr( $cond ); ?>"><?php echo esc_html( gadgero_condition_label( $cond ) ); ?></span><?php endif; ?>
							<?php if ( $sealed ) : ?><span class="pdp-sealed"><?php echo gadgero_icon( 'box' ); // phpcs:ignore ?> Originalverpackt, versiegelt</span><?php endif; ?>
						</p>

						<div class="pdp-price" id="pdpPrice" data-initial="<?php echo esc_attr( gadgero_card_price( $product ) ); ?>"><?php echo wp_kses_post( gadgero_card_price( $product ) ); ?></div>
						<p class="pdp-tax"><?php echo wp_kses_post( gadgero_price_notice() ); ?></p>

						<?php if ( $product->get_short_description() ) : ?>
							<div class="pdp-short"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
						<?php endif; ?>

						<?php if ( $fixed ) : ?>
							<div class="pdp-summary">
								<p class="pdp-summary-h">Ausstattung</p>
								<dl><?php foreach ( $fixed as $l => $v ) : ?><div><dt><?php echo esc_html( $l ); ?></dt><dd><?php echo esc_html( $v ); ?></dd></div><?php endforeach; ?></dl>
							</div>
						<?php endif; ?>

						<?php if ( $refurb ) : ?>
							<div class="pdp-refurb-panel">
								<p class="pdp-summary-h">Zustand dieses Geräts</p>
								<?php if ( $rows ) : ?>
									<dl class="pdp-refurb"><?php foreach ( $rows as $r ) : ?><div><dt><?php echo esc_html( $r[0] ); ?></dt><dd><?php echo esc_html( $r[1] ); ?></dd></div><?php endforeach; ?></dl>
								<?php endif; ?>
								<?php if ( $cosm ) : ?><p class="pdp-cosmetic"><strong>Optische Hinweise:</strong> <?php echo esc_html( $cosm ); ?></p><?php endif; ?>
								<p class="pdp-photos-note"><?php echo $actual ? 'Die Fotos zeigen genau dieses Gerät.' : 'Fotos dieses Geräts folgen; die Abbildung ist ein Symbolbild.'; ?></p>
								<?php $rm = gadgero_refurb_missing( $product ); echo $rm ? gadgero_flag( 'refurbished data: ' . implode( ', ', $rm ) ) : ''; // phpcs:ignore ?>
							</div>
						<?php endif; ?>

						<div class="pdp-buy">
							<?php woocommerce_template_single_add_to_cart(); ?>
						</div>

						<?php if ( $product->is_type( 'variable' ) ) : ?>
							<p class="pdp-config" id="pdpConfig" data-name="<?php echo esc_attr( $product->get_name() ); ?>" data-cond="<?php echo esc_attr( gadgero_condition_label( $cond ) ); ?>" data-order="<?php echo esc_attr( wp_json_encode( gadgero_attr_order() ) ); ?>" data-fixed="<?php echo esc_attr( wp_json_encode( (object) $fixed_tax ) ); ?>" aria-live="polite"></p>
						<?php endif; ?>

						<div class="product_meta pdp-meta">
							<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
								<span class="sku_wrapper">Art.-Nr.: <span class="sku"><?php echo esc_html( $product->get_sku() ? $product->get_sku() : 'nach Auswahl' ); ?></span></span>
							<?php endif; ?>
						</div>

						<p class="pdp-help"><?php echo gadgero_icon( 'chat' ); // phpcs:ignore ?> Fragen zu diesem Gerät? <a href="<?php echo esc_url( gadgero_advice_link( sprintf( 'Hallo %1$s, ich habe eine Frage zu: %2$s', get_bloginfo( 'name' ), $product->get_name() ) ) ); ?>"><?php echo esc_html( gadgero_advice_label() ); ?></a> <small>Nur Beratung – bestellt wird über die Kasse.</small></p>
					</div>
				</div>
			</div>
		</section>

		<section class="pdp-details">
			<div class="wrap pdp-details-grid<?php echo get_the_content() ? '' : ' one'; ?>">
				<?php if ( get_the_content() ) : ?>
					<div class="pdp-desc prose">
						<h2>Über dieses Gerät</h2>
						<?php the_content(); ?>
					</div>
				<?php endif; ?>
				<div class="pdp-specs">
					<h2>Technische Daten</h2>
					<?php if ( $specs ) : ?>
						<?php foreach ( $specs as $group => $list ) : ?>
							<h3><?php echo esc_html( $group ); ?></h3>
							<dl><?php foreach ( $list as $row ) : ?><div><dt><?php echo esc_html( $row[0] ); ?></dt><dd><?php echo esc_html( $row[1] ); ?></dd></div><?php endforeach; ?></dl>
						<?php endforeach; ?>
						<p class="spec-note">Technische Daten laut Herstellerangaben.</p>
					<?php else : ?>
						<dl>
							<?php foreach ( $attrs as $tax => $a ) : ?>
								<div><dt><?php echo esc_html( wc_attribute_label( $tax ) ); ?></dt><dd><?php echo esc_html( implode( ', ', $a->is_taxonomy() ? wc_get_product_terms( $product->get_id(), $tax, array( 'fields' => 'names' ) ) : $a->get_options() ) ); ?></dd></div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
					<?php echo $missing ? gadgero_flag( 'technical data missing: ' . implode( ', ', $missing ) ) : ''; // phpcs:ignore ?>
				</div>
			</div>
		</section>

		<?php if ( $refurb ) : ?>
			<section class="about proc-band"><div class="wrap"><?php get_template_part( 'template-parts/home/process' ); ?></div></section>
		<?php endif; ?>

		<?php $related = gadgero_related( $product ); ?>
		<?php if ( $related ) : ?>
			<section class="pdp-related">
				<div class="wrap">
					<h2 class="sec-s"><?php echo $refurb ? 'Weitere Refurbished-Geräte' : 'Das könnte Sie auch interessieren'; ?></h2>
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
<dialog class="zoom-dlg" id="zoomDlg" aria-label="Bildansicht">
	<button type="button" class="zoom-x" id="zoomClose" aria-label="Schließen">&times;</button>
	<div class="zoom-body" id="zoomBody"></div>
</dialog>
	<?php
endwhile;

get_footer();
