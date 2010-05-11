<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/page.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/nzb.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/releases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/users.php");

$page = new Page;
$users = new Users;

//
// page is accessible only by the rss token, or logged in users.
//
if (!$users->isLoggedIn())
{
	if (!isset($_GET["i"]) || !isset($_GET["r"]))
		$page->show403();

	$res = $users->getByIdAndRssToken($_GET["i"], $_GET["r"]);
	if (!$res)
		$page->show403();
}

if (isset($_GET["id"]))
{
	$rel = new Releases;
	$reldata = $rel->getByGuid($_GET["id"]);
	if ($reldata)
	{
		$rel->updateGrab($_GET["id"]);
		$users->incrementGrabs($users->currentUserId());
	}
	else
		$page->show404();

	$nzb = new NZB;
	$nzbdata = $nzb->getNZBforRelease($_GET["id"]);
	$page->smarty->assign('binaries',$nzbdata);

	header("Content-type: text/xml");
	header("X-DNZB-Name: ".$reldata["searchname"]);
	header("X-DNZB-Category: ".$reldata["category_name"]);
	header("X-DNZB-MoreInfo: "); //TODO:
	header("X-DNZB-NFO: "); //TODO:
	header("Content-Disposition: attachment; filename=".$reldata["searchname"].".nzb");

	echo $page->smarty->fetch('nzb.tpl');
}

?>