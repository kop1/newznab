<?php
require_once(WWW_DIR."/lib/util.php");

if (!$users->isLoggedIn())
	$page->show403();

if (!isset($_COOKIE['sabnzbd_'.$users->currentUserId().'__apikey']) || $_COOKIE['sabnzbd_'.$users->currentUserId().'__apikey'] == "")
	$page->show404();

$key = $_COOKIE['sabnzbd_'.$users->currentUserId().'__apikey'];
$server = $_COOKIE['sabnzbd_'.$users->currentUserId().'__host'];	

if (isset($_REQUEST["del"]))
{
	getUrl($server."api/?mode=queue&name=delete&value=".$_REQUEST["del"]."&apikey=".$key);
}

$page->smarty->assign('sabserver',$server);	
$page->title = "Your Download Queue";
$page->meta_title = "View Sabnzbd Queue";
$page->meta_keywords = "view,sabznbd,queue";
$page->meta_description = "View Sabnzbd Queue";

$page->content = $page->smarty->fetch('viewqueue.tpl');
$page->render();

?>