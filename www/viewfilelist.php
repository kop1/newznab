<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/releases.php");

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

	$zd = gzopen($page->site->nzbpath.$_GET["id"].".nzb.gz", "r");
	if (!$zd)
		$page->show404();
	else
	{
		$nzbfile = gzread($zd, 50000);
		gzclose($zd);	
	}
		
		
	$page->smarty->assign('rel', $rel);

	$page->title = "File List";
	$page->meta_title = "View Nzb file list";
	$page->meta_keywords = "view,nzb,file,list,description,details";
	$page->meta_description = "View Nzb File List";
	
	$page->content = $page->smarty->fetch('viewfilelist.tpl');
	$page->render();
}

?>
