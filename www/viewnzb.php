<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/page.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/releases.php");

$page = new Page();

if (isset($_GET["id"]))
{
	$releases = new Releases;
	$data = $releases->getByGuid($_GET["id"]);

	$page->smarty->assign('release',$data);

	echo $page->smarty->fetch('viewnzb.tpl');
}

?>