<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/page.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/users.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/releases.php");

$page = new Page;
$users = new Users;

if (!$users->isLoggedIn())
	$page->show403();

if (isset($_GET["id"]))
{
	$releases = new Releases;
	$data = $releases->getByGuid($_GET["id"]);

	if (!$data)
		$page->show404();

	if ($page->isPostBack())
			$releases->addComment($data["ID"], $_POST["txtAddComment"], $users->currentUserId(), $_SERVER['REMOTE_ADDR']); 
	
	$comments = $releases->getComments($data["ID"]);
	$similars = $releases->searchSimilar($data["searchname"]);

	$page->smarty->assign('release',$data);
	$page->smarty->assign('comments',$comments);
	$page->smarty->assign('similars',$similars);

	$page->meta_title = "View NZB";
	$page->meta_keywords = "view,nzb,description,details";
	$page->meta_description = "View NZB for".$data["searchname"] ;
	
	$page->content = $page->smarty->fetch('viewnzb.tpl');
	$page->render();
}

?>