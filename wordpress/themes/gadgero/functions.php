<?php
/**
 * Gadgero theme bootstrap.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

define( 'GADGERO_VERSION', '1.0.0' );
define( 'GADGERO_DIR', get_template_directory() );
define( 'GADGERO_URI', get_template_directory_uri() );

require GADGERO_DIR . '/inc/helpers.php';
require GADGERO_DIR . '/inc/setup.php';
require GADGERO_DIR . '/inc/customizer.php';
require GADGERO_DIR . '/inc/shortcodes.php';

if ( class_exists( 'WooCommerce' ) ) {
	require GADGERO_DIR . '/inc/woocommerce.php';
	require GADGERO_DIR . '/inc/product-fields.php';
	require GADGERO_DIR . '/inc/admin-flags.php';
}
