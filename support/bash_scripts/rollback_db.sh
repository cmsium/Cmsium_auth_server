#!/usr/bin/env bash
if  [ $1 ]
then
    dir=$(find . -type d -name $1)
    if [[ "$1" == last || $dir = "./support/migration_tool/migrations/$1" ]]
    then
        php ./support/migration_tool/rollback.php $1
    else
        echo "Migration not found!"
    fi
else
    echo "You have to pass an argument!"
fi