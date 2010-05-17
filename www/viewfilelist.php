<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/binaries.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/nzb.php");

$page = new Page;
$users = new Users;
$releases = new Releases;
$nzb = new Nzb;

if (!$users->isLoggedIn())
	$page->show403();

if (isset($_GET["id"]))
{
	$rel = $releases->getByGuid($_GET["id"]);
	if (!$rel)
		$page->show404();

	if ($page->isPostBack())
	{
		$nzbdata = $nzb->getNZB($_POST);
		$page->smarty->assign('binaries',$nzbdata);
	
		header("Content-type: text/xml");
		header("X-DNZB-Name: ".$rel["searchname"]);
		header("X-DNZB-Category: ".$rel["category_name"]);
		header("X-DNZB-MoreInfo: "); //TODO:
		header("X-DNZB-NFO: "); //TODO:
		header("Content-Disposition: attachment; filename=".$rel["searchname"].".nzb");
	
		echo $page->smarty->fetch('nzb.tpl');
		die();
	}

	$binaries = new Binaries;
	$data = $binaries->getForReleaseGuid($_GET["id"]);

	$page->smarty->assign('rel', $rel);
	$page->smarty->assign('binaries', $data);

	$page->title = "File List";
	$page->meta_title = "View Nzb file list";
	$page->meta_keywords = "view,nzb,file,list,description,details";
	$page->meta_description = "View Nzb File List";
	
	$page->content = $page->smarty->fetch('viewfilelist.tpl');
	$page->render();
}

?>
