<?php
/**
 * Template Name: Canvas (home-style, no page chrome)
 *
 * Renders the page body raw and full-width — no page-header or pagehead hero. The body
 * supplies its own hero and sections from the home-style `.ac-*` components (exactly like
 * the front page). Use for pages that should be laid out like the home page.
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	ac_render_raw_content( get_the_ID() );
endwhile;

get_footer();
