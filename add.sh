#!/bin/bash
set -e

add() {
    local type="$1"    # plugin | theme
    local target="$2"  # názov alebo URL

    if [ -z "$type" ] || [ -z "$target" ]; then
        echo "Usage: add plugin|theme <name-or-url>"
        return 1
    fi

    # Rozlíšenie či ide o URL alebo názov
    if [[ "$target" =~ ^https?:// ]]; then
        install_target="$target"
    else
        install_target="$target"
    fi

    docker exec -u root -it wordpress-cloud-php bash -c "
        cd /var/www/html/wordpress && \
        wp ${type} install '${install_target}' --activate --allow-root
    "
}

add "$1" "$2"

#bash add.sh plugin generate-child-theme