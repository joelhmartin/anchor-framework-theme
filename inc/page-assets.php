<?php
/**
 * Per-page custom CSS & JS: meta registration, save, and front-end output.
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post types that get per-page CSS/JS (mirrors the Monaco editor takeover).
 *
 * @return string[]
 */
function ac_page_assets_post_types() {
	return function_exists( 'ac_monaco_post_types' ) ? ac_monaco_post_types() : array( 'page' );
}

/**
 * Register the CSS/JS meta keys (auth gated to users who can edit the post).
 */
function ac_register_page_assets_meta() {
	foreach ( ac_page_assets_post_types() as $pt ) {
		foreach ( array( '_ac_custom_css', '_ac_custom_js' ) as $key ) {
			register_post_meta(
				$pt,
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'default'           => '',
					'show_in_rest'      => false,
					'sanitize_callback' => 'ac_sanitize_page_asset',
					'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
						return current_user_can( 'edit_post', $post_id );
					},
				)
			);
		}
	}
}
add_action( 'init', 'ac_register_page_assets_meta' );

/**
 * Sanitize stored CSS/JS: keep raw code, but neutralize closing-tag context breakouts.
 *
 * @param string $value Raw value.
 * @return string
 */
function ac_sanitize_page_asset( $value ) {
	$value = (string) $value;
	// Prevent breaking out of the <style>/<script> wrapper.
	$value = preg_replace( '#</\s*(style|script)#i', '<\\/$1', $value );
	return $value;
}

/**
 * Save CSS/JS from the editor on post save. Nonce + capability verified.
 *
 * @param int $post_id Post ID.
 */
function ac_save_page_assets( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['ac_editor_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ac_editor_nonce'] ) ), 'ac_editor_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$map = array(
		'ac_custom_css' => '_ac_custom_css',
		'ac_custom_js'  => '_ac_custom_js',
	);
	foreach ( $map as $field => $meta_key ) {
		if ( isset( $_POST[ $field ] ) ) {
			// wp_unslash only; do NOT sanitize_text_field (would strip the code). Meta sanitize_callback runs the breakout guard.
			// wp_slash() because update_post_meta() unslashes AGAIN: without it every backslash in the
			// code (regex \s, \d, escaped quotes) was stripped on every editor save (TMJ 2026-10-10).
			update_post_meta( $post_id, $meta_key, wp_slash( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
}
add_action( 'save_post', 'ac_save_page_assets' );

/**
 * Print the current singular page's custom CSS in the head.
 */
function ac_print_page_css() {
	if ( ! is_singular( ac_page_assets_post_types() ) ) {
		return;
	}
	$css = get_post_meta( get_queried_object_id(), '_ac_custom_css', true );
	if ( '' === trim( (string) $css ) ) {
		return;
	}
	echo "\n<style id=\"ac-page-css\">\n" . $css . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- admin-authored CSS, breakout-guarded on save.
}
add_action( 'wp_head', 'ac_print_page_css', 99 );

/**
 * Print the current singular page's custom JS in the footer.
 */
function ac_print_page_js() {
	if ( ! is_singular( ac_page_assets_post_types() ) ) {
		return;
	}
	$js = get_post_meta( get_queried_object_id(), '_ac_custom_js', true );
	if ( '' === trim( (string) $js ) ) {
		return;
	}
	echo "\n<script id=\"ac-page-js\">\n" . $js . "\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- admin-authored JS, breakout-guarded on save.
}
add_action( 'wp_footer', 'ac_print_page_js', 99 );
