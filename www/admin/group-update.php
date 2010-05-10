<?php

require_once($_SERVER['DOCUMENT_ROOT']."/lib/adminpage.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/groups.php");

$page = new AdminPage();

$groups = new Groups;
$msgs = $groups->updateGroupList();

$page->smarty->assign('groupmsglist',$msgs);	

$page->title = "Update Newsgroup List";
$page->content = $page->smarty->fetch('admin/group-update.tpl');
$page->render();

?>
