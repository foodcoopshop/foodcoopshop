#!/usr/bin/env bash
bash devtools/setup-git.sh
rm -Rf vendor/studio-42/elfinder/.git
mkdir -p webroot/js/elfinder
ls -l vendor
ls -l vendor/studio-42
cp -Rp vendor/studio-42/elfinder/* webroot/js/elfinder
#rm -Rf vendor/studio-42