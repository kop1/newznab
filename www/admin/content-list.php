<?php

require_once($_SERVER['DOCUMENT_ROOT']."/lib/adminpage.php");

$page = new AdminPage();

$contents = new Contents();
$contentlist = $contents->getAll();
$page->smarty->assign('contentlist',$contentlist);	

$page->title = "Content List";

$page->content = $page->smarty->fetch('admin/content-list.tpl');
$page->render();

?>
