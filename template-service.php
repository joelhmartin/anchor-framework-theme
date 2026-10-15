<?php
/**
 * Template Name: Service Page
 *
 * Service-page layout (reuses the home design system):
 *   1. Slim navy hero — H1 (service name) + breadcrumb + lead + CTA.
 *   2. Warranty bar — "Ask about the {brand} warranty".
 *   3. Content + sticky sidebar — feature image + body (post_content), then the three
 *      Advantage cards; estimate card + services list in the sidebar.
 *   4. Reason For Choosing Us (shared).
 *   5. Insurance CTA (per-service `_service_insurance`).
 *   6. Testimonials, Process, Final estimate CTA (shared).
 *
 * Per-service meta: `_service_lead` (hero), `_service_advantages` (array of {title,text}),
 * `_service_insurance` (insurance CTA body). Body/intro lives in post_content.
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$ac_lead    = trim( (string) get_post_meta( get_the_ID(), '_service_lead', true ) );
	$ac_adv     = get_post_meta( get_the_ID(), '_service_advantages', true );
	$ac_ins     = trim( (string) get_post_meta( get_the_ID(), '_service_insurance', true ) );
	$ac_brand   = trim( do_shortcode( '[config_business_name]' ) );
	$ac_hero_bg = has_post_thumbnail()
		? get_the_post_thumbnail_url( get_the_ID(), 'full' )
		: trim( (string) get_post_meta( get_the_ID(), '_hero_bg', true ) );
	if ( '' === $ac_hero_bg ) {
		$ac_hero_bg = ac_default_hero_bg( 'service' );
	}
	?>

<!-- ============ SERVICE HERO ============ -->
<section class="ac-pagehead">
	<div class="ac-pagehead__photo" style="background-image:url('<?php echo esc_url( $ac_hero_bg ); ?>')"></div>
	<div class="ac-pagehead__scrim"></div>
	<div class="ac-grid-lines"><div class="ac-grid-lines__inner"><span></span><span></span><span></span><span></span></div></div>
	<div class="ac-container ac-pagehead__body">
		<nav class="ac-breadcrumb" aria-label="Breadcrumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span>
			<a href="<?php echo esc_url( home_url( '/services/' ) ); ?>">Services</a><span aria-hidden="true">/</span>
			<span aria-current="page"><?php the_title(); ?></span>
		</nav>
		<h1 class="ac-pagehead__title"><?php the_title(); ?></h1>
		<?php if ( '' !== $ac_lead ) : ?>
			<p class="ac-pagehead__lead"><?php echo esc_html( $ac_lead ); ?></p>
		<?php endif; ?>
		<a href="#estimate" class="ac-btn ac-btn--solid">Get a Free Estimate →</a>
	</div>
</section>

<!-- ============ WARRANTY BAR ============ -->
<div class="ac-warranty-bar">
	<div class="ac-container">
		<a href="#estimate" class="ac-warranty-bar__text">Ask about the <?php echo esc_html( $ac_brand ); ?> warranty</a>
	</div>
</div>

<!-- ============ CERTIFICATIONS ============ -->
<section class="ac-certs">
	<div class="ac-container"><?php echo ac_certs_strip( false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_* internally. ?></div>
	<div class="ac-container"><div class="ac-certs__rule"></div></div>
</section>

<!-- ============ CONTENT + SIDEBAR ============ -->
<section class="section">
	<div class="ac-container ac-svc-layout">
		<div class="ac-svc-main ac-prose">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="ac-svc-figure media"><?php the_post_thumbnail( 'full', array( 'loading' => 'lazy' ) ); ?></figure>
			<?php endif; ?>
			<?php ac_render_raw_content( get_the_ID() ); ?>

			<?php if ( is_array( $ac_adv ) && ! empty( $ac_adv ) ) : ?>
				<div class="ac-advantages">
					<div class="ac-eyebrow"><span class="ac-eyebrow__label">Advantages</span></div>
					<div class="ac-advantages__grid">
						<?php foreach ( $ac_adv as $adv ) : ?>
							<div class="ac-adv-card" data-fade>
								<h3 class="ac-adv-card__title"><?php echo esc_html( $adv['title'] ); ?></h3>
								<p class="ac-adv-card__text"><?php echo esc_html( $adv['text'] ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<aside class="ac-svc-aside"><?php echo ac_block( 'sidebar' ); ?></aside>
	</div>
</section>

<?php
echo ac_block( 'extra' );
echo ac_reasons_for_choosing();           // Shared "Reason For Choosing Us" section.

// Per-service Insurance CTA.
if ( '' !== $ac_ins ) :
	?>
	<section class="ac-insurance">
		<div class="ac-container ac-insurance__inner">
			<div class="ac-insurance__body" data-fade>
				<div class="ac-eyebrow ac-eyebrow--on-dark"><span class="ac-eyebrow__label">Insurance</span></div>
				<h2 class="ac-h2 ac-h2--inverse ac-insurance__title">Let us deal with your insurance company.</h2>
				<p class="ac-insurance__lead"><?php echo esc_html( $ac_ins ); ?></p>
			</div>
			<a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>" class="ac-btn ac-btn--solid ac-insurance__btn" data-fade data-fade-delay="100">Talk to us about a claim →</a>
		</div>
	</section>
	<?php
endif;

/**
 * Extension point immediately after the per-service Insurance CTA. Lets a child
 * theme append a service-specific section (e.g. the Gutters block on the
 * Roofing & Gutters page) without overriding this template.
 *
 * @param int $post_id Current service page ID.
 */
do_action( 'ac_service_after_insurance', get_the_ID() );

echo ac_service_testimonials();           // Service-specific trust/reviews section.
echo ac_block( 'process' );
echo ac_block( 'cta' );

endwhile;

get_footer();
