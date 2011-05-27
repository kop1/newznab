<?php
/*
  DESCRIPTION:
    This script is an alternative to backfill.php.
    It allows you to backfill based on a specific date, bypassing
    the "backfill target" setting for each group, used by backfill.php
  
  PURPOSE:
    If you are backfilling many groups over a long span of time, the # of days
    set as your backfill target can become quickly outdated, resulting in
    potential gaps in your database. In this case, it may be more practical
    to specify a date explicity and just let the backfill work from there.
  
  USAGE:
    $ php backfill_date.php 2011-05-15
    => Script will backfill ALL active groups from May 15, 2011
    
    $ php backfill_date.php 2011-05-15 alt.binaries.games.xbox
    => Script will backfill ONLY a.b.games.xbox from May 15, 2011
*/

require("config.php");
require_once(WWW_DIR."/lib/backfill.php");

if (strtotime($argv[1]) && (strtotime($argv[1]) < $strtotime('now'))) {
  $groupName = (isset($argv[2]) ? $argv[2] : '');
  $backfill = new Backfill();
  $backfill->backfillAllGroups($groupName, strtotime($argv[1]));
} else {
  echo "You must provide a backfill date in the format YYYY-MM-DD to use backfill_range.php\n";
  echo "example: backfill_range.php 2002-04-27 alt.binaries.games.xbox\n";
  echo "This will backfill your index with everything posted to a.b.g.x since April 27, 2002";
  echo "If you choose not to provide a groupname, all active groups will be backfilled.\n";
  echo "\nIf you do not want to use a date, use the backfill.php script instead.\n";
}

?>
