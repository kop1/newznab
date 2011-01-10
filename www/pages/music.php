<?php
require_once(WWW_DIR."/lib/music.php");
require_once(WWW_DIR."/lib/category.php");

$music = new Music;
$cat = new Category;

if (!$users->isLoggedIn())
	$page->show403();


$musiccats = $cat->getChildren(Category::CAT_PARENT_MUSIC);
$mtmp = array();
foreach($musiccats as $mcat) {
	$mtmp[$mcat['ID']] = $mcat;
}
$category = Category::CAT_PARENT_MUSIC;
if (isset($_REQUEST["t"]) && array_key_exists($_REQUEST['t'], $mtmp))
	$category = $_REQUEST["t"] + 0;
	
$catarray = array();
$catarray[] = $category;	

$page->smarty->assign('catlist', $mtmp);
$page->smarty->assign('category', $category);

$browsecount = $music->getMusicCount($catarray, -1, $page->userdata["categoryexclusions"]);

$offset = (isset($_REQUEST["offset"]) && ctype_digit($_REQUEST['offset'])) ? $_REQUEST["offset"] : 0;
$ordering = $music->getMusicOrdering();
$orderby = isset($_REQUEST["ob"]) && in_array($_REQUEST['ob'], $ordering) ? $_REQUEST["ob"] : '';

$results = $musics = array();
$results = $music->getMusicRange($catarray, $offset, ITEMS_PER_PAGE, $orderby, -1, $page->userdata["categoryexclusions"]);
foreach($results as $result) {
	//$result['genre'] = $music->makeFieldLinks($result, 'genre');
	
	$musics[] = $result;
}

$artist = (isset($_REQUEST['artist']) && !empty($_REQUEST['artist'])) ? stripslashes($_REQUEST['artist']) : '';
$page->smarty->assign('artist', $artist);

$genres = $music->getGenres();
$genre = (isset($_REQUEST['genre']) && array_key_exists($_REQUEST['genre'], $genres)) ? $_REQUEST['genre'] : '';
$page->smarty->assign('genres', $genres);
$page->smarty->assign('genre', $genre);

$years = range(1950, (date("Y")+1));
rsort($years);
$year = (isset($_REQUEST['year']) && in_array($_REQUEST['year'], $years)) ? $_REQUEST['year'] : '';
$page->smarty->assign('years', $years);
$page->smarty->assign('year', $year);

$browseby_link = "";
//$browseby_link = '&amp;title='.$title.'&amp;actors='.$actors.'&amp;director='.$director.'&amp;rating='.$rating.'&amp;genre='.$genre.'&amp;year='.$year;

$page->smarty->assign('pagertotalitems',$browsecount);
$page->smarty->assign('pageroffset',$offset);
$page->smarty->assign('pageritemsperpage',ITEMS_PER_PAGE);
$page->smarty->assign('pagerquerybase', WWW_TOP."/music?t=".$category.$browseby_link."&amp;ob=".$orderby."&amp;offset=");
$page->smarty->assign('pagerquerysuffix', "#results");

$pager = $page->smarty->fetch($page->getCommonTemplate("pager.tpl"));
$page->smarty->assign('pager', $pager);

if ($category == -1)
	$page->smarty->assign("catname","All");			
else
{
	$cat = new Category();
	$cdata = $cat->getById($category);
	if ($cdata)
		$page->smarty->assign('catname',$cdata["title"]);			
	else
		$page->show404();
}

foreach($ordering as $ordertype) 
	$page->smarty->assign('orderby'.$ordertype, WWW_TOP."/music?t=".$category.$browseby_link."&amp;ob=".$ordertype."&amp;offset=0");

$page->smarty->assign('results',$musics);		

$page->meta_title = "Browse Albums";
$page->meta_keywords = "browse,nzb,albums,description,details";
$page->meta_description = "Browse for Albums";
	
$page->content = $page->smarty->fetch('music.tpl');
$page->render();

?>
