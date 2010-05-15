<?php

require_once($_SERVER['DOCUMENT_ROOT']."/lib/tvrage.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/adminpage.php");

$page = new AdminPage();

if (isset($_GET['id']))
{
	$tvrage = new TvRage();
	$tvrage->delete($_GET['id']);
}

$referrer = $_SERVER['HTTP_REFERER'];
header("Location: " . $referrer);

?>
