<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/page.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/nzb.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/releases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/users.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/category.php");

$page = new Page;
$users = new Users;
$releases = new Releases;
$category = new Category;
$nzb = new NZB;

//
// page is accessible only by the site apikey, or logged in users.
//
if (!$users->isLoggedIn())
{
	if (!isset($_GET["k"]) || $page->site->apikey != $_GET["k"])
		$page->show403();
}

if (isset($_GET["dl"]) && $_GET["dl"] = "1")
	$page->smarty->assign("dl","1");

$page->smarty->assign("k",$page->site->apikey);


//
// output is either json or xml
//
$outputtype = "xml";
if (isset($_GET["o"]))
	if ($_GET["o"] == "json")
		$outputtype = "json";

//
// api functions, extend this, 
// currently search (s) or individual (i)
//		
$function = "s";
if (isset($_GET["t"]))
{
	if ($_GET["t"] == "i")
		$function = "i";
	elseif ($_GET["t"] == "g")
		$function = "g";
	elseif ($_GET["t"] == "s")
		$function = "s";	
	elseif ($_GET["t"] == "c")
		$function = "c";			
	else
		showApiHelp();
}
else
	showApiHelp();
		
switch ($function)
{
	//
	// search releases
	//
	case "s":
		if (!isset($_GET["q"]) && !isset($_GET["rid"]))
			showApiError("no query/rageid specified");	
			
		if (isset($_GET["q"]))
			$reldata = $releases->search($_GET["q"]);
		else
			$reldata = $releases->searchbyRageId($_GET["rid"], (isset($_GET["season"]) ? $_GET["season"] : "")
											, (isset($_GET["ep"]) ? $_GET["ep"] : ""));
				
		if ($outputtype == "xml")
		{
			$page->smarty->assign('releases',$reldata);
			header("Content-type: text/xml");
			echo $page->smarty->fetch('rss.tpl');	
		}
		else
		{
			echo json_encode($reldata);//TODO:make that a more specific array of data to return rather than resultset
		}
		break;
	
	//
	// get nzb
	//
	case "g":
		if (!isset($_GET["id"]) && !isset($_GET["rid"]))
			showApiError("no id/rageid specified");

		if (isset($_GET["id"]))
			$reldata = $releases->getByGuid($_GET["id"]);
		else
			$reldata = $releases->getbyRageId($_GET["rid"], (isset($_GET["season"]) ? $_GET["season"] : "")
											, (isset($_GET["ep"]) ? $_GET["ep"] : ""));

		if ($reldata)
		{
			$releases->updateGrab($_GET["id"]);
			
			$nzbdata = $nzb->getNZBforRelease($_GET["id"]);
			$page->smarty->assign('binaries',$nzbdata);

			header("Content-type: text/xml");
			header("X-DNZB-Name: ".$reldata["searchname"]);
			header("X-DNZB-Category: ".$reldata["category_name"]);
			header("X-DNZB-MoreInfo: "); //TODO:
			header("X-DNZB-NFO: "); //TODO:
			header("Content-Disposition: attachment; filename=".$reldata["searchname"].".nzb");

			echo $page->smarty->fetch('nzb.tpl');
		}
		else
		{
			showApiError("nzb not found");
		}
		break;		
		
	//
	// get individual nzb info
	//
	case "i":
		if (!isset($_GET["id"]) && !isset($_GET["rid"]))
			showApiError("no id/rageid specified");

		if (isset($_GET["id"]))
			$data = $releases->getByGuid($_GET["id"]);
		else
			$data = $releases->getbyRageId($_GET["rid"], (isset($_GET["season"]) ? $_GET["season"] : "")
											, (isset($_GET["ep"]) ? $_GET["ep"] : ""));
		
		if ($data)
			$reldata[] = $data;
		else
			$reldata = array();
			
		if ($outputtype == "xml")
		{
			$page->smarty->assign('releases',$reldata);
			header("Content-type: text/xml");
			echo $page->smarty->fetch('rss.tpl');
		}	
		else
			echo json_encode($data); //TODO:make that a more specific array of data to return rather than resultset
			
		break;
		
	//
	// capabilities request
	//
	case "c":
		$cats = $category->getFlat(true);
		$page->smarty->assign('cats',$cats);
		header("Content-type: text/xml");
		echo $page->smarty->fetch('caps.tpl');	
		break;		
	
	default:
		showApiError("no api function declared");
		break;
}		

function showApiError($err)
{
	echo $err. " - check /api for list of available parameters";
	die();
}

function showApiHelp()
{
	global $page;
	$page->title = "Api";
	$page->meta_title = "Api Help Topics";
	$page->meta_keywords = "view,nzb,api,details,help,json,rss,atom";
	$page->meta_description = "View description of the site Nzb Api.";
	
	$page->content = $page->smarty->fetch('apidesc.tpl');
	$page->render();
}

?>