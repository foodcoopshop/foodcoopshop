#!/usr/bin/env bash

CURRENT_UID=$(id -u):$(id -g) docker compose exec -T database-dev mysql --port 3310 foodcoopshop-dev < ./config/sql/_installation/clean-db-structure.sql
CURRENT_UID=$(id -u):$(id -g) docker compose exec -T database-dev mysql --port 3310 foodcoopshop-dev < ./tests/config/sql/test-db-data.sql
CURRENT_UID=$(id -u):$(id -g) docker compose run --rm composer install --ignore-platform-reqs
bash ./devtools/setup-dev/set-permissions.sh
bash ./devtools/setup-dev/copy-config-files.sh
docker compose run -w /var/www/html/webroot --rm node npm install
