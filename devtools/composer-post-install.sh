#!/usr/bin/env bash

bash devtools/setup-git.sh

rm -Rf vendor/studio-42/elfinder/.git
mkdir -p webroot/node_modules/elfinder
cp -Rp vendor/studio-42/elfinder/* webroot/node_modules/elfinder
rm -Rf vendor/studio-42