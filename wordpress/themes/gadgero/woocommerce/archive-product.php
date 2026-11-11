<?php
/**
 * Shop / category archive in the Gadgero layout: condition toggle (All / New / Refurbished), category chips,
 * the approved product grid and WooCommerce pagination. Condition filtering uses WooCommerce's native
 * filter_condition query var, category links keep it.
 *
 * @package Gadgero
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cond    = isset( $_GET['filter_condition'] ) ? sanitize_title( wp_unslash( $_GET['filter_condition'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$cond    = in_array( $cond, array( 'new', 'refurbished' ), true ) ? $cond : '';
$current = is_product_category() ? get_queried_object() : null;
$base    = $current ? get_term_link( $current ) : wc_get_page_permalink( 'shop' );
$shop    = wc_get_page_permalink( 'shop' );
$cats    = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
	)
);
$title = $current ? $current->name : __( 'Shop', 'gadgero' );
if ( 'new' === $cond ) {
	$title = $current ? sprintf( __( 'New %s', 'gadgero' ), strtolower( $current->name ) ) : __( 'New devices', 'gadgero' );
} elseif ( 'refurbished' === $cond ) {
	$title = $current ? sprintf( __( 'Refurbished %s', 'gadgero' ), strtolower( $current->name ) ) : __( 'Refurbished devices', 'gadgero' );
}
?>
<main id="main" class="page-main shop-main">
	<section class="page-head">
		<div class="wrap">
			<?php woocommerce_breadcrumb(); ?>
			<p class="eyebrow"><?php echo esc_html( 'refurbished' === $cond ? __( 'Inspected, cleaned, data-wiped, battery-tested', 'gadgero' ) : ( 'new' === $cond ? __( 'Brand-new devices', 'gadgero' ) : __( 'In stock now', 'gadgero' ) ) ); ?></p>
			<h1 class="sec"><?php echo esc_html( $title ); ?></h1>
			<?php if ( $current && $current->description ) : ?><div class="lead"><?php echo wp_kses_post( wpautop( $current->description ) ); ?></div><?php endif; ?>
			<div class="filters">
				<div class="seg" role="group" aria-label="<?php esc_attr_e( 'Condition', 'gadgero' ); ?>">
					<a class="seg-b" href="<?php echo esc_url( remove_query_arg( 'filter_condition', $base ) ); ?>" <?php echo '' === $cond ? 'aria-current="true"' : ''; ?>><?php esc_html_e( 'All', 'gadgero' ); ?></a>
					<a class="seg-b" href="<?php echo esc_url( add_query_arg( 'filter_condition', 'new', $base ) ); ?>" <?php echo 'new' === $cond ? 'aria-current="true"' : ''; ?>><?php esc_html_e( 'New', 'gadgero' ); ?></a>
					<a class="seg-b" href="<?php echo esc_url( add_query_arg( 'filter_condition', 'refurbished', $base ) ); ?>" <?php echo 'refurbished' === $cond ? 'aria-current="true"' : ''; ?>><?php esc_html_e( 'Refurbished', 'gadgero' ); ?></a>
				</div>
				<nav class="chips" aria-label="<?php esc_attr_e( 'Categories', 'gadgero' ); ?>">
					<a class="chip" href="<?php echo esc_url( $cond ? add_query_arg( 'filter_condition', $cond, $shop ) : $shop ); ?>" <?php echo $current ? '' : 'aria-current="page"'; ?>><?php esc_html_e( 'All', 'gadgero' ); ?></a>
					<?php foreach ( (array) $cats as $c ) : ?>
						<?php $link = get_term_link( $c ); ?>
						<a class="chip" href="<?php echo esc_url( $cond ? add_query_arg( 'filter_condition', $cond, $link ) : $link ); ?>" <?php echo ( $current && $current->term_id === $c->term_id ) ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $c->name ); ?></a>
					<?php endforeach; ?>
				</nav>
			</div>
		</div>
	</section>
	<section class="shop-body">
		<div class="wrap">
			<?php woocommerce_output_all_notices(); ?>
			<?php if ( woocommerce_product_loop() ) : ?>
				<div class="shop-tools"><?php woocommerce_result_count(); ?><?php woocommerce_catalog_ordering(); ?></div>
				<div class="grid">
					<?php
					while ( have_posts() ) {
						the_post();
						wc_get_template_part( 'content', 'product' );
					}
					?>
				</div>
				<?php woocommerce_pagination(); ?>
			<?php else : ?>
				<p class="empty-shop"><?php esc_html_e( 'No devices match this selection right now.', 'gadgero' ); ?> <a href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'Show all devices', 'gadgero' ); ?></a></p>
			<?php endif; ?>
			<p class="note"><?php echo wp_kses_post( sprintf( __( 'All prices in euros, %s.', 'gadgero' ), gadgero_price_notice() ) ); ?></p>
		</div>
	</section>
	<?php if ( 'refurbished' === $cond ) : ?>
		<section class="about proc-band"><div class="wrap"><?php get_template_part( 'template-parts/home/process', null, array( 'compact' => true ) ); ?></div></section>
	<?php endif; ?>
</main>
<?php
get_footer();
