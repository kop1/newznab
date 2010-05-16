<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/page.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/users.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/binaries.php");

$page = new Page;
$users = new Users;

if (!$users->isLoggedIn())
	$page->show403();

$binaries = new Binaries;

$page->addToBody("onload=\"setFocus('search');\"");
$page->meta_title = "Search Binaries";
$page->meta_keywords = "search,binaries,binsearch,nzb,description,details";
$page->meta_description = "Search for Binaries";

$results = array();

//TODO: bug here in javascript cludge to turn request in a get, rather than post is losing + signs in search query.
if (isset($_REQUEST["search"]))
{
	$page->smarty->assign('search', $_REQUEST["search"]);
	$results = $binaries->search($_REQUEST["search"]);
}

$page->smarty->assign('results', $results);

$page->content = $page->smarty->fetch('searchraw.tpl');
$page->render();

?>