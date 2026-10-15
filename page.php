<?php
/**
 * Generic page — renders the page's raw HTML body verbatim.
 *
 * @package AnchorFramework
 */

get_header();

while ( have_posts() ) {
	the_post();
	ac_render_raw_content();
}

get_footer();
