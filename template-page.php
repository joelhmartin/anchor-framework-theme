<?php
/**
 * Template Name: Content Page
 *
 * Styled standalone content page (About, Meet the Doctor, Tour the Office, Why
 * Choose Us, FAQ, etc.): a page-header hero (title + breadcrumb) followed by the
 * raw HTML body rendered inside the theme's contained `.post-body` typography
 * wrapper, so plain content (headings, paragraphs, lists, images, buttons) picks
 * up the theme styling instead of rendering as unstyled full-width HTML.
 *
 * @package AnchorFramework
 */

get_header();

while ( have_posts() ) :
	the_post();
	$ac_parent = wp_get_post_parent_id( get_the_ID() ) ? get_post_parent( get_the_ID() ) : null;
	?>
<!-- Page Header Start -->
<div class="page-header">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-lg-12">
				<div class="page-header-box">
					<h2 class="text-anime-style-3 hero-header" data-cursor="-opaque"><?php the_title(); ?></h2>
					<nav class="wow fadeInUp">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'anchor-framework' ); ?></a></li>
							<?php if ( $ac_parent ) : ?>
								<li class="breadcrumb-item"><a href="<?php echo esc_url( get_permalink( $ac_parent ) ); ?>"><?php echo esc_html( get_the_title( $ac_parent ) ); ?></a></li>
							<?php endif; ?>
							<li class="breadcrumb-item active" aria-current="page"><?php the_title(); ?></li>
						</ol>
					</nav>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Page Header End -->

<!-- Page Content Start -->
<div class="page-service-single py-5">
	<div class="container">
		<div class="row">
			<div class="col-lg-10 offset-lg-1">
				<div class="service-single-content post-body wow fadeInUp">
					<?php ac_render_raw_content( get_the_ID() ); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Page Content End -->
	<?php
endwhile;

get_footer();
