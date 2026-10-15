<?php
/**
 * Updates from GitHub releases (joelhmartin/anchor-framework-theme).
 *
 * Same mechanism as the Anchor Tools plugin: Plugin Update Checker v5 reads the latest GitHub
 * release and offers its `anchor-framework.zip` asset through the normal WordPress update flow
 * (Dashboard → Updates, Appearance → Themes, auto-updates). Every site runs the theme from a
 * folder named `anchor-framework`, so the zip's top folder is `anchor-framework/` too.
 *
 * Shipping a release: bump `Version:` in style.css + AC_THEME_VERSION in functions.php, commit,
 * then push a matching tag (`git tag 1.2.1 && git push origin 1.2.1`). The release workflow builds
 * the zip and publishes the release; sites pick it up on their next update check.
 *
 * The repo is public, so no token is needed. If it ever goes private, define GITHUB_ACCESS_TOKEN
 * in wp-config.php (the same constant Anchor Tools reads).
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Always load our bundled copy: PUC is built to run several versions side by side (each
// version registers its own namespace), and a site running an older Anchor Tools may have
// loaded an older PUC that lacks the v5p6 classes referenced below.
require_once AC_THEME_DIR . '/inc/vendor/plugin-update-checker/plugin-update-checker.php';

$ac_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	'https://github.com/joelhmartin/anchor-framework-theme/',
	AC_THEME_DIR . '/functions.php',
	'anchor-framework'
);

$ac_update_token = getenv( 'GITHUB_ACCESS_TOKEN' ) ?: ( defined( 'GITHUB_ACCESS_TOKEN' ) ? GITHUB_ACCESS_TOKEN : null );
if ( $ac_update_token ) {
	$ac_update_checker->setAuthentication( $ac_update_token );
}

// Install the release's built zip (not GitHub's source archive, whose top folder is
// "anchor-framework-theme-<tag>" and would install as a second, differently named theme).
$ac_update_vcs = method_exists( $ac_update_checker, 'getVcsApi' ) ? $ac_update_checker->getVcsApi() : null;
if ( $ac_update_vcs && method_exists( $ac_update_vcs, 'enableReleaseAssets' ) ) {
	$ac_update_vcs->enableReleaseAssets( '/^anchor-framework\.zip$/', \YahnisElsts\PluginUpdateChecker\v5p6\Vcs\Api::REQUIRE_RELEASE_ASSETS );
}
unset( $ac_update_token, $ac_update_vcs );

// Releases only. Without this, PUC falls back to tags and then to the `main` branch when it
// finds no usable release — so a version bump pushed to main (before tagging) would auto-update
// every site to unreleased code, installed from GitHub's raw source archive (dev files included).
// Seen on the first 1.2.0 install: a site that checked before the release existed cached `main`.
add_filter(
	'puc_vcs_update_detection_strategies-anchor-framework',
	function ( $strategies ) {
		$release = \YahnisElsts\PluginUpdateChecker\v5p6\Vcs\Api::STRATEGY_LATEST_RELEASE;
		return isset( $strategies[ $release ] ) ? array( $release => $strategies[ $release ] ) : array();
	}
);
