#!/usr/bin/env bash
# use CodeSniffer
PHPCS="Vendor/bin/phpcs"
# use CodeBeautifierFixer
#PHPCS="Vendor/bin/phpcbf"

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
				$PHPCS --standard=PSR2 --encoding=utf-8 -n -p $filelist
				if [ $? != 0 ]
				then
					echo "Fix the error(s) and commit the changed files before push."
					exit 1
				fi
		fi
	fi
done
exit 0

