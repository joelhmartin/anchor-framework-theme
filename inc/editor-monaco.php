<?php
/**
 * Replace the page editor with Monaco (admin only) + a media-library URL button.
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post types whose editor is taken over by Monaco.
 *
 * @return string[]
 */
function ac_monaco_post_types() {
	/**
	 * Post types that get the Monaco raw-HTML editor and per-page CSS/JS.
	 *
	 * Filterable so a child theme can add its vertical's CPTs. Note that
	 * inc/page-assets.php derives ac_page_assets_post_types() from this, so
	 * adding a type here also grants it _ac_custom_css / _ac_custom_js.
	 *
	 * @param string[] $post_types Post type slugs.
	 */
	return apply_filters( 'ac_monaco_post_types', array( 'page', 'post' ) );
}

/**
 * Disable the block editor (Gutenberg) for our post types.
 *
 * @param bool   $use_block_editor Whether to use the block editor.
 * @param string $post_type        Post type being edited.
 * @return bool
 */
function ac_disable_block_editor( $use_block_editor, $post_type ) {
	return in_array( $post_type, ac_monaco_post_types(), true ) ? false : $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'ac_disable_block_editor', 10, 2 );

/**
 * Remove the default classic editor UI for our post types so Monaco replaces it.
 */
function ac_remove_default_editor() {
	foreach ( ac_monaco_post_types() as $pt ) {
		remove_post_type_support( $pt, 'editor' );
	}
}
add_action( 'init', 'ac_remove_default_editor', 100 );

/**
 * Render the tabbed Monaco editor (HTML/CSS/JS), media button, and AI panel.
 *
 * @param WP_Post $post Current post.
 */
function ac_render_monaco_editor( $post ) {
	if ( ! in_array( $post->post_type, ac_monaco_post_types(), true ) ) {
		return;
	}
	wp_nonce_field( 'ac_editor_save', 'ac_editor_nonce' );
	$css = get_post_meta( $post->ID, '_ac_custom_css', true );
	$js  = get_post_meta( $post->ID, '_ac_custom_js', true );
	$ai  = get_option( 'ac_ai_settings', array() );
	$active = isset( $ai['active'] ) ? $ai['active'] : '';
	?>
	<div class="ac-monaco-wrap">
		<div class="ac-monaco-toolbar">
			<div class="ac-tabs" role="tablist">
				<button type="button" class="ac-tab is-active" data-tab="html"><?php esc_html_e( 'HTML', 'anchor-framework' ); ?></button>
				<button type="button" class="ac-tab" data-tab="css"><?php esc_html_e( 'CSS', 'anchor-framework' ); ?></button>
				<button type="button" class="ac-tab" data-tab="js"><?php esc_html_e( 'JS', 'anchor-framework' ); ?></button>
			</div>
			<button type="button" class="button" id="ac-format-code" title="<?php esc_attr_e( 'Re-indent / beautify the active tab', 'anchor-framework' ); ?>">
				<span class="dashicons dashicons-editor-code"></span>
				<?php esc_html_e( 'Format Code', 'anchor-framework' ); ?>
			</button>
			<button type="button" class="button" id="ac-media-button">
				<span class="dashicons dashicons-admin-media"></span>
				<?php esc_html_e( 'Insert from Media Library', 'anchor-framework' ); ?>
			</button>
			<button type="button" class="button" id="ac-ai-toggle">
				<span class="dashicons dashicons-superhero"></span>
				<?php esc_html_e( 'AI Assistant', 'anchor-framework' ); ?>
			</button>
			<span class="ac-monaco-hint"><?php esc_html_e( 'Editing raw page HTML, CSS, and JS.', 'anchor-framework' ); ?></span>
		</div>

		<div id="ac-monaco-editor" class="ac-monaco-editor"></div>

		<div class="ac-ai-panel" id="ac-ai-panel" hidden>
			<?php if ( $active ) : ?>
				<div class="ac-ai-row">
					<textarea id="ac-ai-prompt" class="ac-ai-prompt" rows="2" placeholder="<?php esc_attr_e( 'Describe the change… (e.g. "make the hero heading larger and use the accent color")', 'anchor-framework' ); ?>"></textarea>
					<button type="button" class="button button-primary" id="ac-ai-send"><?php esc_html_e( 'Ask AI', 'anchor-framework' ); ?></button>
				</div>
				<div class="ac-ai-meta"><?php echo esc_html( sprintf( /* translators: provider name */ __( 'Provider: %s', 'anchor-framework' ), $active ) ); ?></div>
				<div class="ac-ai-status" id="ac-ai-status" aria-live="polite"></div>
				<div class="ac-ai-results" id="ac-ai-results"></div>
			<?php else : ?>
				<p class="ac-ai-meta">
					<?php
					printf(
						/* translators: %s settings page link */
						esc_html__( 'No AI provider configured. Set one up in %s.', 'anchor-framework' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=ac-ai-settings' ) ) . '">' . esc_html__( 'AI Assistant settings', 'anchor-framework' ) . '</a>'
					);
					?>
				</p>
			<?php endif; ?>
		</div>

		<textarea id="content" name="content" class="ac-monaco-hidden"><?php echo esc_textarea( $post->post_content ); ?></textarea>
		<textarea id="ac_custom_css" name="ac_custom_css" class="ac-monaco-hidden"><?php echo esc_textarea( $css ); ?></textarea>
		<textarea id="ac_custom_js" name="ac_custom_js" class="ac-monaco-hidden"><?php echo esc_textarea( $js ); ?></textarea>
	</div>
	<?php
}
add_action( 'edit_form_after_title', 'ac_render_monaco_editor' );

/**
 * Enqueue Monaco (CDN) + glue script on the page edit screen ONLY. Never on the front end.
 *
 * @param string $hook Current admin page hook.
 */
function ac_monaco_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, ac_monaco_post_types(), true ) ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'ac-editor', AC_THEME_URI . '/assets/css/ac-editor.css', array(), AC_THEME_VERSION );

	// Monaco AMD loader from jsDelivr.
	$monaco_base = 'https://cdn.jsdelivr.net/npm/monaco-editor@0.52.2/min';
	wp_enqueue_script( 'ac-monaco-loader', $monaco_base . '/vs/loader.js', array(), '0.52.2', true );
	wp_enqueue_script( 'ac-editor', AC_THEME_URI . '/assets/js/ac-editor.js', array( 'ac-monaco-loader', 'jquery' ), AC_THEME_VERSION, true );
	$ai_settings = get_option( 'ac_ai_settings', array() );
	wp_localize_script(
		'ac-editor',
		'ACEditor',
		array(
			'monacoBase' => $monaco_base,
			'mediaTitle' => __( 'Select or upload media', 'anchor-framework' ),
			'mediaBtn'   => __( 'Use this URL', 'anchor-framework' ),
			'aiEnabled'  => ! empty( $ai_settings['active'] ),
			'restUrl'    => esc_url_raw( rest_url( 'anchor-dental/v1/ai' ) ),
			'restNonce'  => wp_create_nonce( 'wp_rest' ),
			'postId'     => isset( $GLOBALS['post'] ) && $GLOBALS['post'] ? (int) $GLOBALS['post']->ID : 0,
			'i18n'       => array(
				'thinking' => __( 'Thinking…', 'anchor-framework' ),
				'accept'   => __( 'Accept', 'anchor-framework' ),
				'reject'   => __( 'Reject', 'anchor-framework' ),
				'acceptAll'=> __( 'Accept all', 'anchor-framework' ),
				'rejectAll'=> __( 'Reject all', 'anchor-framework' ),
				'error'    => __( 'AI request failed.', 'anchor-framework' ),
				'noChange' => __( 'The AI did not propose any changes.', 'anchor-framework' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'ac_monaco_admin_assets' );
