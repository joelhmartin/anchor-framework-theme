<?php
/**
 * Front page — renders the static front page's raw HTML body.
 *
 * @package AnchorFramework
 */

get_header();

while ( have_posts() ) {
	the_post();
	ac_render_raw_content();
}

get_footer();
