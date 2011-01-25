<?php
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/category.php");

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

$invitedby = '';
if ($data["invitedby"] != "")	
	$invitedby = $users->getById($data["invitedby"]);
	
$page->smarty->assign('userinvitedby',$invitedby);
$page->smarty->assign('user',$data);

$commentcount = $releases->getCommentCountForUser($userid);
$offset = isset($_REQUEST["offset"]) ? $_REQUEST["offset"] : 0;
$page->smarty->assign('pagertotalitems',$commentcount);
$page->smarty->assign('pageroffset',$offset);
$page->smarty->assign('pageritemsperpage',ITEMS_PER_PAGE);
$page->smarty->assign('pagerquerybase', "/profile?id=".$userid."&offset=");
$page->smarty->assign('pagerquerysuffix', "#comments");

$pager = $page->smarty->fetch($page->getCommonTemplate("pager.tpl"));
$page->smarty->assign('pager', $pager);

$commentslist = $releases->getCommentsForUserRange($userid, $offset, ITEMS_PER_PAGE);
$page->smarty->assign('commentslist',$commentslist);	

$exccats = $users->getCategoryExclusionNames($userid);
$page->smarty->assign('exccats', implode(",", $exccats));

$page->meta_title = "View User Profile";
$page->meta_keywords = "view,profile,user,details";
$page->meta_description = "View User Profile for ".$data["username"] ;

$page->content = $page->smarty->fetch('profile.tpl');
$page->render();

?>
