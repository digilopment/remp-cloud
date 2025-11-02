#!/bin/bash
set -e  # zastaví script, ak nastane chyba

. ../../docker/.env

CONTAINER="${PROJECT_NAME}-sso"
APP_DIR="Web"   # cesta v kontejnere, kde je aplikácia

cp config/.env ../../apps/$APP_DIR/.env

echo "Spúšťam inštaláciu SSO Web cez Docker ($CONTAINER)..."

cd ../../apps/$APP_DIR && bash install.sh