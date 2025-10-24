#!/bin/bash

# Použitie:
# ./install.sh [--force]
# Ak zadáš --force, najprv zmaže DB a potom ju znova vytvorí.

. ./.env

if [ "$1" == "--force" ]; then
  echo "⚠️  Forcing drop and recreate of database '${DB_NAME}'..."
  docker exec -i ${DB_HOST} mysql -u${DB_USER} -p"${DB_PASS}" -e "DROP DATABASE IF EXISTS \`${DB_NAME}\`;"
fi

docker exec -i ${DB_HOST} mysql -u${DB_USER} -p"${DB_PASS}" -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "✅ Database '${DB_NAME}' is ready."


#init and install
cd setup
bash init.sh
bash install.sh
bash symlinks.sh
bash pluginsAndThemes.sh

#migrations
cd ../
bash migration.sh users
bash migration.sh pages
if [ "$INSTALL_WOOCOMMERCE" = "true" ]; then
    bash migration.sh products
fi
bash migration.sh posts
bash migration.sh settings

if [ "$INSTALL_WOOCOMMERCE" = "true" ]; then
    bash migration.sh woocommerceSettings
fi

bash wp.sh "wp theme activate $SELECTED_THEME --allow-root"