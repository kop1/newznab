#!/bin/bash

# if you move the cronned files to a different place you
# may need to tweak the path in /path/to/misc/cron_update/setpath to point to root of newznab
/path/to/php /path/to/misc/cron_update/update_binaries.php
/path/to/php /path/to/misc/cron_update/update_releases.php