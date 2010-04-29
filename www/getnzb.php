<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/page.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/nzb.php");

$page = new Page();

$ids = array(1,2,3);

$nzb = new NZB;
$nzbdata = $nzb->getNZB($ids);

$page->smarty->assign('binaries',$nzbdata);

//header("Content-type: text/xml");
echo $page->smarty->fetch('nzb.tpl');

?>