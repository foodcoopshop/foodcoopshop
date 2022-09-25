#!/usr/bin/env bash

docker compose exec -T database-dev mysql --port 3310 foodcoopshop-dev < ./config/sql/_installation/clean-db-structure.sql
docker compose exec -T database-dev mysql --port 3310 foodcoopshop-dev < ./tests/config/sql/test-db-data.sql
docker compose run --rm composer install
bash ./devtools/setup-dev/set-permissions.sh
bash ./devtools/setup-dev/config-files.sh
docker compose run -w /var/www/html/webroot --rm node npm install
docker exec -w /var/www/html fcs-php-nginx bash ./bin/cake npm_post_install
