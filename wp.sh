#!/bin/bash
set -e

wp() {
    local cmd="$1"

    if [ -z "$cmd" ]; then
        echo "Usage: wp.sh \"<wp-cli-command>\""
        return 1
    fi

    docker exec -it startitup-cloud-php bash -c "
    cd /var/www/html/wordpress && \
    $cmd
    "
}

# --- Volanie ---
wp "$1"
