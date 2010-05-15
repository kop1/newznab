<?php

include "setpath.php";
require_once($_SERVER['DOCUMENT_ROOT']."/lib/releases.php");

$releases = new Releases;
$releases->processReleases(true);

?>