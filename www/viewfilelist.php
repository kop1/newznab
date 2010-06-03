<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
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


	if (!file_exists($page->site->nzbpath.$_GET["id"].".nzb.gz")) 
		$page->show404();

	ob_start();
	@readgzfile($page->site->nzbpath.$_GET["id"].".nzb.gz");
	$nzbfile = ob_get_contents();
	ob_end_clean();
		
	$ret = $nzb->nzbFileList($nzbfile);
		
	$page->smarty->assign('rel', $rel);
	$page->smarty->assign('files', $ret);

	$page->title = "File List";
	$page->meta_title = "View Nzb file list";
	$page->meta_keywords = "view,nzb,file,list,description,details";
	$page->meta_description = "View Nzb File List";
	
	$page->content = $page->smarty->fetch('viewfilelist.tpl');
	$page->render();
}

?>
