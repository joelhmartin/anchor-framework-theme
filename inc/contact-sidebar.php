<?php
/**
 * [ac_contact_sidebar] — reusable sticky contact sidebar.
 *
 * Logo + "Contact Us" (phone, emergency phone, email) + the CTM embedded
 * form (dashboard.anchorcorps.com). Shared by the blog single template and
 * the Service Detail page so both stay in sync with Site Config.
 *
 * Attributes:
 *   form_token  CTM form token (defaults to the site contact form).
 *   heading     Heading text (default "Contact Us").
 *
 * @package AnchorFramework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the contact sidebar.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function ac_contact_sidebar_shortcode( $atts = array() ) {
	$atts = shortcode_atts(
		array(
			// No default token. This previously carried another client's CTM form,
			// so any use of the shortcode without an explicit token routed this
			// site's leads into their account, under their consent language.
			'form_token' => '',
			'heading'    => __( 'Contact Us', 'anchor-framework' ),
		),
		$atts,
		'ac_contact_sidebar'
	);

	$logo      = trim( do_shortcode( '[config_brand_logo]' ) );
	$phone     = trim( do_shortcode( '[config_phone]' ) );
	$phone_h   = trim( do_shortcode( '[config_phone_href]' ) );
	$emergency = trim( do_shortcode( '[config_emergency_phone]' ) );
	$emerg_h   = trim( do_shortcode( '[config_emergency_phone_href]' ) );
	$email     = trim( do_shortcode( '[config_email]' ) );

	$tel = function ( $num, $href ) {
		if ( '' === $num ) {
			return '';
		}
		return $href ? $href : 'tel:' . preg_replace( '/[^\d+]/', '', $num );
	};

	ob_start();
	?>
	<div class="contact-sidebar">
		<?php if ( $logo ) : ?>
			<div class="contact-sidebar-logo">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" /></a>
			</div>
		<?php endif; ?>

		<div class="contact-sidebar-info">
			<h3 class="content-subtitle"><?php echo esc_html( $atts['heading'] ); ?></h3>
			<ul class="contact-sidebar-list">
				<?php if ( $phone ) : ?>
					<li class="contact-sidebar-phone">
						<i class="fa-solid fa-phone" aria-hidden="true"></i>
						<span><span class="contact-sidebar-label"><?php esc_html_e( 'Call Now', 'anchor-framework' ); ?></span>
						<a href="<?php echo esc_url( $tel( $phone, $phone_h ) ); ?>"><?php echo esc_html( $phone ); ?></a></span>
					</li>
				<?php endif; ?>
				<?php if ( $emergency ) : ?>
					<li class="contact-sidebar-emergency">
						<i class="fa-solid fa-kit-medical" aria-hidden="true"></i>
						<span><span class="contact-sidebar-label"><?php esc_html_e( 'Emergency Hotline', 'anchor-framework' ); ?></span>
						<a href="<?php echo esc_url( $tel( $emergency, $emerg_h ) ); ?>"><?php echo esc_html( $emergency ); ?></a></span>
					</li>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<li class="contact-sidebar-email">
						<i class="fa-solid fa-envelope" aria-hidden="true"></i>
						<span><span class="contact-sidebar-label"><?php esc_html_e( 'Email', 'anchor-framework' ); ?></span>
						<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></span>
					</li>
				<?php endif; ?>
			</ul>
		</div>

		<div class="contact-sidebar-form">
			<div class="cta-form-title"><?php esc_html_e( 'Message us', 'anchor-framework' ); ?></div>
			<p class="cta-form-intro"><?php esc_html_e( 'Send us a note and a real person from our team will get back to you — usually the same day.', 'anchor-framework' ); ?></p>
			<div data-ctm-form-token="<?php echo esc_attr( $atts['form_token'] ); ?>" data-api-base="https://dashboard.anchorcorps.com/api"></div>
			<script src="https://dashboard.anchorcorps.com/ctm-forms/ctm-forms.js" async></script>
			<p class="cta-form-disclaimer"><?php esc_html_e( 'Your information is safe with us. We never sell or share your details.', 'anchor-framework' ); ?></p>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'ac_contact_sidebar', 'ac_contact_sidebar_shortcode' );
