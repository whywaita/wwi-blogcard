#!/usr/bin/env bash
# Usage: ./scripts/tagpr-command.sh
# Responsibility: Synchronize versions from package.json; does not bump versions itself.
set -euo pipefail

version=$(awk -F'"' '/"version"[[:space:]]*:/ { print $4; exit }' package.json)
if [[ -z "${version}" ]]; then
  echo "version not found in package.json" >&2
  exit 1
fi

tmpfile() {
  mktemp 2>/dev/null || mktemp -t tagpr
}

# Sync readme stable tag.
tmp=$(tmpfile)
awk -v v="${version}" '
  BEGIN { updated=0 }
  /^Stable tag:/ { print "Stable tag: " v; updated=1; next }
  { print }
  END { if (!updated) { exit 2 } }
' readme.txt > "${tmp}"
mv "${tmp}" readme.txt

# Sync plugin header version and constant.
tmp=$(tmpfile)
awk -v v="${version}" '
  BEGIN { header=0; constant=0 }
  /^ \* Version:/ {
    sub(/Version:[[:space:]]+.*/, "Version:           " v)
    header=1
  }
  /WWI_BLOGCARD_VERSION/ {
    sub(/\047[0-9]+\.[0-9]+\.[0-9]+\047/, "\047" v "\047")
    constant=1
  }
  { print }
  END { if (!header || !constant) { exit 3 } }
' wwi-blogcard.php > "${tmp}"
mv "${tmp}" wwi-blogcard.php

# Sync block.json versions.
for f in src/wwi-blogcard/block.json; do
  if [[ -f "${f}" ]]; then
    tmp=$(tmpfile)
    awk -v v="${version}" '
      BEGIN { updated=0 }
      /"version"[[:space:]]*:/ {
        sub(/"version"[[:space:]]*:[[:space:]]*"[^"]+"/, "\"version\": \"" v "\"")
        updated=1
      }
      { print }
      END { if (!updated) { exit 4 } }
    ' "${f}" > "${tmp}"
    mv "${tmp}" "${f}"
  fi
done
