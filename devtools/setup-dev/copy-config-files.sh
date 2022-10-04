#!/usr/bin/env bash

cp -n ./config/custom_config.dev.php ./config/custom_config.php
cp -n ./config/credentials.default.php ./config/credentials.php
cp -n ./config/asset_compress.dev.ini ./config/asset_compress.local.ini