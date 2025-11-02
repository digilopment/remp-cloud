#!/bin/bash
set -e

CURRENT_DIR="$PWD"

cd setup && bash clone.sh
cd $CURRENT_DIR && cp sites-deployer/Crm/config/Dockerfile apps/Crm/docker/php/Dockerfile
cd $CURRENT_DIR/docker && make docker-build
docker compose build
docker compose up -d
sleep 15

cd $CURRENT_DIR/sites-deployer/Beam && bash install.sh
cd $CURRENT_DIR/sites-deployer/Campaign && bash install.sh
cd $CURRENT_DIR/sites-deployer/Crm && bash install.sh
cd $CURRENT_DIR/sites-deployer/Sso && bash install.sh
cd $CURRENT_DIR/sites-deployer/Web && bash install.sh
cd $CURRENT_DIR/sites-deployer/Mailer && bash install.sh