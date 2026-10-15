# Changelog

## 1.2.0 — 2026-10-15
First versioned release. Consolidates the per-site copies (Smiles of Cary, Princeton Dental, TMJ Therapy Centre
International, Marathon Restorations, Green Acres, H&H Asphalt, DEKA Dental Lasers) onto one codebase.
- **Fix:** per-page CSS/JS lost every backslash on save (`update_post_meta()` unslashes twice). From TMJ International.
- **Fix:** footer NAP address prints street and locality on separate lines (no mid-address wrap). From Princeton.
- **Fix (older copies):** removed another client's hard-coded CTM form token and TrustIndex widget id from the
  contact sidebar, category template and testimonials; both are now per-site filters
  (`ac_ctm_form_token`, `ac_testimonials_widget_id`).
- **New:** `ac_parent_asset_url()` + `{child}/assets/parent-overrides/` — a child can replace any parent asset without editing the parent.
- **New:** `ac_testimonials_intro` filter (the heading said "patients" on every site).
- **New:** `ac_enable_magic_cursor` filter (Cary had removed the script by editing the parent).
- **New:** GitHub-release updates (Plugin Update Checker v5.6, same as Anchor Tools).
- Removed from the theme: `tests/` (DB snapshots with live API keys, screenshots), `_mig/`, `.superpowers/`, `bin/sync-home.php`.
