<?php
/**
 * Blog archive / index — Marathon design (slim navy hero + square card grid).
 *
 * @package AnchorFramework
 */

get_header();

$ac_blog_url = get_permalink( get_option( 'page_for_posts' ) );
if ( ! $ac_blog_url ) {
	$ac_blog_url = home_url( '/' );
}

if ( is_search() ) {
	/* translators: %s search query */
	$ac_title = sprintf( __( 'Search: %s', 'anchor-framework' ), get_search_query() );
} elseif ( is_category() || is_tag() || is_tax() ) {
	$ac_title = single_term_title( '', false );
} else {
	$ac_title = get_the_title( get_option( 'page_for_posts' ) );
	if ( ! $ac_title ) {
		$ac_title = __( 'Our Blog', 'anchor-framework' );
	}
}
?>

<!-- ============ BLOG HERO ============ -->
<section class="ac-pagehead ac-pagehead--blog">
	<div class="ac-pagehead__photo" style="background-image:url('<?php echo esc_url( ac_default_hero_bg( 'blog' ) ); ?>')"></div>
	<div class="ac-pagehead__scrim"></div>
	<div class="ac-grid-lines"><div class="ac-grid-lines__inner"><span></span><span></span><span></span><span></span></div></div>
	<div class="ac-container ac-pagehead__body">
		<nav class="ac-breadcrumb" aria-label="Breadcrumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span>
			<span aria-current="page">News</span>
		</nav>
		<div class="ac-eyebrow ac-eyebrow--on-photo"><span class="ac-eyebrow__label">News</span></div>
		<h1 class="ac-pagehead__title"><?php echo esc_html( $ac_title ); ?></h1>
	</div>
</section>

<!-- ============ BLOG GRID ============ -->
<section class="section">
	<div class="ac-container">
		<div class="blog-filter-bar">
			<form role="search" method="get" class="blog-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="search" id="ac-blog-search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search articles…', 'anchor-framework' ); ?>" aria-label="<?php esc_attr_e( 'Search articles', 'anchor-framework' ); ?>">
				<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'anchor-framework' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
				</button>
			</form>
		</div>
		<?php if ( have_posts() ) : ?>
			<div class="ac-blog-grid ac-seam-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article class="ac-blog-card" data-fade>
						<a class="ac-blog-card__media media media--zoom" href="<?php the_permalink(); ?>" data-reveal style="aspect-ratio:3/2">
							<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } ?>
						</a>
						<div class="ac-blog-card__body">
							<div class="ac-blog-card__date"><?php echo esc_html( get_the_date() ); ?></div>
							<h3 class="ac-blog-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="ac-blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
							<a class="ac-blog-card__more" href="<?php the_permalink(); ?>">Read more →</a>
						</div>
					</article>
					<?php
				endwhile;
				?>
			</div>

			<?php
			$ac_links = paginate_links( array( 'type' => 'array', 'prev_text' => '←', 'next_text' => '→' ) );
			if ( $ac_links ) :
				?>
				<nav class="ac-pagination" aria-label="Posts">
					<?php foreach ( $ac_links as $ac_link ) : ?>
						<span class="ac-pagination__item"><?php echo wp_kses_post( $ac_link ); ?></span>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>

		<?php else : ?>
			<p class="ac-prose"><?php esc_html_e( 'No articles yet — check back soon.', 'anchor-framework' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
