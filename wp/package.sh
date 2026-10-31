#!/usr/bin/env bash
# Builds the zips WordPress accepts on "Upload Theme" / "Upload Plugin":
#   dist/gcc-core.zip          the plugin (small)
#   dist/glorious.zip          the theme WITHOUT its photographs (small, uploads anywhere)
#   dist/glorious-images.zip   the photographs alone, extract into wp-content/uploads/ (makes gcc-photos/)
#   dist/glorious-full.zip     the theme with photographs, for hosts whose upload limit allows it
# Each zip unpacks to a single folder of the right name. macOS junk is left out.
set -euo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"
mkdir -p "$HERE/dist"
X=(-x '*.DS_Store' -x '__MACOSX/*' -x '*/.git/*' -x '*/.git' -x '*/.gitignore' -x '*/.idea/*' -x '*/.vscode/*' -x '*/node_modules/*')

rm -f "$HERE"/dist/*.zip
(cd "$HERE/wp-content/plugins" && zip -qr "$HERE/dist/gcc-core.zip" gcc-core "${X[@]}")
(cd "$HERE/wp-content/themes" && zip -qr "$HERE/dist/glorious.zip" glorious "${X[@]}" -x 'glorious/assets/images/*.webp')
rm -rf "$HERE/dist/.stage" && mkdir -p "$HERE/dist/.stage/gcc-photos" && cp "$HERE"/wp-content/themes/glorious/assets/images/*.webp "$HERE/dist/.stage/gcc-photos/"
(cd "$HERE/dist/.stage" && zip -qr "$HERE/dist/glorious-images.zip" gcc-photos) && rm -rf "$HERE/dist/.stage"
(cd "$HERE/wp-content/themes" && zip -qr "$HERE/dist/glorious-full.zip" glorious "${X[@]}")
for z in gcc-core glorious glorious-images glorious-full; do echo "built dist/$z.zip ($(du -h "$HERE/dist/$z.zip" | cut -f1))"; done

cat <<'EOF'

Upload order, on any host:
  1. Plugins → Add New → Upload  gcc-core.zip   → Activate (creates pages, menu and content)
  2. Appearance → Themes → Upload  glorious.zip  → Activate
  3. Photos: in the host's File Manager (or FTP) open wp-content/uploads/, upload
     glorious-images.zip there and extract it. It creates uploads/gcc-photos/ with every photo.
     That folder is kept when the theme or plugin is replaced. Until this step the site
     renders with empty photo frames (and wp-admin shows a notice saying so).

If the host's PHP upload limit is 16M or more, glorious-full.zip can replace steps 2 and 3.
"The link you followed has expired" on upload means the zip is over that limit
(upload_max_filesize / post_max_size). Raise both in cPanel → MultiPHP INI Editor, or add a
.user.ini in the WordPress root with:  upload_max_filesize = 32M  and  post_max_size = 32M
EOF
