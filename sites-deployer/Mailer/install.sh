#!/bin/bash
set -e  # zastaví script, ak nastane chyba

. ../../docker/.env

CONTAINER="${PROJECT_NAME}-mailer"
APP_DIR="Mailer"   # cesta v kontejnere, kde je aplikácia

cp config/.env ../../apps/$APP_DIR/.env
cp config/config.local.neon ../../apps/$APP_DIR/app/config/config.local.neon

echo "Spúšťam inštaláciu Mailer CRM cez Docker ($CONTAINER)..."

# 1. Nastavenie práv
echo "Nastavujem práva..."
docker exec -it $CONTAINER bash -c "chmod -R 0777 $APP_DIR/temp $APP_DIR/log $APP_DIR/content || true"

# 2. Inštalácia PHP závislostí
echo "Inštalujem Composer balíčky..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && composer install --no-interaction --no-progress --no-ansi"

# 3. Inštalácia JS/HTML závislostí
echo "Inštalujem JS/HTML balíčky..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && yarn install"

# 4. Generovanie assetov
echo "Generujem assety..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && make js"

# 5. Spustenie migrácií
#echo "Spúšťam migrácie..."
#docker exec -it $CONTAINER bash -c "cd $APP_DIR && php bin/command.php migrate:migrate"

# 6. Seed databázy
#echo "Seedujem databázu..."
#docker exec -it $CONTAINER bash -c "cd $APP_DIR && php bin/command.php db:seed"

echo "Hotovo! Mailer CRM je pripravený."
