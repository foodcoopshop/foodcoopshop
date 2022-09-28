#!/usr/bin/env bash

CURRENT_UID=$(id -u):$(id -g) docker compose run --rm composer install
bash ./devtools/setup-dev/set-permissions.sh
bash ./devtools/setup-dev/copy-config-files.sh
docker compose run -w /var/www/html/webroot --rm node npm install
CURRENT_UID=$(id -u):$(id -g) docker exec -w /var/www/html fcs-php-nginx bash ./bin/cake migrations migrate
CURRENT_UID=$(id -u):$(id -g) docker exec -w /var/www/html fcs-php-nginx bash ./bin/cake migrations seed --source Seeds/tests