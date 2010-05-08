<?php

require_once($_SERVER['DOCUMENT_ROOT']."/lib/adminpage.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/groups.php");

$page = new AdminPage();

$groups = new Groups();
$grouplist = $groups->getAll();
$page->smarty->assign('grouplist',$grouplist);	

$page->title = "Group List";

$page->content = $page->smarty->fetch('admin/group-list.tpl');
$page->render();

?>
