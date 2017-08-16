#!/usr/bin/env bash

cp -f $(dirname $0)/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
