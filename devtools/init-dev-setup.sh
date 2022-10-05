#!/usr/bin/env bash

bash ./devtools/setup-dev/set-permissions.sh
bash ./devtools/setup-dev/copy-config-files.sh

CURRENT_UID=$(id -u):$(id -g) docker compose run --rm composer install

CURRENT_UID=$(id -u):$(id -g) docker exec -w /var/www/html fcs-php-nginx bash ./bin/cake migrations migrate -p Queue
CURRENT_UID=$(id -u):$(id -g) docker exec -w /var/www/html fcs-php-nginx bash ./bin/cake migrations migrate --source Migrations/init
CURRENT_UID=$(id -u):$(id -g) docker exec -w /var/www/html fcs-php-nginx bash ./bin/cake migrations seed --source Seeds/tests --seed InitTestDataSeed
CURRENT_UID=$(id -u):$(id -g) docker exec -w /var/www/html fcs-php-nginx bash ./bin/cake migrations migrate

docker exec -w /var/www/html/webroot fcs-php-nginx npm install