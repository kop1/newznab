<?php

if (!$users->isLoggedIn())
	$page->show403();

$uid = $users->currentUserId();

if (!isset($_COOKIE['sabnzbd_'.$uid.'__host']))
	$page->show403();

if (!isset($_COOKIE['sabnzbd_'.$uid.'__apikey']))
	$page->show403();
	
$server = $_COOKIE['sabnzbd_'.$uid.'__host'];
$key = $_COOKIE['sabnzbd_'.$uid.'__apikey'];
$priority = $_COOKIE['sabnzbd_'.$uid.'__priority'];

$guid = $_GET["id"];

$fullsaburl = $server. "api/?mode=addurl&priority=".$priority."&apikey=" . $key;
$nzburl = $page->serverurl."getnzb/" . $guid . "&i=" . $uid . "&r=" . $page->userdata["rsstoken"];
$fullsaburl = $fullsaburl."&name=".urlencode($nzburl);
$json = file_get_contents($fullsaburl);


?>