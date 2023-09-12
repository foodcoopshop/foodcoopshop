#!/usr/bin/env bash

# allows script to be called from /webroot and root directory
SCRIPT=$(readlink -f "$0")
APP=$(dirname "$SCRIPT")/..

rm -Rf $APP/webroot/node_modules/jquery-backstretch/examples
rm -Rf $APP/webroot/node_modules/jquery-backstretch/test
rm -Rf $APP/webroot/node_modules/@fortawesome/fontawesome-free/js
rm -Rf $APP/webroot/node_modules/@fortawesome/fontawesome-free/metadata
rm -Rf $APP/webroot/node_modules/@fortawesome/fontawesome-free/svgs
rm $APP/webroot/node_modules/@fortawesome/fontawesome-free/css/all.min.css 2> /dev/null
rm $APP/webroot/node_modules/@fortawesome/fontawesome-free/css/fontawesome.css 2> /dev/null
rm $APP/webroot/node_modules/@fortawesome/fontawesome-free/css/fontawesome.min.css 2> /dev/null
rm $APP/webroot/node_modules/@fortawesome/fontawesome-free/css/v4-shims.css 2> /dev/null
rm $APP/webroot/node_modules/@fortawesome/fontawesome-free/css/v4-shims.min.css 2> /dev/null
rm -Rf $APP/webroot/node_modules/jquery-ui/external
rm -Rf $APP/webroot/node_modules/tooltipster/demo
rm -Rf $APP/webroot/node_modules/tooltipster/doc
rm -Rf $APP/webroot/node_modules/chart.js/dist/docs

cp -R $APP/webroot/node_modules/@fortawesome/fontawesome-free/webfonts $APP/webroot
cp -R $APP/webroot/node_modules/jquery-ui/dist/themes/smoothness/images $APP/webroot/cache

cp $APP/config/elfinder/php/connector.minimal.php $APP/webroot/js/elfinder/php/connector.minimal.php
