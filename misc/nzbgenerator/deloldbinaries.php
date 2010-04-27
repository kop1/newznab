<?
set_time_limit(0);

include('db.php');
include('nzb.class.php');

$nzb = new NZB;
$nzb -> delOldBinaries();

?>