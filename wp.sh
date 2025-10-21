#!/bin/bash
set -e

wp() {
    local cmd="$1"

    if [ -z "$cmd" ]; then
        echo "Usage: wp.sh \"<wp-cli-command>\""
        return 1
    fi

    docker exec -i wordpress-cloud-php bash -c "cd /var/www/html/wordpress && $cmd --allow-root"
}

wp "$1"
