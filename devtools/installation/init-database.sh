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

./bin/cake migrations migrate -p Queue
./bin/cake migrations migrate --source Migrations/init
./bin/cake migrations seed --source Seeds/locale/$locale --seed InitDataSeed
./bin/cake migrations seed --seed InitDataSeed
./bin/cake migrations migrate
