#!/usr/bin/env bash

bin/cake i18n extract --ignore-model-validation --output src\\Locale --paths src,config --overwrite --extract-core no --merge yes --no-location

msgmerge src/Locale/de_DE/default.po src/Locale/default.pot --output-file=src/Locale/de_DE/default.po
msgmerge src/Locale/en_US/default.po src/Locale/default.pot --output-file=src/Locale/en_US/default.po