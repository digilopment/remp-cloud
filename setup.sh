#!/bin/bash

. ./.env

docker compose down
sudo rm -rf volumes/*

bash up -d
sleep 15

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