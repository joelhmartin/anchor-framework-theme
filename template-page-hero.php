<?php
/**
 * Template Name: Content Page (Service Hero)
 *
 * Standalone content page that reuses the service-page hero (`.ac-pagehead`):
 * dark photo hero with breadcrumb + eyebrow + H1 + lead + CTA, followed by the
 * raw body rendered full-width so the body's own `.ac-*` section components lay
 * out edge-to-edge like the home page.
 *
 * Hero copy comes from `_service_eyebrow` / `_service_lead` post meta (shared with
 * the service template); the hero background uses the featured image, else the
 * `_hero_bg` meta URL, else a placeholder. Breadcrumb is Home / {title}.
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
	$ac_hero_bg = has_post_thumbnail()
		? get_the_post_thumbnail_url( get_the_ID(), 'full' )
		: trim( (string) get_post_meta( get_the_ID(), '_hero_bg', true ) );
	if ( '' === $ac_hero_bg ) {
		$ac_hero_bg = ac_default_hero_bg( 'page' );
	}
	?>

<!-- ============ PAGE HERO ============ -->
<section class="ac-pagehead">
	<div class="ac-pagehead__photo" style="background-image:url('<?php echo esc_url( $ac_hero_bg ); ?>')"></div>
	<div class="ac-pagehead__scrim"></div>
	<div class="ac-grid-lines"><div class="ac-grid-lines__inner"><span></span><span></span><span></span><span></span></div></div>
	<div class="ac-container ac-pagehead__body">
		<nav class="ac-breadcrumb" aria-label="Breadcrumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span>
			<span aria-current="page"><?php the_title(); ?></span>
		</nav>
		<h1 class="ac-pagehead__title"><?php the_title(); ?></h1>
		<?php if ( '' !== $ac_lead ) : ?>
			<p class="ac-pagehead__lead"><?php echo esc_html( $ac_lead ); ?></p>
		<?php endif; ?>
		<a href="<?php echo esc_url( home_url( '/#estimate' ) ); ?>" class="ac-btn ac-btn--solid">Get a Free Estimate →</a>
	</div>
</section>

<?php ac_render_raw_content( get_the_ID() ); ?>
	<?php
endwhile;

get_footer();
