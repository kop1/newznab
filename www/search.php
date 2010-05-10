<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/page.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/users.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/releases.php");

$page = new Page;
$users = new Users;

if (!$users->isLoggedIn())
	$page->show403();

$releases = new Releases;

$page->meta_title = "Search Nzbs";
$page->meta_keywords = "search,nzb,description,details";
$page->meta_description = "Search for Nzbs";

$results = array();

if (isset($_REQUEST["search"]))
{
	$page->smarty->assign('search', $_REQUEST["search"]);
	$results = $releases->search($_REQUEST["search"]);
}

$page->smarty->assign('results', $results);

$page->content = $page->smarty->fetch('search.tpl');
$page->render();

?>