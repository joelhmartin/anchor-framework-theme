<?php
/**
 * Template Name: Content Page (Wide / Sectioned)
 *
 * For standalone pages whose body is built from the theme's full-width section
 * components (e.g. the home-style `.about-us` two-column image/content blocks):
 * renders a page-header hero (title + breadcrumb) then the raw body verbatim at
 * full width, so the body's own `.container` sections lay out edge-to-edge like
 * the home page (rather than inside a narrow text column).
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

<?php ac_render_raw_content( get_the_ID() ); ?>
	<?php
endwhile;

get_footer();
