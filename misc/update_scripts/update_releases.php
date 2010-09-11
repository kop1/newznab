<?php

require("config.php");
require_once(WWW_DIR."/lib/releases.php");

$releases = new Releases;
$releases->processReleases(true);

?>
