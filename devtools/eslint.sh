#!/usr/bin/env bash

eslint . --fix --ignore-pattern 'webroot/js/elfinder/*' --ignore-pattern 'vendor/*' --ignore-pattern '.history/*'