<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/releases.php");

$page = new Page;
$users = new Users;
$releases = new Releases;

if (!$users->isLoggedIn())
	$page->show403();

$page->meta_title = "Search Nzbs";
$page->meta_keywords = "search,nzb,description,details";
$page->meta_description = "Search for Nzbs";

$results = array();

//TODO: bug here in javascript cludge to turn request in a get, rather than post is losing + signs in search query.
if (isset($_REQUEST["search"]))
{
	$categoryId = array();
	if (isset($_GET["t"]))
		$categoryId = explode(",",$_REQUEST["t"]);
	else
		$categoryId[] = -1;		

	$ordering = $releases->getBrowseOrdering();
	$orderby = isset($_REQUEST["ob"]) && in_array($_REQUEST['ob'], $ordering) ? $_REQUEST["ob"] : '';
	foreach($ordering as $ordertype) {
		$page->smarty->assign('orderby'.$ordertype, WWW_TOP."/search.php?search=".htmlentities($_REQUEST["search"])."&amp;t=".(implode(',',$categoryId))."&amp;ob=".$ordertype);
	}
	$page->smarty->assign('category', $categoryId);
	$page->smarty->assign('search', $_REQUEST["search"]);
	$page->smarty->assign('lastvisit', $page->userdata['lastlogin']);
	$results = $releases->search($_REQUEST["search"], $categoryId, 0, 1000, $orderby, -1, $page->userdata["categoryexclusions"]);
}

$page->smarty->assign('results', $results);

$page->content = $page->smarty->fetch('search.tpl');
$page->render();

?>
