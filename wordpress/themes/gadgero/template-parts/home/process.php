<?php
/**
 * Refurbishment process — applies to refurbished devices only (Phase 4). Used in the About section and on
 * refurbished product pages. Pass array( 'compact' => true ) for the product-page variant.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

$compact = ! empty( $args['compact'] );
$shop    = function_exists( 'wc_get_page_permalink' ) ? add_query_arg( 'filter_condition', 'refurbished', wc_get_page_permalink( 'shop' ) ) : '';
$steps   = array(
	array( __( 'Inspection', 'gadgero' ), __( 'Each device is inspected before it is listed.', 'gadgero' ) ),
	array( __( 'Cleaning & data wiping', 'gadgero' ), __( 'The device is cleaned and all previous data is wiped.', 'gadgero' ) ),
	array( __( 'Battery testing', 'gadgero' ), __( 'Battery health is measured and shown on the listing.', 'gadgero' ) ),
	array( __( 'Software installation', 'gadgero' ), __( 'A fresh operating system is installed, ready to set up.', 'gadgero' ) ),
);
?>
<div class="refurb-process<?php echo $compact ? ' is-compact' : ''; ?>" id="refurbished-process">
	<div class="proc-head">
		<div>
			<p class="eyebrow rv"><?php esc_html_e( 'Refurbished devices only', 'gadgero' ); ?></p>
			<h3 class="rv"><?php esc_html_e( 'From our bench to your hands.', 'gadgero' ); ?></h3>
		</div>
		<p class="rv" style="--d:.1s"><?php esc_html_e( 'Every refurbished device goes through these steps before it is listed. New devices are sold as supplied by the manufacturer.', 'gadgero' ); ?><?php if ( $shop && ! $compact ) : ?> <a class="inline-link" href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'Shop refurbished', 'gadgero' ); ?> &rarr;</a><?php endif; ?></p>
	</div>
	<div class="proc rv" id="proc">
		<?php foreach ( $steps as $i => $s ) : ?>
			<div class="step-p"><i><?php echo (int) $i + 1; ?></i><h4><?php echo esc_html( $s[0] ); ?></h4><p><?php echo esc_html( $s[1] ); ?></p></div>
		<?php endforeach; ?>
	</div>
</div>
