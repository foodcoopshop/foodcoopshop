#!/usr/bin/env bash

# remove overhead from tcpdf library
rm -Rf vendor/tecnickcom/tcpdf/examples

#delete all files and folders in folder /fonts except starting with freesans* or helvetica*
find vendor/tecnickcom/tcpdf/fonts/ -maxdepth 1 -type f ! -name 'helvetica*' ! -name 'freesans*' -delete


rm -Rf vendor/studio-42/elfinder/.git
mkdir -p webroot/js/elfinder
cp -Rp vendor/studio-42/elfinder/* webroot/js/elfinder
rm -Rf vendor/studio-42
