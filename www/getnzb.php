<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/nzb.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/releases.php");

$page = new Page;
$users = new Users;
$rel = new Releases;
$uid = 0;

//
// page is accessible only by the rss token, or logged in users.
//
if (!$users->isLoggedIn())
{
	if ((!isset($_GET["i"]) || !isset($_GET["r"])))
		$page->show403();

	$res = $users->getByIdAndRssToken($_GET["i"], $_GET["r"]);
	if (!$res)
		$page->show403();
		
	$uid = $res["ID"];
}
else
{
	$uid = $users->currentUserId();
}

//
// user requested a zip of guid,guid,guid releases
//
if (isset($_GET["id"]) && isset($_GET["zip"]) && $_GET["zip"] == "1")
{
	$guids = explode(",", $_GET["id"]);
	
	$zip = $rel->getZipped($guids);	

	if (strlen($zip) > 0)
	{
		$users->incrementGrabs($uid, count($guids));
		foreach ($guids as $guid)
			$rel->updateGrab($guid);

		$filename = date("Ymdhis").".nzb.zip";
		header("Content-type: application/octet-stream");
		header("Content-disposition: attachment; filename=".$filename);
		echo $zip;
		die();
	}
	else
		$page->show404();
}


if (isset($_GET["id"]))
{
	$reldata = $rel->getByGuid($_GET["id"]);

	if (!file_exists($page->site->nzbpath.$_GET["id"].".nzb.gz"))
		$page->show404();

	if ($reldata)
	{
		$rel->updateGrab($_GET["id"]);
		$users->incrementGrabs($uid);
	}
	else
		$page->show404();
		
	header("Content-type: application/x-nzb");
	header("X-DNZB-Name: ".$reldata["searchname"]);
	header("X-DNZB-Category: ".$reldata["category_name"]);
	header("X-DNZB-MoreInfo: "); //TODO:
	header("X-DNZB-NFO: "); //TODO:
	header("Content-Disposition: attachment; filename=".$reldata["searchname"].".nzb");
	
	readgzfile($page->site->nzbpath.$_GET["id"].".nzb.gz");
}

?>
