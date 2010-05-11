<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/page.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/users.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/releases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/category.php");

$page = new Page;
$users = new Users;
$category = new Category;

//
// user has to either be logged in, or using magic token
//

//
// if no content id provided then show user the rss selection page
//
if (!isset($_GET["t"]))
{
	//
	// must be logged in to view this help page
	//
	if (!$users->isLoggedIn())
		$page->show403();
		
	$page->title = "Rss Feeds";
	$page->meta_title = "Rss Nzb Feeds";
	$page->meta_keywords = "view,nzb,description,details,rss,atom";
	$page->meta_description = "View available Rss Nzb feeds.";
	
	$categorylist = $category->get();
	$page->smarty->assign('categorylist',$categorylist);
	
	$page->content = $page->smarty->fetch('rssdesc.tpl');
	$page->render();
	
}
//
// user requested a feed, ensure either logged in or passing a valid token
//
else
{
	if (!$users->isLoggedIn())
	{
		if (!isset($_GET["i"]) || !isset($_GET["r"]))
			$page->show403();
	
		$res = $users->getByIdAndRssToken($_GET["i"], $_GET["r"]);
		if (!$res)
			$page->show403();
	}
	
	//
	// valid or logged in user, get them the requested feed
	//
	if (isset($_GET["dl"]) && $_GET["dl"] = "1")
		$page->smarty->assign('dl',"1");
	
	$usercat = -1;
	if (isset($_GET["t"]))
		$usercat = $_GET["t"]+0;
		
	$usernum = 100;
	if (isset($_GET["num"]))
		$usernum = $_GET["num"]+0;		

	$releases = new Releases;
	$reldata = $releases->getRss($usercat, $usernum);
	$page->smarty->assign('releases',$reldata);
	header("Content-type: text/xml");
	echo $page->smarty->fetch('rss.tpl');

}

?>