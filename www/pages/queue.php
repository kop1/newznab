<?php

if (!$users->isLoggedIn())
	$page->show403();

if (!isset($_COOKIE['sabnzbd_'.$users->currentUserId().'__apikey']) || $_COOKIE['sabnzbd_'.$users->currentUserId().'__apikey'] == "")
	$page->show404();
	
$page->smarty->assign('sabserver',$_COOKIE['sabnzbd_'.$users->currentUserId().'__host']);	

$page->title = "Your Download Queue";
$page->meta_title = "View Sabnzbd Queue";
$page->meta_keywords = "view,sabznbd,queue";
$page->meta_description = "View Sabnzbd Queue";

$page->content = $page->smarty->fetch('viewqueue.tpl');
$page->render();

?>