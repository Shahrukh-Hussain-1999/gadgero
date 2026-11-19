<?php
/**
 * Direct entries for New and Refurbished (brief item 03). Each opens the shop filtered by the pa_condition
 * attribute, so the two collections never mix.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_page_permalink' ) ) {
	return;
}
$shop  = wc_get_page_permalink( 'shop' );
$count = function ( $slug ) {
	$t = get_term_by( 'slug', $slug, 'pa_condition' );
	return $t ? (int) $t->count : 0;
};
?>
<section class="routes" id="routes" aria-labelledby="routes-h">
	<div class="wrap">
		<h2 class="sr-only" id="routes-h">Neu oder Refurbished</h2>
		<div class="route-grid">
			<a class="route route-new" href="<?php echo esc_url( add_query_arg( 'filter_condition', 'new', $shop ) ); ?>">
				<span class="route-body">
					<span class="route-kicker">Neu</span>
					<b>Neue Geräte</b>
					<span>Originalverpackt, wo im Angebot angegeben.</span>
				</span>
				<em><?php echo (int) $count( 'new' ); ?> Geräte <span aria-hidden="true">&rarr;</span></em>
			</a>
			<a class="route route-ref" href="<?php echo esc_url( add_query_arg( 'filter_condition', 'refurbished', $shop ) ); ?>">
				<span class="route-body">
					<span class="route-kicker">Refurbished</span>
					<b>Refurbished-Geräte</b>
					<span>Geprüft, gereinigt, Daten gelöscht, Akku gemessen.</span>
				</span>
				<em><?php echo (int) $count( 'refurbished' ); ?> Geräte <span aria-hidden="true">&rarr;</span></em>
			</a>
		</div>
	</div>
</section>
