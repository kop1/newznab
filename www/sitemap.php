<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/sitemaps.php");

$page = new Page();
$te = $page->smarty;

$arPages = array();

$arPages[] = buildURL("Home", "Home Page", "/", 'daily', '1.0');


//
// useful links
//
$contents = new Contents();
$contentlist =  $contents->getForMenuByType(Contents::TYPEUSEFUL);
foreach ($contentlist as $content)
{
	$arPages[] = buildURL("Useful Links", $content->title, $content->url."c".$content->id, 'monthly', '0.50');	
}

//
// articles
//
$contentlist =  $contents->getForMenuByType(Contents::TYPEARTICLE);
foreach ($contentlist as $content)
{
	$arPages[] = buildURL("Articles", $content->title, $content->url."c".$content->id, 'monthly', '0.50');	
}

//
// static pages
//
$arPages[] = buildURL("Useful Links", "Contact Us", "/contact-us.php", 'yearly', '0.30');	
$arPages[] = buildURL("Useful Links", "Site Map", "/sitemap.php", 'weekly', '0.50');	
$arPages[] = buildURL("Useful Links", "Rss Feeds", "/rss.php", 'weekly', '0.50');	

$arPages[] = buildURL("Nzb", "Search Nzb", "/search/", 'weekly', '0.50');	
$arPages[] = buildURL("Nzb", "Browse Nzb", "/browse/", 'daily', '0.80');	



//
// echo appropriate site map
//
asort($arPages);
$page->smarty->assign('sitemaps',$arPages);	

if (isset($_GET["type"]) && $_GET["type"] == "xml")
{
	echo $page->smarty->fetch('sitemap-xml.tpl');
}
else
{
	$page->title = $page->site->title. " site map";
	$page->meta_title = $page->site->title. " site map";
	$page->meta_keywords = "sitemap,site,map";
	$page->meta_description = $page->site->title." site map shows all our pages.";
	$page->content = $page->smarty->fetch('sitemap.tpl');
	$page->render();
}

function buildURL($type, $name, $url, $freq='daily', $p='1.0')
{
	$s = new Sitemap($type, $name, $url, $freq, $p);
	return $s;
}

?>







