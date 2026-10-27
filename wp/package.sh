#!/usr/bin/env bash
# Builds the two zips WordPress accepts on "Upload Theme" / "Upload Plugin":
#   dist/glorious.zip        the theme
#   dist/gcc-core.zip   the plugin
# Each zip unpacks to a single folder of the right name, which is what
# wp-admin expects. macOS junk (.DS_Store, __MACOSX) is left out.
set -euo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"
mkdir -p "$HERE/dist"
for kind in themes plugins; do
  for dir in "$HERE/wp-content/$kind"/*/; do
    name="$(basename "$dir")"
    out="$HERE/dist/$name.zip"
    rm -f "$out"
    # No git history, editor files or macOS junk: a .git folder inside the
    # theme once doubled the zip and pushed it past the host's upload limit.
    (cd "$HERE/wp-content/$kind" && zip -qr "$out" "$name" -x '*.DS_Store' -x '__MACOSX/*' -x '*/.git/*' -x '*/.git' -x '*/.gitignore' -x '*/.idea/*' -x '*/.vscode/*' -x '*/node_modules/*')
    echo "built dist/$name.zip ($(du -h "$out" | cut -f1))"
  done
done
echo
echo "If wp-admin says \"The link you followed has expired\" on upload, the zip is over the host's"
echo "PHP upload limit (upload_max_filesize / post_max_size). Raise both to 16M in cPanel → MultiPHP INI"
echo "Editor, or unzip the folder into wp-content/themes via File Manager or FTP instead."
echo
echo "Upload order: Plugins → Add New → Upload (gcc-core.zip) and activate, then Appearance → Themes → Upload (glorious.zip) and activate."
