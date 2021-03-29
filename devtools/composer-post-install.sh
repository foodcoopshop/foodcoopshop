#!/usr/bin/env bash

bash devtools/setup-git.sh

## remove overhead from tcpdf library
rm -Rf vendor/tecnickcom/tcpdf/examples
rm -Rf vendor/tecnickcom/tcpdf/fonts/ae_fonts_2.0
rm -Rf vendor/tecnickcom/tcpdf/fonts/dejavu-fonts-ttf-2.33
rm -Rf vendor/tecnickcom/tcpdf/fonts/free*
rm -Rf vendor/tecnickcom/tcpdf/fonts/dejavu-fonts-ttf-2.34/status.txt

rm -Rf vendor/studio-42/elfinder/.git
mkdir -p webroot/js/elfinder
cp -Rp vendor/studio-42/elfinder/* webroot/js/elfinder
rm -Rf vendor/studio-42