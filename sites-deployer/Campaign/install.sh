#!/bin/bash
set -e  # zastaví script, ak nastane chyba

. ../../docker/.env

CONTAINER="${PROJECT_NAME}-campaign"
APP_DIR="Campaign"   # cesta v kontejnere, kde je Laravel aplikácia

cp config/.env ../../apps/$APP_DIR/.env

echo "Spúšťam inštaláciu Campaign CRM cez Docker ($CONTAINER)..."

# 1. Nastavenie práv
echo "Nastavujem práva..."
docker exec -it $CONTAINER bash -c "chmod -R 0777 $APP_DIR/temp $APP_DIR/log $APP_DIR/content || true"

# 2. Inštalácia PHP závislostí
echo "Inštalujem Composer balíčky..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && composer install --no-interaction --no-progress --no-ansi"

# 3. Inštalácia JS/HTML závislostí
echo "Inštalujem JS/HTML balíčky..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && yarn install"
docker exec -it $CONTAINER bash -c "cd $APP_DIR && yarn install --no-bin-links"

# 4. Generovanie assetov
echo "Generujem assety..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && yarn run all-dev"

# 5. Spustenie migrácií
echo "Spúšťam migrácie..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && php artisan migrate --force"

# 6. Generovanie aplikačného kľúča
echo "Generujem app key..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && php artisan key:generate"

# 7. Seed databázy (voliteľné)
echo "Seedujem databázu..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && php artisan db:seed --force"

echo "Hotovo! Campaign CRM je pripravený."
