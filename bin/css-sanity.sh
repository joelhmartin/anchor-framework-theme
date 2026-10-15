#!/usr/bin/env bash
# Non-visual sanity checks for the anchor-dental CSS refactor. Run after each phase.
# (a) every url("/wp-content/uploads/...") referenced by theme CSS resolves to a file on disk
# (b) every var(--token) used in theme CSS is defined as --token: somewhere in theme CSS
# (c) print theme-authored CSS line count + tokens.css+primitives.css share (>=70% goal)
set -uo pipefail
THEME="/Users/bif/DevKinsta/public/great-smiles-new/wp-content/themes/anchor-dental"
SITE="/Users/bif/DevKinsta/public/great-smiles-new"
cd "$THEME" || exit 2

CSS=$(find assets/css -name '*.css' ! -name '*.min.css' ! -name 'bootstrap*' ! -name 'all*' \
  ! -name 'animate*' ! -name 'swiper*' ! -name 'magnific*' ! -name 'slicknav*' \
  ! -name 'twentytwenty*' ! -name 'mousecursor*')

fail=0

echo "== (a) asset check =="
while read -r p; do
  [ -z "$p" ] && continue
  if [ ! -f "$SITE$p" ]; then echo "  MISSING ASSET: $p"; fail=1; fi
done < <(grep -rhoE 'url\(\s*"?/wp-content/uploads/[^")]+' $CSS 2>/dev/null | sed -E 's/url\(\s*"?//' | sort -u)
[ "$fail" -eq 0 ] && echo "  ok"

echo "== (b) token check =="
# Only flag NO-FALLBACK uses: var(--x) closing directly with ')'. var(--x, fallback) is
# intentionally optional and is fine. Allowlist covers (1) tokens injected at runtime by the
# Anchor site-config module (fonts), which have no static CSS definition, and (2) a known
# pre-existing vendor typo to fix in Phase 8.
KNOWN_PREEXISTING="--anchor-font-heading --anchor-font-body --anchor-font-sans"
# Write defs to a temp file: avoids a bash 3.2 stdin-conflict bug where
# `printf ... | grep` inside `while done < <(...)` gets a corrupted stdin.
_defs_tmp=$(mktemp)
cat $CSS 2>/dev/null | grep -oE '\-\-[A-Za-z0-9_-]+[[:space:]]*:' | grep -oE '\-\-[A-Za-z0-9_-]+' | sort -u > "$_defs_tmp"
tokfail=0
while read -r u; do
  [ -z "$u" ] && continue
  case " $KNOWN_PREEXISTING " in *" $u "*) continue ;; esac
  grep -qxF -- "$u" "$_defs_tmp" || { echo "  UNDEFINED TOKEN: $u"; tokfail=1; }
done < <(cat $CSS 2>/dev/null | grep -oE 'var\([[:space:]]*\-\-[A-Za-z0-9_-]+[[:space:]]*\)' | grep -oE '\-\-[A-Za-z0-9_-]+' | sort -u)
rm -f "$_defs_tmp"
if [ "$tokfail" -ne 0 ]; then fail=1; else echo "  ok"; fi

echo "== (c) reuse metric =="
total=$(cat $CSS 2>/dev/null | wc -l | tr -d ' ')
core=0
for f in assets/css/tokens.css assets/css/primitives.css; do
  [ -f "$f" ] && core=$((core + $(wc -l < "$f")))
done
echo "  theme CSS lines: $total ; tokens+primitives: $core"
[ "$total" -gt 0 ] && echo "  reusable share: $(( core * 100 / total ))%"

if [ "$fail" -ne 0 ]; then echo "SANITY: FAIL"; exit 1; fi
echo "SANITY: PASS"
