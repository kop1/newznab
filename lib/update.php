<?
set_time_limit(0);
$timeout = 36;

include('nzb.class.php');

while(1) {

	$nzb = new NZB;
	$nzb -> connect();
	$nzb -> updateAllGroups();
	$nzb -> quit();

	echo "\nDone... waiting {$timeout} seconds...\n\n";
	sleep($timeout);
}

?>
