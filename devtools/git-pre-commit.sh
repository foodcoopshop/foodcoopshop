#!/usr/bin/env bash

################################################################################
#
#  The script below is derived from http://tech.zumba.com/2014/04/14/control-code-quality/
#
################################################################################

PROJECT=`php -r "echo dirname(dirname(dirname(realpath('$0'))));"`
STAGED_FILES_CMD=`git diff --cached --name-only --diff-filter=ACMR HEAD | grep \\\\.php`

# Determine if a file list is passed
if [ "$#" -eq 1 ]
then
	oIFS=$IFS
	IFS='
	'
	SFILES="$1"
	IFS=$oIFS
fi
SFILES=${SFILES:-$STAGED_FILES_CMD}

echo "Checking PHP Lint..."
for FILE in $SFILES
do
	php -l -d display_errors=0 $PROJECT/$FILE
	if [ $? != 0 ]
	then
		echo "Fix the error(s) before commit."
		exit 1
	fi
done

################################################################################
#
#  The script below is derived from https://github.com/imoldman/config/blob/master/pre-commit.git.sh
#
################################################################################

# A git hook script to find and fix trailing whitespace
# in your commits. Bypass it with the --no-verify option
# to git-commit

# detect platform
platform="win"
uname_result=`uname`
if [ "$uname_result" = "Linux" ]; then
  platform="linux"
elif [ "$uname_result" = "Darwin" ]; then
  platform="mac"
fi

# change IFS to ignore filename's space in |for|
IFS="
"
# autoremove trailing whitespace
for line in `git diff --check --cached | sed '/^[+-]/d'` ; do
  # get file name
  if [ "$platform" = "mac" ]; then
    file="`echo $line | sed -E 's/:[0-9]+: .*//'`"
  else
    file="`echo $line | sed -r 's/:[0-9]+: .*//'`"
  fi
  # display tips
  echo -e "auto remove trailing whitespace in \033[31m$file\033[0m!"
  # since $file in working directory isn't always equal to $file in index, so we backup it
  mv -f "$file" "${file}.save"
  # discard changes in working directory
  git checkout -- "$file"
  # remove trailing whitespace
  if [ "$platform" = "win" ]; then
    # in windows, `sed -i` adds ready-only attribute to $file(I don't kown why), so we use temp file instead
    sed 's/[[:space:]]*$//' "$file" > "${file}.bak"
    mv -f "${file}.bak" "$file"
  elif [ "$platform" == "mac" ]; then
    sed -i "" 's/[[:space:]]*$//' "$file"
  else
    sed -i 's/[[:space:]]*$//' "$file"
  fi
  git add "$file"
  # restore the $file
  sed 's/[[:space:]]*$//' "${file}.save" > "$file"
  rm "${file}.save"
done

if [ "x`git status -s | grep '^[A|D|M]'`" = "x" ]; then
  # empty commit
  echo
  echo -e "\033[31mNO CHANGES ADDED, ABORT COMMIT!\033[0m"
  exit 1
fi

# Now we can commit
exit

