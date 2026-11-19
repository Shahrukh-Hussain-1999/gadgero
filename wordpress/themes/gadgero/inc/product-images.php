<?php
/**
 * Product image sync. Compressed photos live in assets/product-images/ as "<PRODUCT CODE>--<colour-slug>.webp"
 * (codes from inc/catalogue.php, e.g. "S24U--titanium-blue.webp"). Whenever that folder changes (new files after a
 * git pull), the images are imported into the media library and set on every variation of that colour; the first
 * colour with a photo becomes the product's main image. Runs in small batches, like the catalogue migration.
 *
 * Source / usage rights of each batch: docs/IMAGE-SOURCES.md.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'gadgero_sync_product_images', 120 );

function gadgero_product_image_dir() {
	return get_template_directory() . '/assets/product-images';
}

/** Files in the folder, as [ 'S24U--titanium-blue.webp' => mtime ]. */
function gadgero_product_image_files() {
	$out = array();
	foreach ( (array) glob( gadgero_product_image_dir() . '/*--*.{webp,jpg,jpeg,png}', GLOB_BRACE ) as $f ) {
		$out[ basename( $f ) ] = filemtime( $f );
	}
	ksort( $out );
	return $out;
}

/** Product ID by catalogue code (e.g. "S24U"). */
function gadgero_product_by_code( $code ) {
	foreach ( gadgero_catalogue_v3() as $d ) {
		if ( $d['code'] === $code ) {
			return gadgero_mig_product_any( $d['n'] );
		}
	}
	return 0;
}

/**
 * @param bool $force Run everything now (WP-CLI); otherwise max. 6 imports per request.
 * @return bool true when all files are in sync.
 */
function gadgero_sync_product_images( $force = false ) {
	if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'gadgero_catalogue_v3' ) ) {
		return false;
	}
	if ( (int) get_option( 'gadgero_db_version', 1 ) < 3 ) {
		return false; // catalogue first
	}
	$files = gadgero_product_image_files();
	$hash  = md5( wp_json_encode( $files ) );
	if ( ! $force && get_option( 'gadgero_img_hash' ) === $hash ) {
		return true;
	}
	if ( ! $force && get_transient( 'gadgero_img_sync' ) ) {
		return false;
	}
	set_transient( 'gadgero_img_sync', 1, 120 );
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$done   = (array) get_option( 'gadgero_img_done', array() ); // filename => attachment id
	$budget = $force ? PHP_INT_MAX : 6;
	$todo   = false;
	foreach ( $files as $file => $mtime ) {
		$key = $file . '@' . $mtime;
		if ( isset( $done[ $key ] ) && get_post( $done[ $key ] ) ) {
			continue;
		}
		if ( $budget-- <= 0 ) {
			$todo = true;
			break;
		}
		[ $code, $colour ] = explode( '--', pathinfo( $file, PATHINFO_FILENAME ), 2 );
		$pid = gadgero_product_by_code( $code );
		$p   = $pid ? wc_get_product( $pid ) : null;
		$term = get_term_by( 'slug', $colour, 'pa_colour' );
		if ( ! $p || ! $term ) {
			error_log( "Gadgero images: no product/colour for $file" ); // phpcs:ignore
			$done[ $key ] = 0;
			continue;
		}
		$tmp = wp_tempnam( $file );
		copy( gadgero_product_image_dir() . '/' . $file, $tmp );
		$alt = $p->get_name() . ' ' . $term->name;
		$aid = media_handle_sideload( array( 'name' => sanitize_file_name( strtolower( $p->get_name() . '-' . $colour ) ) . '.' . pathinfo( $file, PATHINFO_EXTENSION ), 'tmp_name' => $tmp ), $pid, $alt );
		if ( is_wp_error( $aid ) ) {
			error_log( "Gadgero images: $file: " . $aid->get_error_message() ); // phpcs:ignore
			continue;
		}
		update_post_meta( $aid, '_wp_attachment_image_alt', $alt );
		update_post_meta( $aid, '_gadgero_src', $file );
		// Replace an older import of the same file.
		foreach ( $done as $k => $old ) {
			if ( $old && 0 === strpos( $k, $file . '@' ) && (int) $old !== (int) $aid ) {
				wp_delete_attachment( $old, true );
				unset( $done[ $k ] );
			}
		}
		$done[ $key ] = $aid;
		foreach ( $p->get_children() as $vid ) {
			$v = wc_get_product( $vid );
			if ( $v && $colour === ( $v->get_attributes()['pa_colour'] ?? '' ) ) {
				$v->set_image_id( $aid );
				$v->save();
			}
		}
		gadgero_set_main_image( $p );
	}
	update_option( 'gadgero_img_done', $done, false );
	if ( ! $todo ) {
		update_option( 'gadgero_img_hash', $hash );
		wc_delete_product_transients();
	}
	delete_transient( 'gadgero_img_sync' );
	return ! $todo;
}

/** Main image = photo of the first colour (catalogue order) that has one. */
function gadgero_set_main_image( WC_Product $p ) {
	$p      = wc_get_product( $p->get_id() );
	$attrs  = $p->get_attributes();
	$colour = $attrs['pa_colour'] ?? null;
	if ( ! $colour ) {
		return;
	}
	$by_colour = array();
	foreach ( $p->get_children() as $vid ) {
		$v = wc_get_product( $vid );
		if ( $v && $v->get_image_id() ) {
			$by_colour[ $v->get_attributes()['pa_colour'] ?? '' ] = $v->get_image_id();
		}
	}
	foreach ( $colour->get_options() as $term_id ) {
		$t = get_term( $term_id );
		if ( $t && ! empty( $by_colour[ $t->slug ] ) ) {
			if ( (int) $p->get_image_id() !== (int) $by_colour[ $t->slug ] ) {
				$p->set_image_id( $by_colour[ $t->slug ] );
				$p->save();
			}
			return;
		}
	}
}
