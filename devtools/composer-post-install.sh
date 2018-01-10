#!/usr/bin/env bash

bash devtools/setup-git.sh

rm -Rf Plugin/AssetCompress/.git

rm -Rf Vendor/sunhater/kcfinder/.git
mkdir -p webroot/node_modules/kcfinder
cp -Rp Vendor/sunhater/kcfinder/* webroot/node_modules/kcfinder
rm -Rf Vendor/sunhater