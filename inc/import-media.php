<?php
/**
 * Sideload bundled theme asset images into the Media Library and resolve URLs.
 *
 * The theme ships its imagery in assets/images and historically referenced it
 * straight from the theme folder, so nothing appeared in the Media Library.
 * This importer copies each file into wp-content/uploads as a real attachment
 * and exposes ac_asset_url() so templates/content can point at the library copy.
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Attachment meta key: stores the originating asset filename (e.g. logo.svg). */
const AC_ASSET_META = '_ac_asset_file';

/**
 * Map of asset filename => Media Library URL, for every attachment imported
 * from the theme's assets/images directory. Cached per request.
 *
 * @return array<string,string>
 */
function ac_asset_map() {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}
	$map = array();
	$q   = new WP_Query(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => AC_ASSET_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'no_found_rows'  => true,
		)
	);
	foreach ( $q->posts as $id ) {
		$file = get_post_meta( $id, AC_ASSET_META, true );
		if ( $file ) {
			$map[ $file ] = wp_get_attachment_url( $id );
		}
	}
	return $map;
}

/**
 * Resolve an asset filename to its Media Library URL, falling back to the
 * bundled theme file when that asset has not been imported yet.
 *
 * @param string $filename e.g. 'logo.svg'.
 * @return string
 */
function ac_asset_url( $filename ) {
	$map = ac_asset_map();
	return isset( $map[ $filename ] ) ? $map[ $filename ] : AC_THEME_URI . '/assets/images/' . $filename;
}

/**
 * Permit SVG uploads (WordPress blocks them by default). Filter is only
 * attached for the duration of ac_import_media().
 *
 * @param array $mimes Allowed mime types.
 * @return array
 */
function ac_allow_svg_mimes( $mimes ) {
	$mimes['svg']  = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';
	return $mimes;
}

/**
 * Sideload every file in assets/images into the Media Library. Idempotent: a
 * file already imported (tracked by AC_ASSET_META) is skipped.
 *
 * @return array Log lines.
 */
function ac_import_media() {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$dir   = AC_THEME_DIR . '/assets/images';
	$files = glob( $dir . '/*' );
	if ( empty( $files ) ) {
		return array( "No files found in {$dir}" );
	}

	// Allow SVG for this run, and force the filetype check to accept .svg.
	add_filter( 'upload_mimes', 'ac_allow_svg_mimes' );
	$svg_filetype_fix = function ( $data, $file, $filename ) {
		if ( preg_match( '/\.svg$/i', (string) $filename ) ) {
			$data['ext']  = 'svg';
			$data['type'] = 'image/svg+xml';
		}
		return $data;
	};
	add_filter( 'wp_check_filetype_and_ext', $svg_filetype_fix, 10, 3 );

	// Existing imports, keyed by filename, so re-runs skip rather than duplicate.
	$existing = array();
	$q        = new WP_Query(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => AC_ASSET_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'no_found_rows'  => true,
		)
	);
	foreach ( $q->posts as $id ) {
		$existing[ get_post_meta( $id, AC_ASSET_META, true ) ] = $id;
	}

	$log     = array();
	$created = 0;
	$skipped = 0;
	$errors  = 0;
	foreach ( $files as $path ) {
		if ( ! is_file( $path ) ) {
			continue;
		}
		$name = basename( $path );
		if ( isset( $existing[ $name ] ) ) {
			++$skipped;
			continue;
		}
		// media_handle_sideload consumes (moves) a temp file.
		$tmp = wp_tempnam( $name );
		if ( ! $tmp || ! copy( $path, $tmp ) ) {
			++$errors;
			$log[] = "TMP FAIL {$name}";
			continue;
		}
		$id = media_handle_sideload(
			array(
				'name'     => $name,
				'tmp_name' => $tmp,
			),
			0
		);
		if ( is_wp_error( $id ) ) {
			if ( file_exists( $tmp ) ) {
				unlink( $tmp );
			}
			++$errors;
			$log[] = "ERROR {$name}: " . $id->get_error_message();
			continue;
		}
		update_post_meta( $id, AC_ASSET_META, $name );
		++$created;
	}

	remove_filter( 'upload_mimes', 'ac_allow_svg_mimes' );
	remove_filter( 'wp_check_filetype_and_ext', $svg_filetype_fix, 10 );

	$log[] = "Media import complete: created={$created} skipped={$skipped} errors={$errors}";
	return $log;
}

/**
 * Add the media importer under the Tools menu (mirrors the content importer).
 */
function ac_import_media_menu() {
	add_management_page(
		__( 'Import Theme Images', 'anchor-framework' ),
		__( 'Import Theme Images', 'anchor-framework' ),
		'manage_options',
		'ac-import-media',
		'ac_import_media_render_page'
	);
}
add_action( 'admin_menu', 'ac_import_media_menu' );

/**
 * Render + handle the media importer page.
 */
function ac_import_media_render_page() {
	$log = array();
	if ( isset( $_POST['ac_import_media_go'] ) && check_admin_referer( 'ac_import_media', 'ac_import_media_nonce' ) ) {
		$log = ac_import_media();
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import Theme Images', 'anchor-framework' ); ?></h1>
		<p><?php esc_html_e( 'Copies the bundled theme images (assets/images) into the Media Library. Safe to run multiple times.', 'anchor-framework' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'ac_import_media', 'ac_import_media_nonce' ); ?>
			<?php submit_button( __( 'Import Images', 'anchor-framework' ), 'primary', 'ac_import_media_go' ); ?>
		</form>
		<?php if ( ! empty( $log ) ) : ?>
			<h2><?php esc_html_e( 'Result', 'anchor-framework' ); ?></h2>
			<pre style="background:#fff;border:1px solid #c3c4c7;padding:12px;"><?php echo esc_html( implode( "\n", $log ) ); ?></pre>
		<?php endif; ?>
	</div>
	<?php
}
