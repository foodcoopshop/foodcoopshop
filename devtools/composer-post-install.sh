#!/usr/bin/env bash

bash devtools/setup-git.sh

rm -Rf vendor/sunhater/kcfinder/.git
mkdir -p webroot/node_modules/kcfinder
cp -Rp vendor/sunhater/kcfinder/* webroot/node_modules/kcfinder
rm -Rf vendor/sunhater