<?php

require_once("config.php");
require_once(WWW_DIR."/lib/nzb.php");

$nzb = new NZB;
$nzb->updateAllGroups();

?>
