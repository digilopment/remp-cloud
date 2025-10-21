#!/bin/bash
set -e

if [ -e ../.env ]; then
    . ../.env
else
    . ../.env.dev
fi

cd ../
bash add.sh theme storefront
bash add.sh theme blocksy
bash add.sh plugin woocommerce

bash wp.sh "wp language core install ${LOCALE}"
bash wp.sh "wp language core activate ${LOCALE}"

if [ "$INSTALL_WOOCOMMERCE" = "true" ]; then
    bash wp.sh "wp plugin install woocommerce --activate"
    bash wp.sh "wp language plugin install woocommerce ${LOCALE}"
fi

bash wp.sh "wp plugin install generate-child-theme --activate"
bash wp.sh "wp plugin activate ai-headlines"

#bash add.sh plugin https://downloads.wordpress.org/plugin/woocommerce-services.latest-stable.zip
#bash add.sh plugin https://downloads.wordpress.org/plugin/woocommerce-payments.latest-stable.zip