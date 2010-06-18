<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/releases.php");

$page = new Page;
$users = new Users;
$releases = new Releases;

if (!$users->isLoggedIn())
	$page->show403();
	
if (isset($_GET["id"]))
{
	$movie = $releases->getMovieInfo($_GET['id']);
	
	if (!$movie)
		$page->show404();
	
	$page->smarty->assign('movie', $movie);

	$page->title = "Info for ".$movie['title'];
	$page->meta_title = "";
	$page->meta_keywords = "";
	$page->meta_description = "";

	$modal = false;
	if (isset($_GET['modal'])) 
	{
		$modal = true;
		$page->smarty->assign('modal', true);
	}
	
	$page->content = $page->smarty->fetch('viewmovie.tpl');

	if ($modal)
		echo $page->content;
	else
		$page->render();
}

?>
