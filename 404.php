<?php
/**
 * 404 page.
 *
 * @package AnchorFramework
 */

get_header();
?>
<section class="error-page-section py-5 text-center">
	<div class="container py-5">
		<h1 class="display-1 content-header">404</h1>
		<p><?php esc_html_e( 'The page you are looking for was not found.', 'anchor-framework' ); ?></p>
		<a class="btn-default" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Home', 'anchor-framework' ); ?></a>
	</div>
</section>
<?php
get_footer();
