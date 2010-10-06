<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/groups.php");

$page = new AdminPage();

$groups = new Groups();
$grouplist = $groups->getAll();

$page->smarty->assign('grouplist',$grouplist);	

$page->title = "Group List";

$page->content = $page->smarty->fetch('group-list.tpl');
$page->render();

?>
