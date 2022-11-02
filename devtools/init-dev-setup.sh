#!/usr/bin/env bash

bash ./devtools/installation/set-permissions.sh
bash ./devtools/setup-dev/copy-config-files.sh

CURRENT_UID=$(id -u):$(id -g) docker exec -w /app fcs-php-nginx composer install

CURRENT_UID=$(id -u):$(id -g) docker exec -w /app fcs-php-nginx bash ./bin/cake migrations migrate -p Queue
CURRENT_UID=$(id -u):$(id -g) docker exec -w /app fcs-php-nginx bash ./bin/cake migrations migrate
CURRENT_UID=$(id -u):$(id -g) docker exec -w /app fcs-php-nginx bash ./bin/cake migrations seed --source Seeds/tests --seed InitTestDataSeed

CURRENT_UID=$(id -u):$(id -g) docker exec -w /app/webroot fcs-php-nginx npm install