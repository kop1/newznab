<?php

require_once($_SERVER['DOCUMENT_ROOT']."/lib/content.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/adminpage.php");

$page = new AdminPage();

if (isset($_GET['id']))
{
	$users = new Users();
	$users->delete($_GET['id']);
}

$referrer = $_SERVER['HTTP_REFERER'];
header("Location: " . $referrer);

?>
