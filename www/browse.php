<?php
require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/category.php");
define("ITEMS_PER_PAGE", "50");

$page = new Page;
$users = new Users;
$releases = new Releases;

if (!$users->isLoggedIn())
	$page->show403();

$category = -1;
if (isset($_REQUEST["t"]))
	$category = $_REQUEST["t"] + 0;

$catarray = array();
$catarray[] = $category;	
	
$browsecount = $releases->getBrowseCount($catarray);

$offset = isset($_REQUEST["offset"]) ? $_REQUEST["offset"] : 0;
$ordering = $releases->getBrowseOrdering();
$orderby = isset($_REQUEST["ob"]) && in_array($_REQUEST['ob'], $ordering) ? $_REQUEST["ob"] : '';

$results = array();
$results = $releases->getBrowseRange($catarray, $offset, ITEMS_PER_PAGE, $orderby);

$page->smarty->assign('pagertotalitems',$browsecount);
$page->smarty->assign('pageroffset',$offset);
$page->smarty->assign('pageritemsperpage',ITEMS_PER_PAGE);
$page->smarty->assign('pagerquerybase', WWW_TOP."/browse?t=".$category."&amp;ob=".$orderby."&amp;offset=");
$page->smarty->assign('pagerquerysuffix', "#results");

$pager = $page->smarty->fetch("pager.tpl");
$page->smarty->assign('pager', $pager);

if ($category == -1)
	$page->smarty->assign("catname","All");			
else
{
	$cat = new Category();
	$cdata = $cat->getById($category);
	if ($cdata)
		$page->smarty->assign('catname',$cdata["title"]);			
	else
		$page->show404();
}

foreach($ordering as $ordertype) 
	$page->smarty->assign('orderby'.$ordertype, WWW_TOP."/browse?t=".$category."&amp;ob=".$ordertype."&amp;offset=0");

$page->smarty->assign('results',$results);		

$page->meta_title = "Browse Nzbs";
$page->meta_keywords = "browse,nzb,description,details";
$page->meta_description = "Browse for Nzbs";
	
$page->content = $page->smarty->fetch('browse.tpl');
$page->render();

?>
