<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/page.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/users.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/binaries.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/nzb.php");

$page = new Page;
$users = new Users;
$nzb = new Nzb;

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

if ($page->isPostBack())
{
	$nzbdata = $nzb->getNZB($_POST);
	$page->smarty->assign('binaries',$nzbdata);

	header("Content-type: text/xml");
	header("Content-Disposition: attachment; filename=nzbfileparts.nzb");

	echo $page->smarty->fetch('nzb.tpl');
	die();
}


$page->smarty->assign('results', $results);

$page->content = $page->smarty->fetch('searchraw.tpl');
$page->render();

?>