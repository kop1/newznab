<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/releases.php");

$page = new Page;
$users = new Users;

if (!$users->isLoggedIn())
	$page->show403();

$releases = new Releases;

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

	$page->smarty->assign('search', $_REQUEST["search"]);
	$results = $releases->search($_REQUEST["search"], $categoryId);
}

$page->smarty->assign('results', $results);

$page->content = $page->smarty->fetch('search.tpl');
$page->render();

?>
