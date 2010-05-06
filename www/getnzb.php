<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/page.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/nzb.php");

$page = new Page();

if (isset($_GET["id"]))
{
	$nzb = new NZB;
	$nzbdata = $nzb->getNZBforRelease($_GET["id"]);

	$page->smarty->assign('binaries',$nzbdata);

	header("Content-type: text/xml");
	echo $page->smarty->fetch('nzb.tpl');
}

?>