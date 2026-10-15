<?php
/**
 * Rank Math schema / OpenGraph corrections that are not reachable from settings.
 *
 * Both of these exist because _mig/21-seo-schema-indexing.php set the page post type's
 * default Schema Type to "None". That was necessary — pages were emitting Article, with
 * a headline and a byline, on things like /contact-us/ — but Rank Math treats "None" as
 * "this post type opts out of structured data entirely", not "this post type is just a
 * page". Two side effects have to be undone in code; there is no setting for either.
 *
 * @package AnchorFramework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Keep the global entities on pages that carry no per-post schema.
 *
 * RankMath\Schema\JsonLD::can_add_global_entities() falls through to the post type's
 * default Schema Type when a singular has no schema of its own. With that set to "None"
 * it returns false, which drops WebSite, WebPage, Organization/Publisher and
 * PrimaryImage — so /about-us/, /contact-us/, /gallery/ and /services/ were left with a
 * bare BreadcrumbList. Those global nodes are the baseline every page should have; only
 * the Article node was ever the problem.
 *
 * The service pages are unaffected either way — they have their own Service schema,
 * which already satisfies the check.
 *
 * @param bool $can_add Whether Rank Math intends to emit the global entities.
 * @return bool
 */
add_filter(
	'rank_math/schema/add_global_entities',
	function ( $can_add ) {
		return is_singular() ? true : $can_add;
	}
);

/**
 * Stop calling every page an "article" in OpenGraph.
 *
 * Facebook::get_type() hardcodes og:type = "article" for any singular that is not the
 * front page, with no regard for the schema type. That is right for /news/ posts and
 * wrong for a contact page or a service page. Posts keep "article"; every other
 * singular becomes "website".
 *
 * @param string $type OpenGraph type Rank Math resolved.
 * @return string
 */
add_filter(
	'rank_math/opengraph/type',
	function ( $type ) {
		if ( 'article' !== $type || ! is_singular() || is_singular( 'post' ) ) {
			return $type;
		}

		return 'website';
	}
);
