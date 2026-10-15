<?php
/**
 * Anchor Framework theme bootstrap — brand-agnostic engine (parent theme).
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AC_THEME_VERSION', '1.2.1' );
define( 'AC_THEME_DIR', get_template_directory() );
define( 'AC_THEME_URI', get_template_directory_uri() );

/**
 * Basic theme supports.
 */
function ac_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	// Nav + footer are hardcoded in the templates (no WP menu locations / walkers).
}
add_action( 'after_setup_theme', 'ac_theme_setup' );

require_once AC_THEME_DIR . '/inc/disable-defaults.php';
require_once AC_THEME_DIR . '/inc/enqueue.php';
require_once AC_THEME_DIR . '/inc/colors.php';
require_once AC_THEME_DIR . '/inc/editor-monaco.php';
require_once AC_THEME_DIR . '/inc/admin-file-editor-undo.php';
require_once AC_THEME_DIR . '/inc/import-media.php';
require_once AC_THEME_DIR . '/inc/import-content.php';
require_once AC_THEME_DIR . '/inc/page-assets.php';
require_once AC_THEME_DIR . '/inc/social-icons.php';
require_once AC_THEME_DIR . '/inc/ai-settings.php';
require_once AC_THEME_DIR . '/inc/ai-tools.php';
require_once AC_THEME_DIR . '/inc/ai-providers.php';
require_once AC_THEME_DIR . '/inc/ai-endpoint.php';
require_once AC_THEME_DIR . '/inc/fa-icons.php';
require_once AC_THEME_DIR . '/inc/blog-slider.php';
require_once AC_THEME_DIR . '/inc/contact-sidebar.php';
require_once AC_THEME_DIR . '/inc/service-taxonomy.php';
require_once AC_THEME_DIR . '/inc/services-cards.php';
require_once AC_THEME_DIR . '/inc/featured-projects.php';
require_once AC_THEME_DIR . '/inc/service-testimonials.php';
require_once AC_THEME_DIR . '/inc/seo-schema.php';
require_once AC_THEME_DIR . '/inc/updater.php'; // GitHub releases → WP updates

/**
 * Output a post's raw HTML body verbatim — shortcodes run, but wpautop/wptexturize do not.
 *
 * @param int $post_id Post ID (default current post).
 */
function ac_render_raw_content( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$raw     = get_post_field( 'post_content', $post_id );
	// Run shortcodes + embeds but skip the paragraph/texturize mangling.
	$out = do_shortcode( $raw );
	/**
	 * Filter the rendered raw page body before output (e.g. swap icon SVGs for Font Awesome).
	 *
	 * @param string $out     Rendered HTML.
	 * @param int    $post_id Post ID.
	 */
	$out = apply_filters( 'ac_raw_content', $out, $post_id );
	echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentional raw HTML page body.
}

/**
 * Render a shared page section as an Anchor Block, with per-page override.
 *
 * Resolution for ac_block( 'cta' ) on a page whose slug is "roofing":
 *   1. anchor_block slug "roofing-cta"  — per-page override (exists only if you create it;
 *      an EMPTY override block hides the section on that page).
 *   2. anchor_block slug "service-cta"  — the global section, edited once for every page.
 *
 * Edit the section everywhere → edit the "service-*" block in Anchor Blocks.
 * Change/remove it on one page → create a "{page-slug}-{name}" block.
 * Use name "extra" for a per-page full-width slot ("{page-slug}-extra"); no global default.
 *
 * @param string   $name Section name (sidebar|process|reviews|cta|extra|…).
 * @param int|null $post Page ID (defaults to the current post).
 * @return string Rendered HTML ('' when no matching block exists).
 */
function ac_block( $name, $post = null ) {
	$post = $post ? get_post( $post ) : get_post();
	$slug = 'service-' . $name; // Global default.
	if ( $post && $post->post_name ) {
		$override = get_page_by_path( $post->post_name . '-' . $name, OBJECT, 'anchor_block' );
		if ( $override instanceof WP_Post && 'publish' === $override->post_status ) {
			$slug = $override->post_name;
		}
	}
	return do_shortcode( '[anchor_block slug="' . esc_attr( $slug ) . '"]' );
}

/**
 * Default hero background, used when a page has no featured image and no _hero_bg,
 * and for the blog archive/single header.
 *
 * Brand-agnostic like the rest of the parent theme: it returns '' by default and the
 * active child theme supplies the real photo via the `ac_default_hero_bg` filter.
 * Returning '' just means the hero renders on its scrim colour, which is fine.
 *
 * @param string $context Where it is being used: 'blog'|'page'|'service'.
 * @return string Image URL, or '' for no background image.
 */
function ac_default_hero_bg( $context = 'page' ) {
	return (string) apply_filters( 'ac_default_hero_bg', '', $context );
}

/**
 * URL of the contact page — where the header's "Free Estimate" CTA sends people.
 *
 * Resolved by slug so it follows the page if it is moved or renested, with a
 * filter (`ac_contact_url`) for sites whose contact page lives elsewhere.
 *
 * @return string
 */
function ac_contact_url() {
	$page = get_page_by_path( 'contact-us' );
	$url  = ( $page instanceof WP_Post && 'publish' === $page->post_status )
		? get_permalink( $page )
		: home_url( '/contact-us/' );
	return (string) apply_filters( 'ac_contact_url', $url );
}

/**
 * `[ac_sms_href]` — an sms: link built from the Site Config phone number.
 *
 * Anchor Tools ships [config_phone] and [config_phone_href] (tel:) but no SMS
 * equivalent, which is why the contact page had a texting number hardcoded — it
 * would have gone stale the moment the client's number changed in Site Config.
 *
 * @return string e.g. "sms:+13025070032", or '' when no phone is configured.
 */
function ac_sms_href_shortcode() {
	$digits = preg_replace( '/\D+/', '', do_shortcode( '[config_phone]' ) );
	if ( '' === $digits ) {
		return '';
	}
	if ( 10 === strlen( $digits ) ) {
		$digits = '1' . $digits; // Assume NANP when no country code is present.
	}
	return 'sms:+' . $digits;
}
add_shortcode( 'ac_sms_href', 'ac_sms_href_shortcode' );
