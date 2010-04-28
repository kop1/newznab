<?php

set_time_limit(0);
$timeout = 36;

require_once($_SERVER['DOCUMENT_ROOT']."/lib/nzb.php");

while(1) {

	$nzb = new NZB;
	$nzb -> connect();
	$nzb -> updateAllGroups();
	$nzb -> quit();

	echo "\nDone... waiting {$timeout} seconds...\n\n";
	sleep($timeout);
}

?>
