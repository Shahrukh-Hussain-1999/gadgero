<?php
/**
 * Homepage: approved section order, with the new / refurbished routes added after the trust strip.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main">
	<?php
	get_template_part( 'template-parts/home/hero' );
	get_template_part( 'template-parts/home/trust' );
	get_template_part( 'template-parts/home/routes' );
	get_template_part( 'template-parts/home/shop' );
	get_template_part( 'template-parts/home/about' );
	get_template_part( 'template-parts/home/buyback' );
	?>
</main>
<?php
get_footer();
