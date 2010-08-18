<?php

require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");
require_once(WWW_DIR."/lib/releases.php");

$page = new Page;
$users = new Users;

if (!$users->isLoggedIn())
	$page->show403();

if (isset($_GET["add"]))
{
	$releases = new Releases;
	$data = $releases->getByGuid($_GET["add"]);

	if (!$data)
		$page->show404();
	
	$users->addCart($users->currentUserId(), $data["ID"]);
}
else
{
	if (isset($_GET["delete"]))
		$users->delCart($_GET["delete"], $users->currentUserId());
	
	$page->meta_title = "My Nzb Cart";
	$page->meta_keywords = "search,add,to,cart,nzb,description,details";
	$page->meta_description = "Manage Your Nzb Cart";
	
	$results = $users->getCart($users->currentUserId());
	$page->smarty->assign('results', $results);
	
	$page->content = $page->smarty->fetch('cart.tpl');
	$page->render();
}


?>
