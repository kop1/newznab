<?php

require_once("config.php");
require_once(WWW_DIR."/lib/binaries.php");
require_once(WWW_DIR."/lib/adminpage.php");

$page = new AdminPage();

$binaries = new Binaries();
$binaries->updateAllGroups();

?>
