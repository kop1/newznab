<?php

if (!$users->isLoggedIn())
	$page->show403();

if (!isset($_COOKIE['sabnzbd_'.$users->currentUserId().'__host']))
	$page->show403();

if (!isset($_COOKIE['sabnzbd_'.$users->currentUserId().'__apikey']))
	$page->show403();
	
$server = $_COOKIE['sabnzbd_'.$users->currentUserId().'__host'];
$key = $_COOKIE['sabnzbd_'.$users->currentUserId().'__apikey'];
$priority = $_COOKIE['sabnzbd_'.$users->currentUserId().'__priority'];

$guid = $_GET["id"];
$uid = $users->currentUserId();

$fullsaburl = $server. "api/?mode=addurl&priority=".$priority."&apikey=" . $key;
$nzburl = $page->serverurl."getnzb/" . $guid . "&i=" . $uid . "&r=" . $page->userdata["rsstoken"];
$fullsaburl = $fullsaburl."&name=".urldecode($nzburl);
$json = file_get_contents($fullsaburl);


?>