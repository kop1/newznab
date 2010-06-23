<?php
require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/nzb.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/category.php");

$page = new Page;
$users = new Users;
$releases = new Releases;
$category = new Category;
$nzb = new NZB;

//
// api functions
//		
$function = "s";
if (isset($_GET["t"]))
{
	if ( $_GET["t"] == "details" || $_GET["t"] == "d")
		$function = "d";
	elseif ( $_GET["t"] == "get" || $_GET["t"] == "g")
		$function = "g";
	elseif ($_GET["t"] == "search" || $_GET["t"] == "s" )
		$function = "s";	
	elseif ($_GET["t"] == "caps" || $_GET["t"] == "c")
		$function = "c";			
	else
		showApiHelp();
}
else
	showApiHelp();


//
// page is accessible only by the apikey, or logged in users.
//
$user="";
$apikey="";
if (!$users->isLoggedIn())
{
	if ($function != "c")
	{
		if (!isset($_GET["apikey"]))
			$page->show403();
		
		$res = $users->getByRssToken($_GET["apikey"]);
		if (!$res)
		{
			echo $_GET["apikey"];die();
			$page->show403();			
		}
		$uid=$res["ID"];
		$apikey=$_GET["apikey"];
	}	
}
else
{
	$uid=$page->userdata["ID"];
	$apikey=$page->userdata["rsstoken"];
}

$page->smarty->assign("uid",$uid);
$page->smarty->assign("rsstoken",$apikey);


//
// output is either json or xml
//
$outputtype = "xml";
if (isset($_GET["o"]))
	if ($_GET["o"] == "json")
		$outputtype = "json";

		
switch ($function)
{
	//
	// search releases
	//
	case "s":
		if (!isset($_GET["q"]) && !isset($_GET["rid"]))
			showApiError("no query/rageid specified");	

		$categoryId = array();
		if (isset($_GET["cat"]))
			$categoryId = explode(",",$_GET["cat"]);
		else
			$categoryId[] = -1;
		
		$limit = 100;
		if (isset($_GET["limit"]) && is_numeric($_GET["limit"]) && $_GET["limit"] < 100)
			$limit = $_GET["limit"];
		
		if (isset($_GET["q"]))
			$reldata = $releases->search($_GET["q"], $categoryId, $limit);
		else
			$reldata = $releases->searchbyRageId($_GET["rid"], (isset($_GET["season"]) ? $_GET["season"] : "")
											, (isset($_GET["ep"]) ? $_GET["ep"] : ""));
				
		if ($outputtype == "xml")
		{
			$page->smarty->assign('releases',$reldata);
			header("Content-type: text/xml");
			echo $page->smarty->fetch('apiresult.tpl');	
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
			header("Location:".WWW_TOP."/getnzb.php?i=".$uid."&r=".$apikey."&id=".$reldata["guid"]);
		}
		else
		{
			showApiError("nzb not found");
		}
		break;		
		
	//
	// get individual nzb details
	//
	case "d":
		if (!isset($_GET["id"]))
			showApiError("no id specified");

		$data = $releases->getByGuid($_GET["id"]);
		
		if ($data)
			$reldata[] = $data;
		else
			$reldata = array();
			
		if ($outputtype == "xml")
		{
			$page->smarty->assign('releases',$reldata);
			header("Content-type: text/xml");
			echo $page->smarty->fetch('apidetail.tpl');
		}	
		else
			echo json_encode($data); //TODO:make that a more specific array of data to return rather than resultset
			
		break;
		
	//
	// capabilities request
	//
	case "c":
		$parentcatlist = $category->getForMenu();
		$page->smarty->assign('parentcatlist',$parentcatlist);
		header("Content-type: text/xml");
		echo $page->smarty->fetch('caps.tpl');	
		break;		
	
	default:
		showApiError("no api function declared");
		break;
}		

function showApiError($err, $errcode=-1)
{
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
	echo "<error code=\"$errcode\" description=\"$err\"/>";
	echo "</xml>";
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
	die();
}

?>