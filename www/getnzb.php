<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/page.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/nzb.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/releases.php");

$page = new Page();

if (isset($_GET["id"]))
{
	$nzb = new NZB;
	$nzbdata = $nzb->getNZBforRelease($_GET["id"]);
	$page->smarty->assign('binaries',$nzbdata);

	$rel = new Releases;
	$reldata = $rel->getByGuid($_GET["id"]);

	header("Content-type: text/xml");
	header("Content-Disposition: attachment; filename=".$reldata["searchname"].".nzb");

	echo $page->smarty->fetch('nzb.tpl');
}

?>