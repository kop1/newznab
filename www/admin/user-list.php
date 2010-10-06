<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/users.php");
define("ITEMS_PER_PAGE", "25");

$page = new AdminPage();

$users = new Users();

$page->title = "User List";

$usercount = $users->getCount();

$offset = isset($_REQUEST["offset"]) ? $_REQUEST["offset"] : 0;
$ordering = $users->getBrowseOrdering();
$orderby = isset($_REQUEST["ob"]) && in_array($_REQUEST['ob'], $ordering) ? $_REQUEST["ob"] : '';

$page->smarty->assign('pagertotalitems',$usercount);
$page->smarty->assign('pageroffset',$offset);
$page->smarty->assign('pageritemsperpage',ITEMS_PER_PAGE);
$page->smarty->assign('pagerquerybase', WWW_TOP."/user-list.php?ob=".$orderby."&amp;offset=");

$pager = $page->smarty->fetch("pager.tpl");
$page->smarty->assign('pager', $pager);

foreach($ordering as $ordertype) 
	$page->smarty->assign('orderby'.$ordertype, WWW_TOP."/user-list.php?ob=".$ordertype."&amp;offset=0");

$userlist = $users->getRange($offset, ITEMS_PER_PAGE, $orderby);
$page->smarty->assign('userlist',$userlist);	

$page->content = $page->smarty->fetch('user-list.tpl');
$page->render();

?>
