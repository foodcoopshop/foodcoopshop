#!/usr/bin/env bash

source $(dirname $0)/locales.sh

for locale in "${LOCALES[@]}"
do
    msgattrib --no-fuzzy --no-obsolete --empty -o resources/locales/$locale/cake.po resources/locales/$locale/cake.po --width=1000
    msgattrib --no-fuzzy --no-obsolete --empty -o resources/locales/$locale/default.po resources/locales/$locale/default.po --width=1000
done

for locale in "${LOCALES[@]}"
do
    msgattrib --no-fuzzy --no-obsolete --empty -o plugins/Admin/resources/locales/$locale/admin.po plugins/Admin/resources/locales/$locale/admin.po --width=1000
done

for locale in "${LOCALES[@]}"
do
    msgattrib --no-fuzzy --no-obsolete --empty -o plugins/Network/resources/locales/$locale/network.po plugins/Network/resources/locales/$locale/network.po --width=1000
done
