<?php
/**
 * Theme Colors admin page + :root CSS variable injection.
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Color variable definitions.
 *
 * Each entry is [ label, css var, default, where-it-is-used, suggested-color-type ].
 * The usage + suggestion strings drive the help text on the Theme Colors screen so a
 * non-technical editor knows what each color actually controls and what kind of color
 * works well there. (Inject/sanitize only read indexes 1 and 2.)
 *
 * @return array
 */
function ac_color_fields() {
	return array(
		'primary'      => array(
			__( 'Primary', 'anchor-framework' ),
			'--primary-color',
			'#0B0B38',
			__( 'Headings, the dark hero / video section backgrounds, button hover states, and icon tiles.', 'anchor-framework' ),
			__( 'A deep, dark color (near-black, navy, or charcoal). Keep it dark enough that white text sits clearly on top.', 'anchor-framework' ),
		),
		'primary_light' => array(
			__( 'Header Gradient (2nd color)', 'anchor-framework' ),
			'--primary-light',
			'#2C2C66',
			__( 'The lighter second color in the dark gradient on the hero, the inner-page header banner (Services, About, Contact…), and the footer. The gradient blends this into your Primary color.', 'anchor-framework' ),
			__( 'A lighter shade of your Primary color (a touch brighter / more saturated), so the gradient eases smoothly into Primary rather than jumping.', 'anchor-framework' ),
		),
		'secondary'    => array(
			__( 'Secondary', 'anchor-framework' ),
			'--secondary-color',
			'#F8F8FF',
			__( 'The soft background of the "light" sections — FAQs, the appointment block, sidebars, and quote blocks.', 'anchor-framework' ),
			__( 'A very light, near-white tint — just barely off pure white so these sections separate gently from the page.', 'anchor-framework' ),
		),
		'text'         => array(
			__( 'Body Text', 'anchor-framework' ),
			'--text-color',
			'#555574',
			__( 'Paragraph and body copy throughout the site.', 'anchor-framework' ),
			__( 'A medium-dark, slightly muted gray — comfortable to read and softer than pure black.', 'anchor-framework' ),
		),
		'accent'       => array(
			__( 'Accent / Brand', 'anchor-framework' ),
			'--accent-color',
			'#5E5EEE',
			__( 'The brand "pop": buttons, links, the small eyebrow labels above headings, icons, star ratings, and the custom cursor.', 'anchor-framework' ),
			__( 'A vivid, saturated color — your signature brand hue and the boldest color on the page.', 'anchor-framework' ),
		),
		'button'        => array(
			__( 'Button', 'anchor-framework' ),
			'--button-color',
			'#5E5EEE',
			__( 'The background color of buttons (Book Appointment, Read More, form submits, etc.) in their normal state.', 'anchor-framework' ),
			__( 'Whatever color you want your buttons to be — this is fully independent of Accent, so it can match Accent or be its own distinct color.', 'anchor-framework' ),
		),
		'button_hover'  => array(
			__( 'Button Hover', 'anchor-framework' ),
			'--button-hover-color',
			'#0B0B38',
			__( 'The color a button fills with when you hover over it (it animates in from the center).', 'anchor-framework' ),
			__( 'Usually a darker (or lighter) shade of your Button color, or your Primary — enough contrast that the hover is noticeable.', 'anchor-framework' ),
		),
		'button_text'   => array(
			__( 'Button Text', 'anchor-framework' ),
			'--button-text-color',
			'#FFFFFF',
			__( 'The label text color inside buttons.', 'anchor-framework' ),
			__( 'High contrast against both the Button and Button Hover colors — usually white for dark buttons.', 'anchor-framework' ),
		),
		'white'        => array(
			__( 'White / Background', 'anchor-framework' ),
			'--white-color',
			'#FFFFFF',
			__( 'The main page background, and text or icons placed on top of dark sections.', 'anchor-framework' ),
			__( 'White or a barely-tinted off-white. Keep it bright — it doubles as the text color over dark areas.', 'anchor-framework' ),
		),
		'divider'      => array(
			__( 'Divider (light)', 'anchor-framework' ),
			'--divider-color',
			'#F0F0FF',
			__( 'Thin borders and lines between cards and sections on light backgrounds.', 'anchor-framework' ),
			__( 'A barely-visible light tint, just a step darker than the section background, so edges stay subtle.', 'anchor-framework' ),
		),
		'dark_divider' => array(
			__( 'Divider (on dark)', 'anchor-framework' ),
			'--dark-divider-color',
			'#FFFFFF26',
			__( 'Borders and divider lines that sit on dark sections, such as the footer.', 'anchor-framework' ),
			__( 'A translucent light color — white at low opacity, e.g. rgba(255,255,255,0.15) — so lines show faintly on dark.', 'anchor-framework' ),
		),
		'error'        => array(
			__( 'Error', 'anchor-framework' ),
			'--error-color',
			'rgb(230, 87, 87)',
			__( 'Form validation and error messages.', 'anchor-framework' ),
			__( 'A clear warning red.', 'anchor-framework' ),
		),
	);
}

/**
 * Register the settings menu page under Appearance.
 */
function ac_colors_menu() {
	add_theme_page(
		__( 'Theme Colors', 'anchor-framework' ),
		__( 'Theme Colors', 'anchor-framework' ),
		'manage_options',
		'ac-theme-colors',
		'ac_colors_render_page'
	);
}
add_action( 'admin_menu', 'ac_colors_menu' );

/**
 * Register the option + settings field.
 */
function ac_colors_register() {
	register_setting(
		'ac_colors_group',
		'ac_colors',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'ac_colors_sanitize',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'ac_colors_register' );

/**
 * Sanitize submitted colors.
 *
 * @param array $input Raw input.
 * @return array
 */
function ac_colors_sanitize( $input ) {
	$clean  = array();
	$fields = ac_color_fields();
	if ( ! is_array( $input ) ) {
		return $clean;
	}
	foreach ( $fields as $key => $def ) {
		if ( empty( $input[ $key ] ) ) {
			continue;
		}
		$val = trim( wp_strip_all_tags( $input[ $key ] ) );
		// Allow hex (#rgb/#rrggbb/#rrggbbaa) or rgb()/rgba().
		if ( preg_match( '/^#([0-9a-fA-F]{3,8})$/', $val ) || preg_match( '/^rgba?\(\s*[\d.,%\s]+\)$/i', $val ) ) {
			$clean[ $key ] = $val;
		}
	}
	return $clean;
}

/**
 * Enqueue the color-picker assets on our page only.
 *
 * @param string $hook Current admin page hook.
 */
function ac_colors_admin_assets( $hook ) {
	if ( 'appearance_page_ac-theme-colors' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_add_inline_script( 'wp-color-picker', 'jQuery(function($){$(".ac-color-field").wpColorPicker();});' );
}
add_action( 'admin_enqueue_scripts', 'ac_colors_admin_assets' );

/**
 * Render the settings page.
 */
function ac_colors_render_page() {
	$saved  = get_option( 'ac_colors', array() );
	$fields = ac_color_fields();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Theme Colors', 'anchor-framework' ); ?></h1>
		<p><?php esc_html_e( 'Set the colors for this client. Each one lists where it shows up in the design and the kind of color that works best there. Leave a field empty to keep the template default.', 'anchor-framework' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'ac_colors_group' ); ?>
			<table class="form-table" role="presentation"><tbody>
			<?php foreach ( $fields as $key => $def ) : ?>
				<tr>
					<th scope="row"><label for="ac-color-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $def[0] ); ?></label></th>
					<td>
						<input type="text" class="ac-color-field" id="ac-color-<?php echo esc_attr( $key ); ?>"
							name="ac_colors[<?php echo esc_attr( $key ); ?>]"
							value="<?php echo esc_attr( isset( $saved[ $key ] ) ? $saved[ $key ] : '' ); ?>"
							data-default-color="<?php echo esc_attr( $def[2] ); ?>" />
						<p class="description" style="max-width:48em;margin-top:6px;">
							<strong><?php esc_html_e( 'Where it’s used:', 'anchor-framework' ); ?></strong>
							<?php echo esc_html( $def[3] ); ?>
						</p>
						<p class="description" style="max-width:48em;">
							<strong><?php esc_html_e( 'Suggested:', 'anchor-framework' ); ?></strong>
							<?php echo esc_html( $def[4] ); ?>
						</p>
						<p class="description" style="opacity:.6;">
							<span style="display:inline-block;width:11px;height:11px;border:1px solid #c3c4c7;vertical-align:middle;background:<?php echo esc_attr( $def[2] ); ?>;"></span>
							<?php
							/* translators: 1: CSS variable name, 2: default color value */
							printf( esc_html__( 'CSS variable %1$s · template default %2$s', 'anchor-framework' ), '<code>' . esc_html( $def[1] ) . '</code>', '<code>' . esc_html( $def[2] ) . '</code>' );
							?>
						</p>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody></table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Convert a hex or rgb()/rgba() color to a comma-separated "r, g, b" triplet.
 *
 * Lets the header's rgba(var(--anchor-navy-rgb), <alpha>) rules be fed from a
 * single hex color the editor sets, so they never have to enter an RGB value.
 *
 * @param string $color e.g. '#0B0B38', '#abc', or 'rgb(11, 11, 56)'.
 * @return string "r, g, b" (0-255 each), or '' if it can't be parsed.
 */
function ac_color_to_rgb_triplet( $color ) {
	$color = trim( (string) $color );

	if ( preg_match( '/^#([0-9a-fA-F]{3,8})$/', $color, $m ) ) {
		$hex = $m[1];
		if ( strlen( $hex ) < 6 ) {
			// #rgb or #rgba shorthand — duplicate each nibble, RGB only.
			$r = str_repeat( $hex[0], 2 );
			$g = str_repeat( $hex[1], 2 );
			$b = str_repeat( $hex[2], 2 );
		} else {
			// #rrggbb or #rrggbbaa — take the first six digits (ignore alpha).
			$r = substr( $hex, 0, 2 );
			$g = substr( $hex, 2, 2 );
			$b = substr( $hex, 4, 2 );
		}
		return hexdec( $r ) . ', ' . hexdec( $g ) . ', ' . hexdec( $b );
	}

	if ( preg_match( '/^rgba?\(\s*(\d{1,3})\D+(\d{1,3})\D+(\d{1,3})/i', $color, $m ) ) {
		return (int) $m[1] . ', ' . (int) $m[2] . ', ' . (int) $m[3];
	}

	return '';
}

/**
 * Darken a hex/rgb color toward black by a fraction (0–1).
 *
 * Used to derive the slightly-deeper CTA-card navy (--anchor-navy-dark) from the
 * single Primary color the editor sets, so the card stays a touch darker than the
 * footer navy without the editor entering a second value.
 *
 * @param string $color  e.g. '#2a4e79'.
 * @param float  $amount Fraction to darken (0 = unchanged, 1 = black).
 * @return string '#rrggbb', or '' if it can't be parsed.
 */
function ac_color_darken( $color, $amount ) {
	$triplet = ac_color_to_rgb_triplet( $color );
	if ( '' === $triplet ) {
		return '';
	}
	$parts = array_map( 'intval', explode( ',', $triplet ) );
	$factor = max( 0.0, min( 1.0, 1.0 - (float) $amount ) );
	return sprintf(
		'#%02x%02x%02x',
		(int) round( $parts[0] * $factor ),
		(int) round( $parts[1] * $factor ),
		(int) round( $parts[2] * $factor )
	);
}

/**
 * Inject saved colors as a :root override in the document head.
 */
function ac_colors_inject() {
	$saved  = get_option( 'ac_colors', array() );
	$saved  = is_array( $saved ) ? $saved : array();
	$fields = ac_color_fields();
	$lines  = array();
	foreach ( $saved as $key => $val ) {
		if ( isset( $fields[ $key ] ) && '' !== $val ) {
			$lines[] = $fields[ $key ][1] . ': ' . $val . ';';
		}
	}

	// Derive the DEKA header "navy" tokens from the Primary color so the editor
	// only sets one hex value. --anchor-navy mirrors Primary; --anchor-navy-rgb
	// is its computed triplet for the rgba(var(--anchor-navy-rgb), <alpha>) uses.
	$primary = ! empty( $saved['primary'] ) ? $saved['primary'] : $fields['primary'][2];
	$triplet = ac_color_to_rgb_triplet( $primary );
	if ( '' !== $triplet ) {
		$lines[] = '--anchor-navy: ' . $primary . ';';
		$lines[] = '--anchor-navy-rgb: ' . $triplet . ';';
		// --primary-rgb is the .page-header photo-overlay color (rgba(var(--primary-rgb), <alpha>)).
		// Derive it from Primary so the inner-page header banners track Theme Colors instead of a
		// stale hardcoded value.
		$lines[] = '--primary-rgb: ' . $triplet . ';';
		// --anchor-navy-dark backs the expanding CTA card; derive a slightly deeper Primary so the
		// card reads as a darker shade of the same navy rather than a fixed near-black.
		$navy_dark = ac_color_darken( $primary, 0.35 );
		if ( '' !== $navy_dark ) {
			$lines[] = '--anchor-navy-dark: ' . $navy_dark . ';';
		}
	}

	if ( empty( $lines ) ) {
		return;
	}
	echo "<style id=\"ac-theme-colors\">:root{" . esc_html( implode( '', $lines ) ) . "}</style>\n";
}
add_action( 'wp_head', 'ac_colors_inject', 100 );
