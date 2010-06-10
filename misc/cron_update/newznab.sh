#!/bin/sh
# do not forget to change NEWZNAB variables

set -e

export NEWZNAB_PATH="/usr/local/www/newznab/misc/cron_update"
export NEWZNAB_BINUP="update_binaries.php"

export PATH="${PATH}:/usr/sbin:/usr/bin:/usr/games:/usr/local/sbin:/usr/local/bin"
PIDFILE="/var/run/newznab_binup.pid"

case "$1" in
  start)
        echo -n "Starting Newznab binaries update"
        cd ${NEWZNAB_PATH}
        (while (true);do cd ${NEWZNAB_PATH} && php ${NEWZNAB_BINUP} 2>&1 > /dev/null ; sleep 10 ;done) & 
        PID=`echo $!` 
        echo $PID > $PIDFILE
        ;;
  stop)
        echo -n "Stopping Newznab binaries update"
        kill -9 `cat /var/run/newznab_binup.pid`
        ;;

  *)
        echo "Usage: $0 [start|stop]"
        exit 1
esac
exit 0