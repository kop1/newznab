<?php
require_once("config.php");
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");

$users = new Users;
$page = new Page;

if (!$users->isLoggedIn())
	$page->show403();

if (isset($_GET['action']) && $_GET['action'] == "1" && isset($_GET['emailto']))
{
	$emailto = $_GET['emailto'];
	$users->sendInvite($page->site->title, $page->site->email, $page->serverurl, $users->currentUserId(), $emailto);
	print "Invite sent.";
}
else
{
	print "Invite not sent.";
}
