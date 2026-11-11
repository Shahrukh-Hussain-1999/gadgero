<?php
/**
 * Fallback template.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="page-main">
	<section class="page-head"><div class="wrap"><h1 class="sec"><?php echo is_404() ? esc_html__( 'Page not found', 'gadgero' ) : esc_html( wp_get_document_title() ); ?></h1></div></section>
	<section class="page-body">
		<div class="wrap prose">
			<?php if ( is_404() ) : ?>
				<p><?php esc_html_e( 'This page does not exist or has moved.', 'gadgero' ); ?></p>
				<p><a class="solid" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'Go to the shop', 'gadgero' ); ?></a></p>
			<?php else : ?>
				<?php
				while ( have_posts() ) {
					the_post();
					echo '<article><h2><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2>';
					the_excerpt();
					echo '</article>';
				}
				the_posts_pagination();
				?>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
