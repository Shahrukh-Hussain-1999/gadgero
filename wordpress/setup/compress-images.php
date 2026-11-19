<?php
/**
 * Compress supplied product photos into the theme's product-images folder.
 * Usage: wordpress/bin/wp eval-file wordpress/setup/compress-images.php <source-dir> <map-file>
 * map-file lines: "<source file> = <PRODUCT CODE>--<colour-slug>"   e.g. "6.webp = S24U--titanium-violet"
 * Output: max 1000 × 1000 px, WebP quality 80.
 */
if ( ! defined( 'WP_CLI' ) ) {
	exit( 1 );
}
[ $src_dir, $map_file ] = $args;
$out = get_theme_root() . '/gadgero/assets/product-images';
wp_mkdir_p( $out );
foreach ( file( $map_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) as $line ) {
	if ( '#' === $line[0] ) {
		continue;
	}
	[ $file, $target ] = array_map( 'trim', explode( '=', $line, 2 ) );
	$editor = wp_get_image_editor( rtrim( $src_dir, '/\\' ) . '/' . $file );
	if ( is_wp_error( $editor ) ) {
		WP_CLI::warning( "$file: " . $editor->get_error_message() );
		continue;
	}
	$editor->resize( 1000, 1000, false );
	$editor->set_quality( 80 );
	$dest = "$out/$target.webp";
	$res  = $editor->save( $dest, 'image/webp' );
	if ( is_wp_error( $res ) ) {
		WP_CLI::warning( "$file: " . $res->get_error_message() );
		continue;
	}
	WP_CLI::log( sprintf( '%-10s -> %-34s %4d KB (from %d KB)', $file, basename( $dest ), filesize( $dest ) / 1024, filesize( rtrim( $src_dir, '/\\' ) . '/' . $file ) / 1024 ) );
}
