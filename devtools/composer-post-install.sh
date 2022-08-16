#!/usr/bin/env bash

bash devtools/setup-git.sh

# remove overhead from tcpdf library
rm -Rf vendor/tecnickcom/tcpdf/examples

#delete all files and folders in folder /fonts except starting with helvetica*
find vendor/tecnickcom/tcpdf/fonts/ ! -name helvetica* -delete

cp src/Assets/Filter/CleanCss.php vendor/markstory/mini-asset/src/Filter/

rm -Rf vendor/studio-42/elfinder/.git
mkdir -p webroot/js/elfinder
cp -Rp vendor/studio-42/elfinder/* webroot/js/elfinder
rm -Rf vendor/studio-42