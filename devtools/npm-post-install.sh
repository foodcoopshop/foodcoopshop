#!/usr/bin/env bash

rm -Rf ../webroot/node_modules/jquery-backstretch/examples
rm -Rf ../webroot/node_modules/jquery-backstretch/test
rm -Rf ../webroot/node_modules/@fortawesome/fontawesome-free/js
rm -Rf ../webroot/node_modules/@fortawesome/fontawesome-free/metadata
rm -Rf ../webroot/node_modules/@fortawesome/fontawesome-free/svgs
rm ../webroot/node_modules/@fortawesome/fontawesome-free/css/all.min.css
rm ../webroot/node_modules/@fortawesome/fontawesome-free/css/fontawesome.css
rm ../webroot/node_modules/@fortawesome/fontawesome-free/css/fontawesome.min.css
rm ../webroot/node_modules/@fortawesome/fontawesome-free/css/v4-shims.css
rm ../webroot/node_modules/@fortawesome/fontawesome-free/css/v4-shims.min.css
rm -Rf ../webroot/node_modules/jquery-ui/external
rm -Rf ../webroot/node_modules/tooltipster/demo
rm -Rf ../webroot/node_modules/tooltipster/doc
rm -Rf ../webroot/node_modules/chart.js/dist/docs

cp -R ../webroot/node_modules/@fortawesome/fontawesome-free/webfonts ../webroot/webfonts
cp -R ../webroot/node_modules/jquery-ui/dist/themes/smoothness/images ../webroot/cache/images

cp ../config/elfinder/elfinder.html ../webroot/js/elfinder/elfinder.html
cp ../config/elfinder/php/connector.minimal.php ../webroot/js/elfinder/php/connector.minimal.php
