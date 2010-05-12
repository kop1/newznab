<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/page.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/users.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/releases.php");
define("ITEMS_PER_PAGE", "25");

$page = new Page;
$users = new Users;
$releases = new Releases;

if (!$users->isLoggedIn())
	$page->show403();

$userid = 0;
if (isset($_GET["id"]))
	$userid = $_GET["id"] + 0;
elseif (isset($_GET["name"]))
{
	$res = $users->getByUsername($_GET["name"]);
	if ($res)
		$userid = $res["ID"];
}
else
	$userid = $users->currentUserId();

$data = $users->getById($userid);
if (!$data)
	$page->show404();

$page->smarty->assign('user',$data);

$commentcount = $releases->getCommentCountForUser($userid);
$offset = isset($_REQUEST["offset"]) ? $_REQUEST["offset"] : 0;
$page->smarty->assign('pagertotalitems',$commentcount);
$page->smarty->assign('pageroffset',$offset);
$page->smarty->assign('pageritemsperpage',ITEMS_PER_PAGE);
$page->smarty->assign('pagerquerybase', "/profile?id=".$userid."&offset=");
$page->smarty->assign('pagerquerysuffix', "#comments");

$pager = $page->smarty->fetch("pager.tpl");
$page->smarty->assign('pager', $pager);

$commentslist = $releases->getCommentsForUserRange($userid, $offset, ITEMS_PER_PAGE);
$page->smarty->assign('commentslist',$commentslist);	

$page->meta_title = "View User Profile";
$page->meta_keywords = "view,profile,user,details";
$page->meta_description = "View User Profile for ".$data["username"] ;

$page->content = $page->smarty->fetch('profile.tpl');
$page->render();

?>