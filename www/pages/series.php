<?php
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/tvrage.php");

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
	foreach($rel as $rlk=>$rlv)
	{
		$season[$rlk] = $rlv['season'];
		$episode[$rlk] = $rlv['episode'];
	}
	array_multisort($season, SORT_DESC, $episode, SORT_DESC, $rel);
	
	$seasons = array();
	foreach ($rel as $r)
		$seasons[$r['season']][$r['episode']][] = $r;
	
	$page->smarty->assign('seasons', $seasons);
	$page->smarty->assign('rage', $rage);
	
	//get series name(s) and description
	$seriesnames = $seriesdescription = $country = $genre = array();
	foreach($rage as $r)
	{
		$seriesnames[] = $r['releasetitle'];
		if (!empty($r['description']))
			$seriesdescription[] = $r['description'];
		
		if (!empty($r['country']))
			$country[] = $r['country'];
			
		if (!empty($r['genre']))
			$genre[] = str_replace('|', ' - ', $r['genre']);
	}
	$seriesnames = implode('/', array_map("trim", $seriesnames));
	$page->smarty->assign('seriesnames', $seriesnames);
	$page->smarty->assign('seriesdescription', array_shift($seriesdescription));
	$page->smarty->assign('seriescountry', array_shift($country));
	$page->smarty->assign('seriesgenre', array_shift($genre));

	$page->title = "$seriesnames";
	$page->meta_title = "View TV Series $seriesnames";
	$page->meta_keywords = "view,series,tv,show,description,details";
	$page->meta_description = "View $seriesnames Series";
	
	$page->content = $page->smarty->fetch('viewseries.tpl');
	$page->render();
}

?>
