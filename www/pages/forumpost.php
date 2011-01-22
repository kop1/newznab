<?php
require_once(WWW_DIR."/lib/forum.php");

if (!$users->isLoggedIn())
	$page->show403();

$id = $_GET["id"] + 0;

$forum = new Forum();
if ($page->isPostBack())
		$forum->add($id, $users->currentUserId(), "", $_POST["addReply"]); 

$results = $forum->getPosts($id);
if (count($results) == 0)
	$page->show404();

$page->meta_title = "Forum Post";
$page->meta_keywords = "view,forum,post,thread";
$page->meta_description = "View forum post";

$page->smarty->assign('results', $results);

$page->content = $page->smarty->fetch('forumpost.tpl');
$page->render();


?>
