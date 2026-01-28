#!/usr/bin/env bash

locale=$1

if [[ "$locale" == "" ]]; then
    source ./devtools/locales.sh
    localeConcat='';
    for locale in "${LOCALES[@]}"
    do
        localeConcat+="$locale "
    done
    echo "locale is not set, allowed values: $localeConcat"
    exit
fi

bash ./bin/cake migrations migrate -p Queue
bash ./bin/cake migrations migrate
bash ./bin/cake seeds run InitDataSeed

if [[ "$locale" != "de_DE" ]]; then
    bash ./bin/cake seeds run InitDataSeed --source Seeds/locale/$locale
fi
