<?php
/**
 * `[featured_projects]` — the shared "featured projects" grid.
 *
 * Single source of truth for the projects shown on the homepage AND the Gallery
 * page, so the Gallery genuinely renders the same featured work as the homepage.
 * The renderer is brand-agnostic; the project data is supplied by the active child
 * theme / site via the `ac_featured_projects` filter (empty by default).
 *
 * Each project item accepts:
 *   size      required  tile size class, e.g. 'ac-tile--7x2'
 *   img       required  image URL
 *   title     required  caption title text
 *   title_cls optional  full class for the title element (default 'ac-tile__title')
 *   kicker    optional  small label above the title
 *   href      optional  link target (default '#projects')
 *   alt       optional  image alt (default 'Project')
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the featured-projects grid.
 *
 * @return string Grid HTML, or '' when no projects are configured.
 */
function ac_featured_projects_render() {
	$projects = apply_filters( 'ac_featured_projects', array() );
	if ( empty( $projects ) || ! is_array( $projects ) ) {
		return '';
	}

	ob_start();
	echo '<div class="ac-gallery">';
	foreach ( $projects as $p ) {
		if ( empty( $p['img'] ) || empty( $p['size'] ) ) {
			continue;
		}
		$full      = ! empty( $p['full'] ) ? $p['full'] : $p['img'];
		$alt       = isset( $p['alt'] ) ? $p['alt'] : 'Project';
		$title_cls = isset( $p['title_cls'] ) ? $p['title_cls'] : 'ac-tile__title';
		?>
		<a href="<?php echo esc_url( $full ); ?>" data-reveal class="ac-tile <?php echo esc_attr( $p['size'] ); ?>" data-lightbox="ac-gallery" data-lb-title="<?php echo esc_attr( $p['title'] ); ?>">
			<img src="<?php echo esc_url( $p['img'] ); ?>" alt="<?php echo esc_attr( $alt ); ?>">
			<div class="ac-tile__cap">
				<?php if ( ! empty( $p['kicker'] ) ) : ?>
					<div class="ac-tile__kicker"><?php echo esc_html( $p['kicker'] ); ?></div>
				<?php endif; ?>
				<div class="<?php echo esc_attr( $title_cls ); ?>"><?php echo esc_html( $p['title'] ); ?></div>
			</div>
		</a>
		<?php
	}
	echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'featured_projects', 'ac_featured_projects_render' );
