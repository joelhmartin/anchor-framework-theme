<?php
/**
 * Template Name: Service Category (Navigation)
 *
 * Overview page for a service CATEGORY:
 *   1. Page-header hero — SEO eyebrow H1 + category H2 + brief description + breadcrumb.
 *   2. Service cards — the services under this category, as the shared ac-card grid
 *      (desktop 3-up, mobile single-open accordion).
 *   3. Testimonials block.
 *   4. Why Choose Us (shared advantages section).
 *   5. Insurances We Accept block.
 *   6. CTA section.
 *
 * Services are grouped by ac_category_service_slugs() (see inc/service-taxonomy.php).
 *
 * @package AnchorFramework
 */

get_header();

while ( have_posts() ) :
	the_post();
	$ac_slug    = get_post_field( 'post_name', get_the_ID() );
	$ac_eyebrow = get_post_meta( get_the_ID(), '_service_eyebrow', true );
	$ac_name    = get_the_title();
	$ac_label   = $ac_name . ' at Great Smiles Dental Care';
	$ac_cta_bg  = content_url( 'uploads/2026/06/Office-Photo.webp' );
	$ac_intro   = trim( wp_strip_all_tags( get_the_content() ) );
	?>
<!-- Page Header Start -->
<div class="page-header">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-lg-12">
				<div class="page-header-box">
					<?php if ( '' !== trim( (string) $ac_eyebrow ) ) : ?>
						<h1 class="eyebrow-text light wow fadeInUp"><?php echo esc_html( $ac_eyebrow ); ?></h1>
					<?php endif; ?>
					<h2 class="text-anime-style-3 hero-header" data-cursor="-opaque"><?php the_title(); ?></h2>
					<?php
					// Brief description under the H2 — paragraphs only (drop any headings
					// baked into the body so we don't render a second H2 in the hero).
					$ac_rendered = apply_filters( 'the_content', get_the_content() );
					$ac_desc     = preg_match( '#<p\\b[^>]*>.*?</p>#is', $ac_rendered, $ac_pm ) ? $ac_pm[0] : '';
					if ( '' !== trim( wp_strip_all_tags( $ac_desc ) ) ) :
						?>
						<div class="page-header-desc wow fadeInUp" data-wow-delay="0.2s"><?php echo wp_kses_post( $ac_desc ); ?></div>
					<?php endif; ?>
					<nav class="wow fadeInUp">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'anchor-framework' ); ?></a></li>
								<li class="breadcrumb-item active" aria-current="page"><?php the_title(); ?></li>
						</ol>
					</nav>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Page Header End -->

<!-- Category Service Cards — editable per-page anchor block (set via the
     _service_cards_block meta). Falls back to the taxonomy-generated grid only
     if no block is set, so a brand-new category page still shows cards. -->
<?php
$ac_cards_block = get_post_meta( get_the_ID(), '_service_cards_block', true );
echo $ac_cards_block
	? do_shortcode( '[anchor_block id="' . absint( $ac_cards_block ) . '"]' )
	: ac_category_service_cards( $ac_slug );
?>

<!-- Testimonials -->
<?php echo do_shortcode( '[anchor_block id="516"]' ); ?>

<!-- Why Choose Us (shared) -->
<?php echo ac_render_why_choose_advantages( $ac_label ); ?>

<!-- Insurances We Accept -->
<?php echo do_shortcode( '[anchor_block id="27405290"]' ); ?>

<!-- CTA Section Start -->
<div class="cta-section" style="background-image:url('<?php echo esc_url( $ac_cta_bg ); ?>');background-repeat:no-repeat;background-size:cover;background-position:center;padding:3rem 0">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-lg-6 order-lg-1 order-2 cta-text-col">
				<div class="section-title">
					<div class="wow fadeInUp eyebrow-text"><?php echo esc_html( $ac_label ); ?></div>
					<h2 class="text-anime-style-3 content-header" data-cursor="-opaque"><?php esc_html_e( 'Schedule Your Appointment Today', 'anchor-framework' ); ?></h2>
					<p class="wow fadeInUp" data-wow-delay="0.25s"><?php esc_html_e( 'Ready to get started? Call or text our Surprise team and we’ll help you book the right appointment for your smile.', 'anchor-framework' ); ?></p>
				</div>
				<div class="cta-call-btn wow fadeInUp" data-wow-delay="0.3s">
					<a href="<?php echo esc_url( do_shortcode( '[config_business_phone_href]' ) ); ?>" class="btn-default"><i class="fa-solid fa-phone" aria-hidden="true" style="margin-right:8px;"></i><?php echo esc_html( 'Call or Text Now: ' . do_shortcode( '[config_business_phone]' ) ); ?></a>
				</div>
			</div>
			<div class="col-lg-6 order-lg-2 order-1">
				<div class="cta-form-wrap wow fadeInUp" data-wow-delay="0.35s">
					<div class="cta-form-title"><?php esc_html_e( 'Request a Consultation', 'anchor-framework' ); ?></div>
					<p class="cta-form-intro"><?php esc_html_e( 'Tell us what you need and we\'ll be in touch to schedule a free, no-obligation visit. Most estimates come back within 12 hours.', 'anchor-framework' ); ?></p>
					<?php // Token is per-site; the hardcoded one belonged to a different client. ?>
					<?php if ( $ctm = apply_filters( 'ac_ctm_form_token', '' ) ) : ?>
					<div data-ctm-form-token="<?php echo esc_attr( $ctm ); ?>" data-api-base="https://dashboard.anchorcorps.com/api"></div>
					<?php endif; ?>
					<script src="https://dashboard.anchorcorps.com/ctm-forms/ctm-forms.js" async></script>
					<p class="cta-form-disclaimer"><?php esc_html_e( 'Your information is safe with us. We never sell or share your details — we only use them to get back to you about your home.', 'anchor-framework' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- CTA Section End -->
	<?php
endwhile;

get_footer();
