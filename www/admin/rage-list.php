<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/tvrage.php");

$page = new AdminPage();

$tvrage = new TvRage();

$page->title = "TV Rage List";

$ragecount = $tvrage->getCount();

$offset = isset($_REQUEST["offset"]) ? $_REQUEST["offset"] : 0;
$page->smarty->assign('pagertotalitems',$ragecount);
$page->smarty->assign('pageroffset',$offset);
$page->smarty->assign('pageritemsperpage',ITEMS_PER_PAGE);
$page->smarty->assign('pagerquerybase', WWW_TOP."/rage-list.php?offset=");
$pager = $page->smarty->fetch($page->getCommonTemplate("pager.tpl"));
$page->smarty->assign('pager', $pager);

$tvragelist = $tvrage->getRange($offset, ITEMS_PER_PAGE);
$page->smarty->assign('tvragelist',$tvragelist);	

$page->content = $page->smarty->fetch('rage-list.tpl');
$page->render();

?>
