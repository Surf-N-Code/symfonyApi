#!/usr/bin/env bash
#Any other handling of message logic in here
/var/www/project/bin/console messenger:consume-messages >&1;