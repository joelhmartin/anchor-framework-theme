<?php
/**
 * Footer — Anchor Framework dark 4-column footer.
 *
 * Brand-agnostic: logo, name, tagline, email, phone and hours all come from Anchor
 * Site Config (never hardcoded). The two link columns and the legal line are supplied
 * by the active child theme / site via the `ac_footer_services`, `ac_footer_company`
 * and `ac_footer_legal` filters.
 *
 * @package AnchorFramework
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ac_footer_logo    = trim( do_shortcode( '[config_site_image_white]' ) );
$ac_brand          = trim( do_shortcode( '[config_business_name]' ) );
$ac_tagline        = trim( do_shortcode( '[config_business_tagline]' ) );
$ac_footer_email   = trim( do_shortcode( '[config_business_email]' ) );
$ac_footer_phone   = trim( do_shortcode( '[config_phone]' ) );
$ac_footer_hours   = trim( do_shortcode( '[config_business_hours]' ) );

/*
 * Postal address for the footer NAP block.
 *
 * A local business has to publish its address where both people and crawlers can see
 * it — schema alone is not a substitute, and citation matching compares the rendered
 * address against the Google Business Profile. Built from Site Config's `location`
 * group so the footer, the contact page and the LocalBusiness JSON-LD all read from
 * one source and cannot drift apart.
 */
$ac_loc = (array) ( get_option( 'anchor_site_config_options', array() )['location'] ?? array() );
$ac_footer_address          = '';
$ac_footer_address_street   = '';
$ac_footer_address_locality = '';
if ( ! empty( $ac_loc['line1'] ) ) {
	$ac_street = $ac_loc['line1'];
	if ( ! empty( $ac_loc['line2'] ) && preg_match( '/^\s*((?:Ste|Suite|Unit|#)[^,]*)/i', $ac_loc['line2'], $ac_m ) ) {
		$ac_street .= ' ' . trim( $ac_m[1] );
	}
	$ac_region = trim( implode( ' ', array_filter( array( $ac_loc['state'] ?? '', $ac_loc['postal'] ?? '' ) ) ) );
	// Street on one line, locality on the next — matching [config_business_address].
	// A single flat string re-wraps wherever the column happens to run out, which
	// splits "Princeton," off from "NJ 08542" and reads as a broken address.
	$ac_footer_address_street   = $ac_street;
	$ac_footer_address_locality = trim( implode( ', ', array_filter( array( $ac_loc['city'] ?? '', $ac_region ) ) ) );
	$ac_footer_address = trim( implode( ', ', array_filter( array( $ac_footer_address_street, $ac_footer_address_locality ) ) ) );
}

// Social links — pulled from Anchor Site Config's `social` group; only the platforms
// with a URL set are rendered. Icon markup is static/trusted (feather-style outlines).
$ac_cfg    = get_option( 'anchor_site_config_options', array() );

// If every day is open around the clock, show a clean "Open 24 Hours" line
// instead of seven "12 AM - 11:59 PM" rows.
$ac_hours_cfg = ( isset( $ac_cfg['hours'] ) && is_array( $ac_cfg['hours'] ) ) ? $ac_cfg['hours'] : array();
if ( $ac_hours_cfg ) {
	$ac_all_24 = true;
	foreach ( $ac_hours_cfg as $ac_day ) {
		$ac_open  = isset( $ac_day['open'] ) ? $ac_day['open'] : '';
		$ac_close = isset( $ac_day['close'] ) ? $ac_day['close'] : '';
		if ( ! empty( $ac_day['closed'] ) || '00:00' !== $ac_open || ! in_array( $ac_close, array( '23:59', '24:00', '00:00' ), true ) ) {
			$ac_all_24 = false;
			break;
		}
	}
	if ( $ac_all_24 ) {
		$ac_footer_hours = 'Open 24 Hours';
	}
}
$ac_social = ( is_array( $ac_cfg ) && ! empty( $ac_cfg['social'] ) && is_array( $ac_cfg['social'] ) ) ? array_filter( $ac_cfg['social'] ) : array();

// Link columns + legal line — sites/children override via filters. Empty defaults
// so the framework never ships another site's links.
$ac_footer_services = apply_filters( 'ac_footer_services', array() );
$ac_footer_company  = apply_filters( 'ac_footer_company', array() );
$ac_footer_legal    = apply_filters( 'ac_footer_legal', '' );
?>
<footer class="ac-footer">
	<div class="ac-container">
		<div class="ac-footer__top">
			<div class="ac-footer__brand">
				<img src="<?php echo esc_url( $ac_footer_logo ); ?>" alt="<?php echo esc_attr( $ac_brand ); ?>" class="ac-footer__logo">
				<?php if ( $ac_tagline ) : ?><p><?php echo esc_html( $ac_tagline ); ?></p><?php endif; ?>
				<?php if ( ! empty( $ac_social ) ) : ?>
				<div class="ac-footer__social">
					<?php
					echo ac_social_links_html( 'ac-footer__social-link' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_* internally.
					?>
				</div>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $ac_footer_services ) ) : ?>
			<div class="ac-footer__col">
				<div class="ac-footer__title">Services</div>
				<?php foreach ( $ac_footer_services as $ac_link ) : ?>
					<a href="<?php echo esc_url( $ac_link['url'] ); ?>"><?php echo esc_html( $ac_link['label'] ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if ( ! empty( $ac_footer_company ) ) : ?>
			<div class="ac-footer__col">
				<div class="ac-footer__title">Company</div>
				<?php foreach ( $ac_footer_company as $ac_link ) : ?>
					<a href="<?php echo esc_url( $ac_link['url'] ); ?>"><?php echo esc_html( $ac_link['label'] ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<div class="ac-footer__col">
				<div class="ac-footer__title">Contact</div>
				<?php if ( $ac_footer_address ) : ?><span class="ac-footer__address"><?php echo esc_html( $ac_footer_address_street ); ?><?php if ( $ac_footer_address_locality ) : ?><br><?php echo esc_html( $ac_footer_address_locality ); ?><?php endif; ?></span><?php endif; ?>
				<?php if ( $ac_footer_phone ) : ?><a href="<?php echo esc_url( 'tel:+1' . preg_replace( '/\D+/', '', $ac_footer_phone ) ); ?>"><?php echo esc_html( $ac_footer_phone ); ?></a><?php endif; ?>
				<?php if ( $ac_footer_email ) : ?><a href="<?php echo esc_url( 'mailto:' . $ac_footer_email ); ?>"><?php echo esc_html( $ac_footer_email ); ?></a><?php endif; ?>
				<?php if ( $ac_footer_hours ) : ?><span><?php echo wp_kses( $ac_footer_hours, array( 'br' => array() ) ); ?></span><?php endif; ?>
			</div>
		</div>
		<div class="ac-footer__bottom">
			<span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $ac_brand ); ?>. All rights reserved.</span>
			<?php // Legal links are markup by design; the filter output is escaped at source. ?>
			<?php if ( $ac_footer_legal ) : ?><span class="ac-footer__legal"><?php echo wp_kses_post( $ac_footer_legal ); ?></span><?php endif; ?>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
