#!/usr/bin/env bash
DATE=$(date +%Y-%m-%d-%H-%M)
MIGR_DIR_PATH=$(egrep -o 'migrations_path=.*' config/config.ini | cut -d \= -f 2)

if  [ $1 ]
then
    MIGR_PATH=".${MIGR_DIR_PATH}/${DATE}_${1}"
else
    MIGR_PATH=".${MIGR_DIR_PATH}/${DATE}"
fi

mkdir $MIGR_PATH
touch $MIGR_PATH/up.sql
touch $MIGR_PATH/down.sql