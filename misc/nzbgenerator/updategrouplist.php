<?

include('db.php');
include('nzb.class.php');

$nzb = new NZB;
$nzb -> connect();
$nzb -> updateGroupList();
$nzb -> quit();

?>