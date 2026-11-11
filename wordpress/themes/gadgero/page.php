<?php
/**
 * Pages: cart, checkout, legal and service pages. Uses the approved section rhythm on a light ground.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

get_header();
$is_shop_flow = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() );
?>
<main id="main" class="page-main<?php echo $is_shop_flow ? ' is-flow' : ''; ?>">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<section class="page-head">
			<div class="wrap">
				<?php if ( $is_shop_flow ) : ?>
					<ol class="flow" aria-label="Bestellschritte">
						<li<?php echo is_cart() ? ' aria-current="step"' : ''; ?>>1 Warenkorb</li>
						<li<?php echo ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) ? ' aria-current="step"' : ''; ?>>2 Kasse</li>
						<li<?php echo is_wc_endpoint_url( 'order-received' ) ? ' aria-current="step"' : ''; ?>>3 Bestätigung</li>
					</ol>
				<?php endif; ?>
				<h1 class="sec"><?php echo ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) ? 'Vielen Dank für Ihre Bestellung' : esc_html( get_the_title() ); ?></h1>
			</div>
		</section>
		<section class="page-body">
			<div class="wrap <?php echo $is_shop_flow ? 'wc-flow' : 'prose'; ?>">
				<?php the_content(); ?>
			</div>
		</section>
	<?php endwhile; ?>
</main>
<?php
get_footer();
