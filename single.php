<?php
/**
 * Blog single post — Marathon design (navy hero + centered prose + shared CTA).
 *
 * @package AnchorFramework
 */

get_header();

while ( have_posts() ) :
	the_post();
	$ac_blog_url   = get_permalink( get_option( 'page_for_posts' ) );
	$ac_blog_label = get_the_title( get_option( 'page_for_posts' ) );
	if ( ! $ac_blog_label ) {
		$ac_blog_label = __( 'News', 'anchor-framework' );
	}
	$ac_hero_bg  = has_post_thumbnail()
		? get_the_post_thumbnail_url( get_the_ID(), 'full' )
		: ac_default_hero_bg( 'blog' );
	?>

<!-- ============ POST HERO ============ -->
<section class="ac-pagehead">
	<div class="ac-pagehead__photo" style="background-image:url('<?php echo esc_url( $ac_hero_bg ); ?>')"></div>
	<div class="ac-pagehead__scrim"></div>
	<div class="ac-grid-lines"><div class="ac-grid-lines__inner"><span></span><span></span><span></span><span></span></div></div>
	<div class="ac-container ac-pagehead__body">
		<nav class="ac-breadcrumb" aria-label="Breadcrumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span>
			<?php if ( $ac_blog_url ) : ?>
				<a href="<?php echo esc_url( $ac_blog_url ); ?>"><?php echo esc_html( $ac_blog_label ); ?></a><span aria-hidden="true">/</span>
			<?php endif; ?>
			<span aria-current="page"><?php the_title(); ?></span>
		</nav>
		<div class="ac-eyebrow ac-eyebrow--on-photo"><span class="ac-eyebrow__label"><?php echo esc_html( get_the_date() ); ?></span></div>
		<h1 class="ac-pagehead__title ac-pagehead__title--post"><?php the_title(); ?></h1>
	</div>
</section>

<!-- ============ ARTICLE ============ -->
<section class="section">
	<div class="ac-container ac-article-wrap">
		<article <?php post_class( 'ac-article ac-prose' ); ?>>
			<?php ac_render_raw_content( get_the_ID() ); ?>
		</article>

		<?php
		$ac_prev = get_previous_post();
		$ac_next = get_next_post();
		if ( $ac_prev || $ac_next ) :
			?>
			<nav class="ac-article-nav" aria-label="More articles">
				<div>
					<?php if ( $ac_prev ) : ?>
						<a href="<?php echo esc_url( get_permalink( $ac_prev ) ); ?>"><span>Previous</span><strong><?php echo esc_html( get_the_title( $ac_prev ) ); ?></strong></a>
					<?php endif; ?>
				</div>
				<div class="is-next">
					<?php if ( $ac_next ) : ?>
						<a href="<?php echo esc_url( get_permalink( $ac_next ) ); ?>"><span>Next</span><strong><?php echo esc_html( get_the_title( $ac_next ) ); ?></strong></a>
					<?php endif; ?>
				</div>
			</nav>
		<?php endif; ?>

		<?php if ( $ac_blog_url ) : ?>
			<div class="ac-article-back"><a href="<?php echo esc_url( $ac_blog_url ); ?>">← Back to all articles</a></div>
		<?php endif; ?>
	</div>
</section>

<?php echo ac_block( 'cta' ); // shared estimate CTA (overridable per post via "{slug}-cta") ?>

	<?php
endwhile;

get_footer();
