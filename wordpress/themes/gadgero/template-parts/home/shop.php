<?php
/**
 * Short selection (brief item 03): featured products (max. 8) instead of the full range, category links that wrap
 * (no horizontal scrolling) and a visible "Alle Geräte" link. The full range stays in the shop.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_products' ) ) {
	return;
}
$ids = wc_get_featured_product_ids();
$args = array(
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => 8,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	'tax_query'      => array( // phpcs:ignore
		array(
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => array( 'exclude-from-catalog' ),
			'operator' => 'NOT IN',
		),
	),
);
if ( $ids ) {
	$args['post__in'] = $ids;
}
$q    = new WP_Query( $args );
$shop = wc_get_page_permalink( 'shop' );
$cats = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		'orderby'    => 'term_order',
	)
);
?>
<section id="shop" class="home-shop">
	<div class="wrap">
		<div class="shophead">
			<div>
				<p class="eyebrow">Auswahl</p>
				<h2 class="sec">Beliebte Geräte</h2>
			</div>
			<a class="all-link" href="<?php echo esc_url( $shop ); ?>">Alle Geräte <span aria-hidden="true">&rarr;</span></a>
		</div>
		<nav class="catlinks" aria-label="Kategorien">
			<?php foreach ( (array) $cats as $c ) : ?>
				<a class="chip" href="<?php echo esc_url( get_term_link( $c ) ); ?>"><?php echo esc_html( $c->name ); ?></a>
			<?php endforeach; ?>
		</nav>
		<div class="grid" id="grid">
			<?php
			while ( $q->have_posts() ) {
				$q->the_post();
				wc_get_template_part( 'content', 'product' );
			}
			wp_reset_postdata();
			?>
		</div>
		<p class="note">Alle Preise in Euro, <?php echo wp_kses_post( wp_strip_all_tags( gadgero_price_notice() ) ); ?>. <a href="<?php echo esc_url( $shop ); ?>">Alle Geräte ansehen</a></p>
	</div>
</section>
