#!/bin/bash
set -e

REPO="https://github.com/remp2020/remp.git"
TARGET_DIR="../apps"
CURRENT_DIR="$PWD"

APPS=("Beam" "Campaign" "Composer" "Mailer" "Package" "Sso")

mkdir -p "$TARGET_DIR"

for APP in "${APPS[@]}"; do
    echo "Processing $APP..."
    APP_DIR="$TARGET_DIR/$APP"

    if [ -d "$APP_DIR" ]; then
        echo "  Directory $APP_DIR already exists, skipping..."
        continue
    fi

    # Klon priamo do $APP_DIR
    git clone --no-checkout "$REPO" "$APP_DIR"
    cd "$APP_DIR"

    # Sparse checkout len pre konkrétnu appku
    git sparse-checkout init --cone
    git sparse-checkout set "$APP"
    git checkout master

    # Presun všetkých súborov z podpriečinka $APP do root $APP_DIR
    shopt -s dotglob
    mv "$APP"/* ./
    rmdir "$APP"

    cd "$CURRENT_DIR"
    echo "  $APP done."
done

cd "$CURRENT_DIR"
CRM_REPO="https://github.com/remp2020/crm-skeleton.git"
CRM_DIR="$TARGET_DIR/Crm"
git clone $CRM_REPO $CRM_DIR


cd "$CURRENT_DIR"
WORDPRESS_CLOUD_REPO="git@github.com:digilopment/wordpress-cloud.git"
WEB_DIR="$TARGET_DIR/Web"
# Odstránime /Web, ak existuje a chceme čistý clone
rm -rf "$WEB_DIR"
mkdir -p "$WEB_DIR"
echo "Cloning wordpress-cloud directly into $WEB_DIR..."
git clone "$WORDPRESS_CLOUD_REPO" "$WEB_DIR"
echo "wordpress-cloud done."


echo "All apps processed."