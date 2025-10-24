#!/bin/bash
set -e

if [ -e ../.env ]; then
    . ../.env
else
    . ../.env.dev
fi


docker exec -u root -it ${PHP_HOST} bash -c "cd ${CONTAINER_DIR}/wordpress && wp core install \
  --url='${DOMAIN}' \
  --title='$TITLE' \
  --admin_user='$DEFAULT_USER' \
  --admin_password='$DEFAULT_PASSWORD' \
  --admin_email='$DEFAULT_EMAIL' \
  --allow-root"

