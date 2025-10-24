#!/bin/bash

if [ -e ./.env ]; then
    . ./.env
else
    . ./.env.dev
fi

if [ -z "$1" ]; then
    echo "Available migrations:"
    echo "------------------"
    for file in ./www/migrations/*.php; do
        filename=$(basename "$file" .php)
        if [ "$filename" != "autoload" ]; then
            echo "$filename"
        fi
    done
    echo
    echo "Use: bash $0 <migration>"
    exit 1
else
    docker exec -it ${PHP_HOST} php ${CONTAINER_DIR}/migrations/"$1".php
fi
