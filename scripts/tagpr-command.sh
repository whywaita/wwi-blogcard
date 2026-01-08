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
for f in src/wwi-blogcard/block.json build/wwi-blogcard/block.json; do
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

# Update readme changelog from CHANGELOG.md.
if [[ -f "CHANGELOG.md" ]]; then
  tmp_changelog=$(tmpfile)
  awk '
    BEGIN { in_changelog=0; printed=0 }
    /^# Changelog/ { in_changelog=1; next }
    in_changelog==0 { next }
    /^## / {
      if (match($0, /^## \[v?([0-9]+\.[0-9]+\.[0-9]+)\]/, m)) {
        if (printed) {
          print ""
        }
        print "= " m[1] " ="
        printed=1
      }
      next
    }
    /^- / { print "* " substr($0, 3); next }
    /^[[:space:]]*$/ { next }
  ' CHANGELOG.md > "${tmp_changelog}"

  tmp=$(tmpfile)
  awk -v insert_file="${tmp_changelog}" '
    BEGIN { inserted=0; skipping=0; found_upgrade=0 }
    /^== Changelog ==/ {
      print
      while ((getline line < insert_file) > 0) {
        print line
      }
      close(insert_file)
      inserted=1
      skipping=1
      next
    }
    skipping {
      if ($0 ~ /^== Upgrade Notice ==/) {
        print
        found_upgrade=1
        skipping=0
      }
      next
    }
    { print }
    END {
      if (!inserted || !found_upgrade) {
        exit 5
      }
    }
  ' readme.txt > "${tmp}"
  mv "${tmp}" readme.txt
  rm -f "${tmp_changelog}"
fi
