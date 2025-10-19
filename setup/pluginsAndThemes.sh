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

bash wp.sh "wp language core install ${LOCALE} --allow-root"
bash wp.sh "wp language core activate ${LOCALE} --allow-root"
bash wp.sh "wp plugin install woocommerce --activate --allow-root"
bash wp.sh "wp language plugin install woocommerce ${LOCALE} --allow-root"
bash wp.sh "wp plugin install generate-child-theme --activate --allow-root"

#bash add.sh plugin https://downloads.wordpress.org/plugin/woocommerce-services.latest-stable.zip
#bash add.sh plugin https://downloads.wordpress.org/plugin/woocommerce-payments.latest-stable.zip