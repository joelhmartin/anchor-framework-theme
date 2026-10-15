# Anchor Framework (parent theme)

Brand-agnostic WordPress parent theme: Monaco page editor (HTML/CSS/JS tabs, media button, AI panel),
per-page CSS/JS, import tools, Site Config–driven chrome, and a shared section/asset layer.
Every site runs it from `wp-content/themes/anchor-framework/` with its own **child theme** on top.

## Rules for sites
- **Never edit this theme on a server.** Updates replace the whole folder. Site work goes in the child theme.
- Need a different copy of a parent asset (tokens, custom CSS, header CSS)? Put it at
  `{child}/assets/parent-overrides/{css|js}/{file}` — the parent loads it under the same handle and order.
  Relative `url(../…)` references inside an override must point back at the parent:
  `url(../../../../anchor-framework/assets/…)` (the file now lives four levels deeper, in the child).
- Override a template by copying it into the child (normal WordPress child-theme hierarchy).
- Behaviour hooks: `ac_primary_nav`, `ac_footer_*`, `ac_raw_content`, `ac_default_hero_bg`, `ac_contact_url`,
  `ac_monaco_post_types`, `ac_ctm_form_token`, `ac_testimonials_widget_id`, `ac_enable_magic_cursor`, `ac_testimonials_intro`, `shortcode_atts_ac_contact_sidebar` (default form token).

## Releasing
1. Bump `Version:` in `style.css` and `AC_THEME_VERSION` in `functions.php` (must match).
2. Add a `CHANGELOG.md` entry, commit, push.
3. `git tag X.Y.Z && git push origin X.Y.Z` — the Release workflow lints, builds `anchor-framework.zip`
   and publishes the GitHub release. Sites see it under Dashboard → Updates (auto-update is on fleet-wide).
