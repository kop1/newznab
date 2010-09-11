#!/bin/sh
# call this script from within screen to get binaries and processes releases

set -e

export NEWZNAB_PATH="/usr/local/www/newznab/misc/update_scripts"
export NEWZNAB_SLEEP_TIME="600" # in seconds

while :

 do
/usr/bin/php5 ${NEWZNAB_PATH}/update_binaries.php
/usr/bin/php5 ${NEWZNAB_PATH}/update_releases.php
sleep ${NEWZNAB_SLEEP_TIME}

done
