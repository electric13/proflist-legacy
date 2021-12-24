#!/bin/bash

BCK_CMD=/usr/bin/gbak
USER=SYSDBA
PASSWD=masterkey

echo `date`" - Backup of DBs started" >> /var/log/messages

for FN in tlmp  ; do
    DBNAME="$FN"
    echo "processing database $FN..."
    if $BCK_CMD -user $USER -password $PASSWD -c /home/e13/$DBNAME.fbk /tmp/$DBNAME.gdb ; then
	echo "database restored succesfully"
    fi
done;
#echo `date`" - Backup of DBs finished" >> /var/log/messages
