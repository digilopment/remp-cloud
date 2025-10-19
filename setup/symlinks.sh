#!/bin/bash

# Základná cesta k témam
src_base="../../../themes/"
dest_base="../www/wordpress/wp-content/themes"

# Načítanie všetkých tém
for source in ../www/themes/*; do
    # Skontroluj, či je to priečinok
    [ -d "$source" ] || continue

    # Zisti názov témy
    theme=$(basename "$source")
    dest="$dest_base/$theme"
    source="$src_base$theme"

    # Vymaž existujúce linky/priečinok
    if [ -L "$dest" ] || [ -d "$dest" ]; then
        rm -rf "$dest"
    fi

    # Vytvor symbolický link
    ln -s "$source" "$dest"
    echo "Symbolický link vytvorený: $dest -> $source"
done
