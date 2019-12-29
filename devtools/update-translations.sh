#!/usr/bin/env bash

source $(dirname $0)/locales.sh

#get and merge translations for main app
#to extract core strings change --extract-core to "yes"

bin/cake i18n extract --ignore-model-validation --output src\\Locale --paths src,config --overwrite --extract-core no --merge no --no-location --exclude plugins
for locale in "${LOCALES[@]}"
do
    msgmerge resources/locales/$locale/cake.po resources/locales/cake.pot --output-file=resources/locales/$locale/cake.po --width=1000
    msgmerge resources/locales/$locale/default.po resources/locales/default.pot --output-file=resources/locales/$locale/default.po --width=1000
done

#get and merge translations for admin plugin
bin/cake i18n extract --plugin Admin --ignore-model-validation --overwrite --extract-core no --merge yes --no-location
for locale in "${LOCALES[@]}"
do
    msgmerge plugins/Admin/resources/locales/$locale/admin.po plugins/Admin/resources/locales/default.pot --output-file=plugins/Admin/resources/locales/$locale/admin.po --width=1000
done

#get and merge translations for network plugin
bin/cake i18n extract --plugin Network --ignore-model-validation --overwrite --extract-core no --merge yes --no-location
for locale in "${LOCALES[@]}"
do
    msgmerge plugins/Network/resources/locales/$locale/network.po plugins/Network/resources/locales/default.pot --output-file=plugins/Network/resources/locales/$locale/network.po --width=1000
done
