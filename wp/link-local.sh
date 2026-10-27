#!/usr/bin/env bash
# Symlinks the theme and plugin from this repo into a Local by Flywheel site,
# so edits here show up there immediately and nothing is copied twice.
#
#   ./link-local.sh            # assumes the site is called "glorious"
#   ./link-local.sh mysite     # a different site name
set -euo pipefail

SITE="${1:-glorious}"
HERE="$(cd "$(dirname "$0")" && pwd)"
WP="$HOME/Local Sites/$SITE/app/public/wp-content"

if [ ! -d "$WP" ]; then
  echo "No Local site at: $WP" >&2
  echo "Create a site named '$SITE' in Local first, then run this again." >&2
  exit 1
fi

for kind in themes plugins; do
  for dir in "$HERE/wp-content/$kind"/*/; do
    name="$(basename "$dir")"
    target="$WP/$kind/$name"
    if [ -L "$target" ] || [ -e "$target" ]; then
      rm -rf "$target"
    fi
    ln -s "$dir" "$target"
    echo "linked $kind/$name"
  done
done

echo
echo "Now in wp-admin: Plugins → activate 'GCC Core', then Appearance → Themes → activate 'Glorious'."
echo "Activating the plugin creates the pages, sets the homepage and builds the menu."
