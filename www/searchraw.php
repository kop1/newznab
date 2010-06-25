<?php
require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/binaries.php");
require_once(WWW_DIR."/lib/nzb.php");

$page = new Page;
$users = new Users;
$nzb = new Nzb;

if (!$users->isLoggedIn())
	$page->show403();

$binaries = new Binaries;

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
