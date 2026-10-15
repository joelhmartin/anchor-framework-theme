<?php
/**
 * Token-safe CSS class rename across the database.
 *
 * Rewrites whole class tokens (inside class="..." attributes only) in:
 *   - post_content of all pages/posts
 *   - the ab_html post-meta of anchor_block CPT posts
 *
 * Usage (from the SITE ROOT) — positional args via $args. NOTE: DevKinsta's wp runs in a
 * container; the map file MUST live under the site root (e.g. the theme's tests/maps/),
 * NOT in host /tmp, or the container cannot read it. Paths are relative to the site root.
 *   wp eval-file wp-content/themes/anchor-dental/bin/migrate-classes.php wp-content/themes/anchor-dental/tests/maps/<map>.json dry-run
 *   wp eval-file wp-content/themes/anchor-dental/bin/migrate-classes.php wp-content/themes/anchor-dental/tests/maps/<map>.json apply
 *
 * map.json is { "old-class": "new-class", ... }. A value may contain spaces to
 * ADD a class while keeping the old token, e.g. {"content-subtitle":"content-subtitle u-subtitle"}.
 * NEVER list a JS-coupled hook class in the map (see plan Global Constraints).
 *
 * dry-run changes nothing and prints, per affected row, which class tokens were
 * replaced and how many times. apply writes the changes.
 */

// wp eval-file exposes positional args as $args (env vars do NOT pass through the container).
$map_file = isset($args[0]) ? $args[0] : '';
$mode     = isset($args[1]) ? $args[1] : '';
if ($map_file === '' || ! is_readable($map_file)) {
	WP_CLI::error('Usage: wp eval-file .../migrate-classes.php <map.json under site root> <dry-run|apply>');
}
if ($mode !== 'dry-run' && $mode !== 'apply') {
	WP_CLI::error("Second arg must be 'dry-run' or 'apply' (got '" . $mode . "').");
}
$map = json_decode(file_get_contents($map_file), true);
if (! is_array($map) || ! $map) {
	WP_CLI::error('Map is empty or not valid JSON.');
}
$apply = ($mode === 'apply');

// Per-run tally of how many times each old token was replaced.
$tally = array();

/**
 * Replace whole class tokens inside class="..." attributes only.
 * Returns array($newHtml, $countsForThisRow).
 */
$rewrite = function ($html) use ($map, &$tally) {
	$row_counts = array();
	$new = preg_replace_callback('/class\s*=\s*"([^"]*)"/i', function ($m) use ($map, &$tally, &$row_counts) {
		$tokens = preg_split('/\s+/', trim($m[1]));
		$out = array();
		foreach ($tokens as $t) {
			if ($t !== '' && isset($map[$t])) {
				$out[] = $map[$t];
				$tally[$t] = (isset($tally[$t]) ? $tally[$t] : 0) + 1;
				$row_counts[$t] = (isset($row_counts[$t]) ? $row_counts[$t] : 0) + 1;
			} elseif ($t !== '') {
				$out[] = $t;
			}
		}
		return 'class="' . implode(' ', $out) . '"';
	}, $html);
	return array($new, $row_counts);
};

$changed = 0;

// 1) page/post content
$q = new WP_Query(array('post_type' => array('page', 'post'), 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids'));
foreach ($q->posts as $id) {
	$old = get_post_field('post_content', $id);
	list($new, $rc) = $rewrite($old);
	if ($new !== $old) {
		$changed++;
		$summary = array();
		foreach ($rc as $tok => $n) { $summary[] = "$tok->{$map[$tok]} x$n"; }
		WP_CLI::log("post {$id} (" . get_post_field('post_title', $id) . "): " . implode(', ', $summary));
		if ($apply) { wp_update_post(array('ID' => $id, 'post_content' => $new)); }
	}
}

// 2) anchor_block ab_html meta
$blocks = new WP_Query(array('post_type' => 'anchor_block', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids'));
foreach ($blocks->posts as $id) {
	$old = get_post_meta($id, 'ab_html', true);
	if (! $old) { continue; }
	list($new, $rc) = $rewrite($old);
	if ($new !== $old) {
		$changed++;
		$summary = array();
		foreach ($rc as $tok => $n) { $summary[] = "$tok->{$map[$tok]} x$n"; }
		WP_CLI::log("anchor_block {$id} (ab_html): " . implode(', ', $summary));
		if ($apply) { update_post_meta($id, 'ab_html', $new); }
	}
}

WP_CLI::log('---');
foreach ($tally as $tok => $n) { WP_CLI::log("  total {$tok}: {$n}"); }
WP_CLI::log(($apply ? 'APPLIED' : 'DRY-RUN') . " — rows changed: {$changed}");
