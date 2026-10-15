<?php
/**
 * Strip WordPress default front-end output that this theme does not use.
 *
 * Anchor Framework ships its own CSS framework (Bootstrap, Swiper, etc.) and
 * renders page bodies raw via {@see ac_render_raw_content()}. Core's block
 * editor stylesheets, theme.json global styles, classic-theme CSS, emoji
 * assets, SVG duotone filters, and the wpautop/wptexturize content filters
 * all inject markup/CSS we neither need nor want fighting our own styles.
 *
 * Everything here is scoped to the front end only — the block editor, admin,
 * and the theme's own Monaco/AI tooling keep their styles intact.
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dequeue core block + classic default stylesheets on the front end.
 *
 * Runs late (priority 100) so it fires after WordPress core has enqueued
 * these handles. Both dequeue + deregister so nothing can re-pull them as a
 * dependency.
 */
function ac_dequeue_default_styles() {
	if ( is_admin() ) {
		return;
	}

	$handles = array(
		'wp-block-library',       // Gutenberg block styles.
		'wp-block-library-theme', // Block theme-specific styles.
		'global-styles',          // theme.json-generated inline CSS custom properties.
		'classic-theme-styles',   // WP 6.1+ default button/gallery/layout CSS.
		'wc-blocks-style',        // WooCommerce blocks CSS (harmless no-op if WC absent).
		'wp-emoji-styles',        // Emoji <style> block.
	);

	foreach ( $handles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'ac_dequeue_default_styles', 100 );

/**
 * Stop core from generating theme.json global styles + SVG duotone filters
 * on the front end. Removing the actions means the CSS/SVG is never built,
 * which is cheaper than dequeuing after the fact.
 */
function ac_remove_global_styles_output() {
	if ( is_admin() ) {
		return;
	}

	// The enqueued <style id="global-styles-inline-css">.
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	// The inline custom-property block printed in <head>.
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
	// The hidden <svg> duotone filter presets dumped into the page body.
	remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
	remove_action( 'in_footer', 'wp_global_styles_render_svg_filters' );
}
add_action( 'init', 'ac_remove_global_styles_output' );

/**
 * Disable the WordPress emoji detection script + supporting assets.
 *
 * Removes the front-end and admin emoji <script>/<style>, the DNS prefetch
 * hint for s.w.org, the feed/email staticizers, and the TinyMCE plugin.
 */
function ac_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'tiny_mce_plugins', 'ac_disable_emojis_tinymce' );
	add_filter( 'wp_resource_hints', 'ac_disable_emojis_dns_prefetch', 10, 2 );
}
add_action( 'init', 'ac_disable_emojis' );

/**
 * Remove the emoji plugin from the TinyMCE plugin list.
 *
 * @param array $plugins TinyMCE plugins.
 * @return array Filtered plugins.
 */
function ac_disable_emojis_tinymce( $plugins ) {
	return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
}

/**
 * Drop the s.w.org emoji DNS-prefetch resource hint.
 *
 * @param array  $urls          Resource hint URLs.
 * @param string $relation_type Hint relation type.
 * @return array Filtered URLs.
 */
function ac_disable_emojis_dns_prefetch( $urls, $relation_type ) {
	if ( 'dns-prefetch' === $relation_type ) {
		$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/' );
		$urls          = array_diff( $urls, array( $emoji_svg_url ) );
	}
	return $urls;
}

/**
 * Turn off wpautop / wptexturize so WordPress stops auto-inserting <p>/<br>
 * tags and "smart" punctuation into rendered content.
 *
 * The theme's own pages render through {@see ac_render_raw_content()} which
 * already skips these, but stripping them globally keeps the_content(),
 * the_excerpt(), and comment output equally untouched.
 */
function ac_disable_content_formatting() {
	remove_filter( 'the_content', 'wpautop' );
	remove_filter( 'the_content', 'wptexturize' );
	remove_filter( 'the_excerpt', 'wpautop' );
	remove_filter( 'the_excerpt', 'wptexturize' );
	remove_filter( 'comment_text', 'wpautop' );
	remove_filter( 'comment_text', 'wptexturize' );
	remove_filter( 'widget_text_content', 'wpautop' );
	remove_filter( 'widget_text_content', 'wptexturize' );
}
add_action( 'init', 'ac_disable_content_formatting' );
