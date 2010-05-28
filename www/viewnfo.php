<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/util.php");

$page = new Page;
$users = new Users;
$releases = new Releases;

if (!$users->isLoggedIn())
	$page->show403();
	
if (isset($_GET["id"]))
{
	$rel = $releases->getByGuid($_GET["id"]);

	if (!$rel)
		$page->show404();

	$nfo = $releases->getReleaseNfo($rel['ID']);
	$nfo['nfoUTF'] = cp437toUTF($nfo['nfo']);
	
	
	$page->smarty->assign('rel', $rel);
	$page->smarty->assign('nfo', $nfo);

	$page->title = "NFO File";
	$page->meta_title = "View Nfo";
	$page->meta_keywords = "view,nzb,nfo,description,details";
	$page->meta_description = "View Nfo File";
	
	$page->content = $page->smarty->fetch('viewnfo.tpl');
	$page->render();
}

?>
