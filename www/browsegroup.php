<?php
require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/groups.php");

$page = new Page;
$users = new Users;
$groups = new Groups;

if (!$users->isLoggedIn())
	$page->show403();


$grouplist = $groups->getAll();
$page->smarty->assign('results',$grouplist);		

$page->meta_title = "Browse Groups";
$page->meta_keywords = "browse,groups,description,details";
$page->meta_description = "Browse groups";
	
$page->content = $page->smarty->fetch('browsegroup.tpl');
$page->render();

?>
