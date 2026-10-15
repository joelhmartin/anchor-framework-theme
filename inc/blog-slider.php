<?php
/**
 * [ac_latest_posts_slider] — dynamic "Latest News" blog slider.
 *
 * Renders the latest published posts as Swiper slides using the theme's
 * existing .blog-item card design. Dropped into raw page HTML, which is run
 * through do_shortcode() by ac_render_raw_content().
 *
 * Attributes:
 *   count       Number of posts to show (default 6).
 *   per_view    Cards visible per view on desktop (default 2).
 *
 * @package AnchorFramework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the latest-posts slider.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function ac_latest_posts_slider( $atts = array() ) {
	$atts = shortcode_atts(
		array(
			'count'    => 6,
			'per_view' => 2,
		),
		$atts,
		'ac_latest_posts_slider'
	);

	$q = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, (int) $atts['count'] ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	if ( ! $q->have_posts() ) {
		return '';
	}

	$per_view = max( 1, (int) $atts['per_view'] );

	ob_start();
	?>
	<div class="blog-slider our-blog-slider" data-per-view="<?php echo esc_attr( $per_view ); ?>">
		<div class="swiper">
			<div class="swiper-wrapper">
				<?php
				while ( $q->have_posts() ) :
					$q->the_post();
					?>
					<div class="swiper-slide">
						<div class="blog-item ac-panel ac-panel--flush">
							<div class="post-featured-image" data-cursor-text="View">
								<figure>
									<a href="<?php the_permalink(); ?>" class="image-anime">
										<?php
										if ( has_post_thumbnail() ) {
											the_post_thumbnail( 'large' );
										} else {
											printf( '<img src="%s" alt="%s" />', esc_url( get_template_directory_uri() . '/assets/images/post-placeholder.jpg' ), esc_attr( get_the_title() ) );
										}
										?>
									</a>
								</figure>
							</div>
							<div class="post-item-body">
								<div class="post-item-meta">
									<span class="post-date"><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?php echo esc_html( get_the_date() ); ?></span>
								</div>
								<div class="post-item-content">
									<h3 class="content-subtitle"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 12 ) ); ?></p>
								</div>
								<div class="post-readmore-btn">
									<a href="<?php the_permalink(); ?>"><?php esc_html_e( 'learn more', 'anchor-framework' ); ?></a>
								</div>
							</div>
						</div>
					</div>
					<?php
				endwhile;
				?>
			</div>
		</div>
		<div class="blog-slider-nav">
			<button type="button" class="blog-button-prev" aria-label="<?php esc_attr_e( 'Previous posts', 'anchor-framework' ); ?>"><i class="fa-solid fa-arrow-left-long" aria-hidden="true"></i></button>
			<button type="button" class="blog-button-next" aria-label="<?php esc_attr_e( 'Next posts', 'anchor-framework' ); ?>"><i class="fa-solid fa-arrow-right-long" aria-hidden="true"></i></button>
		</div>
	</div>
	<?php
	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'ac_latest_posts_slider', 'ac_latest_posts_slider' );
