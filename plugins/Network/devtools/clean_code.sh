#!/usr/bin/env bash

vendor/bin/phpcbf --standard=vendor/foodcoopshop/foodcoopshop/devtools/fcs-rules.xml config src tests webroot
vendor/bin/phpcs --standard=Vendor/foodcoopshop/foodcoopshop/devtools/fcs-rules.xml config src tests webroot