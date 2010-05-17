<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/groups.php");

$page = new AdminPage();

$groups = new Groups;
$msgs = $groups->updateGroupList();

$page->smarty->assign('groupmsglist',$msgs);	

$page->title = "Update Newsgroup List";
$page->content = $page->smarty->fetch('admin/group-update.tpl');
$page->render();

?>
