<?php
/**
 * Persist undo history across saves/reloads in the WordPress File Editors.
 *
 * The core Theme/Plugin File Editor (Appearance → Theme File Editor) loses the
 * editor's undo stack whenever the page reloads after a save. When CodeMirror is
 * active WordPress normally saves over Ajax (no reload), but if the editor falls
 * back to a plain <textarea> the form does a full POST and the native undo
 * history is wiped — Ctrl/Cmd+Z stops working.
 *
 * This module enqueues a small script on the editor screens that:
 *   - mirrors CodeMirror's getHistory()/setHistory() into localStorage and
 *     restores it after a reload, and
 *   - provides a localStorage-backed undo/redo stack for the textarea fallback.
 *
 * Admin-only, scoped to theme-editor.php and plugin-editor.php. Never loads on
 * the front end. Touches no plugin or core files.
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue the undo-persistence script on the file editor screens only.
 *
 * @param string $hook Current admin page hook.
 */
function ac_file_editor_undo_assets( $hook ) {
	if ( ! in_array( $hook, array( 'theme-editor.php', 'plugin-editor.php' ), true ) ) {
		return;
	}

	$rel = '/assets/js/admin-file-editor-undo.js';
	$ver = AC_THEME_VERSION;
	$abs = AC_THEME_DIR . $rel;
	if ( file_exists( $abs ) ) {
		$ver .= '.' . filemtime( $abs ); // Cache-bust while iterating.
	}

	// Depend on the core editor script so wp.themePluginEditor exists when ours runs.
	wp_enqueue_script(
		'ac-file-editor-undo',
		AC_THEME_URI . $rel,
		array( 'jquery', 'wp-theme-plugin-editor', 'underscore' ),
		$ver,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'ac_file_editor_undo_assets' );
