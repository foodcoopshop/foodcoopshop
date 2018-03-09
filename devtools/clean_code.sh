#!/usr/bin/env bash

vendor/bin/phpcbf --standard=devtools/fcs-rules.xml config src plugins tests webroot
vendor/bin/phpcs --standard=devtools/fcs-rules.xml config src plugins tests webroot
