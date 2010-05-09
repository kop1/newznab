<?php

require_once($_SERVER['DOCUMENT_ROOT']."/lib/nzb.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/adminpage.php");

$page = new AdminPage();

$nzb = new NZB;
$proccount = $nzb->processReleases();

$page->title = "Updating Releases";
$page->smarty->assign('proccount', $proccount);
$page->content = $page->smarty->fetch('admin/release-update.tpl');
$page->render();

?>
