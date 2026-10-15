<?php
/**
 * Swap simple built-in icon SVGs for Font Awesome glyphs on the front end.
 *
 * Font Awesome (6.6) is already enqueued, and FA glyphs are colored by the CSS
 * `color` property — so rendering these icons as <i> elements lets them inherit
 * the theme's --accent-color (and follow per-client color changes), instead of
 * being baked into a sandboxed <img> SVG that can't read the page's variables.
 *
 * Only <img> icons embedded in page content are swapped (via the ac_raw_content
 * filter). The original SVG files are left in place as a fallback, and the page
 * content stored in the database is never modified.
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Map of icon SVG filename => [ Font Awesome classes, intended pixel size ].
 *
 * Size mirrors the SVG's intrinsic width so the glyph renders at a comparable
 * scale without needing per-location CSS. Extend this map to cover more icons.
 *
 * @return array<string,array{0:string,1:int}>
 */
function ac_fa_icon_map() {
	return apply_filters(
		'ac_fa_icon_map',
		array(
			'arrow-accent.svg'       => array( 'fa-solid fa-arrow-right', 31 ),
			'icon-phone-info.svg'    => array( 'fa-solid fa-phone', 60 ),
			'icon-location-info.svg' => array( 'fa-solid fa-location-dot', 60 ),
			'icon-watch-info.svg'    => array( 'fa-solid fa-clock', 61 ),
			'icon-quote.svg'         => array( 'fa-solid fa-quote-left', 51 ),
			// CTA "phone" icon rendered as a primary-colored circle with a white glyph.
			'icon-faqs-cta-box.svg'  => array( 'fa-solid fa-phone ac-fa-circle', 60 ),
		)
	);
}

/**
 * Replace <img> tags pointing at mapped icon SVGs with Font Awesome <i> elements.
 *
 * Matches any <img> whose src ends in one of the mapped filenames, regardless of
 * the rest of the URL (theme assets path or Media Library URL) or attributes.
 *
 * @param string $html Rendered HTML.
 * @return string
 */
function ac_fa_replace_imgs( $html ) {
	if ( false === strpos( $html, '<img' ) ) {
		return $html;
	}
	$map = ac_fa_icon_map();

	return preg_replace_callback(
		'/<img\b[^>]*?\bsrc=("|\')([^"\']+?)\1[^>]*>/i',
		function ( $m ) use ( $map ) {
			$src  = $m[2];
			$file = basename( wp_parse_url( $src, PHP_URL_PATH ) );
			if ( ! isset( $map[ $file ] ) ) {
				return $m[0]; // Not a mapped icon — leave the <img> untouched.
			}
			list( $fa, $size ) = $map[ $file ];

			// Preserve any existing class on the original <img>.
			$extra = '';
			if ( preg_match( '/\bclass=("|\')(.*?)\1/i', $m[0], $cm ) ) {
				$extra = ' ' . trim( $cm[2] );
			}

			return sprintf(
				'<i class="ac-fa %s%s" style="font-size:%dpx" aria-hidden="true"></i>',
				esc_attr( $fa ),
				esc_attr( $extra ),
				(int) $size
			);
		},
		$html
	);
}
add_filter( 'ac_raw_content', 'ac_fa_replace_imgs' );

/**
 * Baseline styling for swapped icons: themeable accent color + sensible alignment.
 */
function ac_fa_inline_css() {
	if ( is_admin() ) {
		return;
	}
	echo "<style id=\"ac-fa-icons\">"
		. ".ac-fa{color:var(--accent-color);line-height:1;vertical-align:middle;display:inline-block;}"
		// Circular icon box: primary-color disc, white glyph centered. font-size = circle diameter.
		. ".ac-fa-circle{color:var(--white-color);background-color:var(--primary-color);border-radius:50%;width:1em;height:1em;display:inline-flex;align-items:center;justify-content:center;}"
		. ".ac-fa-circle::before{font-size:.45em;}"
		. "</style>\n";
}
add_action( 'wp_head', 'ac_fa_inline_css', 101 );
