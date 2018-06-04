#!/usr/bin/env bash

#get and merge translations for main app
bin/cake i18n extract --ignore-model-validation --output src\\Locale --paths src,config --overwrite --extract-core no --merge yes --no-location --exclude plugins
msgmerge src/Locale/de_DE/default.po src/Locale/default.pot --output-file=src/Locale/de_DE/default.po
msgmerge src/Locale/en_US/default.po src/Locale/default.pot --output-file=src/Locale/en_US/default.po

#get and merge translations for admin plugin
bin/cake i18n extract --plugin Admin --ignore-model-validation --overwrite --extract-core no --merge yes --no-location
msgmerge plugins/Admin/src/Locale/de_DE/admin.po plugins/Admin/src/Locale/default.pot --output-file=plugins/Admin/src/Locale/de_DE/admin.po
msgmerge plugins/Admin/src/Locale/en_US/admin.po plugins/Admin/src/Locale/default.pot --output-file=plugins/Admin/src/Locale/en_US/admin.po

#todo: get and merge translations for network plugin