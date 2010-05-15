<?php

include "setpath.php";
require_once($_SERVER['DOCUMENT_ROOT']."/lib/nzb.php");

$nzb = new NZB;
$nzb->updateAllGroups();

?>