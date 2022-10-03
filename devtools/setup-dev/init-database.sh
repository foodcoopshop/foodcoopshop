#!/usr/bin/env bash

locale=$1

./bin/cake migrations migrate -p Queue
./bin/cake migrations migrate
./bin/cake migrations seed --source Seeds/locale/$locale --seed InitDataSeed
./bin/cake migrations seed --seed InitDataSeed