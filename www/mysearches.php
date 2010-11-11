<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/releases.php");

$page = new Page;
$users = new Users;

if (!$users->isLoggedIn())
	$page->show403();

$page->meta_title = "My Searches";
$page->meta_keywords = "TODO";
$page->meta_description = "Manage My Searches";

if (isset($_GET['terms']) && isset($_GET['category']))
{
  // do stuff
}
else
{
  // Fetch user searches
}

$page->content = $page->smarty->fetch('mysearches.tpl');
$page->render();
