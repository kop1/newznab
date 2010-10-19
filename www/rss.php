<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/category.php");

$page = new Page;
$users = new Users;
$category = new Category;

//
// user has to either be logged in, or using rsskey
//

//
// if no content id provided then show user the rss selection page
//
if (!isset($_GET["t"]) && !isset($_GET["rage"]))
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
	
	$categorylist = $category->get(true, $page->userdata["categoryexclusions"]);
	$page->smarty->assign('categorylist',$categorylist);
	
	$parentcategorylist = $category->getForMenu($page->userdata["categoryexclusions"]);
	$page->smarty->assign('parentcategorylist',$parentcategorylist);

	$page->content = $page->smarty->fetch('rssdesc.tpl');
	$page->render();
	
}
//
// user requested a feed, ensure either logged in or passing a valid token
//
else
{
	$uid = -1;
	$rsstoken = -1;
	if (!$users->isLoggedIn())
	{
		if (!isset($_GET["i"]) || !isset($_GET["r"]))
			$page->show403();
	
		$res = $users->getByIdAndRssToken($_GET["i"], $_GET["r"]);
		if (!$res)
			$page->show403();
		
		$uid = $_GET["i"];
		$rsstoken = $_GET["r"];
	}
	else
	{
		$uid = $page->userdata["ID"];
		$rsstoken = $page->userdata["rsstoken"];
	}

	//
	// valid or logged in user, get them the requested feed
	//
	if (isset($_GET["dl"]) && $_GET["dl"] = "1")
		$page->smarty->assign("dl","1");
	
	$usercat = -1;
	if (isset($_GET["t"]))
		$usercat = ($_GET["t"]==0 ? -1 : $_GET["t"]+0);
		
	$userrage = -1;
	if (isset($_GET["rage"]))
		$userrage = ($_GET["rage"]==0 ? -1 : $_GET["rage"]+0);

	$usernum = 100;
	if (isset($_GET["num"]))
		$usernum = $_GET["num"]+0;		

		
	$page->smarty->assign('uid',$uid);		
	$page->smarty->assign('rsstoken',$rsstoken);		
		
	$releases = new Releases;
	$reldata = $releases->getRss($usercat, $usernum, $uid, $userrage);
	$page->smarty->assign('releases',$reldata);
	header("Content-type: text/xml");
	echo $page->smarty->fetch('rss.tpl');

}

?>
