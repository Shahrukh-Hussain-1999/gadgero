<?php
/**
 * Homepage product grid (approved compact grid + category chips). Products, prices and stock come from WooCommerce.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_products' ) ) {
	return;
}
$q = new WP_Query(
	array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 16,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'tax_query'      => array( // phpcs:ignore
			array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => array( 'exclude-from-catalog' ),
				'operator' => 'NOT IN',
			),
		),
	)
);
$cats = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		'orderby'    => 'term_order',
	)
);
?>
<section id="shop">
	<div class="wrap">
		<div class="shophead">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'In stock now', 'gadgero' ); ?></p>
				<h3 class="sec"><?php esc_html_e( 'Pick your next device.', 'gadgero' ); ?></h3>
			</div>
			<div class="chips" id="chips" role="group" aria-label="<?php esc_attr_e( 'Filter by category', 'gadgero' ); ?>">
				<button class="chip" type="button" data-f="all" aria-pressed="true"><?php esc_html_e( 'All', 'gadgero' ); ?></button>
				<?php foreach ( (array) $cats as $c ) : ?>
					<button class="chip" type="button" data-f="<?php echo esc_attr( $c->slug ); ?>" aria-pressed="false"><?php echo esc_html( $c->name ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="grid" id="grid">
			<?php
			while ( $q->have_posts() ) {
				$q->the_post();
				wc_get_template_part( 'content', 'product' );
			}
			wp_reset_postdata();
			?>
		</div>
		<p class="note"><?php echo wp_kses_post( sprintf( __( 'All prices in euros, %1$s. <a href="%2$s">See all devices</a>.', 'gadgero' ), wp_strip_all_tags( gadgero_price_notice() ), esc_url( wc_get_page_permalink( 'shop' ) ) ) ); ?></p>
	</div>
</section>
