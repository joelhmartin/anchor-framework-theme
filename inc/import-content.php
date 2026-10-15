<?php
/**
 * One-time, idempotent importer: template HTML bodies -> WP Pages.
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Absolute path to the staged source template directory (inside the theme).
 *
 * @return string
 */
function ac_template_src_dir() {
	return get_template_directory() . '/import-src';
}

/**
 * Map of slug => [page title, source filename].
 *
 * @return array
 */
function ac_import_map() {
	return array(
		'home'             => array( 'Home', 'index-video.html' ),
		'about'            => array( 'About', 'about.html' ),
		'services'         => array( 'Services', 'service.html' ),
		'service'          => array( 'Service Detail', 'service-single.html' ),
		'doctors'          => array( 'Doctors', 'doctor.html' ),
		'doctor'           => array( 'Doctor Detail', 'doctor-single.html' ),
		'blog'             => array( 'Blog', 'blog.html' ),
		'gallery'          => array( 'Gallery', 'gallery.html' ),
		'testimonials'     => array( 'Testimonials', 'testimonials.html' ),
		'faqs'             => array( 'FAQs', 'faqs.html' ),
		'contact'          => array( 'Contact', 'contact.html' ),
		'book-appointment' => array( 'Book Appointment', 'book-appointment.html' ),
		'not-found'        => array( '404', '404.html' ),
	);
}

/**
 * Extract the body region of a template file: everything between the end of the
 * header block and the start of the footer block, with brand text swept and
 * relative image paths rewritten to the theme asset URL.
 *
 * @param string $html Full template HTML.
 * @return string Cleaned body HTML fragment.
 */
function ac_extract_body( $html ) {
	// Grab inner <body> first.
	if ( preg_match( '/<body[^>]*>(.*)<\/body>/is', $html, $m ) ) {
		$html = $m[1];
	}
	// Drop preloader. Require the full comment delimiters so nested markers
	// (e.g. "<!-- Hero Content Footer End -->") can't terminate the match early.
	$html = preg_replace( '/<!--\s*Preloader Start\s*-->.*?<!--\s*Preloader End\s*-->/is', '', $html );
	// Drop the shared header block (owned by header.php).
	$html = preg_replace( '/<!--\s*Header Start\s*-->.*?<!--\s*Header End\s*-->/is', '', $html );
	// Drop the shared footer block (owned by footer.php).
	$html = preg_replace( '/<!--\s*Footer Start\s*-->.*?<!--\s*Footer End\s*-->/is', '', $html );
	// Drop any trailing <script> tags.
	$html = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $html );
	// Rewrite relative image references to the Media Library URL (ac_asset_url
	// falls back to the bundled theme asset when a file has not been imported).
	$html = preg_replace_callback(
		'/(src|href)=("|\')images\/([^"\']+)\2/i',
		function ( $m ) {
			return $m[1] . '=' . $m[2] . esc_url( ac_asset_url( $m[3] ) ) . $m[2];
		},
		$html
	);
	$html = preg_replace_callback(
		'/url\(\s*(["\']?)images\/([^"\')]+)\1\s*\)/i',
		function ( $m ) {
			return 'url(' . $m[1] . esc_url( ac_asset_url( $m[2] ) ) . $m[1] . ')';
		},
		$html
	);
	// Sweep branding text.
	// Rebrand the visible word only; never touch URLs/filenames (no adjacent path/word chars).
	$html = preg_replace( '/(?<![\w\/.\-])primecare(?![\w\/.\-])/i', 'Anchor Corps', $html );
	$html = str_replace( 'Awaiken', 'Anchor Corps', $html );
	return trim( $html );
}

/**
 * Run the import. Creates/updates one page per map entry. Idempotent.
 *
 * @return array Log lines.
 */
function ac_run_import() {
	$log = array();
	$dir = ac_template_src_dir();
	$home_id = 0;

	foreach ( ac_import_map() as $slug => $def ) {
		list( $title, $file ) = $def;
		$path = $dir . '/' . $file;
		if ( ! file_exists( $path ) ) {
			$log[] = "SKIP {$slug}: missing {$file}";
			continue;
		}
		$raw = file_get_contents( $path );
		if ( false === $raw ) {
			$log[] = "READ ERROR {$slug}: {$file}";
			continue;
		}
		$body = ac_extract_body( $raw );

		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		$postarr  = array(
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => $body,
			'post_status'  => 'publish',
			'post_type'    => 'page',
		);
		if ( $existing ) {
			$postarr['ID'] = $existing->ID;
			$id            = wp_update_post( $postarr, true );
			$log[]         = "UPDATE {$slug} (#{$id})";
		} else {
			$id    = wp_insert_post( $postarr, true );
			$log[] = "CREATE {$slug} (#{$id})";
		}
		if ( ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_ac_imported', '1' );
			if ( 'home' === $slug ) {
				$home_id = $id;
			}
		} else {
			$log[] = "ERROR {$slug}: " . $id->get_error_message();
		}
	}

	if ( $home_id ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );
		$log[] = "Set Home (#{$home_id}) as static front page";
	}
	return $log;
}

/**
 * Add the importer under the Tools menu.
 */
function ac_import_menu() {
	add_management_page(
		__( 'Import Demo Content', 'anchor-framework' ),
		__( 'Import Demo Content', 'anchor-framework' ),
		'manage_options',
		'ac-import',
		'ac_import_render_page'
	);
}
add_action( 'admin_menu', 'ac_import_menu' );

/**
 * Render + handle the importer page.
 */
function ac_import_render_page() {
	$log = array();
	if ( isset( $_POST['ac_import_go'] ) && check_admin_referer( 'ac_import', 'ac_import_nonce' ) ) {
		$log = ac_run_import();
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import Demo Content', 'anchor-framework' ); ?></h1>
		<p><?php esc_html_e( 'Creates (or updates) the template pages and sets Home as the front page. Safe to run multiple times.', 'anchor-framework' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'ac_import', 'ac_import_nonce' ); ?>
			<?php submit_button( __( 'Run Import', 'anchor-framework' ), 'primary', 'ac_import_go' ); ?>
		</form>
		<?php if ( ! empty( $log ) ) : ?>
			<h2><?php esc_html_e( 'Result', 'anchor-framework' ); ?></h2>
			<pre style="background:#fff;border:1px solid #c3c4c7;padding:12px;"><?php echo esc_html( implode( "\n", $log ) ); ?></pre>
		<?php endif; ?>
	</div>
	<?php
}
