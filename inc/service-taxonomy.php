<?php
/**
 * Central service taxonomy for Great Smiles Dental Care (Surprise, AZ).
 *
 * Single source of truth for the five service CATEGORIES, the grouping of the
 * individual service pages under them, and the two home-page "highlight" services.
 * Drives the mega menu (header.php), the category nav pages (template-category.php),
 * and the home services section ([ac_service_cards]).
 *
 * URL model — NESTED (not flat):
 *   Category pages:    /<category>/               (top-level, post_parent=0)
 *   Child services:    /<category>/<service>/     (WP child page)
 *   Standalone pages:  /<slug>/                   (top-level, no children)
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The five service categories, in display order.
 *
 * @return array[] each: slug, title, eyebrow (SEO heading), desc (home-card blurb).
 */
function ac_service_categories() {
	return array(
		array(
			'slug'    => 'general-dentistry',
			'title'   => 'General Dentistry',
			'eyebrow' => 'General Dentistry in Surprise, AZ | Great Smiles Dental Care',
			'desc'    => 'Keep your smile healthy with general dentistry in Surprise, AZ at Great Smiles Dental Care. Comprehensive care for the whole family — cleanings, exams, and more.',
		),
		array(
			'slug'    => 'cosmetic-dentistry',
			'title'   => 'Cosmetic Dentistry',
			'eyebrow' => 'Cosmetic Dentistry in Surprise, AZ | Great Smiles Dental Care',
			'desc'    => 'Transform your smile with cosmetic dentistry in Surprise, AZ at Great Smiles Dental Care. Personalized treatments to help you look and feel your best.',
		),
		array(
			'slug'    => 'restorative-dentistry',
			'title'   => 'Restorative Dentistry',
			'eyebrow' => 'Restorative Dentistry in Surprise, AZ | Great Smiles Dental Care',
			'desc'    => 'Rebuild your smile with restorative dentistry in Surprise, AZ at Great Smiles Dental Care. Long-lasting solutions for damaged, decayed, or missing teeth.',
		),
		array(
			'slug'    => 'dental-implants',
			'title'   => 'Dental Implants',
			'eyebrow' => 'Dental Implants in Surprise, AZ | Great Smiles Dental Care',
			'desc'    => 'Replace missing teeth permanently with dental implants in Surprise, AZ at Great Smiles Dental Care. Natural-looking results that restore your bite and confidence.',
		),
		array(
			'slug'    => 'oral-surgery',
			'title'   => 'Oral Surgery',
			'eyebrow' => 'Oral Surgery in Surprise, AZ | Great Smiles Dental Care',
			'desc'    => 'Get expert oral surgery in Surprise, AZ at Great Smiles Dental Care. Safe, comfortable extractions and surgical care from an experienced dental team.',
		),
	);
}

/**
 * Home-page "highlight" cards — standalone services shown on the home page
 * alongside the five category cards. Each links to its top-level service page.
 *
 * @return array[] each: title, desc, service (standalone service slug).
 */
function ac_service_highlights() {
	return array(
		array(
			'title'   => 'Invisalign',
			'desc'    => 'Straighten your teeth discreetly with Invisalign clear aligners — the comfortable, removable alternative to traditional braces.',
			'service' => 'invisalign',
		),
		array(
			'title'   => 'Dental Emergencies',
			'desc'    => 'Experiencing a dental emergency in Surprise, AZ? Great Smiles Dental Care offers prompt care for pain, injuries, and urgent dental issues.',
			'service' => 'dental-emergencies',
		),
	);
}

/**
 * Ordered service slugs (leaf slugs) grouped under each category.
 *
 * These are the child page slugs only — the category prefix is NOT included here.
 * Full nested URL is built as /<category>/<slug>/.
 *
 * @param string $category_slug Category slug.
 * @return string[] Service page slugs.
 */
function ac_category_service_slugs( $category_slug ) {
	$map = array(
		'general-dentistry'     => array(
			'dental-cleanings-exams',
			'gum-disease',
		),
		'cosmetic-dentistry'    => array(
			'dental-veneers',
			'teeth-whitening',
			'smile-makeover',
			'tooth-bonding',
			'teeth-contouring',
			'gum-reshaping',
		),
		'restorative-dentistry' => array(
			'tooth-fillings',
			'root-canals',
			'dental-crowns-bridges',
			'dentures',
		),
		'dental-implants'       => array(
			'full-mouth-reconstruction',
		),
		'oral-surgery'          => array(
			'tooth-removal',
			'wisdom-teeth-removal',
		),
	);
	return isset( $map[ $category_slug ] ) ? $map[ $category_slug ] : array();
}

/**
 * Map a service slug to its parent category slug.
 *
 * Returns the category slug if the service is a child page under a category.
 * Returns null for standalone top-level services (family-dentistry, invisalign,
 * dental-emergencies, sleep-apnea, tmj-treatment) and for category slugs themselves.
 *
 * @param string $slug Service or category slug.
 * @return string|null Parent category slug, or null if top-level / standalone.
 */
function ac_service_category_for( $slug ) {
	static $index = null;
	if ( null === $index ) {
		$index = array();
		foreach ( ac_service_categories() as $cat ) {
			foreach ( ac_category_service_slugs( $cat['slug'] ) as $child ) {
				$index[ $child ] = $cat['slug'];
			}
		}
	}
	return isset( $index[ $slug ] ) ? $index[ $slug ] : null;
}

/**
 * Short display label for a service or category slug (for menus).
 *
 * Avoids DB lookups in the header/nav. Falls back to title-cased slug.
 *
 * @param string $slug Service or category slug.
 * @return string Human-readable label.
 */
function ac_service_label( $slug ) {
	$labels = array(
		// Child services — general dentistry
		'dental-cleanings-exams'    => 'Dental Cleanings and Exams',
		'gum-disease'               => 'Gum Disease Treatment',
		// Child services — cosmetic dentistry
		'dental-veneers'            => 'Dental Veneers',
		'teeth-whitening'           => 'Teeth Whitening',
		'smile-makeover'            => 'Smile Makeover',
		'tooth-bonding'             => 'Tooth Bonding',
		'teeth-contouring'          => 'Teeth Contouring / Crown Lengthening',
		'gum-reshaping'             => 'Gum Reshaping / Gummy Smile Treatment',
		// Child services — restorative dentistry
		'tooth-fillings'            => 'Tooth Fillings',
		'root-canals'               => 'Root Canals',
		'dental-crowns-bridges'     => 'Dental Crowns and Bridges',
		'dentures'                  => 'Dentures',
		// Child services — dental implants
		'full-mouth-reconstruction' => 'Full-Mouth Reconstruction',
		// Child services — oral surgery
		'tooth-removal'             => 'Tooth Removal / Extractions',
		'wisdom-teeth-removal'      => 'Wisdom Teeth Removal',
		// Standalone top-level service pages
		'family-dentistry'          => 'Family Dentistry',
		'invisalign'                => 'Invisalign',
		'dental-emergencies'        => 'Dental Emergencies',
		'sleep-apnea'               => 'Sleep Apnea',
		'tmj-treatment'             => 'TMJ Treatment',
	);
	if ( isset( $labels[ $slug ] ) ) {
		return $labels[ $slug ];
	}
	return ucwords( str_replace( '-', ' ', $slug ) );
}

/**
 * Card data for a single service or category slug (for grids + mega menu).
 *
 * Resolves the WP page and builds the URL using the NESTED model:
 *
 *   Child service  → get_page_by_path( "$category/$slug" )   url: /$category/$slug/
 *   Category page  → get_page_by_path( $slug )               url: /$slug/
 *   Standalone svc → get_page_by_path( $slug )               url: /$slug/
 *
 * BEFORE (flat): get_page_by_path( 'services/' . $slug )
 * AFTER  (nested): get_page_by_path( $category . '/' . $slug ) for child services
 *
 * @param string $slug Service or category slug.
 * @return array|null { title, url, thumb, blurb } or null if the page is missing.
 */
function ac_service_card( $slug ) {
	$category = ac_service_category_for( $slug );

	if ( null !== $category ) {
		// Child service: nested at /<category>/<slug>/
		$page = get_page_by_path( $category . '/' . $slug );
		$url  = home_url( '/' . $category . '/' . $slug . '/' );
	} else {
		// Category landing page or standalone top-level service: /<slug>/
		$page = get_page_by_path( $slug );
		$url  = home_url( '/' . $slug . '/' );
	}

	if ( ! $page ) {
		return null;
	}

	$thumb = get_the_post_thumbnail_url( $page->ID, 'medium_large' );
	// Blurb: first sentence / ~140 chars of the body, tags stripped.
	$text  = trim( wp_strip_all_tags( (string) $page->post_content ) );
	$text  = preg_replace( '/\s+/', ' ', $text );
	$blurb = '';
	if ( '' !== $text ) {
		if ( preg_match( '/^(.{40,150}?[.!?])(\s|$)/', $text, $m ) ) {
			$blurb = $m[1];
		} else {
			$blurb = rtrim( mb_substr( $text, 0, 140 ) ) . '…';
		}
	}

	return array(
		'title' => get_the_title( $page ),
		'url'   => $url,
		'thumb' => $thumb ? $thumb : '',
		'blurb' => $blurb,
	);
}
