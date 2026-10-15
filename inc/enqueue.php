<?php
/**
 * Front-end asset registration for Anchor Framework.
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL of a framework asset, letting the active child theme override it.
 *
 * A child theme that needs a different copy of a parent asset (its own tokens.css, a tweaked
 * custom.css, …) puts it at `{child}/assets/parent-overrides/{css|js}/{file}`. The parent then
 * enqueues that file under the SAME handle, in the SAME order — so the site keeps its styling
 * across framework updates without anyone editing the parent. The dedicated folder means a
 * child's own assets/css/*.css can never shadow a parent file by accident.
 *
 * @param string $rel Path under assets/, e.g. 'css/tokens.css'.
 * @return string
 */
function ac_parent_asset_url( $rel ) {
	$rel = ltrim( $rel, '/' );
	if ( get_stylesheet_directory() !== get_template_directory()
		&& is_file( get_stylesheet_directory() . '/assets/parent-overrides/' . $rel ) ) {
		return get_stylesheet_directory_uri() . '/assets/parent-overrides/' . $rel;
	}
	return AC_THEME_URI . '/assets/' . $rel;
}

/**
 * Enqueue the template's CSS and JS on the front end.
 */
function ac_enqueue_assets() {
	$css = 'css';
	$js  = 'js';
	$v   = AC_THEME_VERSION;
	// Optional effects a child theme can switch off (return false from the filter).
	$magic_cursor = (bool) apply_filters( 'ac_enable_magic_cursor', true );

	// Styles (custom.css last so it overrides vendor CSS).
	wp_enqueue_style( 'ac-bootstrap', ac_parent_asset_url( $css . '/bootstrap.min.css' ), array(), $v );
	wp_enqueue_style( 'ac-slicknav', ac_parent_asset_url( $css . '/slicknav.min.css' ), array(), $v );
	wp_enqueue_style( 'ac-swiper', ac_parent_asset_url( $css . '/swiper-bundle.min.css' ), array(), $v );
	wp_enqueue_style( 'ac-fontawesome', ac_parent_asset_url( $css . '/all.css' ), array(), $v );
	// Brand fonts (belt-and-suspenders with Site Config's own font link).
	wp_enqueue_style( 'ac-gfonts', 'https://fonts.googleapis.com/css2?family=Raleway:wght@500;600;700;800;900&family=Source+Sans+3:wght@400;500;600&family=Space+Mono:wght@400;700&display=swap', array(), null );
	wp_enqueue_style( 'ac-animate', ac_parent_asset_url( $css . '/animate.css' ), array(), $v );
	wp_enqueue_style( 'ac-magnific', ac_parent_asset_url( $css . '/magnific-popup.css' ), array(), $v );
	wp_enqueue_style( 'ac-twentytwenty', ac_parent_asset_url( $css . '/twentytwenty.css' ), array(), $v );
	wp_enqueue_style( 'ac-mousecursor', ac_parent_asset_url( $css . '/mousecursor.css' ), array(), $v );
	// tokens.css: L1 design tokens (colors, spacing, shadows, radii, transitions, asset URLs).
	wp_enqueue_style( 'ac-tokens', ac_parent_asset_url( $css . '/tokens.css' ), array(), $v );
	// framework.css: the vendor template + .section system (core). Tokens must load first.
	wp_enqueue_style( 'ac-framework', ac_parent_asset_url( $css . '/framework.css' ), array( 'ac-bootstrap', 'ac-tokens' ), $v );
	// primitives.css: L2 reusable primitives (canonical .u-subtitle etc.). Loads after framework.
	wp_enqueue_style( 'ac-primitives', ac_parent_asset_url( $css . '/primitives.css' ), array( 'ac-framework' ), $v );
	// custom.css: thin site personalization layer (loads after primitives so it wins).
	wp_enqueue_style( 'ac-custom', ac_parent_asset_url( $css . '/custom.css' ), array( 'ac-primitives' ), $v );
	wp_enqueue_style( 'ac-theme-style', get_stylesheet_uri(), array(), $v );
	// DEKA header: tokens + nav + super-header + call-bar (loads after theme styles).
	wp_enqueue_style( 'anchor-header', ac_parent_asset_url( $css . '/anchor-header.css' ), array( 'ac-custom' ), $v );
	// DEKA footer + expanding CTA (depends on anchor-header for the --anchor-* tokens).
	wp_enqueue_style( 'anchor-footer', ac_parent_asset_url( $css . '/anchor-footer.css' ), array( 'anchor-header' ), $v );
	// NB: the home-services visual skin (formerly marathon-home.css/js) is enqueued by
	// the active child theme at a later priority, so it loads after 'ac-custom'/'anchor-footer'.

	// Scripts. WP bundles jQuery; load it (footer) then the libs that depend on it.
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'anchor-nav', ac_parent_asset_url( $js . '/anchor-nav.js' ), array(), $v, true );
	wp_enqueue_script( 'anchor-cta', ac_parent_asset_url( $js . '/anchor-cta.js' ), array(), $v, true );
	wp_enqueue_script( 'ac-bootstrap', ac_parent_asset_url( $js . '/bootstrap.min.js' ), array( 'jquery' ), $v, true );
	wp_enqueue_script( 'ac-swiper', ac_parent_asset_url( $js . '/swiper-bundle.min.js' ), array(), $v, true );
	wp_enqueue_script( 'ac-gsap', ac_parent_asset_url( $js . '/gsap.min.js' ), array(), $v, true );
	wp_enqueue_script( 'ac-scrolltrigger', ac_parent_asset_url( $js . '/ScrollTrigger.min.js' ), array( 'ac-gsap' ), $v, true );
	wp_enqueue_script( 'ac-splittext', ac_parent_asset_url( $js . '/SplitText.js' ), array( 'ac-gsap' ), $v, true );
	wp_enqueue_script( 'ac-waypoints', ac_parent_asset_url( $js . '/jquery.waypoints.min.js' ), array( 'jquery' ), $v, true );
	wp_enqueue_script( 'ac-counterup', ac_parent_asset_url( $js . '/jquery.counterup.min.js' ), array( 'ac-waypoints' ), $v, true );
	wp_enqueue_script( 'ac-magnific', ac_parent_asset_url( $js . '/jquery.magnific-popup.min.js' ), array( 'jquery' ), $v, true );
	wp_enqueue_script( 'ac-slicknav', ac_parent_asset_url( $js . '/jquery.slicknav.js' ), array( 'jquery' ), $v, true );
	wp_enqueue_script( 'ac-ytplayer', ac_parent_asset_url( $js . '/jquery.mb.YTPlayer.min.js' ), array( 'jquery' ), $v, true );
	wp_enqueue_script( 'ac-eventmove', ac_parent_asset_url( $js . '/jquery.event.move.js' ), array( 'jquery' ), $v, true );
	wp_enqueue_script( 'ac-twentytwenty', ac_parent_asset_url( $js . '/jquery.twentytwenty.js' ), array( 'ac-eventmove' ), $v, true );
	wp_enqueue_script( 'ac-wow', ac_parent_asset_url( $js . '/wow.js' ), array(), $v, true );
	wp_enqueue_script( 'ac-smoothscroll', ac_parent_asset_url( $js . '/SmoothScroll.js' ), array(), $v, true );
	wp_enqueue_script( 'ac-parallaxie', ac_parent_asset_url( $js . '/parallaxie.js' ), array( 'jquery' ), $v, true );
	if ( $magic_cursor ) {
		wp_enqueue_script( 'ac-magiccursor', ac_parent_asset_url( $js . '/magiccursor.js' ), array( 'jquery' ), $v, true );
	}
	wp_enqueue_script( 'ac-validator', ac_parent_asset_url( $js . '/validator.min.js' ), array(), $v, true );
	$function_deps = array( 'jquery', 'ac-bootstrap', 'ac-swiper', 'ac-gsap', 'ac-scrolltrigger', 'ac-splittext', 'ac-waypoints', 'ac-counterup', 'ac-magnific', 'ac-slicknav', 'ac-ytplayer', 'ac-eventmove', 'ac-twentytwenty', 'ac-wow', 'ac-smoothscroll', 'ac-parallaxie' );
	if ( $magic_cursor ) {
		$function_deps[] = 'ac-magiccursor';
	}
	$function_deps[] = 'ac-validator';
	wp_enqueue_script(
		'ac-function',
		ac_parent_asset_url( $js . '/function.js' ),
		$function_deps,
		$v,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'ac_enqueue_assets' );

/**
 * Cache-bust theme assets by file modification time.
 *
 * Every asset above is enqueued with the static AC_THEME_VERSION, so editing a
 * file (framework.css, custom.css, function.js, …) never changes its URL and the
 * browser + Kinsta CDN keep serving the stale copy. This filter rewrites the
 * ?ver= on any CSS/JS served from THIS theme's folder to the file's last-modified
 * timestamp, so a save instantly busts the cache for that one file — no manual
 * version bump needed. Non-theme assets (core, plugins) are left untouched.
 *
 * @param string $src Asset URL.
 * @return string
 */
function ac_asset_cache_bust( $src ) {
	$src_path = wp_parse_url( $src, PHP_URL_PATH );
	if ( ! $src_path ) {
		return $src;
	}
	// Parent theme files and child-theme parent-overrides alike.
	foreach ( array( get_template_directory_uri() => get_template_directory(), get_stylesheet_directory_uri() => get_stylesheet_directory() ) as $uri => $dir ) {
		$theme_path = wp_parse_url( $uri, PHP_URL_PATH );
		if ( $theme_path && strpos( $src_path, $theme_path . '/' ) === 0 ) {
			$file = $dir . substr( $src_path, strlen( $theme_path ) );
			if ( is_file( $file ) ) {
				return add_query_arg( 'ver', filemtime( $file ), $src );
			}
		}
	}
	return $src; // Not one of our theme files — leave it alone.
}
add_filter( 'style_loader_src', 'ac_asset_cache_bust', 20 );
add_filter( 'script_loader_src', 'ac_asset_cache_bust', 20 );
