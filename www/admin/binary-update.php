<?php

require_once("config.php");
require_once(WWW_DIR."/lib/nzb.php");
require_once(WWW_DIR."/lib/adminpage.php");

$page = new AdminPage();

$nzb = new NZB;
$nzb->updateAllGroups();

?>
