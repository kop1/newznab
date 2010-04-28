<?php

require_once($_SERVER['DOCUMENT_ROOT']."/lib/adminpage.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/nzb.php");

$page = new AdminPage();

$nzb = new NZB;
$nzb->connect();
$msgs = $nzb->updateGroupList();
$nzb->quit();

$page->smarty->assign('groupmsglist',$msgs);	

$page->title = "Update Newsgroup List";
$page->content = $page->smarty->fetch('admin/group-update.tpl');
$page->render();

?>
