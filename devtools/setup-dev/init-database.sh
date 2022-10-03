#!/usr/bin/env bash

locale=$1

if [[ "$locale" == "" ]]; then
    echo "locale is not set"
    exit
fi

./bin/cake migrations migrate -p Queue
./bin/cake migrations migrate --source Migrations/init
./bin/cake migrations seed --source Seeds/locale/$locale --seed InitDataSeed
./bin/cake migrations seed --seed InitDataSeed
./bin/cake migrations migrate
