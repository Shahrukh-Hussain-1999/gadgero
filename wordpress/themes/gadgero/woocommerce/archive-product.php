<?php
/**
 * Shop / category archive (brief item 04): two labelled filter groups ("Zustand", "Kategorie") that combine,
 * sorting, the filtered result count and a clear reset when a combination has no matches.
 * Condition uses WooCommerce's native filter_condition query var; category links keep it.
 * The refurbishment process appears on the refurbished journey only.
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
$orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$keep    = function ( $url ) use ( $orderby ) {
	return $orderby ? add_query_arg( 'orderby', $orderby, $url ) : $url;
};
$cats = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		'orderby'    => 'term_order',
	)
);
$title = $current ? $current->name : 'Alle Geräte';
if ( 'new' === $cond ) {
	$title = $current ? $current->name . ' – neu' : 'Neue Geräte';
} elseif ( 'refurbished' === $cond ) {
	$title = $current ? $current->name . ' – refurbished' : 'Refurbished-Geräte';
}
$filtered = $cond || $current;
?>
<main id="main" class="page-main shop-main">
	<section class="page-head">
		<div class="wrap">
			<?php woocommerce_breadcrumb(); ?>
			<h1 class="sec"><?php echo esc_html( $title ); ?></h1>
			<?php if ( $current && $current->description ) : ?><div class="lead"><?php echo wp_kses_post( wpautop( $current->description ) ); ?></div><?php endif; ?>
			<div class="filters">
				<div class="fgroup">
					<p class="fgroup-label" id="f-cond">Zustand</p>
					<div class="seg" role="group" aria-labelledby="f-cond">
						<a class="seg-b" href="<?php echo esc_url( $keep( remove_query_arg( 'filter_condition', $base ) ) ); ?>" <?php echo '' === $cond ? 'aria-current="true"' : ''; ?>>Alle</a>
						<a class="seg-b" href="<?php echo esc_url( $keep( add_query_arg( 'filter_condition', 'new', $base ) ) ); ?>" <?php echo 'new' === $cond ? 'aria-current="true"' : ''; ?>>Neu</a>
						<a class="seg-b" href="<?php echo esc_url( $keep( add_query_arg( 'filter_condition', 'refurbished', $base ) ) ); ?>" <?php echo 'refurbished' === $cond ? 'aria-current="true"' : ''; ?>>Refurbished</a>
					</div>
				</div>
				<div class="fgroup">
					<p class="fgroup-label" id="f-cat">Kategorie</p>
					<nav class="chips" aria-labelledby="f-cat">
						<a class="chip" href="<?php echo esc_url( $keep( $cond ? add_query_arg( 'filter_condition', $cond, $shop ) : $shop ) ); ?>" <?php echo $current ? '' : 'aria-current="page"'; ?>>Alle</a>
						<?php foreach ( (array) $cats as $c ) : ?>
							<?php $link = get_term_link( $c ); ?>
							<a class="chip" href="<?php echo esc_url( $keep( $cond ? add_query_arg( 'filter_condition', $cond, $link ) : $link ) ); ?>" <?php echo ( $current && $current->term_id === $c->term_id ) ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $c->name ); ?></a>
						<?php endforeach; ?>
					</nav>
				</div>
			</div>
		</div>
	</section>
	<section class="shop-body">
		<div class="wrap">
			<?php woocommerce_output_all_notices(); ?>
			<?php if ( woocommerce_product_loop() ) : ?>
				<div class="shop-tools">
					<?php woocommerce_result_count(); ?>
					<div class="tools-r">
						<?php if ( $filtered ) : ?><a class="reset-link" href="<?php echo esc_url( $shop ); ?>">Filter zurücksetzen</a><?php endif; ?>
						<?php woocommerce_catalog_ordering(); ?>
					</div>
				</div>
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
				<div class="empty-shop" role="status">
					<p class="empty-title">Keine Geräte für diese Auswahl.</p>
					<p>
						<?php
						if ( $cond && $current ) {
							echo esc_html( sprintf( 'Zurzeit gibt es in „%s“ keine Geräte im Zustand „%s“.', $current->name, gadgero_condition_label( $cond ) ) );
						} else {
							echo 'Ändern Sie den Zustand oder die Kategorie.';
						}
						?>
					</p>
					<p class="empty-actions">
						<a class="solid" href="<?php echo esc_url( $shop ); ?>">Filter zurücksetzen</a>
						<?php if ( $cond && $current ) : ?>
							<a class="ghost" href="<?php echo esc_url( get_term_link( $current ) ); ?>">Alle <?php echo esc_html( $current->name ); ?></a>
							<a class="ghost" href="<?php echo esc_url( add_query_arg( 'filter_condition', $cond, $shop ) ); ?>"><?php echo esc_html( 'new' === $cond ? 'Alle neuen Geräte' : 'Alle Refurbished-Geräte' ); ?></a>
						<?php endif; ?>
					</p>
				</div>
			<?php endif; ?>
			<p class="note">Alle Preise in Euro, <?php echo wp_kses_post( gadgero_price_notice() ); ?>.</p>
		</div>
	</section>
	<?php if ( 'refurbished' === $cond ) : ?>
		<section class="about proc-band"><div class="wrap"><?php get_template_part( 'template-parts/home/process' ); ?></div></section>
	<?php endif; ?>
</main>
<?php
get_footer();
