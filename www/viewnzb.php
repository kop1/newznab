<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/tvrage.php");

$page = new Page;
$users = new Users;

if (!$users->isLoggedIn())
	$page->show403();

if (isset($_GET["id"]))
{
	$releases = new Releases;
	$tvrage = new TvRage;
	$data = $releases->getByGuid($_GET["id"]);

	if (!$data)
		$page->show404();

	if ($page->isPostBack())
			$releases->addComment($data["ID"], $_POST["txtAddComment"], $users->currentUserId(), $_SERVER['REMOTE_ADDR']); 
	
	$nfo = $releases->getReleaseNfo($data["ID"], false);
	$comments = $releases->getComments($data["ID"]);
	$similars = $releases->searchSimilar($data["ID"], $data["searchname"]);
	if ($data["rageID"] != "")
	{
		$rage = $tvrage->getByRageID($data["rageID"]);
		if (count($rage) > 0)
			$page->smarty->assign('rage',$rage[0]);
	}
	
	$mov = '';
	if ($data['imdbID'] != '') {
		require_once(WWW_DIR."/lib/movie.php");
		$movie = new Movie();
		$mov = $movie->getMovieInfo($data['imdbID']);
	}
	
	$page->smarty->assign('release',$data);
	$page->smarty->assign('nfo',$nfo);
	$page->smarty->assign('movie',$mov);
	$page->smarty->assign('comments',$comments);
	$page->smarty->assign('similars',$similars);
	$page->smarty->assign('searchname',$releases->getSimilarName($data['searchname']));

	$page->meta_title = "View NZB";
	$page->meta_keywords = "view,nzb,description,details";
	$page->meta_description = "View NZB for".$data["searchname"] ;
	
	$page->content = $page->smarty->fetch('viewnzb.tpl');
	$page->render();
}

?>
