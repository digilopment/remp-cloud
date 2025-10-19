#!/bin/bash
set -e

docker exec -u root -it startitup-cloud-php bash -c "cd /var/www/html/wordpress && wp core install \
  --url='http://localhost:8855' \
  --title='Startitup' \
  --admin_user='admin' \
  --admin_password='admin' \
  --admin_email='admin@admin.loc' \
  --allow-root"
