#!/usr/bin/env sh

################################################################################
#
#  The script below is derived from http://tech.zumba.com/2014/04/14/control-code-quality/
#
################################################################################

cp devtools/git-pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
cp devtools/git-pre-push .git/hooks/pre-push
chmod +x .git/hooks/pre-push
