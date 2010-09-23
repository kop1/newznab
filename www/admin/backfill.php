<?php

require("config.php");
require_once(WWW_DIR."/lib/backfill.php");

$backfill = new Backfill();
$backfill->backfillAllGroups();

?>
