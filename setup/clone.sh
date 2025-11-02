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
CRM_DIR="$TARGET_DIR/Crm"
echo "Processing Crm..."
if [ -d "$CRM_DIR" ]; then
    echo "  Directory $CRM_DIR already exists, skipping..."
else
    git clone https://github.com/remp2020/crm-skeleton.git $CRM_DIR
fi


cd "$CURRENT_DIR"
WEB_DIR="$TARGET_DIR/Web"
echo "Processing Web..."
if [ -d "$WEB_DIR" ]; then
    echo "  Directory $WEB_DIR already exists, skipping..."
else
    git clone git@github.com:digilopment/wordpress-cloud.git "$WEB_DIR"
fi


echo "All apps processed."