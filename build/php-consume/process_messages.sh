#!/usr/bin/env bash
#Any other handling of message logic in here
./wait-for-it.sh $DATABASE_HOST_PORT -t 1200
/var/www/project/bin/console messenger:consume-messages >&1;