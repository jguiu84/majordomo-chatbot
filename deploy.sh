#! /bin/bash

SERVER="max.creaclick.net"
PROJECT_DIR="/srv/www/mihai.creaclick.net"
EXCLUDE_FILE="exclude.txt"
PASS=$1
if [ "$PASS" = "" ]
then
    SSH_LOGIN=""
else
    SSH_LOGIN="sshpass -p '$PASS'"
fi
rsync -ncrve "$SSH_LOGIN ssh -p1105" --exclude-from=$EXCLUDE_FILE --delete ./ * mihai@$SERVER:$PROJECT_DIR  | grep -v "building file list" | grep -v "bytes/sec" | grep -v "DRY RUN" | tee /tmp/rsync_deploy.log

echo
echo -n "$(tput setaf 1)Subir? [y/N]$(tput sgr0) "
read QQ
echo
if [ "$QQ" = "y" ]; then
    rsync -crve "$SSH_LOGIN ssh -p1105" --exclude-from=$EXCLUDE_FILE --delete ./ * mihai@$SERVER:$PROJECT_DIR
fi

echo "$(tput setaf 2)"
echo "┏━━━━┓"
echo "┃ OK ┃"
echo "┗━━━━┛"
echo "$(tput sgr0)"

echo "--------------"
echo "RUN THESE COMMANDS FOR FIRST TIME DEPLOY"
echo ""
echo "cd storage/"
echo "mkdir app"
echo "mkdir app/public"
echo "mkdir -p framework/{sessions,views,cache}"
echo "mkdir -p framework/cache/data"
echo "chmod -R 775 framework"

echo "sudo chown -R $USER:www-data storage"
echo "sudo chown -R $USER:www-data bootstrap/cache"

echo "sudo chmod -R 775 storage"
echo "sudo chmod -R 775 bootstrap/cache"