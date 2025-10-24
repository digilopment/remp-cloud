#!/bin/bash
set -e

if [ -e ./.env ]; then
    . ./.env
else
    . ./.env.dev
fi

wp() {
    local cmd="$1"

    if [ -z "$cmd" ]; then
        echo "Usage: wp.sh \"<wp-cli-command>\""
        return 1
    fi

    docker exec -i ${PHP_HOST} bash -c "cd ${CONTAINER_DIR}/wordpress && $cmd --allow-root"
}

wp "$1"
