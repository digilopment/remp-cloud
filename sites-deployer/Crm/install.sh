#!/bin/bash
set -e  # zastaví script, ak nastane chyba

. ../../docker/.env

CONTAINER="${PROJECT_NAME}-crm"
APP_DIR="Crm"   # cesta v kontejnere, kde je aplikácia

cp config/Dockerfile ../../apps/$APP_DIR/docker/php/Dockerfile
cp config/config.local.neon ../../apps/$APP_DIR/app/config/config.local.neon
cp config/.env ../../apps/$APP_DIR/.env

echo "Spúšťam inštaláciu Remp CRM cez Docker ($CONTAINER)..."

# 1. Nastavenie práv
echo "Nastavujem práva..."
docker exec -it $CONTAINER bash -c "chmod -R 0777 $APP_DIR/temp $APP_DIR/log $APP_DIR/content || true"

# 2. Inštalácia Composer balíčkov
echo "Inštalujem Composer balíčky..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && composer install --no-interaction --no-progress --no-ansi"

# 3. Inicializácia a migrácia databázy
echo "Migrácia databázy..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && php bin/command.php phinx:migrate"

# 4. Generovanie aplikačného kľúča
echo "Generujem aplikačný kľúč..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && php bin/command.php application:generate_key"

# 5. Generovanie prístupových práv pre CRM admin
echo "Generujem prístupové práva pre užívateľov..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && php bin/command.php user:generate_access"

# 6. Generovanie prístupových práv pre API
echo "Generujem API prístupové práva..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && php bin/command.php api:generate_access"

# 7. Seed databázy
echo "Seeding databázy..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && php bin/command.php application:seed"

# 8. Inštalácia modulových assetov
echo "Inštalujem modulové assety..."
docker exec -it $CONTAINER bash -c "cd $APP_DIR && php bin/command.php application:install_assets"

echo "Hotovo! Všetky kroky úspešne dokončené."


