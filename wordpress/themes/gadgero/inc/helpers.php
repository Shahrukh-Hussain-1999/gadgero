<?php
/**
 * Small helpers shared by templates.
 *
 * @package Gadgero
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin-only marker for missing client data. Visitors see nothing; logged-in shop managers see a red chip,
 * so gaps are obvious while reviewing the local build but never leak to customers.
 */
function gadgero_flag( $what ) {
	if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
		return '';
	}
	return '<span class="cc-flag" title="' . esc_attr__( 'Only visible to logged-in shop managers', 'gadgero' ) . '">CLIENT CONFIRMATION REQUIRED: ' . esc_html( $what ) . '</span>';
}

/** Theme option (Customizer theme_mod) with trimming. */
function gadgero_opt( $key, $default = '' ) {
	$v = get_theme_mod( $key, $default );
	return is_string( $v ) ? trim( $v ) : $v;
}

/** WhatsApp number in international format without + or spaces, or '' if not configured. */
function gadgero_wa_number() {
	return preg_replace( '/\D+/', '', (string) gadgero_opt( 'gadgero_whatsapp' ) );
}

/** wa.me link with a prefilled message. Without a configured number WhatsApp opens its own chat picker. */
function gadgero_wa_link( $text = '' ) {
	$n = gadgero_wa_number();
	return 'https://wa.me/' . $n . ( $text !== '' ? '?text=' . rawurlencode( $text ) : '' );
}

/** Pretty-print the WhatsApp number for display: +49 151 23456789 style grouping is not guessed, digits are shown as entered. */
function gadgero_wa_display() {
	$raw = (string) gadgero_opt( 'gadgero_whatsapp' );
	return $raw;
}

/** Lines of a textarea option, empty lines removed. */
function gadgero_lines( $key ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/', (string) gadgero_opt( $key ) ) ) ) );
}


/** Sanitise a CSS hex colour, falling back to a default. */
function gadgero_hex( $c, $fallback ) {
	$c = sanitize_hex_color( (string) $c );
	return $c ? $c : $fallback;
}

/**
 * Device illustration from the approved static design (dev() in the original index.html), used when a product has
 * no photo yet. Types: phone, laptop, tablet, desktop, mini.
 */
function gadgero_device_svg( $type, $c1 = '#35c4ff', $c2 = '#0a3fb8', $frame = '#b7c3d9', $label = '' ) {
	static $uid = 0;
	++$uid;
	$c1    = gadgero_hex( $c1, '#35c4ff' );
	$c2    = gadgero_hex( $c2, '#0a3fb8' );
	$frame = gadgero_hex( $frame, '#b7c3d9' );
	$g     = 'gg' . $uid;
	$s     = 'gs' . $uid;
	$c     = 'gc' . $uid;
	$clips = array(
		'phone'   => 'x="13" y="13" width="174" height="374" rx="31"',
		'laptop'  => 'x="50" y="18" width="300" height="184" rx="8"',
		'tablet'  => 'x="13" y="13" width="294" height="214" rx="17"',
		'desktop' => 'x="28" y="18" width="304" height="166" rx="3"',
	);
	$clip  = $clips[ $type ] ?? 'x="0" y="0" width="1" height="1"';
	$aria  = $label !== '' ? ' role="img" aria-label="' . esc_attr( $label ) . '"' : ' aria-hidden="true"';
	$defs  = '<defs><clipPath id="' . $c . '"><rect ' . $clip . '/></clipPath><linearGradient id="' . $g . '" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="' . $c1 . '"/><stop offset="1" stop-color="' . $c2 . '"/></linearGradient>'
		. '<linearGradient id="' . $s . '" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".35"/><stop offset=".5" stop-color="#fff" stop-opacity="0"/></linearGradient></defs>';
	$w     = 'fill="url(#' . $g . ')"';
	$f     = $frame;

	switch ( $type ) {
		case 'phone':
			return '<svg viewBox="0 0 200 400"' . $aria . '>' . $defs
				. '<rect x="2" y="70" width="3" height="30" rx="1.5" fill="' . $f . '"/><rect x="195" y="110" width="3" height="52" rx="1.5" fill="' . $f . '"/>'
				. '<rect x="6" y="6" width="188" height="388" rx="38" fill="' . $f . '"/><rect x="10" y="10" width="180" height="380" rx="34" fill="#050505"/>'
				. '<rect x="13" y="13" width="174" height="374" rx="31" ' . $w . '/>'
				. '<g clip-path="url(#' . $c . ')"><circle cx="150" cy="300" r="90" fill="#fff" fill-opacity=".09"/><circle cx="40" cy="360" r="70" fill="#000" fill-opacity=".18"/></g>'
				. '<rect x="72" y="24" width="56" height="16" rx="8" fill="#000"/>'
				. '<text x="100" y="120" text-anchor="middle" font-family="Sora,Arial,sans-serif" font-weight="600" font-size="52" fill="#fff" fill-opacity=".95">9:41</text>'
				. '<rect x="84" y="376" width="32" height="4" rx="2" fill="#fff" fill-opacity=".8"/>'
				. '<rect x="13" y="13" width="174" height="374" rx="31" fill="url(#' . $s . ')"/></svg>';
		case 'laptop':
			return '<svg viewBox="0 0 400 260"' . $aria . '>' . $defs
				. '<rect x="42" y="10" width="316" height="200" rx="14" fill="' . $f . '"/><rect x="47" y="15" width="306" height="190" rx="10" fill="#050505"/>'
				. '<rect x="50" y="18" width="300" height="184" rx="8" ' . $w . '/>'
				. '<g clip-path="url(#' . $c . ')"><circle cx="290" cy="170" r="80" fill="#fff" fill-opacity=".1"/></g>'
				. '<rect x="50" y="18" width="300" height="10" fill="#000" fill-opacity=".25"/><rect x="186" y="18" width="28" height="8" rx="4" fill="#000"/>'
				. '<rect x="84" y="56" width="150" height="86" rx="7" fill="#fff" fill-opacity=".16"/><rect x="96" y="70" width="70" height="6" rx="3" fill="#fff" fill-opacity=".6"/><rect x="96" y="84" width="118" height="4" rx="2" fill="#fff" fill-opacity=".35"/><rect x="96" y="94" width="104" height="4" rx="2" fill="#fff" fill-opacity=".35"/>'
				. '<rect x="150" y="186" width="100" height="12" rx="6" fill="#fff" fill-opacity=".22"/>'
				. '<path d="M12 212h376l-8 12a8 8 0 0 1-7 4H27a8 8 0 0 1-7-4z" fill="' . $f . '"/><rect x="12" y="210" width="376" height="5" rx="2" fill="#000" fill-opacity=".25"/><rect x="168" y="212" width="64" height="5" rx="2.5" fill="#000" fill-opacity=".35"/>'
				. '<rect x="50" y="18" width="300" height="184" rx="8" fill="url(#' . $s . ')"/></svg>';
		case 'tablet':
			return '<svg viewBox="0 0 320 240"' . $aria . '>' . $defs
				. '<rect x="4" y="4" width="312" height="232" rx="24" fill="' . $f . '"/><rect x="8" y="8" width="304" height="224" rx="21" fill="#050505"/>'
				. '<rect x="13" y="13" width="294" height="214" rx="17" ' . $w . '/>'
				. '<g clip-path="url(#' . $c . ')"><circle cx="240" cy="190" r="86" fill="#fff" fill-opacity=".1"/></g>'
				. '<circle cx="160" cy="19" r="2.5" fill="#000"/>'
				. '<rect x="40" y="52" width="100" height="62" rx="10" fill="#fff" fill-opacity=".2"/><rect x="152" y="52" width="128" height="62" rx="10" fill="#fff" fill-opacity=".14"/><rect x="40" y="126" width="240" height="46" rx="10" fill="#fff" fill-opacity=".12"/>'
				. '<rect x="13" y="13" width="294" height="214" rx="17" fill="url(#' . $s . ')"/></svg>';
		case 'desktop':
			return '<svg viewBox="0 0 360 320"' . $aria . '>' . $defs
				. '<rect x="16" y="6" width="328" height="226" rx="14" fill="' . $f . '"/><rect x="26" y="16" width="308" height="170" rx="4" fill="#050505"/>'
				. '<rect x="28" y="18" width="304" height="166" rx="3" ' . $w . '/><g clip-path="url(#' . $c . ')"><circle cx="270" cy="150" r="70" fill="#fff" fill-opacity=".1"/></g>'
				. '<rect x="28" y="18" width="304" height="9" fill="#000" fill-opacity=".25"/><rect x="60" y="48" width="130" height="76" rx="6" fill="#fff" fill-opacity=".16"/><rect x="72" y="60" width="60" height="6" rx="3" fill="#fff" fill-opacity=".6"/><rect x="72" y="74" width="100" height="4" rx="2" fill="#fff" fill-opacity=".35"/>'
				. '<rect x="140" y="170" width="80" height="10" rx="5" fill="#fff" fill-opacity=".22"/>'
				. '<rect x="16" y="190" width="328" height="42" fill="' . $f . '"/><rect x="16" y="190" width="328" height="42" fill="#fff" fill-opacity=".1"/>'
				. '<path d="M16 220v0a12 12 0 0 0 12 12h304a12 12 0 0 0 12-12z" fill="' . $f . '"/>'
				. '<path d="M158 232h44l10 62h-64z" fill="' . $f . '"/><path d="M158 232h44l10 62h-64z" fill="#000" fill-opacity=".14"/><rect x="118" y="292" width="124" height="10" rx="5" fill="' . $f . '"/>'
				. '<rect x="28" y="18" width="304" height="166" rx="3" fill="url(#' . $s . ')"/></svg>';
		case 'mini':
			return '<svg viewBox="0 0 260 150"' . $aria . '>' . $defs
				. '<rect x="10" y="44" width="240" height="84" rx="22" fill="#000" fill-opacity=".35"/>'
				. '<rect x="10" y="30" width="240" height="90" rx="22" fill="' . $f . '"/><rect x="10" y="30" width="240" height="90" rx="22" fill="url(#' . $s . ')"/>'
				. '<rect x="10" y="94" width="240" height="26" rx="13" fill="#000" fill-opacity=".14"/><circle cx="130" cy="108" r="3" fill="#fff" fill-opacity=".8"/></svg>';
	}
	return '';
}

/** SVG icons (stroke style from the approved design). */
function gadgero_icon( $name ) {
	$p = array(
		'phone'   => '<rect x="7" y="2.5" width="10" height="19" rx="2.5"/><path d="M11 18.5h2"/>',
		'laptop'  => '<rect x="4" y="5" width="16" height="11" rx="1.5"/><path d="M2 19h20"/>',
		'tablet'  => '<rect x="4" y="3" width="16" height="18" rx="2.5"/><path d="M11 18h2"/>',
		'watch'   => '<rect x="7" y="6" width="10" height="12" rx="3"/><path d="M9 6l.6-3h4.8l.6 3M9 18l.6 3h4.8l.6-3"/>',
		'buds'    => '<circle cx="9" cy="8" r="3.6"/><path d="M12.6 8v9.4a1.6 1.6 0 0 1-3.2 0"/><circle cx="17.5" cy="10" r="2.2"/>',
		'shield'  => '<path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6z"/><path d="M9 12l2 2 4-4"/>',
		'tag'     => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1.2"/>',
		'support' => '<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="3" y="14" width="4" height="6" rx="1.5"/><rect x="17" y="14" width="4" height="6" rx="1.5"/><path d="M19 20c0 1.5-2 2-5 2"/>',
		'box'     => '<path d="M3 7.5L12 3l9 4.5v9L12 21l-9-4.5z"/><path d="M3 7.5l9 4.5 9-4.5M12 12v9"/>',
		'refresh' => '<path d="M20 11a8 8 0 0 0-14.6-4.5L4 8"/><path d="M4 3v5h5"/><path d="M4 13a8 8 0 0 0 14.6 4.5L20 16"/><path d="M20 21v-5h-5"/>',
		'chat'    => '<path d="M4 19l1.4-4A8 8 0 1 1 9 18.6z"/>',
		'arrow'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'menu'    => '<path d="M4 7h16M4 12h16M4 17h16"/>',
	);
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $p[ $name ] ?? '' ) . '</svg>';
}
