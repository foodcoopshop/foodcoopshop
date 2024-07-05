#!/usr/bin/env bash

# remove overhead from tcpdf library
rm -Rf vendor/tecnickcom/tcpdf/examples

#delete all files and folders in folder /fonts except starting with freesans*
find vendor/tecnickcom/tcpdf/fonts/ ! -name freesans* -type f -delete

rm -Rf vendor/studio-42/elfinder/.git
mkdir -p webroot/js/elfinder
cp -Rp vendor/studio-42/elfinder/* webroot/js/elfinder
rm -Rf vendor/studio-42
