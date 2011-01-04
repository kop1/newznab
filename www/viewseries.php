<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/tvrage.php");

$page = new Page;
$users = new Users;
$releases = new Releases;
$tvrage = new TvRage;

if (!$users->isLoggedIn())
	$page->show403();
	
if (isset($_GET["id"]) && ctype_digit($_GET['id']))
{

	$rel = $releases->searchbyRageId($_GET["id"]);
	$rage = $tvrage->getByRageID($_GET['id']);
	
	if (!$rel || !$rage)
		$page->show404();
	
	//sort releases by season, episode
	$season = $episode = array();
	foreach($rel as $rlk=>$rlv) {
		$season[$rlk] = $rlv['season'];
		$episode[$rlk] = $rlv['episode'];
	}
	array_multisort($season, SORT_DESC, $episode, SORT_DESC, $rel);
	$page->smarty->assign('rel', $rel);
	
	$page->smarty->assign('rage', $rage);
	
	//get series name(s) and description
	$seriesnames = $seriesdescription = array();
	foreach($rage as $r) {
		$seriesnames[] = $r['releasetitle'];
		if (!empty($r['description'])) {
			$seriesdescription[] = $r['description'];
		}
	}
	$seriesnames = implode('/', array_map("trim", $seriesnames));
	$page->smarty->assign('seriesnames', $seriesnames);
	$page->smarty->assign('seriesdescription', array_shift($seriesdescription));

	$page->title = "$seriesnames";
	$page->meta_title = "View TV Series $seriesnames";
	$page->meta_keywords = "view,series,tv,show,description,details";
	$page->meta_description = "View $seriesnames Series";
	
	$page->content = $page->smarty->fetch('viewseries.tpl');
	$page->render();
}

?>
