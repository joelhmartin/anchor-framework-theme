<?php
/**
 * Reviews / credentials building blocks.
 *
 * - ac_certs_strip()      the certification LOGO strip (GAF, James Hardie, HAAG, BBB,
 *                         Google …), shared by the home page, About page and service
 *                         testimonials. Logo list is filterable via `ac_certs`.
 * - [certs]               shortcode wrapper for ac_certs_strip() (drop into page bodies).
 * - ac_service_testimonials()  a service-specific trust/reviews section for the service
 *                         template — unique heading per service, the shared reviews
 *                         widget, and the logo strip. Replaces the generic reviews block.
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The certification logo strip.
 *
 * @param bool $inverse Render for a dark section (adds the on-dark rule).
 * @return string
 */
function ac_certs_strip( $inverse = false ) {
	$u = content_url( '/uploads/2026/06/' );
	// On dark (inverse) sections use the white James Hardie badge so it stays legible.
	$hardie = $inverse ? $u . 'james-hardie-white.svg' : $u . 'james-hardie.png';
	$certs  = apply_filters(
		'ac_certs',
		array(
			array( 'src' => $u . 'gaf.png', 'alt' => 'GAF Certified', 'h' => 100 ),
			array( 'src' => $hardie, 'alt' => 'James Hardie', 'h' => 48 ),
			array( 'src' => $u . 'haag.webp', 'alt' => 'HAAG Certified Inspector', 'h' => 100 ),
			array( 'src' => $u . 'google.webp', 'alt' => 'Google 5-star rated', 'h' => 72 ),
		),
		$inverse
	);
	if ( empty( $certs ) ) {
		return '';
	}
	ob_start();
	echo '<div class="ac-certs__grid">';
	$delay = 0;
	foreach ( $certs as $c ) {
		printf(
			'<div class="ac-certs__cell"><img data-fade%s src="%s" alt="%s" style="max-height:%dpx;max-width:100%%"></div>',
			$delay ? ' data-fade-delay="' . (int) $delay . '"' : '',
			esc_url( $c['src'] ),
			esc_attr( $c['alt'] ),
			(int) $c['h']
		);
		$delay += 80;
	}
	echo '</div>';
	return ob_get_clean();
}

/**
 * [certs] — the logo strip as a standalone section, for use in page bodies.
 */
function ac_certs_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'inverse' => '' ), $atts, 'certs' );
	$cls  = $atts['inverse'] ? ' section--inverse' : '';
	return '<section class="ac-certs' . esc_attr( $cls ) . '"><div class="ac-container">' . ac_certs_strip( (bool) $atts['inverse'] ) . '</div></section>';
}
add_shortcode( 'certs', 'ac_certs_shortcode' );

/**
 * Core trust/reviews section renderer — eyebrow + heading + the shared reviews widget
 * + the certification logo strip. Used by the service template and the [testimonials]
 * shortcode (homepage etc.). Replaces the old, mangled generic reviews Anchor Block.
 *
 * @param string $heading Section heading (plain text).
 * @param string $eyebrow Small eyebrow label.
 * @return string
 */
function ac_render_testimonials( $heading, $eyebrow = 'Why homeowners choose us' ) {
	ob_start();
	?>
	<section class="section section--inverse ac-trust">
		<div class="ac-grid-lines ac-grid-lines--faint"><div class="ac-grid-lines__inner"><span></span><span></span><span></span><span></span></div></div>
		<div class="ac-container ac-trust__inner">
			<div class="ac-eyebrow ac-eyebrow--on-dark" data-fade><span class="ac-eyebrow__label"><?php echo esc_html( $eyebrow ); ?></span></div>
			<h2 class="ac-h2 ac-h2--inverse ac-trust__title" data-fade data-fade-delay="60"><?php echo esc_html( $heading ); ?></h2>
			<div class="ac-trust__reviews" data-fade data-fade-delay="120">
				<style>.ti-review-item{border:1px solid rgba(255,255,255,.15)}</style>
				<?php // Widget id is per-site; a hardcoded one belonged to a different client. ?>
				<?php if ( $ti = apply_filters( 'ac_testimonials_widget_id', '' ) ) : ?>
				<script defer async src="https://cdn.trustindex.io/loader.js?<?php echo esc_attr( $ti ); ?>"></script>
				<?php endif; ?>
			</div>
			<div class="ac-certs__inline" data-fade><?php echo ac_certs_strip( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_* internally. ?></div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Service-specific testimonials (service template): unique heading per service.
 *
 * @return string
 */
function ac_service_testimonials() {
	// Copy is site-agnostic; the brand name comes from the active install. Non-dental sites
	// change the audience word (or the whole line) with the `ac_testimonials_intro` filter.
	$intro = 'See why patients choose ' . get_bloginfo( 'name' ) . ' for ' . get_the_title() . '.';
	return ac_render_testimonials( apply_filters( 'ac_testimonials_intro', $intro, get_the_title() ) );
}

/**
 * [testimonials heading="…" eyebrow="…"] — the trust/reviews section for page bodies
 * (e.g. the home page), replacing the old [anchor_block] reviews block.
 */
function ac_testimonials_shortcode( $atts ) {
	$a = shortcode_atts(
		array(
			'heading' => 'Trusted across the neighborhood, one home at a time.',
			'eyebrow' => 'Why homeowners choose us',
		),
		$atts,
		'testimonials'
	);
	return ac_render_testimonials( $a['heading'], $a['eyebrow'] );
}
add_shortcode( 'testimonials', 'ac_testimonials_shortcode' );

/**
 * Shared "Reason For Choosing Us" section — the three trust pillars, on every service page.
 *
 * @return string
 */
function ac_reasons_for_choosing() {
	$brand = trim( do_shortcode( '[config_business_name]' ) );
	$items = array(
		array( 'Certified Installation Process', 'Every crew follows the ' . $brand . ' Certified Installation Process — so the work is done the same, correct way on every single job.' ),
		array( 'Fully Insured', 'Licensed, bonded, and fully insured. Your home and our crews are protected from the first day on site to the last.' ),
		array( 'Workmanship Warranty', 'A written warranty covering both labor and materials. Ask about the ' . $brand . ' warranty.' ),
	);
	ob_start();
	?>
	<section class="section ac-reasons">
		<div class="ac-container">
			<div class="ac-reasons__head" data-fade>
				<div class="ac-eyebrow"><span class="ac-eyebrow__label">Reason for choosing us</span></div>
				<h2 class="ac-h2 ac-h2--sm">Built on a process you can trust.</h2>
			</div>
			<div class="ac-reasons__grid">
				<?php foreach ( $items as $i => $it ) : ?>
					<div class="ac-reason" data-fade data-fade-delay="<?php echo (int) ( $i * 80 ); ?>">
						<div class="ac-reason__no"><?php printf( '%02d', $i + 1 ); ?></div>
						<h3 class="ac-reason__title"><?php echo esc_html( $it[0] ); ?></h3>
						<p class="ac-reason__text"><?php echo esc_html( $it[1] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
