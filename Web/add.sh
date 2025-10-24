#!/bin/bash
set -e

if [ -e ./.env ]; then
    . ./.env
else
    . ./.env.dev
fi

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

    docker exec -u root -it ${PHP_HOST} bash -c "
        cd ${CONTAINER_DIR}/wordpress && \
        wp ${type} install '${install_target}' --activate --allow-root
    "
}

add "$1" "$2"

#bash add.sh plugin generate-child-theme