#!/bin/bash
cd Beam
make docker-build
cd ..
rm -f Beam/.env
rm -f Mailer/.env
rm -f Mailer/config/config.local.neon
rm -f Sso/.env
docker ps -a --filter "name=remp-" -q | xargs -r docker rm -f
docker compose build
docker compose up
