#!/bin/bash

# Načítanie prostredia
if [ -e ../.env ]; then
    . ../.env
else
    . ../.env.dev
fi

# --- THEMES ---
src_base="../../../themes/"
dest_base="../www/wordpress/wp-content/themes"

for source in ../www/themes/*; do
    [ -d "$source" ] || continue
    theme=$(basename "$source")
    dest="$dest_base/$theme"
    source="$src_base$theme"

    [ -L "$dest" ] || [ -d "$dest" ] && rm -rf "$dest"

    ln -s "$source" "$dest"
    echo "Symbolický link vytvorený (theme): $dest -> $source"
done

# --- PLUGINS ---
src_base="../../../plugins/"
dest_base="../www/wordpress/wp-content/plugins"

for source in ../www/plugins/*; do
    [ -d "$source" ] || continue
    plugin=$(basename "$source")
    dest="$dest_base/$plugin"
    source="$src_base$plugin"

    [ -L "$dest" ] || [ -d "$dest" ] && rm -rf "$dest"

    ln -s "$source" "$dest"
    echo "Symbolický link vytvorený (plugin): $dest -> $source"
done
