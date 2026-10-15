<?php
/**
 * Service card grid + home "Our Services" section.
 *
 * - ac_render_service_card_grid( $cards, $args )  reusable card grid (ac-card).
 *   Desktop: 3-up grid (panels always shown). Mobile: single-open animated
 *   accordion (driven by anchor-nav.js; styles in custom.css).
 * - [ac_service_cards]  (+ aliases) home section: 5 categories + 2 highlights.
 * - ac_category_service_cards( $slug )  the services under one category, as cards
 *   (used by template-category.php so category pages share the same component).
 * - ac_render_why_choose_advantages( $label )  the "Why Choose" advantages cards,
 *   shared by template-service.php and template-category.php.
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a grid of service cards.
 *
 * @param array[] $cards Each: title, desc, url, img.
 * @param array   $args  Optional: 'eyebrow', 'heading' (render a centered title);
 *                        'wrap' (bool, wrap in .ac-services-wrap padding, default true).
 * @return string
 */
function ac_render_service_card_grid( $cards, $args = array() ) {
	if ( empty( $cards ) ) {
		return '';
	}
	$args    = wp_parse_args(
		$args,
		array(
			'eyebrow' => '',
			'heading' => '',
			'wrap'    => true,
		)
	);
	$has_title = ( '' !== $args['eyebrow'] || '' !== $args['heading'] );

	ob_start();
	if ( $args['wrap'] ) {
		echo '<div class="ac-services-wrap"><div class="container">';
	}
	if ( $has_title ) {
		?>
		<div class="section-title text-center">
			<?php if ( '' !== $args['eyebrow'] ) : ?>
				<div class="wow fadeInUp eyebrow-text"><?php echo esc_html( $args['eyebrow'] ); ?></div>
			<?php endif; ?>
			<?php if ( '' !== $args['heading'] ) : ?>
				<h2 class="text-anime-style-3 content-header" data-cursor="-opaque"><?php echo esc_html( $args['heading'] ); ?></h2>
			<?php endif; ?>
		</div>
		<?php
	}
	?>
	<div class="ac-services">
		<?php foreach ( $cards as $i => $c ) : ?>
			<div class="ac-card">
				<div class="ac-card__summary">
					<span class="ac-card__title"><?php echo esc_html( $c['title'] ); ?></span>
					<span class="ac-card__arrow" aria-hidden="true"></span>
				</div>
				<div class="ac-card__panel">
					<a class="ac-card__media" href="<?php echo esc_url( $c['url'] ); ?>" tabindex="-1" aria-hidden="true">
						<img src="<?php echo esc_url( $c['img'] ); ?>" alt="<?php echo esc_attr( $c['title'] ); ?>" loading="lazy">
					</a>
					<?php if ( ! empty( $c['desc'] ) ) : ?>
						<p class="ac-card__desc"><?php echo esc_html( $c['desc'] ); ?></p>
					<?php endif; ?>
					<a class="ac-card__link" href="<?php echo esc_url( $c['url'] ); ?>">Learn More <span aria-hidden="true">&rarr;</span></a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	if ( $args['wrap'] ) {
		echo '</div></div>';
	}
	return ob_get_clean();
}

/**
 * Home "Our Services" — the 5 categories + 2 highlight services.
 *
 * @return string
 */
function ac_service_cards_shortcode() {
	$fallback = apply_filters( 'ac_service_card_fallback', content_url( 'uploads/2026/06/Office-Photo.webp' ) );
	$cards    = array();

	// Category cards — each category is a top-level page at /<category>/
	foreach ( ac_service_categories() as $cat ) {
		$page    = get_page_by_path( $cat['slug'] );
		$thumb   = $page ? get_the_post_thumbnail_url( $page->ID, 'large' ) : '';
		$cards[] = array(
			'title' => $cat['title'],
			'desc'  => $cat['desc'],
			'url'   => home_url( '/' . $cat['slug'] . '/' ),
			'img'   => $thumb ? $thumb : $fallback,
		);
	}
	// Highlight cards — standalone top-level service pages at /<service>/
	foreach ( ac_service_highlights() as $hl ) {
		$page    = get_page_by_path( $hl['service'] );
		$thumb   = $page ? get_the_post_thumbnail_url( $page->ID, 'large' ) : '';
		$cards[] = array(
			'title' => $hl['title'],
			'desc'  => $hl['desc'],
			'url'   => home_url( '/' . $hl['service'] . '/' ),
			'img'   => $thumb ? $thumb : $fallback,
		);
	}

	return ac_render_service_card_grid(
		$cards,
		array(
			'eyebrow' => 'Our Services',
			'heading' => 'Comprehensive Dental Care for Every Smile',
		)
	);
}
add_shortcode( 'ac_service_cards', 'ac_service_cards_shortcode' );
add_shortcode( 'service_cards', 'ac_service_cards_shortcode' );      // generic alias
add_shortcode( 'tatum_service_cards', 'ac_service_cards_shortcode' ); // back-compat alias

/**
 * The services under one category, rendered as the shared card grid.
 *
 * @param string $category_slug Category slug.
 * @param array  $args          Passed through to ac_render_service_card_grid().
 * @return string
 */
function ac_category_service_cards( $category_slug, $args = array() ) {
	$fallback = apply_filters( 'ac_service_card_fallback', content_url( 'uploads/2026/06/Office-Photo.webp' ) );
	$cards    = array();
	foreach ( ac_category_service_slugs( $category_slug ) as $slug ) {
		$card = ac_service_card( $slug );
		if ( ! $card ) {
			continue;
		}
		$cards[] = array(
			'title' => $card['title'],
			'desc'  => $card['blurb'],
			'url'   => $card['url'],
			'img'   => $card['thumb'] ? $card['thumb'] : $fallback,
		);
	}
	return ac_render_service_card_grid( $cards, $args );
}

/**
 * Shared "Why Choose Us" advantages section (service + category pages).
 *
 * @param string $eyebrow Small eyebrow label above the heading.
 * @return string
 */
function ac_render_why_choose_advantages( $eyebrow = '' ) {
	$items = array(
		array( 'fa-user-md', __( 'Experienced, Expert Team', 'anchor-framework' ), __( 'Dr. Eric Wendelschafer and our Surprise, AZ team bring extensive training and a gentle touch to every appointment.', 'anchor-framework' ) ),
		array( 'fa-hand-holding-heart', __( 'Comfort-First Care', 'anchor-framework' ), __( 'Anxiety-free options and a calm, welcoming environment keep you relaxed throughout your visit.', 'anchor-framework' ) ),
		array( 'fa-tooth', __( 'High-Quality, Lasting Results', 'anchor-framework' ), __( 'We use modern techniques and premium materials to deliver results that look great and last.', 'anchor-framework' ) ),
		array( 'fa-face-smile', __( 'Renewed Confidence', 'anchor-framework' ), __( 'From your first visit to your final result, we help you smile with confidence again.', 'anchor-framework' ) ),
	);
	ob_start();
	?>
	<div class="advantages-offer section">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 offset-lg-2">
					<div class="section-title text-center">
						<?php if ( '' !== $eyebrow ) : ?>
							<div class="wow fadeInUp eyebrow-text"><?php echo esc_html( $eyebrow ); ?></div>
						<?php endif; ?>
							<h2 class="text-anime-style-3 content-header" data-cursor="-opaque"><?php esc_html_e( 'Why Choose Great Smiles Dental Care?', 'anchor-framework' ); ?></h2>
							<p class="wow fadeInUp" data-wow-delay="0.25s"><?php esc_html_e( 'Patients across Surprise, AZ trust our experienced team for comfortable, high-quality dental care. Here’s what sets us apart.', 'anchor-framework' ); ?></p>
					</div>
				</div>
			</div>
			<div class="offer-cards">
				<?php foreach ( $items as $n => $it ) : ?>
					<div class="offer-card wow fadeInUp"<?php echo $n ? ' data-wow-delay="' . esc_attr( ( $n * 0.15 ) . 's' ) . '"' : ''; ?>>
						<div class="offer-card__icon"><i class="fa-solid <?php echo esc_attr( $it[0] ); ?>" aria-hidden="true"></i></div>
						<h3 class="offer-card__title"><?php echo esc_html( $it[1] ); ?></h3>
						<p><?php echo esc_html( $it[2] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
