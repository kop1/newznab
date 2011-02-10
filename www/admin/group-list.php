<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/groups.php");

$page = new AdminPage();

$groups = new Groups();

$groupcount = $groups->getCount();

$offset = isset($_REQUEST["offset"]) ? $_REQUEST["offset"] : 0;
$groupname = (isset($_REQUEST['groupname']) && !empty($_REQUEST['groupname'])) ? $_REQUEST['groupname'] : '';

$page->smarty->assign('groupname',$groupname);
$page->smarty->assign('groupstatus',$groupstatus);
$page->smarty->assign('pagertotalitems',$groupcount);
$page->smarty->assign('pageroffset',$offset);
$page->smarty->assign('pageritemsperpage',ITEMS_PER_PAGE);

$groupsearch = (isset($_REQUEST['groupname']) && !empty($_REQUEST['groupname'])) ? 'groupname='.$_REQUEST['groupname'].'&amp;' : '';
$page->smarty->assign('pagerquerybase', WWW_TOP."/group-list.php?".$groupsearch."offset=");
$pager = $page->smarty->fetch($page->getCommonTemplate("pager.tpl"));
$page->smarty->assign('pager', $pager);

$grouplist = $groups->getRange($offset, ITEMS_PER_PAGE);

$page->smarty->assign('grouplist',$grouplist);	

$page->title = "Group List";

$page->content = $page->smarty->fetch('group-list.tpl');
$page->render();

?>
