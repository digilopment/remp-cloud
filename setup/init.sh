#!/bin/bash

. ../.env

cd ../

rm -rf $WWW_DIR
mkdir -p "$WWW_DIR"
cd "$WWW_DIR"

# --- Stiahnutie WordPress ---
echo "Stahujem WordPress..."
curl -O https://wordpress.org/latest.tar.gz

# --- Rozbalenie ---
echo "Rozbalujem WordPress..."
tar -xzf latest.tar.gz
mv wordpress/* ./
rm -rf wordpress latest.tar.gz

# --- Nastavenie wp-config.php ---
echo "Nastavujem wp-config.php..."
cp wp-config-sample.php wp-config.php

sed -i "s/database_name_here/$DB_NAME/" wp-config.php
sed -i "s/username_here/$DB_USER/" wp-config.php
sed -i "s/password_here/$DB_PASS/" wp-config.php
sed -i "s/localhost/$DB_HOST/" wp-config.php

echo "WordPress pripravený v $WWW_DIR"

mkdir -p wp-content/uploads/wc-logs
chmod -R 0777 ../wordpress