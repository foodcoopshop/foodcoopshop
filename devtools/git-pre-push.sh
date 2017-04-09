#!/usr/bin/env bash

# make this use phpcs only. In this hook it's impossible to change files. Use "git --work-tree={tmppath}" for all commands
# Generally:
# - git checkout all changed files in the last commited version to temp dir
# - run phpcs on the temp dir (removing the file filter, have all tests in fcs-rules.xml)
# - remove temp dir
# - report errors
# - allow push if no errors

# use CodeSniffer, CodeBeautifierFixer cannot be used here
PHPCS="Vendor/bin/phpcs"

remote="$1"
url="$2"
#signals "no matching branch"
z40=0000000000000000000000000000000000000000

IFS=' '
while read -r local_ref local_sha remote_ref remote_sha;
do
	if [ "$local_sha" = $z40 ]
	then
		# Handle delete
		exit 1
	else
		if [ "$remote_sha" = $z40 ]
		then
			# New branch, examine all commits
			range="$local_sha"
		else
			# Update to existing branch, examine new commits
			range="$remote_sha..$local_sha"
		fi

		# Get commit file list
		files=`git log --pretty=format:'' --name-only --no-merges "$range"`

		filelist=''
		if [ "$files" != "" ]
		then
			while read -r file <&3 || [[ -n $file ]];
			do
				if [ "$file" != "" ]
				then
					if [ "$filelist" != "" ]
					then
						filelist="$filelist "
					fi
					filelist="$filelist$file"
				fi
			done 3<<< "$files"
		fi

		if [ "$filelist" != "" ]
		then
			echo "Running Code Sniffer"
			$PHPCS --standard=devtools/fcs-rules.xml $filelist
			if [ $? != 0 ]
			then
				echo "Fix the error(s) and commit the changed files before push."
				exit 1
			fi
		fi
	fi
done
exit 0

