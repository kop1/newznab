<?php
require_once("config.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/adminpage.php");

$page = new AdminPage();

if (isset($_GET['id']))
{
	$releases = new Releases();
	$releases->deleteComment($_GET['id']);
}

$referrer = $_SERVER['HTTP_REFERER'];
header("Location: " . $referrer);

?>
