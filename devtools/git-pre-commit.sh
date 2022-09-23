#!/usr/bin/env bash
#
# - git checkout the staged versions to temp dir
# - run PHP lint on temp dir
# -- checking *.php, *.inc and *.ctp
# -- see fcs-ruleset.xml, too
# -- abort commit on errors
# - run phpcbf on the temp dir ()
# -- applying automatic repairs for tests in fcs-rules.xml
# -- don't abort commit
# - run phpcs on the temp dir
# -- must pass all tests in fcs-rules.xml now
# -- abort on errors
# - re-add all changes
# - remove temp dir
# - allow commit

# CodeSniffer and CodeBeautifierFixer commands
PHPCBF="vendor/bin/phpcbf --standard=devtools/fcs-rules.xml"
PHPCS="vendor/bin/phpcs --standard=devtools/fcs-rules.xml"

# note the current directory
PWD=`pwd`

# get the project dir from being in [project]/devtools/ and change to it
DIR=`php -r "echo dirname(dirname(realpath('$0')));"`
cd "$DIR"

STAGED_FILES=`git diff --cached --name-only --diff-filter=ACMR HEAD`
if [ "x$STAGED_FILES" == "x" ]
then
    echo "Nothing to do on pre-commit"
    exit 0
fi

# the temp directory used, within $DIR
WORK_DIR=`mktemp -d --tmpdir="$DIR" git.XXXXXXXX`

# check if tmp dir was created
if [[ ! "$WORK_DIR" || ! -d "$WORK_DIR" ]]; then
  echo "User `whoami` could not create temp dir in $DIR"
  exit 1
fi
echo "Created temp dir $WORK_DIR"

# deletes the temp directory
function cleanup {
  rm -rf "$WORK_DIR"
  echo "Deleted temp working directory $WORK_DIR"
  cd "$PWD"
}

# register the cleanup function to be called on the EXIT signal
trap cleanup EXIT

# get a temp copy of all staged files
while read -r file <&3 || [[ -n "$file" ]];
do
    if [ "$file" != "" ]
    then
        `git checkout-index --prefix="$WORK_DIR/" -- "$file"`
        phpfile=`echo $file | grep \\\\.php`
        phpfile2=`echo $file | grep \\\\.ctp`
        phpfile="$phpfile$phpfile2"
        phpfile2=`echo $file | grep \\\\.inc`
        phpfile="$phpfile$phpfile2"
        if [ "$phpfile" != "" ]
        then
            php -l -d display_errors=0 "$WORK_DIR/$file"
            if [ $? != 0 ]
            then
                echo "Fix the error(s) before commit."
                exit 1
            fi
        fi
    fi
done 3<<< "$STAGED_FILES"

# do the coding standards tests
$PHPCBF "$WORK_DIR"

$PHPCS "$WORK_DIR"
if [ $? != 0 ]
then
    # there were problems that could not be solved
    echo "Fix the error(s) and add the changed files before next commit."
    exit 1
fi

# add the changed files to staging area
while read -r file <&3 || [[ -n "$file" ]];
do
    if [ "$file" != "" ]
    then
        # do not overwrite worktree files
        if [ -f "$DIR/$file" ]
        then
            cp -f "$DIR/$file" "$DIR/$file.save" 2> /dev/null
        fi

        cp -f "$WORK_DIR/$file" "$DIR/$file" 2> /dev/null
        `git add "$file"`

        if [ -f "$DIR/$file.save" ]
        then
            mv -f "$DIR/$file.save" "$DIR/$file" 2> /dev/null
        fi
    fi
done 3<<< "$STAGED_FILES"

exit 0
