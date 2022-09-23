#!/usr/bin/env bash

mkdir -p .git/hooks && cp -f devtools/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
