<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/tvrage.php");
define("ITEMS_PER_PAGE", "25");

$page = new AdminPage();

$tvrage = new TvRage();

$page->title = "TV Rage List";

$ragecount = $tvrage->getCount();

$offset = isset($_REQUEST["offset"]) ? $_REQUEST["offset"] : 0;
$page->smarty->assign('pagertotalitems',$ragecount);
$page->smarty->assign('pageroffset',$offset);
$page->smarty->assign('pageritemsperpage',ITEMS_PER_PAGE);
$page->smarty->assign('pagerquerybase', "/admin/rage-list.php?offset=");
$pager = $page->smarty->fetch("pager.tpl");
$page->smarty->assign('pager', $pager);

$tvragelist = $tvrage->getRange($offset, ITEMS_PER_PAGE);
$page->smarty->assign('tvragelist',$tvragelist);	

$page->content = $page->smarty->fetch('admin/rage-list.tpl');
$page->render();

?>
